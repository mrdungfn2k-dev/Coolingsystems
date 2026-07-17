#!/usr/bin/env bash
set -euo pipefail

backup_dir=/opt/cooling-php/backups/20260717_product_image_replace
mkdir -p "$backup_dir"
cp -a /opt/cooling-php/routes/admin.php "$backup_dir/admin.php"
cp -a /opt/cooling-php/includes/helpers.php "$backup_dir/helpers.php"
cp -a /opt/cooling-php/views/admin/product-form.php "$backup_dir/product-form.php"
cp -a /opt/cooling-php/services/product-background-service.py "$backup_dir/product-background-service.py"
sqlite3 /var/lib/cooling/cooling.db ".backup '$backup_dir/cooling.db'"

install -m 0644 /tmp/admin.php.candidate /opt/cooling-php/routes/admin.php
install -m 0644 /tmp/helpers.php.candidate /opt/cooling-php/includes/helpers.php
install -m 0644 /tmp/product-form.php.candidate /opt/cooling-php/views/admin/product-form.php
install -m 0755 /tmp/product-background-service.py.candidate /opt/cooling-php/services/product-background-service.py

php -l /opt/cooling-php/routes/admin.php
php -l /opt/cooling-php/includes/helpers.php
php -l /opt/cooling-php/views/admin/product-form.php
systemctl restart cooling-product-background

for attempt in $(seq 1 30); do
  if curl -fsS http://127.0.0.1:7010/health; then
    echo
    exit 0
  fi
  sleep 1
done

echo 'Background service did not become ready' >&2
exit 1
