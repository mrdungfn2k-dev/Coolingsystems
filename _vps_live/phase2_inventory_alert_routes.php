post('/admin/settings/inventory-alert', function() {
    requireRole('admin', '/admin/login'); csrfCheck();
    $enabled = !empty($_POST['inventory_alert_enabled']) ? '1' : '0';
    $email = strtolower(trim((string)($_POST['inventory_alert_email'] ?? '')));
    if ($enabled === '1' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Vui lòng nhập email nhận cảnh báo hợp lệ trước khi bật cảnh báo tồn kho.');
        redirect('/admin/settings'); return;
    }
    dbRun("INSERT INTO settings (key,value) VALUES ('inventory_alert_enabled',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value", [$enabled]);
    dbRun("INSERT INTO settings (key,value) VALUES ('inventory_alert_email',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value", [$email]);
    flash('success', 'Đã lưu cấu hình email cảnh báo tồn kho.');
    redirect('/admin/settings');
});

post('/admin/settings/inventory-alert/test', function() {
    requireRole('admin', '/admin/login'); csrfCheck();
    require_once __DIR__ . '/../includes/inventory-alerts.php';
    $email = trim(inventoryAlertSetting('inventory_alert_email'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Hãy lưu một email nhận cảnh báo hợp lệ trước khi gửi kiểm tra.');
        redirect('/admin/settings'); return;
    }
    $product = dbGet("SELECT id,name,sku,oem_code,stock,min_stock FROM products ORDER BY id LIMIT 1") ?: ['name'=>'Sản phẩm kiểm tra','sku'=>'TEST','oem_code'=>'','stock'=>0,'min_stock'=>5];
    if (sendInventoryLowStockEmail($email, $product)) {
        flash('success', 'Đã gửi email kiểm tra tới ' . $email . '.');
    } else {
        $smtpError = function_exists('smtpLastError') ? smtpLastError() : '';
        if (str_contains($smtpError, '535')) {
            flash('error', 'Gmail từ chối tài khoản hoặc Mật khẩu ứng dụng SMTP. Hãy tạo App Password mới, dán vào ô Mật khẩu ứng dụng rồi lưu lại.');
        } else {
            flash('error', 'Máy chủ chưa gửi được email kiểm tra. Hãy kiểm tra cấu hình SMTP.');
        }
    }
    redirect('/admin/settings');
});
