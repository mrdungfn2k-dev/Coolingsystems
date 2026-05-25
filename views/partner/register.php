<?php require __DIR__.'/../partials/head.php'; ?>
<section class="block"><div class="wrap" style="max-width:760px"><div class="sec-card">
  <div class="sec-head"><div class="title"><span class="bar"></span><h2>Đăng ký bán hàng trên Cooling</h2></div></div>
  <div class="panel-body"><div class="alert alert-info"><strong>Phí sàn 5%</strong> trên mỗi đơn bán thành công</div>
  <form method="post" action="/partner/register"><?=csrfField()?>
    <h3 class="serif text-navy mb-2 mt-3" style="font-size:18px">Thông tin gian hàng</h3>
    <div class="form-row"><div class="form-group"><label>Tên gian hàng *</label><input type="text" name="shop_name" required></div>
    <div class="form-group"><label>Loại hình *</label><select name="type" required><option value="individual">Cá nhân</option><option value="business">Doanh nghiệp</option></select></div></div>
    <div class="form-group"><label>Địa chỉ kho *</label><input type="text" name="warehouse_address" required></div>
    <?php if(!$user):?><h3 class="serif text-navy mb-2 mt-3" style="font-size:18px">Tài khoản đăng nhập</h3>
    <div class="form-row"><div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
    <div class="form-group"><label>Mật khẩu *</label><input type="password" name="password" required minlength="6"></div></div><?php endif;?>
    <div class="form-group"><label>SĐT liên hệ *</label><input type="tel" name="contact_phone" required pattern="^0[1-9][0-9]{8}$"></div>
    <h3 class="serif text-navy mb-2 mt-3" style="font-size:18px">Tài khoản nhận tiền</h3>
    <div class="form-row"><div class="form-group"><label>Ngân hàng *</label><input type="text" name="bank_name" required></div>
    <div class="form-group"><label>Số TK *</label><input type="text" name="bank_account_number" required></div></div>
    <div class="form-group"><label>Chủ TK *</label><input type="text" name="bank_account_holder" required></div>
    <div class="form-group mt-3" style="background:var(--gold-soft);padding:14px;border-radius:3px"><label><input type="checkbox" name="tos_agree" required> Tôi đồng ý điều khoản & phí sàn 5%</label></div>
    <button type="submit" class="btn btn-gold btn-lg">Gửi hồ sơ đăng ký</button>
  </form></div>
</div></div></section>
<?php require __DIR__.'/../partials/foot.php'; ?>