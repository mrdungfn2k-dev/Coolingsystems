<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Nhân viên - CoolingSystem</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root { --navy:#0a192f; --gold:#d4af37; --bg:#f4f7f6; }
        body { font-family:'Inter',sans-serif; background:var(--bg); margin:0; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .auth-card { background:#fff; width:100%; max-width:400px; padding:40px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.05); text-align:center; border-top:4px solid var(--gold); }
        .auth-card h1 { margin:0 0 8px; font-size:24px; color:var(--navy); font-weight:800; }
        .auth-card p { margin:0 0 24px; font-size:14px; color:#666; }
        .staff-badge { display:inline-block; background:#fff8e6; color:#a8853a; border:1px solid #ecd9a6; font-size:12px; font-weight:700; padding:4px 12px; border-radius:20px; margin-bottom:16px; letter-spacing:.04em; }
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
    <div style="margin-bottom:14px;">
        <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="var(--navy)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
    </div>
    <div class="staff-badge">KHU VỰC NHÂN VIÊN</div>
    <h1>Đăng nhập Nhân viên</h1>
    <p>Dành riêng cho nhân viên được phân quyền</p>
    <?php $flash = getFlash(); foreach($flash as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <form method="post" action="/staff/login">
        <?= csrfField() ?>
        <div class="form-group">
            <label>Email đăng nhập</label>
            <input type="email" name="email" required autofocus placeholder="nhanvien@coolingsystem.vn">
        </div>
        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-submit">Đăng nhập</button>
    </form>
    <div style="margin-top:24px;font-size:12px;color:#888;">
        <a href="/" style="color:var(--navy);text-decoration:none;">&larr; Quay lại trang chủ</a>
    </div>
</div>
</body>
</html>
