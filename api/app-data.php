<?php
// API Endpoint serving 100% complete VPS DB Data (Categories, Car Brands, Part Brands, Products)
header('Content-Type: application/json; charset=utf-8');
chdir(__DIR__ . '/..');
require_once 'includes/db.php';
require_once 'includes/helpers.php';

// 1. Fetch All Categories
$categories = dbAll("
    SELECT c.id, c.name, COUNT(p.id) as prod_count 
    FROM categories c 
    LEFT JOIN products p ON p.category_id=c.id AND p.status='published'
    GROUP BY c.id, c.name 
    ORDER BY c.name ASC
");

// 2. Fetch All Car Brands
$carBrands = dbAll("
    SELECT b.id, b.name, b.logo, COUNT(p.id) as prod_count 
    FROM brands b 
    LEFT JOIN products p ON p.car_brand_id=b.id AND p.status='published'
    GROUP BY b.id, b.name, b.logo 
    ORDER BY b.name ASC
");

// 3. Fetch Distinct Part Brands
$partBrands = dbAll("
    SELECT part_brand AS name, COUNT(*) as prod_count 
    FROM products 
    WHERE status='published' AND part_brand IS NOT NULL AND part_brand != '' 
    GROUP BY part_brand 
    ORDER BY part_brand ASC
");

// 4. Fetch Products with Images
$products = dbAll("
    SELECT p.id, p.sku, p.oem_code, p.name, p.price, p.original_price, 
           c.name AS cat_name, b.name AS brand_name, p.part_brand, p.description,
           COALESCE((SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC, id ASC LIMIT 1), '') AS main_image
    FROM products p
    LEFT JOIN categories c ON c.id=p.category_id
    LEFT JOIN brands b ON b.id=p.car_brand_id
    WHERE p.status='published'
    ORDER BY p.id DESC
    LIMIT 100
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
