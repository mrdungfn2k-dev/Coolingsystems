<?php
require __DIR__.'/core.php';
try {
    $res = dbInsert("INSERT INTO order_items (sub_order_id, product_id, snapshot_name, unit_price, quantity, line_total) VALUES (21, 1, 'test', 100, 1, 100)");
    echo "SUCCESS: " . $res;
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}
