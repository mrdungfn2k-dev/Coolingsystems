#!/usr/bin/env bash
set -euo pipefail

echo '--- product 8 images ---'
sqlite3 -header -column /var/lib/cooling/cooling.db <<'SQL'
SELECT id, product_id, file_path, sort_order, is_main, created_at
FROM product_images
WHERE product_id = 8
ORDER BY sort_order, id;
SQL

echo '--- newest image rows ---'
sqlite3 -header -column /var/lib/cooling/cooling.db <<'SQL'
SELECT id, product_id, file_path, sort_order, is_main, created_at
FROM product_images
ORDER BY id DESC
LIMIT 20;
SQL

echo '--- newest originals ---'
find /var/lib/cooling/product-originals -maxdepth 1 -type f \
  -printf '%TY-%Tm-%Td %TH:%TM:%TS %f %s\n' | sort -r | head -n 15

echo '--- edit requests ---'
grep -E 'POST /admin/products/[0-9]+/edit' /var/log/nginx/access.log | tail -n 15 || true
