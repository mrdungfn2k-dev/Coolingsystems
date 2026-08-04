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
      --navy-dark: #1a3258;
      --navy-main: #1a3258;
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
    .agency-auth-wrap {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px;
    }
    .agency-auth-card {
      width: 100%;
      max-width: 440px;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
      overflow: hidden;
      border: 1px solid var(--gray-border);
    }
    .agency-auth-header {
      background: var(--navy-dark);
      color: #ffffff;
      padding: 28px 24px;
      text-align: center;
    }
    .agency-auth-header img {
      width: 64px;
      height: 64px;
      margin-bottom: 12px;
      border-radius: 12px;
    }
    .agency-auth-header h1 {
      font-size: 19px;
      font-weight: 800;
      color: #ffffff;
      margin: 0;
    }
    .agency-auth-header p {
      font-size: 12px;
      opacity: 0.85;
      margin-top: 6px;
      line-height: 1.4;
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
      border: 1px solid var(--gray-border);
      padding: 0 14px;
      font-size: 13.5px;
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

  <!-- Auth Card Center -->
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

          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; font-size:12px;">
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; color:var(--text-sub); font-weight:600;">
              <input type="checkbox" name="remember" checked> Ghi nhớ đăng nhập
            </label>
            <a href="/auth/forgot-password" style="color:var(--navy-dark); font-weight:700; text-decoration:none;">Quên mật khẩu?</a>
          </div>

          <button type="submit" class="btn-agency-primary">ĐĂNG NHẬP ĐẠI LÝ</button>

          <div style="text-align:center; margin-top:20px; font-size:12.5px; color:var(--text-sub);">
            Chưa có tài khoản Đại lý? <a href="/agency/register" style="color:var(--orange-accent); font-weight:800; text-decoration:none;">Đăng ký Đại lý mới</a>
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
