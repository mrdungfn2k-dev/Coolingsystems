<?php $title = 'Đăng nhập'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng nhập — Cooling</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/cooling.css?v=<?= time() ?>">
<style>
body{background:linear-gradient(135deg,#f5f7fa 0%,#e4e9f0 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;font-family:'Inter',sans-serif}
.auth-wrap{width:100%;max-width:440px;padding:20px}
.auth-card{background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.08);padding:40px 36px}
.auth-logo{text-align:center;margin-bottom:8px;font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#1a3258;letter-spacing:2px}
.auth-sub{text-align:center;font-size:11px;color:#888;letter-spacing:3px;margin-bottom:24px}
.auth-card h2{text-align:center;color:#1a3258;font-family:'Playfair Display',serif;margin-bottom:24px;font-size:22px}
.auth-card .form-group{margin-bottom:16px}
.auth-card label{display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px}
.auth-card input[type=email],.auth-card input[type=password]{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box;transition:border 0.2s}
.auth-card input:focus{border-color:#1a3258;outline:none}
.req{color:#dc2626}
.btn-gold{background:linear-gradient(135deg,#c8a84e,#b8942e);color:#fff;border:none;padding:12px;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;width:100%;transition:opacity 0.2s}
.btn-gold:hover{opacity:0.9}
.forgot-link{text-align:right;margin-top:4px;margin-bottom:16px}
.forgot-link a{font-size:12px;color:#888;text-decoration:none}
.forgot-link a:hover{color:#1a3258}
.auth-footer{text-align:center;margin-top:20px;font-size:13px;color:#666}
.auth-footer a{color:#1a3258;font-weight:700;text-decoration:none}
.flash-error{background:#fef2f2;color:#dc2626;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;border:1px solid #fecaca}
.flash-success{background:#f0fdf4;color:#16a34a;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;border:1px solid #bbf7d0}
</style>
</head>
<body>
<div class="auth-wrap"><div class="auth-card">
  <div class="auth-logo">COOLING</div>
  <div class="auth-sub">PHỤ TÙNG & DỊCH VỤ</div>
  <h2>Đăng nhập</h2>

  <?php if (!empty($_SESSION['flash'])): foreach ((array)$_SESSION['flash'] as $f): ?>
    <div class="flash-<?= $f['type'] ?? 'error' ?>"><?= e($f['msg'] ?? '') ?></div>
  <?php endforeach; unset($_SESSION['flash']); endif; ?>

  <form method="post" action="/auth/login">
    <?= csrfField() ?>
    <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>"></div>
    <div class="form-group"><label>Mật khẩu <span class="req">*</span></label><input type="password" name="password" required minlength="6"></div>
    <div class="forgot-link"><a href="/auth/forgot">Quên mật khẩu?</a></div>
    <button type="submit" class="btn-gold">Đăng nhập</button>
  </form>
  <div class="auth-footer">Chưa có tài khoản? <a href="/auth/register">Đăng ký</a></div>
</div></div>

<script>
(function(){
  <?php if (!empty($_SESSION['flash'])): ?>
  <?php foreach ((array)$_SESSION['flash'] as $f): ?>
    var msg = <?= json_encode($f['msg']??'') ?>;
    var type = <?= json_encode($f['type']??'info') ?>;
    if(msg){
      var d=document.createElement('div');
      d.style.cssText='position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:9999;padding:14px 24px;border-radius:10px;font-size:14px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.15);animation:slideDown 0.3s ease;max-width:400px;text-align:center;';
      d.style.background=type==='success'?'#d1fae5':type==='error'?'#fee2e2':'#e0f2fe';
      d.style.color=type==='success'?'#065f46':type==='error'?'#991b1b':'#0c4a6e';
      d.innerHTML=(type==='success'?'✅ ':type==='error'?'❌ ':'ℹ️ ')+msg;
      document.body.appendChild(d);
      setTimeout(function(){d.style.opacity='0';d.style.transition='opacity 0.3s';setTimeout(function(){d.remove();},300);},4000);
    }
  <?php endforeach; unset($_SESSION['flash']); ?>
  <?php endif; ?>
})();
</script>
<style>@keyframes slideDown{from{opacity:0;transform:translateX(-50%) translateY(-20px);}to{opacity:1;transform:translateX(-50%) translateY(0);}}</style>
</body></html>