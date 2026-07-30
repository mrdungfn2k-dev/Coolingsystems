<?php
get('/api/models', function() {
    $brandId = intval($_GET['brand'] ?? 0);
    if (!$brandId) { jsonResponse(['models'=>[]]); }
    $models = dbAll('SELECT id, name, year_from, year_to FROM car_models WHERE brand_id=? ORDER BY name', [$brandId]);
    jsonResponse(['models'=>$models]);
});

get('/api/homepage-products', function() {
    $catId = intval($_GET['cat_id'] ?? 0);
    $type = trim($_GET['type'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(20, intval($_GET['limit'] ?? 10)));
    $offset = ($page-1)*$limit;

    if ($type === 'sale') {
        $where = "p.status='published' AND (p.is_on_sale=1 OR (p.original_price IS NOT NULL AND p.original_price > p.price))";
        $products = dbAll("SELECT p.*, 'Cooling' AS shop_name, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p WHERE $where ORDER BY p.id DESC LIMIT ? OFFSET ?", [$limit, $offset]);
    } else {
        if (!$catId) { jsonResponse(['ok'=>false, 'msg'=>'Invalid category']); }
        $products = dbAll("SELECT p.*, 'Cooling' AS shop_name, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p WHERE p.category_id=? AND p.status='published' ORDER BY p.created_at DESC LIMIT ? OFFSET ?", [$catId, $limit, $offset]);
    }

    global $user;
    $user = currentUser();

    ob_start();
    foreach ($products as $p) {
        require __DIR__ . '/../views/public/partials/prod-card.php';
    }
    $html = ob_get_clean();

    jsonResponse([
        'ok' => true,
        'html' => $html
    ]);
});
// Favorite toggle API
post('/api/favorites/toggle', function() {
    header('Content-Type: application/json');
    $user = currentUser();
    if (!$user) { echo json_encode(['login'=>true]); exit; }
    csrfCheck();
    $pid = intval($_POST['product_id'] ?? 0);
    if (!$pid) { echo json_encode(['error'=>'missing']); exit; }
    $exists = dbGet("SELECT id FROM favorites WHERE user_id=? AND product_id=?", [$user['id'], $pid]);
    if ($exists) {
        dbRun("DELETE FROM favorites WHERE user_id=? AND product_id=?", [$user['id'], $pid]);
        $count = dbGet("SELECT COUNT(*) as n FROM favorites WHERE user_id=?", [$user['id']])['n'];
        echo json_encode(['ok'=>true,'added'=>false,'count'=>$count]);
    } else {
        dbInsert("INSERT INTO favorites (user_id, product_id, created_at) VALUES (?,?,datetime('now','localtime'))", [$user['id'], $pid]);
        $count = dbGet("SELECT COUNT(*) as n FROM favorites WHERE user_id=?", [$user['id']])['n'];
        echo json_encode(['ok'=>true,'added'=>true,'count'=>$count]);
    }
    exit;
});

// Cart add API
post('/api/cart/add', function() {
    header('Content-Type: application/json');
    $user = currentUser();
    if (!$user) { echo json_encode(['login'=>true]); exit; }
    csrfCheck();
    $pid = intval($_POST['product_id'] ?? 0);
    $qty = max(1, intval($_POST['quantity'] ?? 1));
    if (!$pid) { echo json_encode(['error'=>'missing']); exit; }
    $product = dbGet("SELECT id, stock, status FROM products WHERE id=?", [$pid]);
    if (!$product || $product['status'] !== 'published') { echo json_encode(['error'=>'Sản phẩm không tồn tại']); exit; }
    if ($product['stock'] < 1) { echo json_encode(['error'=>'Hết hàng']); exit; }
    $existing = dbGet("SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=?", [$user['id'], $pid]);
    if ($existing) {
        $newQty = $existing['quantity'] + $qty;
        if ($newQty > $product['stock']) $newQty = $product['stock'];
        dbRun("UPDATE cart_items SET quantity=? WHERE id=?", [$newQty, $existing['id']]);
    } else {
        dbInsert("INSERT INTO cart_items (user_id, product_id, quantity, created_at) VALUES (?,?,?,datetime('now','localtime'))", [$user['id'], $pid, $qty]);
    }
    $count = dbGet("SELECT COALESCE(SUM(quantity),0) as n FROM cart_items WHERE user_id=?", [$user['id']])['n'];
    echo json_encode(['ok'=>true,'count'=>$count]);
    exit;
});

// ── Mark all notifications as read ──
post('/api/notifications/mark-all-read', function() {
    $user = currentUser();
    if (!$user) { echo json_encode(['ok'=>false]); exit; }
    dbRun("UPDATE notifications SET is_read=1, read_at=datetime('now') WHERE user_id=? AND is_read=0", [$user['id']]);
    echo json_encode(['ok'=>true]);
    exit;
});
