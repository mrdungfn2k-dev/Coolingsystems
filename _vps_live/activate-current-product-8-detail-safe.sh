#!/usr/bin/env bash
set -euo pipefail

sqlite3 /var/lib/cooling/cooling.db < /tmp/backup-before-detail-safe.sql
sqlite3 /var/lib/cooling/cooling.db < /tmp/activate-current-product-8-detail-safe.sql

sqlite3 -header -column /var/lib/cooling/cooling.db <<'SQL'
SELECT id, file_path, sort_order, is_main
FROM product_images
WHERE product_id=8
ORDER BY sort_order, id;
SQL
