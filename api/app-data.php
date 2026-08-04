<?php
// API Endpoint serving complete App Data (Products, Categories, Brands, Car Brands)
header('Content-Type: application/json; charset=utf-8');
chdir(__DIR__ . '/..');
require_once 'includes/db.php';
require_once 'includes/helpers.php';

// 1. Fetch Categories
$categories = dbAll("SELECT id, name, (SELECT COUNT(*) FROM products WHERE category_id=c.id AND status='published') as prod_count FROM categories c ORDER BY id ASC");

// 2. Fetch Car Brands
$carBrands = dbAll("SELECT id, name, logo, (SELECT COUNT(*) FROM products WHERE car_brand_id=b.id AND status='published') as prod_count FROM brands b ORDER BY name ASC");

// 3. Fetch Part Brands (distinct part_brand from products)
$partBrands = dbAll("SELECT DISTINCT part_brand FROM products WHERE part_brand IS NOT NULL AND part_brand != '' ORDER BY part_brand ASC");

// 4. Fetch All Published Products with Main Image
$products = dbAll("
    SELECT p.id, p.sku, p.oem_code, p.name, p.price, p.original_price, 
           c.name AS cat_name, b.name AS brand_name, p.part_brand, p.description,
           COALESCE((SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC, id ASC LIMIT 1), '') AS main_image
    FROM products p
    LEFT JOIN categories c ON c.id=p.category_id
    LEFT JOIN brands b ON b.id=p.car_brand_id
    WHERE p.status='published'
    ORDER BY p.id DESC
");

// Format product image URLs
foreach ($products as &$p) {
    $img = $p['main_image'];
    if (!empty($img)) {
        if (!str_starts_with($img, 'http') && !str_starts_with($img, '/')) {
            if (str_starts_with($img, 'uploads/')) {
                $p['image'] = '/' . $img;
            } else {
                $p['image'] = '/uploads/products/' . $img;
            }
        } else {
            $p['image'] = $img;
        }
    } else {
        $p['image'] = '/favicon-512x512.png';
    }
}
unset($p);

echo json_encode([
    'success' => true,
    'categories' => $categories,
    'carBrands' => $carBrands,
    'partBrands' => $partBrands,
    'products' => $products
], JSON_UNESCAPED_UNICODE);
