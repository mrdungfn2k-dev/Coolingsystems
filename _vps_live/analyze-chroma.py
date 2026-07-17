#!/usr/bin/env python3

import sys

import numpy as np
from PIL import Image, ImageOps


source_path, cutout_path = sys.argv[1:3]
cutout = Image.open(cutout_path).convert("RGBA")
source = ImageOps.exif_transpose(Image.open(source_path)).convert("RGB")
source.thumbnail(cutout.size, Image.Resampling.LANCZOS)
source = source.resize(cutout.size, Image.Resampling.LANCZOS)
hsv = np.asarray(source.convert("HSV"), dtype=np.uint8)
alpha = np.asarray(cutout.getchannel("A"), dtype=np.uint8)
hue, saturation, value = hsv[:, :, 0], hsv[:, :, 1], hsv[:, :, 2]

background = (alpha <= 12) & (saturation >= 60) & (value >= 30)
histogram, edges = np.histogram(hue[background], bins=32, range=(0, 256))
dominant_bin = int(np.argmax(histogram))
dominant_hue = float((edges[dominant_bin] + edges[dominant_bin + 1]) / 2.0)
hue_distance = np.minimum(np.abs(hue.astype(float) - dominant_hue), 256 - np.abs(hue.astype(float) - dominant_hue))
dominant_background = background & (hue_distance <= 18)
opaque_same_hue = (alpha >= 200) & (hue_distance <= 24)

for name, mask in [('background', dominant_background), ('opaque_same_hue', opaque_same_hue)]:
    print(name, 'pixels=', int(mask.sum()))
    for channel_name, channel in [('saturation', saturation), ('value', value)]:
        values = channel[mask]
        print(channel_name, dict(zip([1, 3, 5, 10, 25, 50, 75, 90, 95, 99], np.percentile(values, [1, 3, 5, 10, 25, 50, 75, 90, 95, 99]).round(1))))
