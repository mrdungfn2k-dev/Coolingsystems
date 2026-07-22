<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Kiểm kê kho & Điều chỉnh tồn</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Tạo phiên kiểm đếm thực tế, phát hiện chênh lệch và tự động cân bằng tồn kho.</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <button type="button" onclick="document.getElementById('importSCModal').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↑ Nhập CSV</button>
    <a href="/admin/stock-counts/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
    <a href="/admin/stock-counts/new" class="btn btn-navy">+ Tạo phiên kiểm kho mới</a>
  </div>
</div>

<div id="importSCModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/stock-counts/import-csv" enctype="multipart/form-data" style="background:#fff;padding:24px;border-radius:10px;max-width:450px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 14px;color:#1a3258">Nhập kết quả kiểm kho từ CSV</h3>
    <div style="margin-bottom:14px;font-size:12px;color:#64748b">
      Định dạng CSV: <code>Stock_Count_Code, Product_SKU, Actual_Qty, Reason</code>
    </div>
    <div style="margin-bottom:18px">
      <input type="file" name="csv_file" accept=".csv" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('importSCModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Tải lên & Nhập</button>
    </div>
  </form>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.sc-filter{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.sc-filter input,.sc-filter select{height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;background:#fff;font-size:13px}
.sc-table{width:100%;border-collapse:collapse;background:#fff}
.sc-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.sc-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.sc-table tr:hover td{background:#f9fbff}
.sc-badge{padding:3px 9px;border-radius:4px;font-size:11px;font-weight:700}
.sc-badge-draft{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb}
.sc-badge-in_progress{background:#fef9c3;color:#854d0e;border:1px solid #fde68a}
.sc-badge-completed{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
.sc-badge-cancelled{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
</style>

<form class="sc-filter" method="get" action="/admin/stock-counts">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Mã kiểm kho, ghi chú..." style="min-width:250px">
  <select name="status">
    <option value="">Tất cả trạng thái</option>
    <option value="draft" <?= $statusFilter==='draft'?'selected':'' ?>>Nháp</option>
    <option value="in_progress" <?= $statusFilter==='in_progress'?'selected':'' ?>>Đang kiểm đếm</option>
    <option value="completed" <?= $statusFilter==='completed'?'selected':'' ?>>Đã hoàn tất / Cân bằng</option>
    <option value="cancelled" <?= $statusFilter==='cancelled'?'selected':'' ?>>Đã hủy</option>
  </select>
  <button class="btn btn-navy" type="submit">Lọc</button>
  <?php if($q||$statusFilter): ?>
  <a href="/admin/stock-counts" class="btn btn-outline">Xóa lọc</a>
  <?php endif; ?>
</form>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="sc-table">
  <thead>
    <tr>
      <th>Mã kiểm kho</th>
      <th>Kho kiểm kê</th>
      <th>Trạng thái</th>
      <th>Ghi chú</th>
      <th>Người tạo</th>
      <th>Ngày hoàn tất</th>
      <th>Ngày tạo</th>
      <th>Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($stockCounts as $sc): ?>
    <tr>
      <td style="font-family:monospace;font-weight:700">#<?= e($sc['code'] ?? '') ?></td>
      <td style="font-weight:600;color:#1a3258"><?= e($sc['warehouse_name'] ?? 'Kho chính') ?></td>
      <td>
        <?php 
          $st = $sc['status'] ?? 'draft';
          $lbl = ['draft'=>'Nháp','in_progress'=>'Đang kiểm đếm','completed'=>'Đã hoàn tất','cancelled'=>'Đã hủy'][$st] ?? $st;
        ?>
        <span class="sc-badge sc-badge-<?= e($st) ?>"><?= e($lbl) ?></span>
      </td>
      <td style="font-size:12px;color:#6b7280;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($sc['note'] ?? '') ?>"><?= e(!empty($sc['note']) ? $sc['note'] : '—') ?></td>
      <td style="font-size:12px;color:#4b5563"><?= e($sc['creator_name'] ?? 'System') ?></td>
      <td style="font-size:12px;color:#10b981"><?= !empty($sc['completed_at']) ? date('d/m/Y H:i', strtotime($sc['completed_at'])) : '—' ?></td>
      <td style="font-size:12px;color:#9ca3af"><?= !empty($sc['created_at']) ? date('d/m/Y H:i', strtotime($sc['created_at'])) : '—' ?></td>
      <td>
        <a href="/admin/stock-counts/<?= (int)$sc['id'] ?>" class="btn btn-outline" style="padding:4px 10px;font-size:12px">
          <?= $st === 'completed' ? 'Xem kết quả' : 'Kiểm đếm' ?>
        </a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$stockCounts): ?>
    <tr><td colspan="8" style="padding:30px;text-align:center;color:#9ca3af">Chưa có phiên kiểm kho nào. Bấm nút "+ Tạo phiên kiểm kho mới" để bắt đầu.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if($totalPages > 1): $base = ['q'=>$q,'status'=>$statusFilter?:null]; ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/stock-counts?<?= e(http_build_query(array_filter($base+['page'=>$page-1]))) ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/stock-counts?<?= e(http_build_query(array_filter($base+['page'=>$page+1]))) ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
