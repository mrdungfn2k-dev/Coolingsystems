<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>

<div class="dash-head">
  <div>
    <h1>Cấu hình % Hoa hồng Linh hoạt &amp; Quản lý Đại lý</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Cấu hình tỷ lệ % hoa hồng theo Hạng Đại lý, đặt % riêng cho Đại lý VIP và duyệt hồ sơ Đại lý mới.</p>
  </div>
</div>

<?php foreach (getFlash() as $f): ?>
  <div style="background:<?= $f['type']==='error'?'#fef2f2':'#dcfce7' ?>;color:<?= $f['type']==='error'?'#991b1b':'#15803d' ?>;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-weight:700;font-size:14px">
    <?= e($f['message']) ?>
  </div>
<?php endforeach; ?>

<!-- 1. Cấu hình tỷ lệ % Hoa hồng theo Cấp bậc Đại lý -->
<form method="post" action="/admin/agency-tiers/update" style="margin-bottom:28px;">
  <?= csrfField() ?>
  <h2 style="font-size:16px; font-weight:800; color:var(--navy); margin-bottom:14px;">1. CẤU HÌNH % HOA HỒNG THEO CẤP BẬC ĐẠI LÝ (AGENCY TIERS)</h2>
  
  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:16px;">
    <?php foreach ($tiers as $t): ?>
      <div style="background:#fff; border-radius:12px; padding:18px; border:1px solid #cbd5e1; box-shadow:0 4px 12px rgba(0,0,0,0.03);">
        <input type="hidden" name="tier_id[]" value="<?= $t['id'] ?>">
        <div style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; margin-bottom:6px;">Mã Cấp: <?= e($t['tier_code']) ?></div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Tên Cấp Bậc Đại Lý</label>
          <input type="text" name="tier_name[]" value="<?= e($t['tier_name']) ?>" required style="width:100%; height:38px; border-radius:6px; border:1px solid #cbd5e1; padding:0 10px; font-size:13px;">
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Tỷ Lệ Hoa Hồng Hưởng (%)</label>
          <input type="number" step="0.5" min="0" max="50" name="commission_percent[]" value="<?= e($t['commission_percent']) ?>" required style="width:100%; height:38px; border-radius:6px; border:1px solid #cbd5e1; padding:0 10px; font-size:13px; font-weight:800; color:#f26a1b;">
        </div>

        <div>
          <label style="display:block; font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">Doanh Số Tuyến Tối Thiểu (VNĐ/tháng)</label>
          <input type="number" step="1000000" min="0" name="min_monthly_sales[]" value="<?= e($t['min_monthly_sales']) ?>" required style="width:100%; height:38px; border-radius:6px; border:1px solid #cbd5e1; padding:0 10px; font-size:13px;">
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <button type="submit" style="background:#0b1d3a; color:#fff; font-weight:800; font-size:13.5px; padding:10px 24px; border-radius:8px; border:none; cursor:pointer;">
    LƯU CẤU HÌNH HẠNG ĐẠI LÝ
  </button>
</form>

<!-- 2. Danh sách Đăng ký Đại lý chờ duyệt -->
<div style="background:#fff; border-radius:12px; padding:20px; border:1px solid #cbd5e1; margin-bottom:28px;">
  <h2 style="font-size:16px; font-weight:800; color:var(--navy); margin-bottom:14px;">2. XÉT DUYỆT HỒ SƠ ĐĂNG KÝ ĐẠI LÝ MỚI (<?= count($pendingRegistrations) ?>)</h2>
  
  <div style="overflow-x:auto;">
    <table class="tbl" style="width:100%; border-collapse:collapse;">
      <thead>
        <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
          <th style="padding:10px; text-align:left; font-size:11px; text-transform:uppercase; color:#64748b;">Tên Đại lý</th>
          <th style="padding:10px; text-align:left; font-size:11px; text-transform:uppercase; color:#64748b;">Chủ ĐL / SĐT</th>
          <th style="padding:10px; text-align:left; font-size:11px; text-transform:uppercase; color:#64748b;">Mã số thuế</th>
          <th style="padding:10px; text-align:left; font-size:11px; text-transform:uppercase; color:#64748b;">Địa chỉ thực tế</th>
          <th style="padding:10px; text-align:center; font-size:11px; text-transform:uppercase; color:#64748b;">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($pendingRegistrations)): ?>
          <tr>
            <td colspan="5" style="text-align:center; padding:24px; color:#64748b;">Hiện không có hồ sơ Đại lý nào chờ duyệt.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($pendingRegistrations as $reg): ?>
            <tr style="border-bottom:1px solid #edf1f5;">
              <td style="padding:10px;"><strong><?= e($reg['agency_name']) ?></strong></td>
              <td style="padding:10px;"><?= e($reg['owner_name']) ?><br><small style="color:#64748b;"><?= e($reg['phone']) ?></small></td>
              <td style="padding:10px; font-weight:700;"><?= e($reg['tax_code']) ?></td>
              <td style="padding:10px; font-size:12px;"><?= e($reg['address']) ?></td>
              <td style="padding:10px; text-align:center;">
                <form method="post" action="/admin/agency-registrations/<?= $reg['id'] ?>/approve" style="display:inline-block;">
                  <?= csrfField() ?>
                  <button type="submit" class="btn btn-sm" style="background:#16a34a; color:#fff; font-weight:700; padding:5px 12px; border-radius:6px; border:none; cursor:pointer;">Duyệt Đại lý</button>
                </form>
                <form method="post" action="/admin/agency-registrations/<?= $reg['id'] ?>/reject" style="display:inline-block; margin-left:4px;">
                  <?= csrfField() ?>
                  <button type="submit" class="btn btn-sm" style="background:#dc2626; color:#fff; font-weight:700; padding:5px 12px; border-radius:6px; border:none; cursor:pointer;">Từ chối</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
