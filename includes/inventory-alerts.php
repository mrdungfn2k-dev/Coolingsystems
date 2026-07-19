<?php

require_once __DIR__ . '/mailer.php';

function ensureInventoryAlertTables(): void {
    static $ready = false;
    if ($ready) return;
    dbRun("CREATE TABLE IF NOT EXISTS inventory_alert_states (
        product_id INTEGER PRIMARY KEY,
        is_low INTEGER NOT NULL DEFAULT 0,
        last_stock INTEGER NOT NULL DEFAULT 0,
        last_attempt_at TEXT,
        last_sent_at TEXT,
        last_status TEXT,
        updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
        FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    dbRun("CREATE TABLE IF NOT EXISTS inventory_alert_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        recipient TEXT NOT NULL,
        stock INTEGER NOT NULL,
        min_stock INTEGER NOT NULL,
        source TEXT,
        status TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
        FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    $ready = true;
}

function inventoryAlertSetting(string $key, string $fallback = ''): string {
    $row = dbGet('SELECT value FROM settings WHERE key=?', [$key]);
    return (string)($row['value'] ?? $fallback);
}

function sendInventoryLowStockEmail(string $recipient, array $product): bool {
    $name = htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8');
    $sku = htmlspecialchars((string)($product['sku'] ?: $product['oem_code']), ENT_QUOTES, 'UTF-8');
    $stock = (int)$product['stock'];
    $minimum = (int)$product['min_stock'];
    $link = 'https://coolingsystems.vn/admin/inventory?q=' . rawurlencode((string)($product['sku'] ?: $product['oem_code']));
    $body = _emailLayout('Cảnh báo tồn kho', "
      <h2 style='color:#b91c1c;margin:0 0 12px'>Cảnh báo tồn kho thấp</h2>
      <p style='margin:0 0 12px;color:#374151'>Sản phẩm đã chạm mức tồn tối thiểu và cần được kiểm tra.</p>
      <table cellpadding='0' cellspacing='0' style='width:100%;border-collapse:collapse;margin:14px 0'>
        <tr><td style='padding:8px;border-bottom:1px solid #e5e7eb;color:#64748b'>Sản phẩm</td><td style='padding:8px;border-bottom:1px solid #e5e7eb;font-weight:700'>{$name}</td></tr>
        <tr><td style='padding:8px;border-bottom:1px solid #e5e7eb;color:#64748b'>Mã</td><td style='padding:8px;border-bottom:1px solid #e5e7eb'>{$sku}</td></tr>
        <tr><td style='padding:8px;border-bottom:1px solid #e5e7eb;color:#64748b'>Tồn hiện tại</td><td style='padding:8px;border-bottom:1px solid #e5e7eb;color:#b91c1c;font-weight:800'>{$stock}</td></tr>
        <tr><td style='padding:8px;color:#64748b'>Mức tối thiểu</td><td style='padding:8px'>{$minimum}</td></tr>
      </table>
      <p style='margin:22px 0;text-align:center'><a href='{$link}' style='background:#1a3258;color:#fff;padding:12px 22px;text-decoration:none;border-radius:6px;font-weight:700;display:inline-block'>MỞ QUẢN LÝ KHO</a></p>
      <p style='color:#6b7280;font-size:12px;margin:0'>Hệ thống chỉ gửi một email cho mỗi đợt tồn kho thấp. Khi tồn tăng cao hơn mức tối thiểu, cảnh báo sẽ được kích hoạt lại nếu sau đó tồn giảm xuống.</p>
    ");
    return sendEmail($recipient, 'Cảnh báo tồn kho thấp: ' . (string)$product['name'], $body);
}

function inventoryCheckLowStockAlert(int $productId, string $source = ''): bool {
    ensureInventoryAlertTables();
    $enabled = inventoryAlertSetting('inventory_alert_enabled', '0') === '1';
    $recipient = trim(inventoryAlertSetting('inventory_alert_email'));
    if (!$enabled || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) return false;
    $product = dbGet('SELECT id,name,sku,oem_code,stock,min_stock FROM products WHERE id=?', [$productId]);
    if (!$product) return false;
    $isLow = (int)$product['min_stock'] > 0 && (int)$product['stock'] <= (int)$product['min_stock'];
    if (!$isLow) {
        dbRun('DELETE FROM inventory_alert_states WHERE product_id=?', [$productId]);
        return false;
    }
    $state = dbGet('SELECT * FROM inventory_alert_states WHERE product_id=?', [$productId]);
    if ($state && (int)$state['is_low'] === 1 && $state['last_status'] === 'sent') {
        dbRun("UPDATE inventory_alert_states SET last_stock=?,updated_at=datetime('now','localtime') WHERE product_id=?", [(int)$product['stock'], $productId]);
        return false;
    }
    $sent = sendInventoryLowStockEmail($recipient, $product);
    $status = $sent ? 'sent' : 'failed';
    dbRun("INSERT INTO inventory_alert_states (product_id,is_low,last_stock,last_attempt_at,last_sent_at,last_status,updated_at) VALUES (?,?,?,datetime('now','localtime'),?,?,datetime('now','localtime')) ON CONFLICT(product_id) DO UPDATE SET is_low=excluded.is_low,last_stock=excluded.last_stock,last_attempt_at=excluded.last_attempt_at,last_sent_at=excluded.last_sent_at,last_status=excluded.last_status,updated_at=excluded.updated_at", [$productId,1,(int)$product['stock'],$sent ? date('Y-m-d H:i:s') : null,$status]);
    dbRun('INSERT INTO inventory_alert_logs (product_id,recipient,stock,min_stock,source,status) VALUES (?,?,?,?,?,?)', [$productId,$recipient,(int)$product['stock'],(int)$product['min_stock'],$source,$status]);
    return $sent;
}
