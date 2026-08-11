<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Quản trị viên - CoolingSystem</title>
    <meta name="description" content="Đăng nhập trang quản trị hệ thống CoolingSystem.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="48x48" href="/favicon-cooling-round-48x48.png?v=20260730-sharp-v3">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-cooling-round-32x32.png?v=20260730-sharp-v3">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-cooling-round-16x16.png?v=20260730-sharp-v3">
    <link rel="shortcut icon" href="/favicon-cooling-round.ico?v=20260730-sharp-v3">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-cooling-round.png?v=20260730-sharp-v3">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php
    $_canonicalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $_canonicalUrl = 'https://coolingsystems.vn' . rtrim($_canonicalPath, '/');
    ?>
    <link rel="canonical" href="<?= htmlspecialchars($_canonicalUrl) ?>">
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root { --navy:#0a192f; --gold:#d4af37; --bg:#f4f7f6; }
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
    <script type="application/ld+json">
    {"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Trang chủ","item":"https://coolingsystems.vn"},{"@type":"ListItem","position":2,"name":"Đăng nhập Admin","item":"<?= htmlspecialchars($_canonicalUrl) ?>"}]}
    </script>
</head>
<body>

<div class="auth-card">
    <div style="margin-bottom:24px;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--navy)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
    </div>
    <h1>Quản trị viên</h1>
    <p>Đăng nhập hệ thống quản lý CoolingSystem</p>

    <?php $flash = getFlash(); foreach($flash as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= str_contains($f['message'], '<') ? $f['message'] : e($f['message']) ?></div>
    <?php endforeach; ?>

    <form method="post" action="/admin/login">
        <?= csrfField() ?>
        <div class="form-group">
            <label>Email đăng nhập</label>
            <input type="email" name="email" required autofocus placeholder="admin@coolingsystems.vn" value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-submit">Đăng nhập Admin</button>
    </form>
    <div style="margin-top:18px;font-size:13px"><a href="/admin/forgot" style="color:var(--navy);text-decoration:none;font-weight:600">Quên mật khẩu?</a></div>
</div>

<script>
(function() {
  function initLockoutCountdown() {
    var timerEl = document.getElementById('lockoutTimer');
    var btnEl = document.querySelector('button[type="submit"]') || document.querySelector('.btn-submit');
    if (!timerEl) return;
    var until = parseInt(timerEl.getAttribute('data-until') || '0', 10);
    if (!until || until <= Math.floor(Date.now() / 1000)) return;

    var origText = btnEl ? (btnEl.getAttribute('data-orig-text') || btnEl.textContent.trim()) : 'Đăng nhập Admin';
    if (btnEl && !btnEl.getAttribute('data-orig-text')) {
      btnEl.setAttribute('data-orig-text', origText);
    }

    function update() {
      var now = Math.floor(Date.now() / 1000);
      var remaining = until - now;
      if (remaining <= 0) {
        timerEl.textContent = "00:00";
        if (btnEl) {
          btnEl.disabled = false;
          btnEl.style.opacity = "1";
          btnEl.style.cursor = "pointer";
          btnEl.textContent = origText;
        }
        location.reload();
        return;
      }
      var mins = Math.floor(remaining / 60);
      var secs = remaining % 60;
      var formatted = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
      timerEl.textContent = formatted;
      if (btnEl) {
        btnEl.disabled = true;
        btnEl.style.opacity = "0.65";
        btnEl.style.cursor = "not-allowed";
        btnEl.innerHTML = origText + ' (' + formatted + ')';
      }
    }

    update();
    setInterval(update, 1000);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLockoutCountdown);
  } else {
    initLockoutCountdown();
  }
})();
</script>

</body>
</html>
