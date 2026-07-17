#!/usr/bin/env python3

import sys
import time

from PIL import Image, ImageOps
from rembg import new_session, remove


model, source_path, output_path = sys.argv[1:4]
started = time.monotonic()
session = new_session(model)
image = ImageOps.exif_transpose(Image.open(source_path)).convert("RGB")
image.thumbnail((1200, 1200), Image.Resampling.LANCZOS)
result = remove(
    image,
    session=session,
    alpha_matting=True,
    alpha_matting_foreground_threshold=240,
    alpha_matting_background_threshold=10,
    alpha_matting_erode_size=1,
)
result.save(output_path, format="PNG")
print(f"model={model} elapsed={time.monotonic() - started:.2f}s size={result.size}")
