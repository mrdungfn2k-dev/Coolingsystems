#!/usr/bin/env bash
set -euo pipefail

echo '--- product SEO coverage ---'
sqlite3 -header -column /var/lib/cooling/cooling.db <<'SQL'
SELECT
  COUNT(*) AS total_products,
  SUM(status='published') AS published,
  SUM(COALESCE(is_indexed,0)=1) AS marked_indexable,
  SUM(TRIM(COALESCE(seo_keyword,''))<>'') AS with_focus_keyword,
  SUM(TRIM(COALESCE(seo_title,''))<>'') AS with_seo_title,
  SUM(TRIM(COALESCE(seo_description,''))<>'') AS with_seo_description
FROM products;

SELECT id, status, is_indexed, name, sku, oem_code, seo_keyword, seo_title, slug
FROM products
ORDER BY id;
SQL

echo '--- categories ---'
sqlite3 -header -column /var/lib/cooling/cooling.db <<'SQL'
SELECT id, name, slug FROM categories ORDER BY sort_order, id;
SQL

echo '--- SEO/site settings ---'
sqlite3 -header -column /var/lib/cooling/cooling.db <<'SQL'
SELECT key, value FROM settings
WHERE lower(key) LIKE '%seo%' OR lower(key) LIKE '%meta%' OR lower(key) LIKE '%site%'
ORDER BY key;
SELECT key, value FROM system_config
WHERE lower(key) LIKE '%seo%' OR lower(key) LIKE '%meta%' OR lower(key) LIKE '%site%'
ORDER BY key;
SQL

echo '--- homepage head and headings ---'
curl -fsS https://coolingsystem.vn/ -o /tmp/seo-home.html
grep -ioE '<title>[^<]*|<meta[^>]+(name="(description|keywords|robots)"|property="og:(title|description)")[^>]*|<link[^>]+rel="canonical"[^>]*|<h[12][^>]*>[^<]+' /tmp/seo-home.html | head -n 40 || true

echo '--- product head and headings ---'
slug="$(sqlite3 /var/lib/cooling/cooling.db 'SELECT slug FROM products WHERE status="published" ORDER BY id DESC LIMIT 1;')"
curl -fsS "https://coolingsystem.vn/products/${slug}" -o /tmp/seo-product.html
grep -ioE '<title>[^<]*|<meta[^>]+(name="(description|keywords|robots)"|property="og:(title|description)")[^>]*|<link[^>]+rel="canonical"[^>]*|<h[12][^>]*>[^<]+' /tmp/seo-product.html | head -n 40 || true

echo '--- robots and sitemap sample ---'
curl -fsS https://coolingsystem.vn/robots.txt
curl -fsS https://coolingsystem.vn/sitemap.xml | head -n 45
