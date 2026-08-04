<?php require __DIR__ . '/../partials/head.php'; ?>
<style>
.agency-dash-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 16px 60px;
}
.agency-banner {
  background: linear-gradient(135deg, var(--navy-dark) 0%, #244270 100%);
  color: #ffffff;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 10px 25px rgba(26, 50, 88, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}
.agency-banner-info h2 {
  font-size: 22px;
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
  border-radius: 14px;
  padding: 20px;
  border: 1px solid var(--line);
  box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}
.agency-kpi-card span {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: var(--ink-3);
  margin-bottom: 6px;
}
.agency-kpi-card strong {
  font-size: 22px;
  font-weight: 900;
  color: var(--navy-dark);
}
.agency-section-box {
  background: #ffffff;
  border-radius: 14px;
  padding: 20px;
  border: 1px solid var(--line);
  margin-bottom: 24px;
}
.agency-section-title {
  font-size: 16px;
  font-weight: 800;
  color: var(--navy-dark);
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.referral-box {
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 10px;
  padding: 14px;
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.referral-box input {
  flex: 1;
  height: 40px;
  border: 1px solid var(--line);
  border-radius: 8px;
  padding: 0 12px;
  font-size: 13.5px;
  font-weight: 700;
  color: var(--navy-dark);
  background: #ffffff;
}
.tbl-agency {
  width: 100%;
  border-collapse: collapse;
}
.tbl-agency th {
  background: #f8fafc;
  color: var(--ink-2);
  font-size: 11.5px;
  font-weight: 800;
  text-transform: uppercase;
  padding: 12px 14px;
  text-align: left;
  border-bottom: 1px solid var(--line);
}
.tbl-agency td {
  padding: 14px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 13px;
  color: var(--ink-1);
}
</style>

<div class="agency-dash-container">
  
  <?php foreach (getFlash() as $f): ?>
    <div style="background:<?= $f['type']==='error'?'#fef2f2':'#ecfdf5' ?>;color:<?= $f['type']==='error'?'#b91c1c':'#047857' ?>;padding:12px 16px;border-radius:8px;font-size:14px;font-weight:700;margin-bottom:20px;">
      <?= e($f['message']) ?>
    </div>
  <?php endforeach; ?>

  <!-- Banner Top -->
  <div class="agency-banner">
    <div class="agency-banner-info">
      <h2>Xin chào, <?= e($agency['agency_name'] ?? $agency['full_name'] ?? 'Đại lý Partner') ?>!</h2>
      <p>Cổng thông tin Đại lý Chuyên nghiệp · Mã số thuế: <strong><?= e($agency['tax_code'] ?? '0101234567') ?></strong> · Hạng Đại lý: <span style="background:var(--orange-accent); color:#fff; padding:2px 8px; border-radius:10px; font-weight:800; font-size:11px;"><?= e($tierName) ?></span></p>
    </div>
    <div style="display:flex; gap:10px;">
      <button class="btn btn-outline" style="background:#ffffff; color:var(--navy-dark); font-weight:700;" onclick="document.getElementById('withdrawModal').style.display='flex'">💰 Đề Nghị Rút Tiền</button>
      <a href="/agency/logout" class="btn" style="background:rgba(255,255,255,0.15); color:#fff; font-weight:700;">Đăng xuất</a>
    </div>
  </div>

  <!-- KPI Cards Grid -->
  <div class="agency-kpi-grid">
    <div class="agency-kpi-card">
      <span>MỨC HOA HỒNG ĐANG HƯỞNG</span>
      <strong style="color:var(--orange-accent);"><?= number_format($currentRate, 1) ?>%</strong>
    </div>
    <div class="agency-kpi-card">
      <span>DOANH SỐ TUYẾN ĐẠI LÝ</span>
      <strong><?= vnd($totalSales) ?></strong>
    </div>
    <div class="agency-kpi-card">
      <span>TỔNG HOA HỒNG TÍCH LŨY</span>
      <strong style="color:#059669;"><?= vnd($totalEarned) ?></strong>
    </div>
    <div class="agency-kpi-card">
      <span>SỐ GARA THUỘC TUYẾN</span>
      <strong><?= count($downlineGarages) ?> Gara</strong>
    </div>
  </div>

  <!-- Section 1: Referral Link Generator -->
  <div class="agency-section-box">
    <div class="agency-section-title">
      <span>🔗 MÃ &amp; LINK GIỚI THIỆU TẠO GARA THUỘC TUYẾN</span>
    </div>
    <p style="font-size:12.5px; color:var(--ink-2); margin-bottom:12px;">Gửi link này cho các Gara đăng ký. Mọi đơn hàng do Gara này đặt sẽ tự động trích <strong><?= number_format($currentRate, 1) ?>% hoa hồng</strong> vào tài khoản Đại lý của bạn!</p>
    <div class="referral-box">
      <input type="text" id="refLinkInput" readonly value="<?= e($referralUrl) ?>">
      <button class="btn btn-navy" style="height:40px; font-weight:700;" onclick="copyRefLink()">📋 Sao chép Link</button>
    </div>
  </div>

  <!-- Section 2: Downline Garages List -->
  <div class="agency-section-box">
    <div class="agency-section-title">
      <span>🏬 DANH SÁCH GARA THUỘC TUYẾN ĐẠI LÝ (<?= count($downlineGarages) ?>)</span>
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
              <td colspan="5" style="text-align:center; padding:30px; color:var(--ink-3);">Chưa có Gara nào đăng ký qua mã đại lý của bạn. Hãy gửi link giới thiệu ở trên!</td>
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
      <span>📦 LỊCH SỬ HOA HỒNG GIAO DỊCH (<?= count($commissions) ?>)</span>
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
              <td colspan="6" style="text-align:center; padding:30px; color:var(--ink-3);">Chưa có giao dịch hoa hồng nào phát sinh.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($commissions as $c): ?>
              <tr>
                <td><strong>#Sub-<?= $c['sub_order_id'] ?></strong></td>
                <td><?= vnd($c['gross_amount']) ?></td>
                <td><span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:10px; font-weight:800; font-size:11px;"><?= number_format($c['commission_rate'], 1) ?>%</span></td>
                <td><strong style="color:#059669;"><?= vnd($c['commission_fee']) ?></strong></td>
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
<div id="withdrawModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:16px;">
  <form method="post" action="/agency/withdraw" style="background:#fff; border-radius:14px; padding:24px; max-width:480px; width:100%; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
    <?= csrfField() ?>
    <h3 style="font-size:17px; font-weight:800; color:var(--navy-dark); margin:0 0 14px;">💸 YÊU CẦU RÚT TIỀN HOA HỒNG</h3>
    <div style="font-size:12.5px; color:var(--ink-2); margin-bottom:16px;">
      Số dư hoa hồng khả dụng: <strong style="color:#059669; font-size:15px;"><?= vnd($totalEarned) ?></strong>
    </div>

    <div style="margin-bottom:12px;">
      <label style="display:block; font-size:12px; font-weight:700; color:var(--navy-dark); margin-bottom:4px;">Số tiền muốn rút (VNĐ) *</label>
      <input type="number" name="amount" required min="100000" max="<?= (int)$totalEarned ?>" placeholder="VD: 5000000" style="width:100%; height:40px; border-radius:8px; border:1px solid var(--line); padding:0 12px; font-size:14px; font-weight:700;">
    </div>

    <div style="margin-bottom:12px;">
      <label style="display:block; font-size:12px; font-weight:700; color:var(--navy-dark); margin-bottom:4px;">Tên Ngân hàng *</label>
      <input type="text" name="bank_name" required placeholder="VD: Vietcombank / Techcombank / MBBank" style="width:100%; height:40px; border-radius:8px; border:1px solid var(--line); padding:0 12px; font-size:13.5px;">
    </div>

    <div style="margin-bottom:12px;">
      <label style="display:block; font-size:12px; font-weight:700; color:var(--navy-dark); margin-bottom:4px;">Số tài khoản Ngân hàng *</label>
      <input type="text" name="bank_account" required placeholder="VD: 1012345678" style="width:100%; height:40px; border-radius:8px; border:1px solid var(--line); padding:0 12px; font-size:13.5px;">
    </div>

    <div style="margin-bottom:18px;">
      <label style="display:block; font-size:12px; font-weight:700; color:var(--navy-dark); margin-bottom:4px;">Tên chủ tài khoản (Chữ in hoa) *</label>
      <input type="text" name="bank_holder" required placeholder="VD: NGUYEN VAN A" style="width:100%; height:40px; border-radius:8px; border:1px solid var(--line); padding:0 12px; font-size:13.5px; text-transform:uppercase;">
    </div>

    <div style="display:flex; gap:10px; justify-content:flex-end;">
      <button type="button" class="btn btn-outline" onclick="document.getElementById('withdrawModal').style.display='none'">Hủy</button>
      <button type="submit" class="btn btn-orange" style="background:var(--orange-accent); color:#fff; font-weight:800;">Gửi Yêu Cầu Rút Tiền</button>
    </div>
  </form>
</div>

<script>
function copyRefLink() {
  var input = document.getElementById('refLinkInput');
  input.select();
  document.execCommand('copy');
  alert('Đã sao chép Link giới thiệu Đại lý thành công!\n' + input.value);
}
</script>

<?php require __DIR__ . '/../partials/foot.php'; ?>
