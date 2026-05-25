<?php require __DIR__.'/../partials/head.php'; ?>
<section class="block"><div class="wrap" style="max-width:520px"><div class="sec-card"><div class="panel-body" style="padding:36px">
  <h2 class="serif text-navy mb-2 text-center" style="font-size:24px">Đăng nhập Đối tác</h2>
  <form method="post" action="/partner/login"><?=csrfField()?>
    <div class="form-group"><label>Email</label><input type="email" name="email" required autofocus></div>
    <div class="form-group"><label>Mật khẩu</label><input type="password" name="password" required></div>
    <button type="submit" class="btn btn-gold btn-block btn-lg">Đăng nhập</button>
  </form>
  <div class="text-center mt-3 fs-13">Chưa có gian hàng? <a href="/partner/register" class="text-gold fw-700">Đăng ký ngay</a></div>
</div></div></div></section>
<?php require __DIR__.'/../partials/foot.php'; ?>