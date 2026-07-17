#!/usr/bin/env bash
set -euo pipefail

echo '--- services ---'
systemctl is-active cooling-product-background php8.3-fpm nginx
curl -fsS http://127.0.0.1:7010/health
echo

echo '--- current product images ---'
sqlite3 -header -column /var/lib/cooling/cooling.db <<'SQL'
SELECT id, file_path, sort_order, is_main, created_at
FROM product_images
WHERE product_id = 8
ORDER BY sort_order, id;
SQL

echo '--- public product page ---'
slug="$(sqlite3 /var/lib/cooling/cooling.db 'SELECT slug FROM products WHERE id=8;')"
curl -fsS -o /tmp/product-8-image-check.html -w 'HTTP %{http_code}\n' \
  "https://coolingsystem.vn/products/${slug}"
sqlite3 /var/lib/cooling/cooling.db 'SELECT file_path FROM product_images WHERE product_id=8 ORDER BY is_main DESC, sort_order, id;' \
  | while IFS= read -r image; do
      grep -Fq "$image" /tmp/product-8-image-check.html
      curl -fsS -o /dev/null -w "$image HTTP %{http_code} %{content_type}\n" \
        "https://coolingsystem.vn/uploads/products/$image"
    done

echo '--- deployed behavior ---'
grep -Fq 'name="replace_images"' /opt/cooling-php/views/admin/product-form.php
grep -Fq '20 * 1024 * 1024' /opt/cooling-php/views/admin/product-form.php
grep -Fq 'DELETE FROM product_images WHERE product_id=?' /opt/cooling-php/routes/admin.php
grep -Fq 'Bộ ảnh cũ được giữ nguyên' /opt/cooling-php/routes/admin.php
grep -Fq 'uploadVersion' /opt/cooling-php/includes/helpers.php
grep -Fq 'cleaned_rgb[refined_alpha <= 12] = 255' /opt/cooling-php/services/product-background-service.py
echo 'replacement, upload limit, cache version, and spill cleanup are deployed'

echo '--- backup ---'
ls -lh /opt/cooling-php/backups/20260717_product_image_replace/cooling.db
