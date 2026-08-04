<?php require __DIR__ . '/../partials/head.php'; ?>
<style>
.agency-auth-wrap {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--navy-dark);
  padding: 40px 16px;
}
.agency-auth-card {
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  border: 1px solid var(--line);
}
.agency-auth-header {
  background: var(--navy);
  color: #ffffff;
  padding: 28px 24px;
  text-align: center;
}
.agency-auth-header img {
  width: 60px;
  height: 60px;
  margin-bottom: 12px;
  border-radius: 12px;
}
.agency-auth-header h1 {
  font-size: 20px;
  font-weight: 800;
  margin: 0;
  color: #ffffff;
}
.agency-auth-header p {
  font-size: 12px;
  opacity: 0.85;
  margin: 6px 0 0;
}
.agency-auth-body {
  padding: 24px;
}
.agency-input-group {
  margin-bottom: 16px;
}
.agency-input-group label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: var(--navy-dark);
  margin-bottom: 6px;
}
.agency-input-group input {
  width: 100%;
  height: 44px;
  border-radius: 10px;
  border: 1px solid var(--line);
  padding: 0 14px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
}
.agency-input-group input:focus {
  border-color: var(--orange-accent);
}
.btn-agency-primary {
  width: 100%;
  height: 46px;
  background: var(--orange-accent);
  color: #ffffff;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.15s, background-color 0.15s;
}
.btn-agency-primary:hover {
  background: var(--orange-hover);
  transform: translateY(-1px);
}
</style>

<div class="agency-auth-wrap">
  <div class="agency-auth-card">
    <div class="agency-auth-header">
      <img src="/favicon-512x512.png" alt="Cooling Logo" onerror="this.src='/public/favicon-512x512.png'">
      <h1>ĐĂNG NHẬP CỔNG ĐẠI LÝ</h1>
      <p>Kênh dành riêng cho Nhà Phân Phối &amp; Đại lý Cấp 1 hệ thống Cooling Systems</p>
    </div>
    
    <div class="agency-auth-body">
      <?php foreach (getFlash() as $f): ?>
        <div style="background:<?= $f['type']==='error'?'#fef2f2':'#ecfdf5' ?>;color:<?= $f['type']==='error'?'#b91c1c':'#047857' ?>;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:700;margin-bottom:16px;">
          <?= e($f['message']) ?>
        </div>
      <?php endforeach; ?>

      <form method="post" action="/agency/login">
        <?= csrfField() ?>
        <div class="agency-input-group">
          <label>Số điện thoại / Mã số thuế Đại lý *</label>
          <input type="text" name="phone_email" required placeholder="0912 345 678 hoặc Mã số thuế..." value="<?= e($_POST['phone_email'] ?? '') ?>">
        </div>

        <div class="agency-input-group">
          <label>Mật khẩu *</label>
          <input type="password" name="password" required placeholder="••••••••">
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; font-size:12.5px;">
          <label style="display:flex; align-items:center; gap:6px; cursor:pointer; color:var(--ink-2); font-weight:600;">
            <input type="checkbox" name="remember" checked> Ghi nhớ đăng nhập
          </label>
          <a href="/auth/forgot-password" style="color:var(--navy); font-weight:700; text-decoration:none;">Quên mật khẩu?</a>
        </div>

        <button type="submit" class="btn-agency-primary">ĐĂNG NHẬP ĐẠI LÝ</button>

        <div style="text-align:center; margin-top:20px; font-size:13px; color:var(--ink-2);">
          Chưa có tài khoản Đại lý? <a href="/agency/register" style="color:var(--orange-accent); font-weight:800; text-decoration:none;">Đăng ký Đại lý mới</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../partials/foot.php'; ?>
