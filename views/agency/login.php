<?php
require_once __DIR__ . '/../../includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập Cổng Đại lý | Cooling Systems</title>
  <link rel="manifest" href="/app/manifest.json">
  <meta name="theme-color" content="#1a3258">
  <style>
    :root {
      --navy: #0b1d3a;
      --navy-main: #1a3258;
      --bg-color: #f8fafc;
      --border-color: #e2e8f0;
      --text-main: #1e293b;
      --text-sub: #64748b;
      --font-stack: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-stack);
      background-color: var(--bg-color);
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .agency-auth-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px;
    }
    .agency-card {
      width: 100%;
      max-width: 440px;
      background: #ffffff;
      border-radius: 14px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      border: 1px solid var(--border-color);
      padding: 36px 32px;
      text-align: center;
    }
    .agency-logo-wrap {
      width: 64px;
      height: 64px;
      background: #f1f5f9;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 18px;
    }
    .agency-logo-wrap img {
      width: 44px;
      height: 44px;
      border-radius: 50%;
    }
    .agency-title {
      font-size: 20px;
      font-weight: 800;
      color: var(--navy);
      margin-bottom: 6px;
    }
    .agency-subtitle {
      font-size: 13px;
      color: var(--text-sub);
      margin-bottom: 24px;
      line-height: 1.4;
    }
    .form-group {
      text-align: left;
      margin-bottom: 16px;
    }
    .form-group label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: var(--navy);
      margin-bottom: 6px;
    }
    .form-group input, .form-group select {
      width: 100%;
      height: 42px;
      border-radius: 8px;
      border: 1px solid #cbd5e1;
      padding: 0 12px;
      font-size: 13.5px;
      outline: none;
      transition: border-color 0.15s;
    }
    .form-group input:focus {
      border-color: var(--navy-main);
    }
    .btn-submit {
      width: 100%;
      height: 44px;
      background: var(--navy);
      color: #ffffff;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 800;
      cursor: pointer;
      margin-top: 8px;
      transition: background-color 0.15s;
    }
    .btn-submit:hover {
      background: var(--navy-main);
    }
    .btn-register-popup {
      width: 100%;
      height: 42px;
      background: transparent;
      color: var(--navy-main);
      border: 1px solid var(--navy-main);
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 12px;
      transition: background-color 0.15s;
    }
    .btn-register-popup:hover {
      background: #f1f5f9;
    }
    .agency-footer {
      text-align: center;
      padding: 20px;
      font-size: 12px;
      color: var(--text-sub);
    }

    /* Modal Popup Registration */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(11, 29, 58, 0.6);
      backdrop-filter: blur(3px);
      z-index: 99999;
      align-items: center;
      justify-content: center;
      padding: 20px 16px;
    }
    .modal-card {
      width: 100%;
      max-width: 680px;
      max-height: 90vh;
      overflow-y: auto;
      background: #ffffff;
      border-radius: 14px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
      padding: 28px;
      position: relative;
    }
    .modal-close-btn {
      position: absolute;
      top: 16px;
      right: 16px;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: #f1f5f9;
      border: none;
      font-size: 18px;
      font-weight: 800;
      color: #64748b;
      cursor: pointer;
    }
    .reg-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }
    @media (max-width: 550px) {
      .reg-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- Centered Login Card -->
  <div class="agency-auth-container">
    <div class="agency-card">
      <div class="agency-logo-wrap">
        <img src="/favicon-512x512.png" alt="Cooling Logo" onerror="this.src='/public/favicon-512x512.png'">
      </div>

      <h1 class="agency-title">Cổng Đăng nhập Đại lý</h1>
      <p class="agency-subtitle">Đăng nhập hệ thống quản lý kênh phân phối Cooling System</p>

      <?php foreach (getFlash() as $f): ?>
        <div style="background:<?= $f['type']==='error'?'#fef2f2':'#ecfdf5' ?>;color:<?= $f['type']==='error'?'#991b1b':'#15803d' ?>;padding:12px 14px;border-radius:8px;font-size:13px;font-weight:700;margin-bottom:16px;text-align:left;line-height:1.4;">
          <?= e($f['message']) ?>
        </div>
      <?php endforeach; ?>

      <form method="post" action="/agency/login">
        <?= csrfField() ?>
        <div class="form-group">
          <label>Số điện thoại / Mã số thuế Đại lý * (Bắt buộc)</label>
          <input type="text" name="phone_email" required placeholder="0912345678 hoặc Mã số thuế..." value="<?= e($_POST['phone_email'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Mật khẩu * (Bắt buộc)</label>
          <input type="password" name="password" required placeholder="••••••••">
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; font-size:12.5px;">
          <label style="display:flex; align-items:center; gap:6px; cursor:pointer; color:var(--text-sub); font-weight:600;">
            <input type="checkbox" name="remember" checked> Ghi nhớ
          </label>
          <a href="/auth/forgot-password" style="color:var(--navy); font-weight:700; text-decoration:none;">Quên mật khẩu?</a>
        </div>

        <button type="submit" class="btn-submit">Đăng nhập Đại lý</button>
        <button type="button" class="btn-register-popup" onclick="openRegModal()">Đăng ký Đại lý mới</button>
      </form>
    </div>
  </div>

  <footer class="agency-footer">
    &copy; <?= date('Y') ?> Cooling Systems. Tất cả quyền được bảo lưu.
  </footer>

  <!-- Modal Popup Registration (Nút Đăng ký Đại lý hiển thị Popup giữa màn hình) -->
  <div class="modal-overlay" id="regModal">
    <div class="modal-card">
      <button class="modal-close-btn" onclick="closeRegModal()">&times;</button>
      
      <h2 style="font-size:18px; font-weight:800; color:var(--navy); margin-bottom:4px;">ĐĂNG KÝ MẠNG LƯỚI ĐẠI LÝ PHÂN PHỐI</h2>
      <p style="font-size:12.5px; color:var(--text-sub); margin-bottom:18px;">Hưởng chiết khấu hoa hồng linh hoạt từ 5% đến 10% cho mọi đơn hàng hoàn tất</p>

      <form method="post" action="/agency/register" enctype="multipart/form-data" id="agencyRegForm">
        <?= csrfField() ?>
        <div class="reg-grid">
          <div class="form-group">
            <label>Tên Đại lý / Cửa hàng Phụ tùng * (Bắt buộc)</label>
            <input type="text" name="agency_name" required placeholder="VD: Đại Lý Phụ Tùng Hải Hà">
          </div>
          <div class="form-group">
            <label>Họ tên Người đại diện * (Bắt buộc)</label>
            <input type="text" name="owner_name" required placeholder="VD: Nguyễn Văn Mạnh">
          </div>
          <div class="form-group">
            <label>Số điện thoại liên hệ * (Bắt buộc)</label>
            <input type="text" name="phone" required placeholder="VD: 0912345678">
          </div>
          <div class="form-group">
            <label>Email liên hệ * (Bắt buộc)</label>
            <input type="email" name="email" required placeholder="daily@gmail.com">
          </div>
          <div class="form-group">
            <label>Mã số thuế / Số ĐKKD * (Bắt buộc)</label>
            <input type="text" name="tax_code" required placeholder="VD: 0101234567">
          </div>
          <div class="form-group">
            <label>Mật khẩu đăng nhập * (Bắt buộc)</label>
            <input type="password" name="password" required placeholder="••••••••">
          </div>
        </div>

        <div class="form-group">
          <label>Địa chỉ Cửa hàng / Kho hàng Đại lý thực tế * (Bắt buộc)</label>
          <input type="text" name="address" required placeholder="VD: Số 88 Giải Phóng, P. Phương Mai, Q. Đống Đa, Hà Nội">
        </div>

        <div style="border-top:1px solid #e2e8f0; padding-top:14px; margin-top:14px;">
          <h4 style="font-size:13px; font-weight:800; color:var(--navy); margin-bottom:12px;">TẢI LÊN HỒ SƠ XÁC THỰC PHÁP LÝ (BẮT BUỘC)</h4>
          
          <div class="form-group">
            <label style="color:#b91c1c;">1. Ảnh bảng hiệu Cửa hàng / Gara * (Bắt buộc)</label>
            <input type="file" name="signboard_image" accept="image/*" required>
            <small style="color:#64748b; font-size:11.5px;">Chụp rõ tên Gara/Đại lý, địa chỉ &amp; SĐT trên bảng hiệu mặt tiền.</small>
          </div>

          <div class="form-group">
            <label style="color:#b91c1c;">2. Giấy phép kinh doanh / Đăng ký HKD * (Bắt buộc)</label>
            <input type="file" name="license_image" accept="image/*,.pdf" required>
            <small style="color:#64748b; font-size:11.5px;">Ảnh chụp hoặc file PDF Đăng ký kinh doanh / Mã số thuế HKD.</small>
          </div>

          <div class="form-group">
            <label style="color:#b91c1c;">3. Tối thiểu 3 tấm ảnh chụp thực tế Cửa hàng / Gara * (Bắt buộc ≥ 3 ảnh)</label>
            <input type="file" name="real_images[]" id="realImagesInput" accept="image/*" multiple required>
            <small style="color:#64748b; font-size:11.5px;">Chụp các góc: Toàn cảnh xưởng/cửa hàng, khu vực kho hàng/kệ phụ tùng (Giữ phím Ctrl để chọn cùng lúc 3+ ảnh).</small>
          </div>
        </div>

        <button type="submit" class="btn-submit" style="margin-top:10px;">GỬI HỒ SƠ ĐĂNG KÝ ĐẠI LÝ</button>
      </form>
    </div>
  </div>

  <script>
  function openRegModal() {
    document.getElementById('regModal').style.display = 'flex';
  }
  function closeRegModal() {
    document.getElementById('regModal').style.display = 'none';
  }
  window.onclick = function(e) {
    var modal = document.getElementById('regModal');
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  };

  document.getElementById('agencyRegForm').addEventListener('submit', function(e) {
    var input = document.getElementById('realImagesInput');
    if (input.files.length < 3) {
      e.preventDefault();
      alert('Vui lòng chọn tối thiểu 3 tấm ảnh chụp thực tế Cửa hàng/Gara (Giữ phím Ctrl khi chọn tệp).');
      return false;
    }
  });
  </script>

</body>
</html>
