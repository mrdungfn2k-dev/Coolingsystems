post('/admin/settings/smtp', function() {
    requireRole('admin', '/admin/login'); csrfCheck();
    $enabled = !empty($_POST['smtp_enabled']) ? '1' : '0';
    $host = strtolower(trim((string)($_POST['smtp_host'] ?? '')));
    $port = (int)($_POST['smtp_port'] ?? 587);
    $encryption = (string)($_POST['smtp_encryption'] ?? 'tls');
    $username = trim((string)($_POST['smtp_username'] ?? ''));
    $password = preg_replace('/\s+/', '', (string)($_POST['smtp_password'] ?? ''));
    $fromEmail = trim((string)($_POST['smtp_from_email'] ?? ''));
    $fromName = trim((string)($_POST['smtp_from_name'] ?? ''));
    $oldPassword = inventoryAlertSetting('smtp_password');
    if ($password === '') $password = preg_replace('/\s+/', '', $oldPassword);
    if ($enabled === '1') {
        if (!preg_match('/^[a-z0-9.-]+$/i', $host) || $port < 1 || $port > 65535 || !in_array($encryption, ['tls','ssl','none'], true) || !filter_var($username, FILTER_VALIDATE_EMAIL) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL) || $password === '') {
            flash('error', 'Cấu hình SMTP chưa hợp lệ. Hãy kiểm tra máy chủ, cổng, bảo mật, email, và mật khẩu ứng dụng.');
            redirect('/admin/settings'); return;
        }
    }
    foreach (['smtp_enabled'=>$enabled,'smtp_host'=>$host,'smtp_port'=>(string)$port,'smtp_encryption'=>$encryption,'smtp_username'=>$username,'smtp_password'=>$password,'smtp_from_email'=>$fromEmail,'smtp_from_name'=>$fromName] as $key=>$value) {
        dbRun('INSERT INTO settings (key,value) VALUES (?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value', [$key,$value]);
    }
    flash('success', 'Đã lưu cấu hình SMTP. Hãy dùng nút Gửi email kiểm tra trong phần cảnh báo tồn kho để xác nhận.');
    redirect('/admin/settings');
});
