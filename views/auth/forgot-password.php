<?php $title = 'Quên mật khẩu'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Quên mật khẩu — Cooling</title>
<meta name="description" content="Khôi phục mật khẩu tài khoản Cooling.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/cooling.css?v=<?= time() ?>">
<?php
$_canonicalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_canonicalUrl = 'https://coolingsystem.vn' . rtrim($_canonicalPath, '/');
?>
<link rel="canonical" href="<?= htmlspecialchars($_canonicalUrl) ?>">
<meta name="robots" content="noindex, nofollow">
<style>
body{background:linear-gradient(135deg,#f5f7fa 0%,#e4e9f0 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;font-family:'Inter',sans-serif}
.auth-wrap{width:100%;max-width:440px;padding:20px}
.auth-card{background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.08);padding:40px 36px}
.auth-logo{text-align:center;margin-bottom:8px;font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#1a3258;letter-spacing:2px}
.auth-sub{text-align:center;font-size:11px;color:#888;letter-spacing:3px;margin-bottom:24px}
.auth-card h2{text-align:center;color:#1a3258;font-family:'Playfair Display',serif;margin-bottom:12px;font-size:22px}
.auth-card .desc{text-align:center;color:#666;font-size:13px;margin-bottom:24px;line-height:1.5}
.auth-card .form-group{margin-bottom:16px}
.auth-card label{display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px}
.auth-card input[type=email]{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box;transition:border 0.2s}
.auth-card input:focus{border-color:#1a3258;outline:none}
.auth-card .req{color:#dc2626}
.btn-gold{background:linear-gradient(135deg,#c8a84e,#b8942e);color:#fff;border:none;padding:12px;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;width:100%;transition:opacity 0.2s}
.btn-gold:hover{opacity:0.9}
.auth-footer{text-align:center;margin-top:20px;font-size:13px;color:#666}
.auth-footer a{color:#1a3258;font-weight:700;text-decoration:none}
.flash-error{background:#fef2f2;color:#dc2626;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:13px;border:1px solid #fecaca}
.flash-success{background:#f0fdf4;color:#16a34a;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:13px;border:1px solid #bbf7d0}
</style>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Trang chủ","item":"https://coolingsystem.vn"},{"@type":"ListItem","position":2,"name":"Quên mật khẩu","item":"<?= htmlspecialchars($_canonicalUrl) ?>"}]}
</script>
</head>
<body>
<div class="auth-wrap"><div class="auth-card">
  <div class="auth-logo">COOLING</div>
  <div class="auth-sub">PHỤ TÙNG & DỊCH VỤ</div>
  <h2>Quên mật khẩu</h2>
  <p class="desc">Nhập email đã đăng ký, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu cho bạn.</p>
  <?php if (!empty($_SESSION['flash_error'])): ?><div class="flash-error"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div><?php endif; ?>
  <?php if (!empty($_SESSION['flash_success'])): ?><div class="flash-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div><?php endif; ?>
  <form method="post" action="/auth/forgot-password">
    <?= csrfField() ?>
    <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" name="email" required autofocus placeholder="Nhập email của bạn"></div>
    <button type="submit" class="btn-gold">Gửi yêu cầu</button>
  </form>
  <div class="auth-footer"><a href="/auth/login">← Quay lại đăng nhập</a></div>
</div></div>
</body></html>