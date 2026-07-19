#!/usr/bin/env python3
"""Loopback-only background removal service for product uploads."""

from __future__ import annotations

import io
import json
import logging
import os
import threading
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

import numpy as np
from PIL import Image, ImageOps, UnidentifiedImageError
from rembg import new_session, remove
from scipy import ndimage


HOST = os.environ.get("PRODUCT_BG_HOST", "127.0.0.1")
PORT = int(os.environ.get("PRODUCT_BG_PORT", "7010"))
MODEL_NAME = os.environ.get("PRODUCT_BG_MODEL", "isnet-general-use")
MAX_UPLOAD_BYTES = int(os.environ.get("PRODUCT_BG_MAX_BYTES", str(110 * 1024 * 1024)))
MAX_PIXELS = int(os.environ.get("PRODUCT_BG_MAX_PIXELS", "80000000"))
MAX_INFERENCE_SIDE = int(os.environ.get("PRODUCT_BG_MAX_SIDE", "1200"))

Image.MAX_IMAGE_PIXELS = MAX_PIXELS
logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(message)s")
LOGGER = logging.getLogger("product-background-service")

LOGGER.info("Loading background-removal model %s", MODEL_NAME)
SESSION = new_session(MODEL_NAME)
INFERENCE_LOCK = threading.Lock()
LOGGER.info("Background-removal model is ready")


class ProcessingError(Exception):
    pass


def _has_useful_transparency(image: Image.Image) -> bool:
    if "A" not in image.getbands():
        return False
    alpha = np.asarray(image.getchannel("A"), dtype=np.uint8)
    return float(np.mean(alpha < 250)) >= 0.02


