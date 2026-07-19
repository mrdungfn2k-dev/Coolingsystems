#!/usr/bin/env python3
"""Classify restored product images without modifying source files."""

from __future__ import annotations

import argparse
import csv
import json
import sqlite3
from collections import Counter
from pathlib import Path

import numpy as np
from PIL import Image, ImageDraw, ImageFont, ImageOps, UnidentifiedImageError


CURRENT_PRODUCT_IDS = (2, 3, 4, 5, 6, 8)


def classify_image(path: Path) -> dict[str, object]:
    try:
        with Image.open(path) as opened:
            opened.load()
            original_width, original_height = opened.size
            has_alpha = "A" in opened.getbands()
            image = ImageOps.exif_transpose(opened).convert("RGBA")
    except (OSError, ValueError, UnidentifiedImageError) as exc:
        return {"classification": "invalid", "reason": str(exc)}

    image.thumbnail((360, 360), Image.Resampling.LANCZOS)
    rgba = np.asarray(image, dtype=np.uint8)
    rgb = rgba[:, :, :3]
    alpha = rgba[:, :, 3]
    height, width = alpha.shape
    ring = max(2, int(round(min(width, height) * 0.055)))
    edge_mask = np.zeros((height, width), dtype=bool)
    edge_mask[:ring, :] = True
    edge_mask[-ring:, :] = True
    edge_mask[:, :ring] = True
    edge_mask[:, -ring:] = True

    corner = max(3, int(round(min(width, height) * 0.10)))
    corner_mask = np.zeros((height, width), dtype=bool)
    corner_mask[:corner, :corner] = True
    corner_mask[:corner, -corner:] = True
    corner_mask[-corner:, :corner] = True
    corner_mask[-corner:, -corner:] = True

    channel_min = rgb.min(axis=2)
    channel_max = rgb.max(axis=2)
    chroma = channel_max.astype(np.int16) - channel_min.astype(np.int16)
    near_white = (channel_min >= 238) & (chroma <= 18)
    strict_white = (channel_min >= 246) & (chroma <= 10)
    transparent = alpha <= 12

    edge_near_white = float(np.mean(near_white[edge_mask]))
    edge_strict_white = float(np.mean(strict_white[edge_mask]))
    corner_near_white = float(np.mean(near_white[corner_mask]))
    overall_near_white = float(np.mean(near_white))
    edge_transparent = float(np.mean(transparent[edge_mask]))
    useful_transparency = float(np.mean(alpha < 250)) >= 0.02

    if useful_transparency and edge_transparent >= 0.90:
        classification = "clean_transparent"
    elif (
        edge_near_white >= 0.93
        and corner_near_white >= 0.95
        and edge_strict_white >= 0.72
        and overall_near_white >= 0.18
    ):
        classification = "clean_white"
    elif (
        edge_near_white >= 0.80
        and corner_near_white >= 0.85
        and overall_near_white >= 0.12
    ):
        classification = "review_white"
    else:
        classification = "needs_background"

    return {
        "classification": classification,
        "width": original_width,
        "height": original_height,
        "has_alpha": has_alpha,
        "edge_near_white": round(edge_near_white, 5),
        "edge_strict_white": round(edge_strict_white, 5),
        "corner_near_white": round(corner_near_white, 5),
        "overall_near_white": round(overall_near_white, 5),
        "edge_transparent": round(edge_transparent, 5),
    }


def make_contact_sheet(rows: list[dict[str, object]], upload_dir: Path, target: Path) -> None:
    selected = rows[:36]
    if not selected:
        return
    tile_width, tile_height = 220, 190
    columns = 6
    rows_count = (len(selected) + columns - 1) // columns
    sheet = Image.new("RGB", (columns * tile_width, rows_count * tile_height), "white")
    draw = ImageDraw.Draw(sheet)
    font = ImageFont.load_default()
    for index, row in enumerate(selected):
        x = (index % columns) * tile_width
        y = (index // columns) * tile_height
        path = upload_dir / str(row["file_path"])
        try:
            with Image.open(path) as opened:
                thumb = ImageOps.contain(ImageOps.exif_transpose(opened).convert("RGB"), (200, 150))
                px = x + (tile_width - thumb.width) // 2
                py = y + 4 + (150 - thumb.height) // 2
                sheet.paste(thumb, (px, py))
        except OSError:
            pass
        label = f"P{row['product_id']} I{row['image_id']}"
        draw.text((x + 6, y + 158), label, fill="black", font=font)
        draw.text((x + 6, y + 173), str(row["classification"]), fill="black", font=font)
    target.parent.mkdir(parents=True, exist_ok=True)
    sheet.save(target, "JPEG", quality=90, optimize=True)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--db", required=True, type=Path)
    parser.add_argument("--uploads", required=True, type=Path)
    parser.add_argument("--output", required=True, type=Path)
    args = parser.parse_args()

    args.output.mkdir(parents=True, exist_ok=True)
    connection = sqlite3.connect(f"file:{args.db}?mode=ro", uri=True)
    connection.row_factory = sqlite3.Row
    placeholders = ",".join("?" for _ in CURRENT_PRODUCT_IDS)
    records = connection.execute(
        f"""
        SELECT i.id AS image_id, i.product_id, i.file_path, i.is_main, i.sort_order
        FROM product_images i
        WHERE i.product_id NOT IN ({placeholders})
        ORDER BY i.product_id, i.sort_order, i.id
        """,
        CURRENT_PRODUCT_IDS,
    ).fetchall()
    connection.close()

    results: list[dict[str, object]] = []
    for record in records:
        row = dict(record)
        path = args.uploads / str(record["file_path"])
        if not path.is_file():
            details = {"classification": "missing", "reason": "file_not_found"}
        else:
            details = classify_image(path)
        row.update(details)
        results.append(row)

    csv_path = args.output / "stage4-image-audit.csv"
    field_names = sorted({key for row in results for key in row})
    with csv_path.open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=field_names)
        writer.writeheader()
        writer.writerows(results)

    grouped = Counter(str(row["classification"]) for row in results)
    product_groups: dict[int, set[str]] = {}
    for row in results:
        product_groups.setdefault(int(row["product_id"]), set()).add(str(row["classification"]))
    product_counts = Counter(
        "needs_processing"
        if any(value in classes for value in ("needs_background", "review_white", "invalid", "missing"))
        else "all_clean"
        for classes in product_groups.values()
    )
    summary = {
        "total_images": len(results),
        "classifications": dict(sorted(grouped.items())),
        "products_with_images": len(product_groups),
        "product_classifications": dict(sorted(product_counts.items())),
    }
    (args.output / "stage4-image-audit.json").write_text(
        json.dumps(summary, ensure_ascii=False, indent=2), encoding="utf-8"
    )

    for classification in ("clean_white", "clean_transparent", "review_white", "needs_background"):
        sample = [row for row in results if row["classification"] == classification]
        sample.sort(key=lambda row: int(row["image_id"]))
        make_contact_sheet(sample, args.uploads, args.output / f"sample-{classification}.jpg")

    print(json.dumps(summary, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
