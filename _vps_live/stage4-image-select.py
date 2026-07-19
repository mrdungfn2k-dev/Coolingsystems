#!/usr/bin/env python3
"""Select only clearly non-neutral backgrounds for Stage 4 AI processing."""

from __future__ import annotations

import argparse
import csv
import json
from collections import Counter
from pathlib import Path

import numpy as np
from PIL import Image, ImageOps, UnidentifiedImageError


def background_metrics(path: Path) -> dict[str, float]:
    with Image.open(path) as opened:
        opened.load()
        image = ImageOps.exif_transpose(opened).convert("RGB")
    image.thumbnail((360, 360), Image.Resampling.LANCZOS)
    rgb = np.asarray(image, dtype=np.uint8)
    height, width = rgb.shape[:2]
    corner = max(4, int(round(min(width, height) * 0.13)))
    mask = np.zeros((height, width), dtype=bool)
    mask[:corner, :corner] = True
    mask[:corner, -corner:] = True
    mask[-corner:, :corner] = True
    mask[-corner:, -corner:] = True
    sample = rgb[mask].astype(np.float32)
    luminance = 0.2126 * sample[:, 0] + 0.7152 * sample[:, 1] + 0.0722 * sample[:, 2]
    chroma = sample.max(axis=1) - sample.min(axis=1)
    return {
        "corner_luma_median": round(float(np.median(luminance)), 4),
        "corner_luma_p10": round(float(np.percentile(luminance, 10)), 4),
        "corner_luma_std": round(float(np.std(luminance)), 4),
        "corner_chroma_median": round(float(np.median(chroma)), 4),
        "corner_saturated_ratio": round(float(np.mean(chroma >= 24)), 5),
    }


def select_action(classification: str, metrics: dict[str, float]) -> tuple[str, str]:
    if classification in ("clean_white", "clean_transparent"):
        return "keep", "clean_background"
    if classification == "review_white":
        return "keep", "white_or_documentary_background"
    if classification != "needs_background":
        return "keep", "unsupported_or_missing"

    median = metrics["corner_luma_median"]
    deviation = metrics["corner_luma_std"]
    saturated = metrics["corner_saturated_ratio"]
    light_neutral = median >= 205 and saturated <= 0.15 and deviation <= 45
    smooth_neutral = median >= 185 and saturated <= 0.06 and deviation <= 25
    if light_neutral or smooth_neutral:
        return "keep", "clean_neutral_studio_background"
    return "process", "colored_dark_or_complex_background"


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--audit", required=True, type=Path)
    parser.add_argument("--uploads", required=True, type=Path)
    parser.add_argument("--output", required=True, type=Path)
    args = parser.parse_args()

    with args.audit.open("r", encoding="utf-8-sig", newline="") as handle:
        rows = list(csv.DictReader(handle))

    counts: Counter[str] = Counter()
    reason_counts: Counter[str] = Counter()
    for row in rows:
        classification = str(row.get("classification", ""))
        metrics: dict[str, float] = {}
        if classification == "needs_background":
            path = args.uploads / str(row["file_path"])
            try:
                metrics = background_metrics(path)
            except (OSError, ValueError, UnidentifiedImageError):
                row["stage4_action"] = "keep"
                row["stage4_reason"] = "metric_read_failed"
                counts["keep"] += 1
                reason_counts["metric_read_failed"] += 1
                continue
        action, reason = select_action(classification, metrics)
        row.update({key: str(value) for key, value in metrics.items()})
        row["stage4_action"] = action
        row["stage4_reason"] = reason
        counts[action] += 1
        reason_counts[reason] += 1

    args.output.parent.mkdir(parents=True, exist_ok=True)
    field_names = sorted({key for row in rows for key in row})
    with args.output.open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=field_names)
        writer.writeheader()
        writer.writerows(rows)

    summary = {
        "total_images": len(rows),
        "actions": dict(sorted(counts.items())),
        "reasons": dict(sorted(reason_counts.items())),
    }
    print(json.dumps(summary, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
