<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng ký Đại lý Phân phối | Cooling Systems</title>
  <link rel="manifest" href="/app/manifest.json">
  <meta name="theme-color" content="#1a3258">
  <style>
    :root {
      --navy-dark: #1a3258;
      --orange-accent: #f26a1b;
      --orange-hover: #d8570e;
      --gray-bg: #f4f6f9;
      --gray-border: #e8ecf3;
      --text-main: #1e293b;
      --text-sub: #64748b;
      --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-family);
      background-color: #0d1b2f;
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .agency-top-bar {
      background: var(--navy-dark);
      padding: 16px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .agency-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: #ffffff;
    }
    .agency-brand img {
      width: 36px;
      height: 36px;
      border-radius: 8px;
    }
    .agency-brand-title {
      font-weight: 800;
      font-size: 15px;
      letter-spacing: -0.2px;
      color: #ffffff;
    }
    .agency-brand-sub {
      font-size: 10px;
      opacity: 0.8;
      font-weight: 600;
    }
    .agency-reg-wrap {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px;
    }
    .agency-reg-card {
      width: 100%;
      max-width: 680px;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
      overflow: hidden;
      border: 1px solid var(--gray-border);
    }
    .agency-reg-header {
      background: var(--navy-dark);
      color: #ffffff;
      padding: 24px;
      text-align: center;
    }
    .agency-reg-header h1 {
      font-size: 20px;
      font-weight: 800;
      color: #ffffff;
      margin: 0 0 6px;
    }
    .agency-reg-header p {
      font-size: 12.5px;
      opacity: 0.85;
      margin: 0;
    }
    .agency-reg-body {
      padding: 28px;
    }
    .reg-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    @media (max-width: 600px) {
      .reg-grid { grid-template-columns: 1fr; }
    }
    .form-field {
      margin-bottom: 16px;
    }
    .form-field label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: var(--navy-dark);
      margin-bottom: 6px;
    }
    .form-field input, .form-field select {
      width: 100%;
      height: 42px;
      border-radius: 10px;
      border: 1px solid var(--gray-border);
      padding: 0 12px;
      font-size: 13.5px;
      outline: none;
    }
    .benefits-box {
      background: #f0f9ff;
      border: 1px solid #bae6fd;
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 24px;
    }
    .benefits-box h4 {
      font-size: 13.5px;
      font-weight: 800;
      color: #0369a1;
      margin: 0 0 8px;
    }
    .benefits-box ul {
      margin: 0;
      padding-left: 18px;
      font-size: 12.5px;
      color: #0c4a6e;
      line-height: 1.6;
    }
    .btn-agency-primary {
      width: 100%;
      height: 46px;
      background: var(--orange-accent);
      color: #ffffff;
      border: none;
      border-radius: 10px;
      font-size: 14.5px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.15s, background-color 0.15s;
    }
    .btn-agency-primary:hover {
      background: var(--orange-hover);
    }
    .agency-footer {
      background: var(--navy-dark);
      color: rgba(255, 255, 255, 0.6);
      text-align: center;
      padding: 16px;
      font-size: 11.5px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
  </style>
</head>
<body>

  <!-- Standalone Agency Top Header (NO PUBLIC B2C STORE NAV) -->
  <header class="agency-top-bar">
    <a href="/agency/login" class="agency-brand">
      <img src="/favicon-512x512.png" alt="Cooling Logo" onerror="this.src='/public/favicon-512x512.png'">
      <div>
        <div class="agency-brand-title">COOLING SYSTEMS</div>
        <div class="agency-brand-sub">CỔNG DÀNH RIÊNG CHO ĐẠI LÝ</div>
      </div>
    </a>
    <div style="font-size:12px; color:#ffffff; opacity:0.8; font-weight:600;">
      Hotline Đối tác: 0703.0703.21
    </div>
  </header>

  <!-- Registration Card Center -->
  <div class="agency-reg-wrap">
    <div class="agency-reg-card">
      <div class="agency-reg-header">
        <h1>ĐĂNG KÝ MẠNG LƯỚI ĐẠI LÝ PHÂN PHỐI</h1>
        <p>Hưởng chiết khấu hoa hồng linh hoạt từ 5% đến 10% cho mọi đơn hàng bán ra</p>
      </div>

      <div class="agency-reg-body">
        <?php foreach (getFlash() as $f): ?>
          <div style="background:<?= $f['type']==='error'?'#fef2f2':'#ecfdf5' ?>;color:<?= $f['type']==='error'?'#b91c1c':'#047857' ?>;padding:12px 16px;border-radius:8px;font-size:13.5px;font-weight:700;margin-bottom:20px;">
            <?= e($f['message']) ?>
          </div>
        <?php endforeach; ?>

        <div class="benefits-box">
          <h4>Quyền lợi đặc quyền dành cho Đại lý / Nhà phân phối:</h4>
          <ul>
            <li><strong>Chiết khấu / Hoa hồng 5% đến 10%</strong> trực tiếp trên từng đơn hàng hoàn tất.</li>
            <li><strong>Mã giới thiệu (Affiliate Link)</strong> để tự động quản lý tuyến Gara thuộc đại lý.</li>
            <li><strong>Bảng giá buôn gốc tốt nhất thị trường</strong> và chính sách hỗ trợ công nợ gối đầu.</li>
            <li><strong>Bảo hành điện tử 1 đổi 1 trong 12 tháng</strong> đối với Lốc điều hòa và Dàn lạnh.</li>
          </ul>
        </div>

        <form method="post" action="/agency/register" enctype="multipart/form-data">
          <?= csrfField() ?>
          <div class="reg-grid">
            <div class="form-field">
              <label>Tên Đại lý / Cửa hàng Phụ tùng *</label>
              <input type="text" name="agency_name" required placeholder="VD: Đại Lý Phụ Tùng Hải Hà" value="<?= e($_POST['agency_name'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label>Họ tên Người đại diện *</label>
              <input type="text" name="owner_name" required placeholder="VD: Nguyễn Văn Mạnh" value="<?= e($_POST['owner_name'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label>Số điện thoại liên hệ *</label>
              <input type="text" name="phone" required placeholder="VD: 0912345678" value="<?= e($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label>Email liên hệ *</label>
              <input type="email" name="email" required placeholder="daily@gmail.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label>Mã số thuế / Số ĐKKD (Bắt buộc) *</label>
              <input type="text" name="tax_code" required placeholder="VD: 0101234567" value="<?= e($_POST['tax_code'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label>Mật khẩu đăng nhập *</label>
              <input type="password" name="password" required placeholder="••••••••">
            </div>
          </div>

          <div class="form-field">
            <label>Địa chỉ Cửa hàng / Kho hàng Đại lý thực tế *</label>
            <input type="text" name="address" required placeholder="VD: Số 88 Giải Phóng, P. Phương Mai, Q. Đống Đa, Hà Nội" value="<?= e($_POST['address'] ?? '') ?>">
          </div>

          <div style="border-top:1px solid var(--gray-border); padding-top:16px; margin-top:16px;">
            <h4 style="font-size:13px; font-weight:800; color:var(--navy-dark); margin-bottom:12px;">TẢI LÊN HỒ SƠ PHÁP LÝ ĐẠI LÝ</h4>
            
            <div class="reg-grid">
              <div class="form-field">
                <label>1. Ảnh Bảng hiệu Cửa hàng / Đại lý *</label>
                <input type="file" name="signboard_image" accept="image/*" required>
              </div>
              <div class="form-field">
                <label>2. Giấy phép ĐKKD / Mã số thuế HKD *</label>
                <input type="file" name="license_image" accept="image/*" required>
              </div>
            </div>
          </div>

          <button type="submit" class="btn-agency-primary" style="margin-top:10px;">GỬI HỒ SƠ ĐĂNG KÝ ĐẠI LÝ</button>

          <div style="text-align:center; margin-top:16px; font-size:13px; color:var(--text-sub);">
            Đã có tài khoản Đại lý? <a href="/agency/login" style="color:var(--navy-dark); font-weight:800; text-decoration:none;">Đăng nhập ngay</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <footer class="agency-footer">
    &copy; <?= date('Y') ?> Cooling Systems. Hệ thống Quản lý Kênh Phân Phối &amp; Đại lý Toàn Quốc.
  </footer>

</body>
</html>
