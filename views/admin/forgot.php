<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu Quản trị - CoolingSystem</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="48x48" href="/favicon-cooling-round-48x48.png?v=20260730-exact-logo-v2">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-cooling-round-32x32.png?v=20260730-exact-logo-v2">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-cooling-round-16x16.png?v=20260730-exact-logo-v2">
    <link rel="shortcut icon" href="/favicon-cooling-round.ico?v=20260730-exact-logo-v2">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-cooling-round.png?v=20260730-exact-logo-v2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root { --navy:#0a192f; --bg:#f4f7f6; }
        body { font-family:'Inter',sans-serif; background:var(--bg); margin:0; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .auth-card { background:#fff; width:100%; max-width:400px; padding:40px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.05); text-align:center; }
        .auth-card h1 { margin:0 0 8px; font-size:24px; color:var(--navy); font-weight:800; }
        .auth-card p { margin:0 0 24px; font-size:14px; color:#666; }
        .form-group { text-align:left; margin-bottom:16px; }
        .form-group label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:#333; }
        .form-group input { width:100%; padding:12px 14px; border:1px solid #ddd; border-radius:8px; font-size:14px; box-sizing:border-box; transition:border 0.2s; }
        .form-group input:focus { border-color:var(--navy); outline:none; }
        .btn-submit { width:100%; padding:12px; background:var(--navy); color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:600; cursor:pointer; margin-top:8px; transition:background 0.2s; }
        .btn-submit:hover { background:#112240; }
        .alert { padding:12px; border-radius:8px; font-size:13px; margin-bottom:20px; text-align:left; }
        .alert-error { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; }
        .alert-success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
    </style>
</head>
<body>
<div class="auth-card">
    <div style="margin-bottom:18px;"><svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="var(--navy)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></div>
    <h1>Quên mật khẩu</h1>
    <p>Khôi phục mật khẩu tài khoản quản trị — nhập email để nhận mã OTP</p>
    <?php $flash = getFlash(); foreach($flash as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <form method="post" action="/admin/forgot">
        <?= csrfField() ?>
        <div class="form-group">
            <label>Email quản trị</label>
            <input type="email" name="email" required autofocus placeholder="admin@coolingsystems.vn">
        </div>
        <button type="submit" class="btn-submit">Gửi mã OTP</button>
    </form>
    <div style="margin-top:24px;font-size:12px;color:#888;"><a href="/admin/login" style="color:var(--navy);text-decoration:none;">&larr; Quay lại đăng nhập</a></div>
</div>
</body>
</html>
