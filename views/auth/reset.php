<?php require __DIR__ . '/../partials/head-auth.php'; ?>
<style>
.reset-card-wrap {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  padding: 40px 16px;
}
.reset-card {
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  padding: 36px 32px;
}
</style>

<div class="reset-card-wrap">
  <div class="reset-card">
    <div style="text-align:center; margin-bottom:18px;">
      <svg width="120" height="48" viewBox="0 0 480 200"><use href="#cooling-logo"/></svg>
    </div>

    <h2 style="font-size:20px; font-weight:800; color:#0b1d3a; text-align:center; margin-bottom:6px;">Đặt lại mật khẩu</h2>
    <p style="text-align:center; font-size:13px; color:#64748b; margin-bottom:24px;">Nhập mã OTP đã gửi về <strong><?= e($reset_email ?? '') ?></strong></p>

    <?php foreach (getFlash() as $f): ?>
      <div style="background:<?= $f['type']==='error'?'#fef2f2':'#ecfdf5' ?>;color:<?= $f['type']==='error'?'#991b1b':'#15803d' ?>;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:700;margin-bottom:16px;">
        <?= e($f['message']) ?>
      </div>
    <?php endforeach; ?>

    <form method="post" action="/auth/reset" id="resetForm" onsubmit="return validateResetForm()">
      <?= csrfField() ?>
      <div class="form-group" style="margin-bottom:16px;">
        <label style="display:block; font-size:12.5px; font-weight:700; color:#0b1d3a; margin-bottom:6px;">Mã OTP (6 số) *</label>
        <input type="text" name="otp" required maxlength="6" pattern="[0-9]{6}"
               placeholder="______" autofocus
               style="width:100%; height:48px; border-radius:8px; border:1px solid #cbd5e1; font-size:26px; letter-spacing:10px; text-align:center; font-weight:800; color:#0b1d3a;">
      </div>

      <div class="form-group" style="margin-bottom:16px;">
        <label style="display:block; font-size:12.5px; font-weight:700; color:#0b1d3a; margin-bottom:6px;">Mật khẩu mới *</label>
        <input type="password" name="password" required minlength="8" id="npw" oninput="checkPwStrength()" style="width:100%; height:42px; border-radius:8px; border:1px solid #cbd5e1; padding:0 12px; font-size:14px;">
        <div id="pwRules" style="margin-top:8px; font-size:12px; line-height:1.8;">
          <div id="rule-len" style="color:#94a3b8">○ Tối thiểu 8 ký tự</div>
          <div id="rule-upper" style="color:#94a3b8">○ Ít nhất 1 chữ hoa (A-Z)</div>
          <div id="rule-lower" style="color:#94a3b8">○ Ít nhất 1 chữ thường (a-z)</div>
          <div id="rule-num" style="color:#94a3b8">○ Ít nhất 1 chữ số (0-9)</div>
        </div>
      </div>

      <div class="form-group" style="margin-bottom:20px;">
        <label style="display:block; font-size:12.5px; font-weight:700; color:#0b1d3a; margin-bottom:6px;">Nhập lại mật khẩu mới *</label>
        <input type="password" name="password2" required minlength="8" id="npw2" oninput="checkPwMatch()" style="width:100%; height:42px; border-radius:8px; border:1px solid #cbd5e1; padding:0 12px; font-size:14px;">
        <div id="pwMatch" style="margin-top:4px; font-size:12px; display:none;"></div>
      </div>

      <button type="submit" id="submitBtn" style="width:100%; height:44px; background:#0b1d3a; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:800; cursor:pointer;">
        ĐẶT LẠI MẬT KHẨU
      </button>
    </form>

    <div style="text-align:center; margin-top:20px; font-size:12.5px; color:#64748b; display:flex; justify-content:space-around;">
      <a href="/auth/login" style="color:#0b1d3a; font-weight:700; text-decoration:none;">Quay lại Đăng nhập Khách</a>
      <span>|</span>
      <a href="/agency/login" style="color:#0b1d3a; font-weight:800; text-decoration:none;">Quay lại Đăng nhập Đại lý</a>
    </div>
  </div>
</div>

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
  var p1 = document.getElementById('npw').value;
  var p2 = document.getElementById('npw2').value;
  var m = document.getElementById('pwMatch');
  if (!p2) { m.style.display = 'none'; return; }
  m.style.display = 'block';
  if (p1 === p2) {
    m.style.color = '#059669';
    m.textContent = '✓ Mật khẩu khớp';
  } else {
    m.style.color = '#dc2626';
    m.textContent = '✗ Mật khẩu chưa khớp';
  }
}
function validateResetForm() {
  var p1 = document.getElementById('npw').value;
  var p2 = document.getElementById('npw2').value;
  if (p1 !== p2) {
    alert('Mật khẩu nhập lại chưa khớp!');
    return false;
  }
  return true;
}
</script>

<?php require __DIR__ . '/../partials/foot-auth.php'; ?>
