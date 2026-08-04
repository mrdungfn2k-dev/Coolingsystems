<?php require __DIR__ . '/../partials/head-auth.php'; ?>
<style>
.forgot-card-wrap {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  padding: 40px 16px;
}
.forgot-card {
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  padding: 36px 32px;
}
</style>

<div class="forgot-card-wrap">
  <div class="forgot-card">
    <div style="text-align:center; margin-bottom:18px;">
      <svg width="120" height="48" viewBox="0 0 480 200"><use href="#cooling-logo"/></svg>
    </div>

    <h2 style="font-size:20px; font-weight:800; color:#0b1d3a; text-align:center; margin-bottom:6px;">Quên mật khẩu</h2>
    <p style="text-align:center; font-size:13px; color:#64748b; margin-bottom:24px;">Nhập email đăng ký — chúng tôi sẽ gửi mã OTP 6 số để xác minh</p>

    <?php foreach (getFlash() as $f): ?>
      <div style="background:<?= $f['type']==='error'?'#fef2f2':'#ecfdf5' ?>;color:<?= $f['type']==='error'?'#991b1b':'#15803d' ?>;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:700;margin-bottom:16px;">
        <?= e($f['message']) ?>
      </div>
    <?php endforeach; ?>

    <form method="post" action="/auth/forgot">
      <?= csrfField() ?>
      <div class="form-group" style="margin-bottom:20px;">
        <label style="display:block; font-size:12.5px; font-weight:700; color:#0b1d3a; margin-bottom:6px;">Email đăng ký *</label>
        <input type="email" name="email" required autofocus placeholder="VD: ten@gmail.com" style="width:100%; height:42px; border-radius:8px; border:1px solid #cbd5e1; padding:0 12px; font-size:14px;">
      </div>
      <button type="submit" style="width:100%; height:44px; background:#0b1d3a; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:800; cursor:pointer;">
        GỬI MÃ OTP
      </button>
    </form>

    <div style="text-align:center; margin-top:24px; font-size:12.5px; color:#64748b; display:flex; justify-content:space-around;">
      <a href="/auth/login" style="color:#0b1d3a; font-weight:700; text-decoration:none;">Quay lại Đăng nhập Khách</a>
      <span>|</span>
      <a href="/agency/login" style="color:#0b1d3a; font-weight:800; text-decoration:none;">Quay lại Đăng nhập Đại lý</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../partials/foot-auth.php'; ?>
