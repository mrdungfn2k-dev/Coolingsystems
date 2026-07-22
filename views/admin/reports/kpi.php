<?php require __DIR__.'/../../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Báo cáo KPI Nhân sự & Bán hàng</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Thống kê hiệu suất làm việc, doanh số phát sinh và hoa hồng thực nhận của từng nhân viên/đối tác.</p>
  </div>
  <a href="/admin/reports/kpi/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV / Excel</a>
</div>

<style>
.rpt-table{width:100%;border-collapse:collapse;background:#fff}
.rpt-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.rpt-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
</style>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="rpt-table">
  <thead>
    <tr>
      <th>STT</th>
      <th>Nhân viên / Đối tác</th>
      <th>Vai trò</th>
      <th>Số điện thoại</th>
      <th style="text-align:center">Số đơn hàng đã tạo</th>
      <th style="text-align:right">Doanh số phát sinh</th>
      <th style="text-align:right">Hoa hồng ghi nhận</th>
    </tr>
  </thead>
  <tbody>
    <?php $i=1; foreach($staffKpis as $k): ?>
    <tr>
      <td style="color:#94a3b8;font-size:12px"><?= $i++ ?></td>
      <td><strong style="color:#1a3258"><?= e($k['full_name'] ?? 'Staff') ?></strong></td>
      <td>
        <span style="font-size:11px;padding:2px 6px;border-radius:4px;background:#f1f5f9;color:#475569;font-weight:600">
          <?= e($k['role'] ?? 'staff') ?>
        </span>
      </td>
      <td style="font-size:12px;color:#64748b"><?= e($k['phone'] ?: '—') ?></td>
      <td style="text-align:center;font-weight:700;color:#0284c7"><?= number_format((int)($k['order_count'] ?? 0)) ?></td>
      <td style="text-align:right;font-weight:700;color:#1e3a8a"><?= number_format((int)($k['total_sales'] ?? 0)) ?> đ</td>
      <td style="text-align:right;font-weight:700;color:#16a34a"><?= number_format((int)($k['total_commission'] ?? 0)) ?> đ</td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$staffKpis): ?>
    <tr><td colspan="7" style="padding:30px;text-align:center;color:#9ca3af">Chưa có dữ liệu KPI nhân sự.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if(isset($totalPages) && $totalPages > 1): ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/reports/kpi?page=<?= $page-1 ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/reports/kpi?page=<?= $page+1 ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../../partials/dashboard-foot.php'; ?>
