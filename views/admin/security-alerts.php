<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Nhật ký Cảnh báo An toàn & Bất thường</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Phát hiện và tự động ghi nhận các thao tác bất thường (sửa giá bán ngoài hạn mức, xóa đơn hàng, xuất âm kho...).</p>
  </div>
  <a href="/admin/security/alerts/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.sec-table{width:100%;border-collapse:collapse;background:#fff}
.sec-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.sec-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.sec-sev{padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700}
.sec-sev-low{background:#f1f5f9;color:#475569}
.sec-sev-medium{background:#fef9c3;color:#854d0e}
.sec-sev-high{background:#ffedd5;color:#c2410c}
.sec-sev-critical{background:#fee2e2;color:#b91c1c}
</style>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="sec-table">
  <thead>
    <tr>
      <th>STT</th>
      <th>Mức độ cảnh báo</th>
      <th>Loại bất thường</th>
      <th>Nội dung chi tiết cảnh báo</th>
      <th>Thời gian phát hiện</th>
    </tr>
  </thead>
  <tbody>
    <?php $i=1; foreach($alerts as $al): ?>
    <tr>
      <td style="color:#94a3b8;font-size:12px"><?= $i++ ?></td>
      <td>
        <?php 
          $sev = $al['severity'] ?? 'medium';
          $lbl = ['low'=>'Thấp','medium'=>'Trung bình','high'=>'Cao','critical'=>'🚨 NGUY HIỂM'][$sev] ?? $sev;
        ?>
        <span class="sec-sev sec-sev-<?= e($sev) ?>"><?= e($lbl) ?></span>
      </td>
      <td style="font-weight:700;color:#1a3258;font-family:monospace;font-size:12px"><?= e($al['alert_type']) ?></td>
      <td><strong style="color:#1e3a8a"><?= e($al['message']) ?></strong></td>
      <td style="font-size:12px;color:#9ca3af"><?= !empty($al['created_at']) ? date('d/m/Y H:i:s', strtotime($al['created_at'])) : '—' ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$alerts): ?>
    <tr><td colspan="5" style="padding:30px;text-align:center;color:#16a34a">✔ Hệ thống an toàn. Chưa phát hiện bất kỳ thao tác bất thường nào.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if(isset($totalPages) && $totalPages > 1): ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/security/alerts?page=<?= $page-1 ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/security/alerts?page=<?= $page+1 ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
