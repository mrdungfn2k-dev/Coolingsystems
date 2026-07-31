<?php
require __DIR__.'/core.php';

try {
    $id = 18;
    $p = dbGet("SELECT id FROM orders WHERE id=? OR code=?", [$id, $id]);
    echo "Found order.\n";
    $order = dbGet("SELECT o.*, u.full_name, u.email, u.phone, s.full_name as staff_name 
        FROM orders o 
        LEFT JOIN users u ON u.id=o.user_id
        LEFT JOIN users s ON s.id=o.created_by_staff
        WHERE o.id=?", [$p['id']]);
    echo "Fetched order.\n";
    $items = dbAll("SELECT oi.*, p.name AS product_name, (SELECT image_path FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS image_url FROM order_items oi
        INNER JOIN sub_orders so ON so.id = oi.sub_order_id
        LEFT JOIN products p ON p.id = oi.product_id
        WHERE so.order_id=?", [$p['id']]);
    echo "Fetched items.\n";
    
    // Check payments
    $payments = dbAll("SELECT * FROM order_payments WHERE order_id=? ORDER BY created_at ASC", [$order['id']]);
    echo "Fetched payments.\n";
    
    // Check returns
    $returns = dbAll("SELECT * FROM order_returns WHERE order_id=? ORDER BY created_at DESC", [$order['id']]);
    echo "Fetched returns.\n";
    
    echo "SUCCESS.";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
