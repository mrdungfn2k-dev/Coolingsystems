<?php $title = 'Đăng ký'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng ký tài khoản — Cooling</title>
<meta name="description" content="Đăng ký tài khoản Cooling — Phụ tùng & Dịch vụ ô tô chính hãng.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/cooling.css?v=<?= time() ?>">
<?php
$_canonicalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_canonicalUrl = 'https://coolingsystems.vn' . rtrim($_canonicalPath, '/');
?>
<link rel="canonical" href="<?= htmlspecialchars($_canonicalUrl) ?>">
<meta name="robots" content="noindex, nofollow">
<style>
body{background:linear-gradient(135deg,#f5f7fa 0%,#e4e9f0 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;font-family:'Inter',sans-serif;padding:20px 0}
.auth-wrap{width:100%;max-width:500px;padding:20px}
.auth-card{background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.08);padding:36px 32px}
.auth-logo{text-align:center;margin-bottom:6px;font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:#1a3258;letter-spacing:2px}
.auth-sub{text-align:center;font-size:10px;color:#888;letter-spacing:3px;margin-bottom:20px}
.auth-card h2{text-align:center;color:#1a3258;font-family:'Playfair Display',serif;margin-bottom:20px;font-size:20px}
.form-group{margin-bottom:14px;position:relative}
.form-group label{display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:5px}
.form-group input{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box;transition:border 0.2s;font-family:'Inter',sans-serif}
.form-group input:focus{border-color:#1a3258;outline:none}
.form-group input.valid{border-color:#059669!important}
.form-group input.invalid{border-color:#dc2626!important}
.field-msg{font-size:11px;margin-top:3px;min-height:14px;transition:all 0.2s}
.field-msg.ok{color:#059669}.field-msg.err{color:#dc2626}
.form-row{display:flex;gap:12px}
.form-row .form-group{flex:1}
.req{color:#dc2626}
.char-count{font-size:10px;color:#aaa;text-align:right;margin-top:2px}
.pw-rules{background:#f8f9fb;border-radius:8px;padding:10px 14px;margin-top:6px;font-size:11px}
.pw-rules div{margin:2px 0;color:#aaa;transition:color 0.2s}
.pw-rules div.ok{color:#059669}.pw-rules div.fail{color:#dc2626}
.btn-gold{background:linear-gradient(135deg,#c8a84e,#b8942e);color:#fff;border:none;padding:12px;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;width:100%;transition:opacity 0.2s;margin-top:6px}
.btn-gold:hover{opacity:0.9}
.btn-gold:disabled{opacity:0.5;cursor:not-allowed}
.auth-footer{text-align:center;margin-top:16px;font-size:13px;color:#666}
.auth-footer a{color:#1a3258;font-weight:700;text-decoration:none}
.flash-error{background:#fef2f2;color:#dc2626;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;border:1px solid #fecaca}
.flash-success{background:#f0fdf4;color:#16a34a;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;border:1px solid #bbf7d0}
.terms{font-size:12px;color:#888;text-align:center;margin-top:10px;line-height:1.5}
.terms a{color:#1a3258;text-decoration:underline}
@media(max-width:520px){.form-row{flex-direction:column;gap:0}.auth-card{padding:28px 20px}}
</style>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Trang chủ","item":"https://coolingsystems.vn"},{"@type":"ListItem","position":2,"name":"Đăng ký","item":"<?= htmlspecialchars($_canonicalUrl) ?>"}]}
</script>
</head>
<body>
<div class="auth-wrap"><div class="auth-card">
  <div class="auth-logo">COOLING</div>
  <div class="auth-sub">PHỤ TÙNG & DỊCH VỤ</div>
  <h2>Đăng ký tài khoản</h2>

  <?php if (!empty($flash)): foreach ((array)$flash as $f): ?>
    <div class="flash-<?= $f['type']??'error' ?>"><?= e($f['message'] ?? $f['msg']??'') ?></div>
  <?php endforeach;  endif; ?>

  <form method="post" action="/auth/register" id="regForm" onsubmit="return validateAll()">
    <?= csrfField() ?>

    <!-- Họ tên -->
    <div class="form-group">
      <label>Họ và tên <span class="req">*</span></label>
      <input type="text" name="full_name" id="fname" required maxlength="50"
             placeholder="Nguyễn Văn A" value="<?= e($_POST['full_name'] ?? '') ?>"
             oninput="validateName()">
      <div class="field-msg" id="fname-msg"></div>
    </div>

    <!-- Email + SĐT -->
    <div class="form-row">
      <div class="form-group">
        <label>Email <span class="req">*</span></label>
        <input type="email" name="email" id="email" required maxlength="100"
               placeholder="ten@gmail.com" value="<?= e($_POST['email'] ?? '') ?>"
               oninput="validateEmail()" onblur="validateEmail()">
        <div class="field-msg" id="email-msg"></div>
      </div>
      <div class="form-group">
        <label>Số điện thoại <span class="req">*</span></label>
        <input type="tel" name="phone" id="phone" required maxlength="10"
               placeholder="0912345678" value="<?= e($_POST['phone'] ?? '') ?>"
               oninput="filterPhone(this);validatePhone()" onkeypress="return onlyDigit(event)">
        <div class="field-msg" id="phone-msg"></div>
      </div>
    </div>

    <!-- Địa chỉ -->
    <div class="form-group">
      <label>Địa chỉ</label>
      <input type="text" name="address" id="address" maxlength="100"
             placeholder="Số nhà, đường, quận/huyện, tỉnh/thành phố" value="<?= e($_POST['address'] ?? '') ?>"
             oninput="filterSpecial(this);validateAddress()">
      <div class="char-count"><span id="addr-count">0</span>/100</div>
    </div>

    <!-- Mật khẩu -->
    <div class="form-row">
      <div class="form-group">
        <label>Mật khẩu <span class="req">*</span></label>
        <input type="password" name="password" id="pw" required
               placeholder="Tối thiểu 8 ký tự" oninput="validatePw()">
      </div>
      <div class="form-group">
        <label>Xác nhận <span class="req">*</span></label>
        <input type="password" name="password2" id="pw2" required
               placeholder="Nhập lại mật khẩu" oninput="validatePw()">
        <div class="field-msg" id="pw2-msg"></div>
      </div>
    </div>
    <div class="pw-rules" id="pw-rules">
      <div id="r-len">○ Tối thiểu 8 ký tự</div>
      <div id="r-upper">○ Có chữ hoa (A-Z)</div>
      <div id="r-lower">○ Có chữ thường (a-z)</div>
      <div id="r-num">○ Có chữ số (0-9)</div>
      <div id="r-spec">○ Có ký tự đặc biệt (!@#$%...)</div>
    </div>

    <div class="terms">
      Bằng việc đăng ký, bạn đồng ý với <a href="/page/dieu-khoan-bao-mat" target="_blank">Điều khoản bảo mật</a>
      và <a href="/page/chinh-sach" target="_blank">Chính sách</a> của chúng tôi.
    </div>

    <button type="submit" class="btn-gold" id="btnSubmit">Đăng ký</button>
  </form>
  <div class="auth-footer">Đã có tài khoản? <a href="/auth/login">Đăng nhập</a></div>
</div></div>

<script>
// === Filter: block special chars (except password) ===
function filterSpecial(el) {
  // Allow Vietnamese characters, letters, digits, spaces, comma, dot, slash
  // Use composing check to avoid breaking IME
  if (el.dataset.composing === '1') return;
  el.value = el.value.replace(/[^\p{L}\p{M}0-9\s,\.\/\-]/gu, '');
}

// === Filter: phone only digits ===
function filterPhone(el) {
  el.value = el.value.replace(/[^0-9]/g, '');
  if (el.value.length > 10) el.value = el.value.slice(0, 10);
}
function onlyDigit(e) {
  var c = e.which || e.keyCode;
  if (c < 48 || c > 57) { e.preventDefault(); return false; }
  return true;
}

// === Validate: Name ===
function validateName() {
  var el = document.getElementById('fname');
  var v = el.value.trim();
  var msg = document.getElementById('fname-msg');
  if (!v) { setMsg(msg,'err','Vui lòng nhập họ tên'); el.className='invalid'; return false; }
  if (v.length < 2) { setMsg(msg,'err','Họ tên quá ngắn'); el.className='invalid'; return false; }
  if (/[0-9]/.test(v)) { setMsg(msg,'err','Họ tên không được chứa số'); el.className='invalid'; return false; }
  if (/[!@#$%^&*()+=\[\]{};:'"<>?|~`]/.test(v)) { setMsg(msg,'err','Họ tên không được chứa ký tự đặc biệt'); el.className='invalid'; return false; }
  setMsg(msg,'ok','✓ Hợp lệ'); el.className='valid'; return true;
}

// === Validate: Email ===
function validateEmail() {
  var v = document.getElementById('email').value.trim().toLowerCase();
  var msg = document.getElementById('email-msg');
  var el = document.getElementById('email');
  // Remove special chars from email except @._-
  el.value = el.value.replace(/[^a-zA-Z0-9@.\-_]/g, '');
  v = el.value.trim().toLowerCase();
  if (!v) { setMsg(msg,'err','Vui lòng nhập email'); el.className='invalid'; return false; }
  // Basic format check
  var re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!re.test(v)) { setMsg(msg,'err','Email không hợp lệ (VD: ten@gmail.com)'); el.className='invalid'; return false; }
  // STRICT: Only allow @gmail.com
  var domain = v.split('@')[1];
  if (domain !== 'gmail.com') {
    setMsg(msg,'err','Chỉ chấp nhận email @gmail.com'); el.className='invalid'; return false;
  }
  setMsg(msg,'ok','✓ Email hợp lệ'); el.className='valid'; return true;
}

// === Validate: Phone ===
function validatePhone() {
  var v = document.getElementById('phone').value;
  var msg = document.getElementById('phone-msg');
  var el = document.getElementById('phone');
  if (!v) { setMsg(msg,'err','Vui lòng nhập SĐT'); el.className='invalid'; return false; }
  if (v.length > 0 && v[0] !== '0') { setMsg(msg,'err','SĐT phải bắt đầu bằng số 0'); el.className='invalid'; return false; }
  if (v.length >= 2) {
    var d2 = parseInt(v[1]);
    if (d2 < 1 || d2 > 9) { setMsg(msg,'err','Đầu số không hợp lệ (01-09)'); el.className='invalid'; return false; }
  }
  if (v.length < 10) { setMsg(msg,'err','SĐT phải đủ 10 số (còn thiếu '+(10-v.length)+')'); el.className='invalid'; return false; }
  if (v.length > 10) { setMsg(msg,'err','SĐT tối đa 10 số'); el.className='invalid'; return false; }
  // Check all same digit
  if (/^(\d)\1{9}$/.test(v)) { setMsg(msg,'err','SĐT không hợp lệ'); el.className='invalid'; return false; }
  setMsg(msg,'ok','✓ SĐT hợp lệ'); el.className='valid'; return true;
}

// === Validate: Address ===
function validateAddress() {
  var v = document.getElementById('address').value;
  document.getElementById('addr-count').textContent = v.length;
  if (v.length > 100) document.getElementById('address').value = v.slice(0,100);
}

// === Validate: Password ===
function validatePw() {
  var pw = document.getElementById('pw').value;
  var pw2 = document.getElementById('pw2').value;
  var rules = [
    {id:'r-len', ok: pw.length >= 8},
    {id:'r-upper', ok: /[A-Z]/.test(pw)},
    {id:'r-lower', ok: /[a-z]/.test(pw)},
    {id:'r-num', ok: /[0-9]/.test(pw)},
    {id:'r-spec', ok: /[^a-zA-Z0-9]/.test(pw)}
  ];
  var allOk = true;
  rules.forEach(function(r) {
    var el = document.getElementById(r.id);
    var txt = el.textContent.replace(/^[○✓✗]\s*/, '');
    if (r.ok) { el.className='ok'; el.textContent='✓ '+txt; }
    else { el.className='fail'; el.textContent='✗ '+txt; allOk=false; }
  });
  // Match check
  var msg2 = document.getElementById('pw2-msg');
  if (pw2) {
    if (pw === pw2) { setMsg(msg2,'ok','✓ Mật khẩu khớp'); }
    else { setMsg(msg2,'err','✗ Mật khẩu không khớp'); allOk=false; }
  } else { msg2.textContent=''; }
  return allOk;
}

function setMsg(el,cls,txt) { el.className='field-msg '+cls; el.textContent=txt; }

// === Final validate ===
function validateAll() {
  var ok = true;
  if (!validateName()) ok = false;
  if (!validateEmail()) ok = false;
  if (!validatePhone()) ok = false;
  if (!validatePw()) ok = false;
  if (!ok) {
    // Scroll to first error
    var first = document.querySelector('.invalid');
    if (first) first.scrollIntoView({behavior:'smooth',block:'center'});
  }
  return ok;
}

// Prevent filterSpecial from running during IME composition
document.addEventListener('compositionstart', function(e) {
  if (e.target && e.target.tagName === 'INPUT') e.target.dataset.composing = '1';
});
document.addEventListener('compositionend', function(e) {
  if (e.target && e.target.tagName === 'INPUT') {
    e.target.dataset.composing = '0';
    // Re-validate after composition ends
    if (e.target.id === 'fname') validateName();
  }
});
</script>

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