<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Chuyển kho nội bộ</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Tạo và theo dõi các phiếu điều chuyển phụ tùng giữa các kho và chi nhánh.</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <button type="button" onclick="document.getElementById('importSTModal').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↑ Nhập CSV</button>
    <a href="/admin/stock-transfers/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
    <a href="/admin/stock-transfers/new" class="btn btn-navy">+ Tạo phiếu chuyển kho mới</a>
  </div>
</div>

<div id="importSTModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/stock-transfers/import-csv" enctype="multipart/form-data" style="background:#fff;padding:24px;border-radius:10px;max-width:450px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 14px;color:#1a3258">Nhập phiếu chuyển kho từ CSV</h3>
    <div style="margin-bottom:14px;font-size:12px;color:#64748b">
      Định dạng CSV: <code>From_Warehouse, To_Warehouse, Product_SKU, Quantity, Note</code>
    </div>
    <div style="margin-bottom:18px">
      <input type="file" name="csv_file" accept=".csv" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('importSTModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Tải lên & Nhập</button>
    </div>
  </form>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.st-filter{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.st-filter input,.st-filter select{height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;background:#fff;font-size:13px}
.st-table{width:100%;border-collapse:collapse;background:#fff}
.st-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.st-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.st-table tr:hover td{background:#f9fbff}
.st-badge{padding:3px 9px;border-radius:4px;font-size:11px;font-weight:700}
.st-badge-pending{background:#fef9c3;color:#854d0e;border:1px solid #fde68a}
.st-badge-shipping{background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe}
.st-badge-completed{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
.st-badge-cancelled{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
</style>

<form class="st-filter" method="get" action="/admin/stock-transfers">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Mã phiếu, ghi chú..." style="min-width:250px">
  <select name="status">
    <option value="">Tất cả trạng thái</option>
    <option value="pending" <?= $statusFilter==='pending'?'selected':'' ?>>Chờ xuất</option>
    <option value="shipping" <?= $statusFilter==='shipping'?'selected':'' ?>>Đang vận chuyển</option>
    <option value="completed" <?= $statusFilter==='completed'?'selected':'' ?>>Đã nhận / Hoàn tất</option>
    <option value="cancelled" <?= $statusFilter==='cancelled'?'selected':'' ?>>Đã hủy</option>
  </select>
  <button class="btn btn-navy" type="submit">Lọc</button>
  <?php if($q||$statusFilter): ?>
  <a href="/admin/stock-transfers" class="btn btn-outline">Xóa lọc</a>
  <?php endif; ?>
</form>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="st-table">
  <thead>
    <tr>
      <th>Mã phiếu</th>
      <th>Kho xuất</th>
      <th>Kho nhận</th>
      <th>Trạng thái</th>
      <th>Ghi chú</th>
      <th>Người tạo</th>
      <th>Ngày nhận</th>
      <th>Ngày tạo</th>
      <th>Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($transfers as $tr): ?>
    <tr>
      <td style="font-family:monospace;font-weight:700">#<?= e($tr['code'] ?? '') ?></td>
      <td style="font-weight:600;color:#1a3258"><?= e($tr['from_warehouse'] ?? 'Kho chính') ?></td>
      <td style="font-weight:600;color:#0284c7"><?= e($tr['to_warehouse'] ?? 'Chi nhánh') ?></td>
      <td>
        <?php 
          $st = $tr['status'] ?? 'pending';
          $lbl = ['pending'=>'Chờ xuất','shipping'=>'Đang vận chuyển','completed'=>'Đã nhận / Hoàn tất','cancelled'=>'Đã hủy'][$st] ?? $st;
        ?>
        <span class="st-badge st-badge-<?= e($st) ?>"><?= e($lbl) ?></span>
      </td>
      <td style="font-size:12px;color:#6b7280;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($tr['note'] ?? '') ?>"><?= e(!empty($tr['note']) ? $tr['note'] : '—') ?></td>
      <td style="font-size:12px;color:#4b5563"><?= e($tr['creator_name'] ?? 'System') ?></td>
      <td style="font-size:12px;color:#10b981"><?= !empty($tr['received_at']) ? date('d/m/Y H:i', strtotime($tr['received_at'])) : '—' ?></td>
      <td style="font-size:12px;color:#9ca3af"><?= !empty($tr['created_at']) ? date('d/m/Y H:i', strtotime($tr['created_at'])) : '—' ?></td>
      <td>
        <a href="/admin/stock-transfers/<?= (int)$tr['id'] ?>" class="btn btn-outline" style="padding:4px 10px;font-size:12px">Chi tiết</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$transfers): ?>
    <tr><td colspan="9" style="padding:30px;text-align:center;color:#9ca3af">Chưa có phiếu chuyển kho nào. Bấm nút "+ Tạo phiếu chuyển kho mới" để bắt đầu.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if($totalPages > 1): $base = ['q'=>$q,'status'=>$statusFilter?:null]; ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/stock-transfers?<?= e(http_build_query(array_filter($base+['page'=>$page-1]))) ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/stock-transfers?<?= e(http_build_query(array_filter($base+['page'=>$page+1]))) ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
