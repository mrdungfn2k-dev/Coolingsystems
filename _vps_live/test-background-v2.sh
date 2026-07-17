#!/usr/bin/env bash
set -euo pipefail

base=dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000
for item in "3:fan-front" "2:fan-side"; do
  suffix="${item%%:*}"
  label="${item##*:}"
  source="/var/lib/cooling/product-originals/${base}-${suffix}-original.png"
  cutout="/tmp/${label}-cutout-v2.png"
  output="/tmp/${label}-normalized-v2.webp"
  curl --fail-with-body --silent --show-error --max-time 120 \
    --dump-header "/tmp/${label}-headers-v2.txt" \
    --header 'Content-Type: application/octet-stream' \
    --data-binary "@${source}" --output "$cutout" \
    http://127.0.0.1:7010/remove
  convert "$cutout" -auto-orient -strip -colorspace sRGB -fuzz 4% -trim +repage \
    -filter Lanczos -resize 1020x765 -gravity center -background '#ffffff' \
    -alpha background -alpha off -extent 1200x900 -quality 88 "$output"
  identify -format '%f %wx%h %b\n' "$output"
  grep -iE '^X-Background-Action:|^X-Foreground-Ratio:' "/tmp/${label}-headers-v2.txt"
done
