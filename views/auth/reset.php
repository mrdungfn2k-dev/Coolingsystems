<?php require __DIR__ . '/../partials/head-auth.php'; ?>
<section class="block"><div class="wrap" style="max-width:480px">
<div class="sec-card"><div class="panel-body" style="padding:40px">
  <div class="text-center mb-3">
    <svg width="100" height="42" viewBox="0 0 480 200"><use href="#cooling-logo"/></svg>
  </div>
  <h2 class="serif text-navy mb-1 text-center">Đặt lại mật khẩu</h2>
  <p class="text-center fs-13 text-muted mb-3">Nhập mã OTP đã gửi về <strong><?= e($reset_email ?? '') ?></strong></p>
  <form method="post" action="/auth/reset" id="resetForm" onsubmit="return validateResetForm()">
    <?= csrfField() ?>
    <div class="form-group">
      <label>Mã OTP (6 số) <span class="req">*</span></label>
      <input type="text" name="otp" required maxlength="6" pattern="[0-9]{6}"
             placeholder="______" autofocus
             style="font-size:28px;letter-spacing:10px;text-align:center;font-weight:700">
    </div>
    <div class="form-group">
      <label>Mật khẩu mới <span class="req">*</span></label>
      <input type="password" name="password" required minlength="8" id="npw" oninput="checkPwStrength()">
      <div id="pwRules" style="margin-top:8px;font-size:12px;line-height:1.8">
        <div id="rule-len" style="color:#999">○ Tối thiểu 8 ký tự</div>
        <div id="rule-upper" style="color:#999">○ Ít nhất 1 chữ hoa (A-Z)</div>
        <div id="rule-lower" style="color:#999">○ Ít nhất 1 chữ thường (a-z)</div>
        <div id="rule-num" style="color:#999">○ Ít nhất 1 chữ số (0-9)</div>
      </div>
    </div>
    <div class="form-group">
      <label>Nhập lại mật khẩu mới <span class="req">*</span></label>
      <input type="password" name="password2" required minlength="8" id="npw2" oninput="checkPwMatch()">
      <div id="pwMatch" style="margin-top:4px;font-size:12px;display:none"></div>
    </div>
    <button type="submit" class="btn btn-navy btn-block btn-lg" id="submitBtn">Đặt lại mật khẩu</button>
  </form>
  <div class="text-center mt-2 fs-12 text-muted">
    Không nhận được OTP? <a href="/auth/forgot" class="text-navy">Gửi lại</a>
  </div>
</div></div>
</div></section>

<script>
function checkPwStrength() {
  var pw = document.getElementById('npw').value;
  var rules = [
    {id:'rule-len', ok: pw.length >= 8},
    {id:'rule-upper', ok: /[A-Z]/.test(pw)},
    {id:'rule-lower', ok: /[a-z]/.test(pw)},
    {id:'rule-num', ok: /[0-9]/.test(pw)}
  ];
  rules.forEach(function(r) {
    var el = document.getElementById(r.id);
    if (r.ok) {
      el.style.color = '#059669';
      el.textContent = '✓ ' + el.textContent.replace(/^[○✓✗] /, '');
    } else {
      el.style.color = '#dc2626';
      el.textContent = '✗ ' + el.textContent.replace(/^[○✓✗] /, '');
    }
  });
  checkPwMatch();
}
function checkPwMatch() {
  var pw = document.getElementById('npw').value;
  var pw2 = document.getElementById('npw2').value;
  var el = document.getElementById('pwMatch');
  if (!pw2) { el.style.display = 'none'; return; }
  el.style.display = 'block';
  if (pw === pw2) {
    el.style.color = '#059669';
    el.textContent = '✓ Mật khẩu khớp';
  } else {
    el.style.color = '#dc2626';
    el.textContent = '✗ Mật khẩu không khớp';
  }
}
function validateResetForm() {
  var pw = document.getElementById('npw').value;
  var pw2 = document.getElementById('npw2').value;
  if (pw.length < 8) { alert('Mật khẩu phải có tối thiểu 8 ký tự.'); return false; }
  if (!/[A-Z]/.test(pw)) { alert('Mật khẩu phải có ít nhất 1 chữ hoa (A-Z).'); return false; }
  if (!/[a-z]/.test(pw)) { alert('Mật khẩu phải có ít nhất 1 chữ thường (a-z).'); return false; }
  if (!/[0-9]/.test(pw)) { alert('Mật khẩu phải có ít nhất 1 chữ số (0-9).'); return false; }
  if (pw !== pw2) { alert('Mật khẩu không khớp.'); return false; }
  return true;
}
</script>

<?php require __DIR__ . '/../partials/foot-auth.php'; ?>
