<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Super Admin - CoolingSystem</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="48x48" href="/favicon-cooling-round-48x48.png?v=20260818-logo-v1">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-cooling-round-32x32.png?v=20260818-logo-v1">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-cooling-round-16x16.png?v=20260818-logo-v1">
    <link rel="shortcut icon" href="/favicon-cooling-round.ico?v=20260818-logo-v1">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-cooling-round.png?v=20260818-logo-v1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root { --navy:#0a192f; --gold:#c9a14a; --bg:#0f1626; }
        body { font-family:'Inter',sans-serif; background:var(--bg); margin:0; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .auth-card { background:#fff; width:100%; max-width:400px; padding:40px; border-radius:14px; box-shadow:0 20px 50px rgba(0,0,0,0.4); text-align:center; border-top:5px solid var(--gold); }
        .sa-badge { display:inline-block; background:#1a2238; color:var(--gold); border:1px solid var(--gold); font-size:11px; font-weight:800; letter-spacing:.12em; padding:5px 14px; border-radius:20px; margin-bottom:14px; }
        .auth-card h1 { margin:0 0 8px; font-size:24px; color:var(--navy); font-weight:800; }
        .auth-card p { margin:0 0 24px; font-size:14px; color:#666; }
        .form-group { text-align:left; margin-bottom:16px; }
        .form-group label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:#333; }
        .form-group input { width:100%; padding:12px 14px; border:1px solid #ddd; border-radius:8px; font-size:14px; box-sizing:border-box; transition:border 0.2s; }
        .form-group input:focus { border-color:var(--navy); outline:none; }
        .btn-submit { width:100%; padding:12px; background:var(--navy); color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:700; cursor:pointer; margin-top:8px; transition:background 0.2s; }
        .btn-submit:hover { background:#112240; }
        .alert { padding:12px; border-radius:8px; font-size:13px; margin-bottom:20px; text-align:left; }
        .alert-error { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; }
        .alert-success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
    </style>
</head>
<body>
<div class="auth-card">
    <div style="margin-bottom:14px;"><svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="#0a192f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 6 6 .9-4.5 4.3 1 6.1L12 17l-5.5 2.3 1-6.1L3 8.9 9 8z"></path></svg></div>
    <div class="sa-badge">SUPER ADMIN</div>
    <h1>Đăng nhập Super Admin</h1>
    <p>Khu vực quản trị cấp cao</p>
    <?php $flash = getFlash(); foreach($flash as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <form method="post" action="/superadmin-k9x27c">
        <?= csrfField() ?>
        <div class="form-group"><label>Email</label><input type="email" name="email" required autofocus placeholder="superadmin@..."></div>
        <div class="form-group"><label>Mật khẩu</label><input type="password" name="password" required placeholder="••••••••"></div>
        <button type="submit" class="btn-submit">Đăng nhập Super Admin</button>
    </form>
</div>
</body>
</html>
