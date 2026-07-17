#!/usr/bin/env python3

import sys

import numpy as np
from PIL import Image, ImageOps


source_path, cutout_path, output_path = sys.argv[1:4]
cutout = Image.open(cutout_path).convert("RGBA")
source = ImageOps.exif_transpose(Image.open(source_path)).convert("RGB")
source.thumbnail(cutout.size, Image.Resampling.LANCZOS)
source = source.resize(cutout.size, Image.Resampling.LANCZOS)
rgb = np.asarray(source, dtype=np.uint8)
hsv = np.asarray(source.convert("HSV"), dtype=np.uint8)
alpha = np.asarray(cutout.getchannel("A"), dtype=np.uint8)
hue, saturation, value = hsv[:, :, 0], hsv[:, :, 1], hsv[:, :, 2]

background = (alpha <= 12) & (saturation >= 60) & (value >= 30)
histogram, edges = np.histogram(hue[background], bins=32, range=(0, 256))
dominant_bin = int(np.argmax(histogram))
dominant_hue = float((edges[dominant_bin] + edges[dominant_bin + 1]) / 2.0)
hue_distance = np.minimum(np.abs(hue.astype(float) - dominant_hue), 256 - np.abs(hue.astype(float) - dominant_hue))
dominant_background = background & (hue_distance <= 18)
sat_floor = max(110, int(np.percentile(saturation[dominant_background], 1) - 12))
value_floor = max(100, int(np.percentile(value[dominant_background], 1) - 14))
remove_mask = (hue_distance <= 24) & (saturation >= sat_floor) & (value >= value_floor)

clean_alpha = alpha.copy()
clean_alpha[remove_mask] = 0
Image.fromarray(np.dstack((rgb, clean_alpha)), mode="RGBA").save(output_path)
print('dominant_hue=', dominant_hue, 'sat_floor=', sat_floor, 'value_floor=', value_floor, 'removed=', int((remove_mask & (alpha > 12)).sum()))
