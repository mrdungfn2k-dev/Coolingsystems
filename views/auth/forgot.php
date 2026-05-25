<?php require __DIR__ . '/../partials/head-auth.php'; ?>
<section class="block"><div class="wrap" style="max-width:480px">
<div class="sec-card"><div class="panel-body" style="padding:40px">
  <div class="text-center mb-3">
    <svg width="100" height="42" viewBox="0 0 480 200"><use href="#cooling-logo"/></svg>
  </div>
  <h2 class="serif text-navy mb-1 text-center">Quên mật khẩu</h2>
  <p class="text-center fs-13 text-muted mb-3">Nhập email đăng ký — chúng tôi sẽ gửi mã OTP 6 số</p>
  <form method="post" action="/auth/forgot">
    <?= csrfField() ?>
    <div class="form-group">
      <label>Email đăng ký <span class="req">*</span></label>
      <input type="email" name="email" required autofocus placeholder="VD: ten@gmail.com">
    </div>
    <button type="submit" class="btn btn-gold btn-block btn-lg">Gửi mã OTP</button>
  </form>
  <div class="text-center mt-3 fs-13 text-muted">
    <a href="/auth/login" class="text-navy">← Quay lại đăng nhập</a>
  </div>
</div></div>
</div></section>
<?php require __DIR__ . '/../partials/foot-auth.php'; ?>
