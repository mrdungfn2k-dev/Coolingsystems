<?php
// Temporary stats script - REMOVE AFTER USE
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$token = $_GET['token'] ?? '';
if ($token !== 'cs_stats_2307') {
    http_response_code(403);
    die(json_encode(['error' => 'Forbidden']));
}

try {
    $db_paths = [
        '/var/lib/coolingsystems/cooling.db',
        '/var/www/coolingsystems/cooling.db',
        dirname(__DIR__) . '/cooling.db',
    ];
    $db = null;
    foreach ($db_paths as $p) {
        if (file_exists($p)) { $db = new PDO("sqlite:$p"); break; }
    }
    if (!$db) throw new Exception('DB not found');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $result = ['generated_at' => date('Y-m-d H:i:s'), 'db_path' => $p];

    // Tong hop theo partner_id (null = admin/import)
    $result['by_status'] = [];
    foreach ($db->query("SELECT status, COUNT(*) as cnt FROM products GROUP BY status") as $r) {
        $result['by_status'][$r['status']] = (int)$r['cnt'];
    }

    // Phan biet nguon: is_admin_created
    foreach ($db->query("SELECT is_admin_created, status, COUNT(*) as cnt FROM products GROUP BY is_admin_created, status") as $r) {
        $src = $r['is_admin_created'] ? 'admin' : 'partner';
        $result['by_source'][$src][$r['status']] = (int)$r['cnt'];
    }

    // Theo danh muc
    foreach ($db->query("SELECT c.name as cat, p.status, COUNT(*) as cnt FROM products p LEFT JOIN categories c ON c.id=p.category_id GROUP BY c.name, p.status ORDER BY c.name") as $r) {
        $result['by_category'][$r['cat']][$r['status']] = (int)$r['cnt'];
    }

    // Chat luong
    $q = $db->query("SELECT
        COUNT(*) as total,
        SUM(CASE WHEN description!='' AND description IS NOT NULL AND LENGTH(description)>50 THEN 1 ELSE 0 END) as has_desc,
        SUM(CASE WHEN seo_title!='' AND seo_title IS NOT NULL THEN 1 ELSE 0 END) as has_seo,
        SUM(CASE WHEN oem_code!='' AND oem_code IS NOT NULL THEN 1 ELSE 0 END) as has_oem,
        SUM(CASE WHEN price>0 THEN 1 ELSE 0 END) as has_price
    FROM products");
    $result['quality'] = $q->fetch(PDO::FETCH_ASSOC);

    // Anh san pham
    try {
        $q2 = $db->query("SELECT COUNT(DISTINCT product_id) as prods, COUNT(*) as imgs FROM product_images");
        $result['images'] = $q2->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        $result['images'] = ['error' => $e->getMessage()];
    }

    // Partner info (neu co)
    try {
        foreach ($db->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role") as $r) {
            $result['users'][$r['role']] = (int)$r['cnt'];
        }
    } catch(Exception $e) {}

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
