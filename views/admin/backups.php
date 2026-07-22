<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Sao lưu & An toàn CSDL (System Backups)</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Quản lý các bản sao lưu cơ sở dữ liệu hệ thống, đảm bảo khôi phục dữ liệu an toàn khi cần thiết.</p>
  </div>
  <form method="post" action="/admin/settings/backups/create" style="display:inline">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <button type="submit" class="btn btn-navy">⚡ Tạo bản sao lưu 1-Click ngay</button>
  </form>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.bk-table{width:100%;border-collapse:collapse;background:#fff}
.bk-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.bk-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
</style>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="bk-table">
  <thead>
    <tr>
      <th>Tên bản sao lưu</th>
      <th>Kích thước file</th>
      <th>Người tạo</th>
      <th>Thời gian sao lưu</th>
      <th>Thao tác tải về</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($backups as $b): ?>
    <tr>
      <td style="font-family:monospace;font-weight:700;color:#1a3258">📦 <?= e($b['filename']) ?></td>
      <td style="font-weight:600;color:#0284c7"><?= number_format(round($b['file_size'] / 1024, 1)) ?> KB</td>
      <td style="font-size:12px;color:#4b5563"><?= e($b['creator_name'] ?? 'System Auto') ?></td>
      <td style="font-size:12px;color:#9ca3af"><?= !empty($b['created_at']) ? date('d/m/Y H:i:s', strtotime($b['created_at'])) : '—' ?></td>
      <td>
        <a href="/admin/settings/backups/download?id=<?= (int)$b['id'] ?>" class="btn btn-outline" style="padding:4px 10px;font-size:12px">⬇ Tải file Backup</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$backups): ?>
    <tr><td colspan="5" style="padding:30px;text-align:center;color:#9ca3af">Chưa có bản sao lưu nào. Bấm nút "⚡ Tạo bản sao lưu 1-Click ngay" để sao lưu dữ liệu.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if(isset($totalPages) && $totalPages > 1): ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/settings/backups?page=<?= $page-1 ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/settings/backups?page=<?= $page+1 ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
