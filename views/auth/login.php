<?php $title = 'Đăng nhập'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng nhập — Cooling</title>
<meta name="description" content="Đăng nhập tài khoản Cooling — Phụ tùng & Dịch vụ ô tô chính hãng.">
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
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Trang chủ","item":"https://coolingsystem.vn"},{"@type":"ListItem","position":2,"name":"Đăng nhập","item":"<?= htmlspecialchars($_canonicalUrl) ?>"}]}
</script>
</head>
<body>
<div class="auth-wrap"><div class="auth-card">
  <div class="auth-logo">COOLING</div>
  <div class="auth-sub">PHỤ TÙNG & DỊCH VỤ</div>
  <h2>Đăng nhập</h2>

  

  <form method="post" action="/auth/login">
    <?= csrfField() ?>
    <div class="form-group"><label>Email hoặc Số điện thoại <span class="req">*</span></label><input type="text" name="email" required autofocus placeholder="Email hoặc số điện thoại" value="<?= e($_POST['email'] ?? '') ?>"></div>
    <div class="form-group"><label>Mật khẩu <span class="req">*</span></label><input type="password" name="password" required minlength="6"></div>
    <div class="forgot-link"><a href="/auth/forgot">Quên mật khẩu?</a></div>
    <button type="submit" class="btn-gold">Đăng nhập</button>
  </form>
  <div class="auth-footer">Chưa có tài khoản? <a href="/auth/register">Đăng ký</a></div>
</div></div>

<script>
(function(){
  <?php if (!empty($__flash_data)): ?>
  <?php foreach ((array)$__flash_data as $f): ?>
    var msg = <?= json_encode($f['message'] ?? $f['msg']??'') ?>;
    var type = <?= json_encode($f['type']??'info') ?>;
    if(msg){
      // Create overlay
      var ov=document.createElement('div');
      ov.style.cssText='position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:99998;';
      document.body.appendChild(ov);
      // Create popup box
      var d=document.createElement('div');
      d.style.cssText='position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.9);z-index:99999;padding:28px 32px;border-radius:16px;font-size:15px;font-weight:600;box-shadow:0 24px 60px rgba(0,0,0,0.3);max-width:400px;width:90%;text-align:center;background:#fff;opacity:0;transition:all 0.25s ease;';
      var icon = type==='success'?'✅':type==='error'?'❌':'ℹ️';
      var iconColor = type==='success'?'#059669':type==='error'?'#dc2626':'#2563eb';
      var btnBg = type==='success'?'linear-gradient(135deg,#059669,#047857)':type==='error'?'linear-gradient(135deg,#dc2626,#b91c1c)':'linear-gradient(135deg,#c8a951,#b8860b)';
      d.innerHTML='<div style="font-size:48px;margin-bottom:12px">'+icon+'</div><div style="color:#1a3258;font-size:15px;line-height:1.6;margin-bottom:20px;word-break:break-word">'+msg+'</div><button onclick="this.parentElement.previousElementSibling.remove();this.parentElement.remove();" style="padding:10px 36px;background:'+btnBg+';color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px;min-width:100px">OK</button>';
      document.body.appendChild(d);
      setTimeout(function(){d.style.opacity='1';d.style.transform='translate(-50%,-50%) scale(1)';},10);
      // Also auto-dismiss after 6s
      setTimeout(function(){if(d.parentNode){d.style.opacity='0';d.style.transform='translate(-50%,-50%) scale(0.9)';setTimeout(function(){if(ov.parentNode)ov.remove();if(d.parentNode)d.remove();},300);}},6000);
      ov.onclick=function(){ov.remove();d.remove();};
    }
  <?php endforeach; ?>
  <?php endif; ?>
})();
</script>
<style>@keyframes slideDown{from{opacity:0;transform:translateX(-50%) translateY(-20px);}to{opacity:1;transform:translateX(-50%) translateY(0);}}</style>

<script>
(function(){
  <?php if (!empty($flash)): ?>
  <?php foreach ((array)$flash as $f): ?>
    var msg = <?= json_encode($f['message'] ?? $f['msg']??'') ?>;
    var type = <?= json_encode($f['type']??'info') ?>;
    if(msg){
      var d = document.createElement('div');
      var color = type === 'success' ? '#16a34a' : (type === 'error' ? '#dc2626' : '#2563eb');
      d.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;padding:16px 24px;border-radius:4px;font-size:16px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.15);background:#fff;border-left:4px solid '+color+';color:'+color+';opacity:0;transform:translateX(100%);transition:all 0.3s ease;';
      d.innerHTML = msg;
      document.body.appendChild(d);
      
      // Animate in
      setTimeout(function(){
        d.style.opacity = '1';
        d.style.transform = 'translateX(0)';
      }, 10);
      
      // Auto dismiss
      setTimeout(function(){
        d.style.opacity = '0';
        d.style.transform = 'translateX(100%)';
        setTimeout(function(){ d.remove(); }, 300);
      }, 4000);
    }
  <?php endforeach; ?>
  <?php endif; ?>
})();
</script>
</body></html>