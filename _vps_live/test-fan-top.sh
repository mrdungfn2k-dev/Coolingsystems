#!/usr/bin/env bash
set -euo pipefail

base=dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000
source="/var/lib/cooling/product-originals/${base}-original.png"
cutout=/tmp/fan-top-cutout.png
output=/tmp/fan-top-normalized.webp

curl --fail-with-body --silent --show-error --max-time 120 \
  --dump-header /tmp/fan-top-headers.txt \
  --header 'Content-Type: application/octet-stream' \
  --data-binary "@${source}" --output "$cutout" \
  http://127.0.0.1:7010/remove

convert "$cutout" -auto-orient -strip -colorspace sRGB -fuzz 4% -trim +repage \
  -filter Lanczos -resize 1020x765 -unsharp '0x0.65+0.65+0.02' \
  -gravity center -background '#ffffff' -alpha background -alpha off \
  -extent 1200x900 -quality 88 "$output"

identify -format '%f %wx%h %b\n' "$output"
grep -iE '^X-Background-Action:|^X-Foreground-Ratio:' /tmp/fan-top-headers.txt