def _validate_foreground(image: Image.Image) -> float:
    alpha = np.asarray(image.getchannel("A"), dtype=np.uint8)
    visible = alpha > 8
    solid = alpha > 200
    visible_ratio = float(np.mean(visible))
    solid_ratio = float(np.mean(solid))

    if visible_ratio < 0.005 or solid_ratio < 0.003:
        raise ProcessingError("No reliable product foreground was detected")
    if visible_ratio > 0.98:
        raise ProcessingError("The background-removal mask covered almost the entire image")

    ys, xs = np.where(visible)
    box_width = int(xs.max() - xs.min() + 1)
    box_height = int(ys.max() - ys.min() + 1)
    if box_width < max(2, image.width // 50) or box_height < max(2, image.height // 50):
        raise ProcessingError("The detected product area is too small")

    return visible_ratio


def _hue_distance(values: np.ndarray, target: float) -> np.ndarray:
    distance = np.abs(values.astype(np.float32) - target)
    return np.minimum(distance, 256.0 - distance)


def _denoise_preserving_edges(image: Image.Image) -> Image.Image:
    """Reduce flat-area sensor noise without softening product edges or text."""
    rgba = np.asarray(image.convert("RGBA"), dtype=np.uint8)
    rgb = rgba[:, :, :3].astype(np.float32)
    alpha = rgba[:, :, 3]
    solid = alpha > 24
    if int(np.count_nonzero(solid)) < 400:
        return image

    luminance = 0.2126 * rgb[:, :, 0] + 0.7152 * rgb[:, :, 1] + 0.0722 * rgb[:, :, 2]
    gradient = np.hypot(
        ndimage.sobel(luminance, axis=0, mode="nearest"),
        ndimage.sobel(luminance, axis=1, mode="nearest"),
    )
    interior = solid & (ndimage.distance_transform_edt(solid) > 3.0)
    if int(np.count_nonzero(interior)) < 400:
        return image

    edge_threshold = max(4.0, float(np.percentile(gradient[interior], 42)))
    flat_weight = np.clip((edge_threshold - gradient) / edge_threshold, 0.0, 1.0)
    flat_weight *= interior.astype(np.float32) * 0.10
    smoothed = ndimage.gaussian_filter(rgb, sigma=(0.42, 0.42, 0.0), mode="nearest")
    cleaned = rgb * (1.0 - flat_weight[:, :, None]) + smoothed * flat_weight[:, :, None]
    output = np.dstack((np.clip(cleaned, 0, 255).astype(np.uint8), alpha))
    return Image.fromarray(output, mode="RGBA")


def _refine_chromatic_background(source: Image.Image, cutout: Image.Image) -> tuple[Image.Image, float]:
    """Remove saturated background that remains visible through product openings."""
    rgb = np.asarray(source.convert("RGB"), dtype=np.uint8)
    alpha = np.asarray(cutout.getchannel("A"), dtype=np.uint8)
    hsv = np.asarray(source.convert("HSV"), dtype=np.uint8)
    hue = hsv[:, :, 0]
    saturation = hsv[:, :, 1]
    value = hsv[:, :, 2]

    confident_background = alpha <= 12
    background_count = int(np.count_nonzero(confident_background))
    chromatic_background = confident_background & (saturation >= 60) & (value >= 30)
    chromatic_count = int(np.count_nonzero(chromatic_background))
    minimum_samples = max(800, int(alpha.size * 0.004))
    if background_count == 0 or chromatic_count < minimum_samples:
        return cutout, 0.0

    sample_hues = hue[chromatic_background]
    histogram, edges = np.histogram(sample_hues, bins=32, range=(0, 256))
    dominant_bin = int(np.argmax(histogram))
    dominant_hue = float((edges[dominant_bin] + edges[dominant_bin + 1]) / 2.0)
    dominant_samples = chromatic_background & (_hue_distance(hue, dominant_hue) <= 18.0)
    dominant_count = int(np.count_nonzero(dominant_samples))
    if dominant_count < minimum_samples or dominant_count / background_count < 0.32:
        return cutout, 0.0

    sample_rgb = rgb[dominant_samples]
    quantized = sample_rgb // 32
    codes = (
        quantized[:, 0].astype(np.int32) * 64
        + quantized[:, 1].astype(np.int32) * 8
        + quantized[:, 2].astype(np.int32)
    )
    counts = np.bincount(codes, minlength=512)
    top_codes = np.argsort(counts)[-16:]
    centers = []
    for code in top_codes:
        if counts[code] == 0:
            continue
        centers.append(sample_rgb[codes == code].mean(axis=0))
    if not centers:
        return cutout, 0.0

    rgb_i16 = rgb.astype(np.int16)
    minimum_distance = np.full(alpha.shape, np.iinfo(np.int32).max, dtype=np.int32)
    for center in centers:
        delta = rgb_i16 - np.rint(center).astype(np.int16)
        distance = np.sum(delta.astype(np.int32) ** 2, axis=2)
        minimum_distance = np.minimum(minimum_distance, distance)

    sample_saturation = saturation[dominant_samples]
    sample_value = value[dominant_samples]
    saturation_floor = max(48, int(np.percentile(sample_saturation, 5) * 0.65))
    value_floor = max(24, int(np.percentile(sample_value, 3) - 35))
    hue_match = _hue_distance(hue, dominant_hue) <= 24.0
    color_match = (
        hue_match
        & (saturation >= saturation_floor)
        & (value >= value_floor)
        & (minimum_distance <= 84 * 84)
    )

    broad_match = (
        (_hue_distance(hue, dominant_hue) <= 27.0)
        & (saturation >= max(42, saturation_floor - 12))
        & (value >= max(22, value_floor - 12))
        & (minimum_distance <= 108 * 108)
    )
    regions = ndimage.binary_closing(
        broad_match,
        structure=np.ones((3, 3), dtype=bool),
        iterations=2,
    )
    labels, region_count = ndimage.label(regions, structure=np.ones((3, 3), dtype=bool))
    boundary_labels = set(
        int(label)
        for label in np.unique(labels[ndimage.binary_dilation(confident_background, iterations=1)])
        if label > 0
    )
    narrow_labels: set[int] = set()

    minimum_major_axis = max(18, int(min(alpha.shape) * 0.02))
    for label, region_slice in enumerate(ndimage.find_objects(labels), start=1):
        if region_slice is None or label in boundary_labels:
            continue
        height = int(region_slice[0].stop - region_slice[0].start)
        width = int(region_slice[1].stop - region_slice[1].start)
        major_axis = max(height, width)
        minor_axis = max(1, min(height, width))
        region_area = int(np.count_nonzero(labels[region_slice] == label))
        elongated = major_axis / minor_axis >= 2.0
        narrow_opening = minor_axis <= max(36, int(min(alpha.shape) * 0.055))
        if (
            elongated
            and narrow_opening
            and major_axis >= minimum_major_axis
            and region_area / alpha.size <= 0.035
        ):
            narrow_labels.add(label)

    boundary_regions = (
        np.isin(labels, np.fromiter(boundary_labels, dtype=np.int32))
        if boundary_labels
        else np.zeros(alpha.shape, dtype=bool)
    )
    narrow_regions = (
        np.isin(labels, np.fromiter(narrow_labels, dtype=np.int32))
        if narrow_labels
        else np.zeros(alpha.shape, dtype=bool)
    )
    distance_from_known_background = ndimage.distance_transform_edt(~confident_background)
    safe_background_depth = max(12.0, min(alpha.shape) * 0.024)
    trusted_boundary = (
        boundary_regions
        & (distance_from_known_background <= safe_background_depth)
        & (alpha <= 176)
    )
    trusted_regions = trusted_boundary | narrow_regions
    remove_mask = trusted_regions & color_match

    # A saturated backdrop can be fully enclosed by fan blades. Use strict
    # chroma thresholds learned from confident background pixels so bright
    # fabric is removed while dark paint and low-saturation reflections remain.
    strict_saturation_floor = max(90, int(np.percentile(sample_saturation, 1) - 30))
    strict_value_floor = max(85, int(np.percentile(sample_value, 1) - 42))
    strict_chroma = (
        hue_match
        & (saturation >= strict_saturation_floor)
        & (value >= strict_value_floor)
        & (minimum_distance <= 145 * 145)
        & trusted_regions
    )
    remove_mask |= strict_chroma

    edge_spill = (
        ndimage.binary_dilation(remove_mask, structure=np.ones((3, 3), dtype=bool), iterations=2)
        & hue_match
        & (saturation >= max(40, saturation_floor - 10))
        & (value >= max(20, value_floor - 10))
        & trusted_regions
        & (minimum_distance <= 104 * 104)
    )
    remove_mask |= edge_spill
    removed_new = remove_mask & (alpha > 12)
    removed_ratio = float(np.mean(removed_new))
    original_foreground = max(1, int(np.count_nonzero(alpha > 12)))
    removed_foreground_ratio = float(np.count_nonzero(removed_new)) / original_foreground
    if removed_ratio > 0.035 or removed_foreground_ratio > 0.08:
        LOGGER.warning(
            "Rejected aggressive chroma mask: image_ratio=%.4f foreground_ratio=%.4f",
            removed_ratio,
            removed_foreground_ratio,
        )
        remove_mask = np.zeros(alpha.shape, dtype=bool)
        removed_ratio = 0.0

    refined_alpha = alpha.copy()
    refined_alpha[remove_mask] = 0

    # Repair only sub-pixel/single-pixel notches in the model matte. Grayscale
    # closing does not grow the silhouette, but it restores small edge chips
    # that become visible as jagged cuts after resizing.
    closed_alpha = ndimage.grey_closing(refined_alpha, size=(3, 3))
    matte_repairs = (closed_alpha > refined_alpha) & (closed_alpha >= 96)
    refined_alpha[matte_repairs] = closed_alpha[matte_repairs]

    # Neutralize color spill reflected by a saturated studio backdrop. This
    # retains texture and alpha on dark product surfaces while removing the
    # blue/green cast that would otherwise remain after compositing on white.
    foreground = refined_alpha > 12
    distance_inside = ndimage.distance_transform_edt(foreground)
    spill_hue_match = _hue_distance(hue, dominant_hue) <= 34.0
    edge_spill_mask = (
        foreground
        & (distance_inside <= 6.0)
        & spill_hue_match
        & (saturation >= 22)
    )
    general_spill_mask = (
        foreground
        & spill_hue_match
        & (saturation >= 42)
    )
    reflected_spill_mask = (
        foreground
        & spill_hue_match
        & (saturation >= 52)
        & (value <= 210)
    )
    sampled_spill_mask = (
        foreground
        & spill_hue_match
        & (saturation >= max(48, saturation_floor - 8))
        & (minimum_distance <= 150 * 150)
    )

    cleaned_hsv = hsv.copy()
    cleaned_saturation = cleaned_hsv[:, :, 1]
    cleaned_saturation[general_spill_mask] = np.minimum(
        cleaned_saturation[general_spill_mask], 28
    )
    cleaned_saturation[reflected_spill_mask] = np.minimum(
        cleaned_saturation[reflected_spill_mask], 22
    )
    cleaned_saturation[sampled_spill_mask] = np.minimum(
        cleaned_saturation[sampled_spill_mask], 16
    )
    cleaned_saturation[edge_spill_mask] = np.minimum(
        cleaned_saturation[edge_spill_mask], 8
    )
    cleaned_rgb = np.asarray(
        Image.fromarray(cleaned_hsv, mode="HSV").convert("RGB"), dtype=np.uint8
    ).copy()
    # Transparent pixels still carry the original backdrop RGB. Neutralizing
    # them prevents blue/green color from bleeding back into fine edges when
    # the PNG is resized and composited onto the white product canvas.
    cleaned_rgb[refined_alpha <= 12] = 255

    refined = Image.fromarray(np.dstack((cleaned_rgb, refined_alpha)))
    return refined, removed_ratio


def process_image(payload: bytes, safe_mask: bool = False) -> tuple[bytes, str, float]:
    try:
        with Image.open(io.BytesIO(payload)) as opened:
            width, height = opened.size
            if width <= 0 or height <= 0 or width * height > MAX_PIXELS:
                raise ProcessingError("Image dimensions are not supported")
            image = ImageOps.exif_transpose(opened).convert("RGBA")
            image.load()
    except (UnidentifiedImageError, OSError, ValueError) as exc:
        raise ProcessingError("Uploaded data is not a supported image") from exc

    image.thumbnail((MAX_INFERENCE_SIDE, MAX_INFERENCE_SIDE), Image.Resampling.LANCZOS)

    if _has_useful_transparency(image):
        result = image
        action = "kept-transparent"
        foreground_ratio = _validate_foreground(result)
    else:
        with INFERENCE_LOCK:
            if safe_mask:
                result = remove(
                    image.convert("RGB"),
                    session=SESSION,
                    alpha_matting=False,
                    post_process_mask=True,
                )
            else:
                result = remove(
                    image.convert("RGB"),
                    session=SESSION,
                    alpha_matting=True,
                    alpha_matting_foreground_threshold=240,
                    alpha_matting_background_threshold=10,
                    alpha_matting_erode_size=1,
                )
        if not isinstance(result, Image.Image):
            raise ProcessingError("Background-removal engine returned invalid data")
        result = result.convert("RGBA")
        result, refined_ratio = _refine_chromatic_background(image, result)
        result = _denoise_preserving_edges(result)
        foreground_ratio = _validate_foreground(result)
        action_prefix = "safe-removed" if safe_mask else "removed"
        action = action_prefix + "+chroma" if refined_ratio > 0 else action_prefix

    output = io.BytesIO()
    result.save(output, format="PNG", optimize=False)
    return output.getvalue(), action, foreground_ratio


class Handler(BaseHTTPRequestHandler):
    server_version = "CoolingProductBackground/1.0"

    def _send_json(self, status: int, data: dict[str, object]) -> None:
        payload = json.dumps(data, ensure_ascii=True).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(payload)))
        self.send_header("Cache-Control", "no-store")
        self.end_headers()
        self.wfile.write(payload)

    def do_GET(self) -> None:
        if self.path != "/health":
            self._send_json(404, {"ok": False, "error": "not_found"})
            return
        self._send_json(200, {"ok": True, "model": MODEL_NAME})

    def do_POST(self) -> None:
        request_path = self.path.split("?", 1)[0]
        if request_path not in ("/remove", "/remove-safe"):
            self._send_json(404, {"ok": False, "error": "not_found"})
            return

        try:
            content_length = int(self.headers.get("Content-Length", "0"))
        except ValueError:
            content_length = 0
        if content_length <= 0 or content_length > MAX_UPLOAD_BYTES:
            self._send_json(413, {"ok": False, "error": "invalid_upload_size"})
            return

        payload = self.rfile.read(content_length)
        try:
            output, action, foreground_ratio = process_image(
                payload,
                safe_mask=request_path == "/remove-safe",
            )
        except ProcessingError as exc:
            LOGGER.warning("Rejected image: %s", exc)
            self._send_json(422, {"ok": False, "error": str(exc)})
            return
        except Exception:
            LOGGER.exception("Unexpected background-removal failure")
            self._send_json(500, {"ok": False, "error": "processing_failed"})
            return

        self.send_response(200)
        self.send_header("Content-Type", "image/png")
        self.send_header("Content-Length", str(len(output)))
        self.send_header("Cache-Control", "no-store")
        self.send_header("X-Background-Action", action)
        self.send_header("X-Foreground-Ratio", f"{foreground_ratio:.4f}")
        self.end_headers()
        self.wfile.write(output)

    def log_message(self, fmt: str, *args: object) -> None:
        LOGGER.info("%s %s", self.client_address[0], fmt % args)


class Server(ThreadingHTTPServer):
    daemon_threads = True
    allow_reuse_address = True


if __name__ == "__main__":
    LOGGER.info("Listening on http://%s:%d", HOST, PORT)
    Server((HOST, PORT), Handler).serve_forever()
