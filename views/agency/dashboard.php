<?php
require_once __DIR__ . '/../../includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bảng Điều Khiển Đại Lý | Cooling Systems</title>
  <link rel="manifest" href="/app/manifest.json">
  <meta name="theme-color" content="#0b1d3a">
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
    .agency-top-bar {
      background: var(--navy);
      padding: 14px 24px;
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
      width: 34px;
      height: 34px;
      border-radius: 8px;
    }
    .agency-brand-title {
      font-weight: 800;
      font-size: 14.5px;
      color: #ffffff;
    }
    .agency-brand-sub {
      font-size: 9.5px;
      opacity: 0.8;
      font-weight: 600;
    }
    .agency-dash-container {
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
      padding: 24px 16px 60px;
      flex: 1;
    }
    .agency-banner {
      background: var(--navy);
      color: #ffffff;
      border-radius: 14px;
      padding: 24px;
      margin-bottom: 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
    }
    .agency-banner-info h2 {
      font-size: 20px;
      font-weight: 800;
      color: #ffffff;
      margin: 0 0 6px;
    }
    .agency-banner-info p {
      font-size: 13px;
      opacity: 0.85;
      margin: 0;
    }
    .agency-kpi-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }
    @media (max-width: 900px) {
      .agency-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 500px) {
      .agency-kpi-grid { grid-template-columns: 1fr; }
    }
    .agency-kpi-card {
      background: #ffffff;
      border-radius: 12px;
      padding: 20px;
      border: 1px solid var(--border-color);
      box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .agency-kpi-card span {
      display: block;
      font-size: 11.5px;
      font-weight: 700;
      color: var(--text-sub);
      margin-bottom: 6px;
    }
    .agency-kpi-card strong {
      font-size: 20px;
      font-weight: 900;
      color: var(--navy);
    }
    .agency-section-box {
      background: #ffffff;
      border-radius: 12px;
      padding: 20px;
      border: 1px solid var(--border-color);
      margin-bottom: 24px;
    }
    .agency-section-title {
      font-size: 15px;
      font-weight: 800;
      color: var(--navy);
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .referral-box {
      background: #f8fafc;
      border: 1px dashed #cbd5e1;
      border-radius: 8px;
      padding: 14px;
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .referral-box input {
      flex: 1;
      height: 40px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 0 12px;
      font-size: 13px;
      font-weight: 700;
      color: var(--navy);
      background: #ffffff;
    }
    .tbl-agency {
      width: 100%;
      border-collapse: collapse;
    }
    .tbl-agency th {
      background: #f8fafc;
      color: var(--text-sub);
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      padding: 12px 14px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }
    .tbl-agency td {
      padding: 14px;
      border-bottom: 1px solid #f1f5f9;
      font-size: 13px;
      color: var(--text-main);
    }
    .btn-action {
      background: var(--navy);
      color: #ffffff;
      border-radius: 8px;
      padding: 8px 16px;
      font-size: 12.5px;
      font-weight: 700;
      border: none;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-action:hover { background: var(--navy-main); }
    .agency-footer {
      text-align: center;
      padding: 20px;
      font-size: 12px;
      color: var(--text-sub);
    }
  </style>
</head>
<body>

  <!-- Standalone Agency Header -->
  <header class="agency-top-bar">
    <a href="/agency/dashboard" class="agency-brand">
      <img src="/favicon-512x512.png" alt="Cooling Logo" onerror="this.src='/public/favicon-512x512.png'">
      <div>
        <div class="agency-brand-title">COOLING SYSTEMS</div>
        <div class="agency-brand-sub">BẢNG ĐIỀU KHUYỂN ĐẠI LÝ</div>
      </div>
    </a>
    <div style="display:flex; align-items:center; gap:16px;">
      <span style="font-size:12.5px; color:#ffffff; font-weight:700;">
        <?= e($user['full_name'] ?? $agency['agency_name'] ?? 'Đại lý Partner') ?>
      </span>
      <a href="/agency/logout" style="color:rgba(255,255,255,0.8); font-size:12px; font-weight:700; text-decoration:none; background:rgba(255,255,255,0.12); padding:5px 12px; border-radius:6px;">Đăng xuất</a>
    </div>
  </header>

  <!-- Dashboard Main Container -->
  <div class="agency-dash-container">
    
    <?php foreach (getFlash() as $f): ?>
      <div style="background:<?= $f['type']==='error'?'#fef2f2':'#ecfdf5' ?>;color:<?= $f['type']==='error'?'#991b1b':'#15803d' ?>;padding:12px 16px;border-radius:8px;font-size:14px;font-weight:700;margin-bottom:20px;">
        <?= e($f['message']) ?>
      </div>
    <?php endforeach; ?>

    <!-- Banner Top -->
    <div class="agency-banner">
      <div class="agency-banner-info">
        <h2>Xin chào, <?= e($agency['agency_name'] ?? $user['full_name'] ?? 'Đại lý Partner') ?>!</h2>
        <p>Cổng thông tin Đại lý Chuyên nghiệp · Mã số thuế: <strong><?= e($agency['tax_code'] ?? 'Chưa cập nhật') ?></strong> · Hạng Đại lý: <span style="background:var(--navy-main); color:#fff; padding:2px 8px; border-radius:10px; font-weight:800; font-size:11px;"><?= e($tierName ?? 'Đại lý Chuẩn') ?></span></p>
      </div>
      <div>
        <button class="btn-action" style="background:#ffffff; color:var(--navy);" onclick="document.getElementById('withdrawModal').style.display='flex'">Đề Nghị Rút Tiền</button>
      </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="agency-kpi-grid">
      <div class="agency-kpi-card">
        <span>MỨC HOA HỒNG ĐANG HƯỞNG</span>
        <strong style="color:var(--navy);"><?= number_format($currentRate ?? 5.0, 1) ?>%</strong>
      </div>
      <div class="agency-kpi-card">
        <span>DOANH SỐ TUYẾN ĐẠI LÝ</span>
        <strong><?= function_exists('vnd') ? vnd($totalSales) : number_format($totalSales, 0, ',', '.') . ' đ' ?></strong>
      </div>
      <div class="agency-kpi-card">
        <span>TỔNG HOA HỒNG TÍCH LŨY</span>
        <strong style="color:#15803d;"><?= function_exists('vnd') ? vnd($totalEarned) : number_format($totalEarned, 0, ',', '.') . ' đ' ?></strong>
      </div>
      <div class="agency-kpi-card">
        <span>SỐ GARA THUỘC TUYẾN</span>
        <strong><?= count($downlineGarages ?? []) ?> Gara</strong>
      </div>
    </div>

    <!-- Section 1: Referral Link Generator -->
    <div class="agency-section-box">
      <div class="agency-section-title">
        <span>MÃ &amp; LINK GIỚI THIỆU TẠO GARA THUỘC TUYẾN</span>
      </div>
      <p style="font-size:12.5px; color:var(--text-sub); margin-bottom:12px;">Gửi link này cho các Gara đăng ký. Mọi đơn hàng do Gara này đặt sẽ tự động trích <strong><?= number_format($currentRate ?? 5.0, 1) ?>% hoa hồng</strong> vào tài khoản Đại lý của bạn!</p>
      <div class="referral-box">
        <input type="text" id="refLinkInput" readonly value="<?= e($referralUrl ?? '') ?>">
        <button class="btn-action" onclick="copyRefLink()">Sao chép Link</button>
      </div>
    </div>

    <!-- Section 2: Downline Garages List -->
    <div class="agency-section-box">
      <div class="agency-section-title">
        <span>DANH SÁCH GARA THUỘC TUYẾN ĐẠI LÝ (<?= count($downlineGarages ?? []) ?>)</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="tbl-agency">
          <thead>
            <tr>
              <th>Tên Gara / Cửa hàng</th>
              <th>Chủ Gara</th>
              <th>Số điện thoại</th>
              <th>Địa chỉ</th>
              <th>Ngày đăng ký</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($downlineGarages)): ?>
              <tr>
                <td colspan="5" style="text-align:center; padding:30px; color:var(--text-sub);">Chưa có Gara nào đăng ký qua mã đại lý của bạn. Hãy gửi link giới thiệu ở trên!</td>
              </tr>
            <?php else: ?>
              <?php foreach ($downlineGarages as $g): ?>
                <tr>
                  <td><strong><?= e($g['garage_name'] ?? $g['full_name']) ?></strong></td>
                  <td><?= e($g['full_name']) ?></td>
                  <td><?= e($g['phone']) ?></td>
                  <td><?= e($g['address'] ?? 'Chưa cập nhật') ?></td>
                  <td><?= date('d/m/Y', strtotime($g['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Section 3: Commission Transactions Log -->
    <div class="agency-section-box">
      <div class="agency-section-title">
        <span>LỊCH SỬ HOA HỒNG GIAO DỊCH (<?= count($commissions ?? []) ?>)</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="tbl-agency">
          <thead>
            <tr>
              <th>Mã Đơn / Sub-order</th>
              <th>Giá trị đơn</th>
              <th>Tỷ lệ %</th>
              <th>Hoa hồng thực nhận</th>
              <th>Trạng thái</th>
              <th>Thời gian</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($commissions)): ?>
              <tr>
                <td colspan="6" style="text-align:center; padding:30px; color:var(--text-sub);">Chưa có giao dịch hoa hồng nào phát sinh.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($commissions as $c): ?>
                <tr>
                  <td><strong>#Sub-<?= $c['sub_order_id'] ?></strong></td>
                  <td><?= function_exists('vnd') ? vnd($c['gross_amount']) : number_format($c['gross_amount'], 0, ',', '.') . ' đ' ?></td>
                  <td><span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:10px; font-weight:800; font-size:11px;"><?= number_format($c['commission_rate'], 1) ?>%</span></td>
                  <td><strong style="color:#15803d;"><?= function_exists('vnd') ? vnd($c['commission_fee']) : number_format($c['commission_fee'], 0, ',', '.') . ' đ' ?></strong></td>
                  <td>
                    <span style="padding:2px 8px; border-radius:10px; font-size:11px; font-weight:700; background:<?= $c['status']==='settled'?'#dcfce7':'#fef9c3' ?>; color:<?= $c['status']==='settled'?'#15803d':'#854d0e' ?>;">
                      <?= $c['status']==='settled'?'Đã đối soát':'Chờ đối soát' ?>
                    </span>
                  </td>
                  <td><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Modal Đề nghị rút tiền hoa hồng -->
  <div id="withdrawModal" style="display:none; position:fixed; inset:0; background:rgba(11,29,58,0.6); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <form method="post" action="/agency/withdraw" style="background:#fff; border-radius:12px; padding:24px; max-width:480px; width:100%; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
      <?= csrfField() ?>
      <h3 style="font-size:16px; font-weight:800; color:var(--navy); margin:0 0 14px;">YÊU CẦU RÚT TIỀN HOA HỒNG</h3>
      <div style="font-size:12.5px; color:var(--text-sub); margin-bottom:16px;">
        Số dư hoa hồng khả dụng: <strong style="color:#15803d; font-size:15px;"><?= function_exists('vnd') ? vnd($totalEarned) : number_format($totalEarned, 0, ',', '.') . ' đ' ?></strong>
      </div>

      <div style="margin-bottom:12px;">
        <label style="display:block; font-size:12px; font-weight:700; color:var(--navy); margin-bottom:4px;">Số tiền muốn rút (VNĐ) *</label>
        <input type="number" name="amount" required min="100000" max="<?= (int)($totalEarned ?? 0) ?>" placeholder="VD: 5000000" style="width:100%; height:40px; border-radius:8px; border:1px solid var(--border-color); padding:0 12px; font-size:14px; font-weight:700;">
      </div>

      <div style="margin-bottom:12px;">
        <label style="display:block; font-size:12px; font-weight:700; color:var(--navy); margin-bottom:4px;">Tên Ngân hàng *</label>
        <input type="text" name="bank_name" required placeholder="VD: Vietcombank / Techcombank / MBBank" style="width:100%; height:40px; border-radius:8px; border:1px solid var(--border-color); padding:0 12px; font-size:13.5px;">
      </div>

      <div style="margin-bottom:12px;">
        <label style="display:block; font-size:12px; font-weight:700; color:var(--navy); margin-bottom:4px;">Số tài khoản Ngân hàng *</label>
        <input type="text" name="bank_account" required placeholder="VD: 1012345678" style="width:100%; height:40px; border-radius:8px; border:1px solid var(--border-color); padding:0 12px; font-size:13.5px;">
      </div>

      <div style="margin-bottom:18px;">
        <label style="display:block; font-size:12px; font-weight:700; color:var(--navy); margin-bottom:4px;">Tên chủ tài khoản (Chữ in hoa) *</label>
        <input type="text" name="bank_holder" required placeholder="VD: NGUYEN VAN A" style="width:100%; height:40px; border-radius:8px; border:1px solid var(--border-color); padding:0 12px; font-size:13.5px; text-transform:uppercase;">
      </div>

      <div style="display:flex; gap:10px; justify-content:flex-end;">
        <button type="button" class="btn-action" style="background:#cbd5e1; color:#334155;" onclick="document.getElementById('withdrawModal').style.display='none'">Hủy</button>
        <button type="submit" class="btn-action">Gửi Yêu Cầu Rút Tiền</button>
      </div>
    </form>
  </div>

  <footer class="agency-footer">
    &copy; <?= date('Y') ?> Cooling Systems. Tất cả quyền được bảo lưu.
  </footer>

  <script>
  function copyRefLink() {
    var input = document.getElementById('refLinkInput');
    input.select();
    document.execCommand('copy');
    alert('Đã sao chép Link giới thiệu Đại lý thành công!\n' + input.value);
  }
  </script>

</body>
</html>
