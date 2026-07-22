<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Báo giá độc lập</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Tạo và gửi báo giá cho khách hàng trước khi chuyển đổi thành đơn hàng chính thức.</p>
  </div>
  <a href="/admin/quotations/new" class="btn btn-navy">+ Tạo báo giá mới</a>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.q-filter{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.q-filter input,.q-filter select{height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;background:#fff;font-size:13px}
.q-table{width:100%;border-collapse:collapse;background:#fff}
.q-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.q-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.q-table tr:hover td{background:#f9fbff}
.q-status{padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700}
.q-status-pending{background:#fef9c3;color:#854d0e;border:1px solid #fde68a}
.q-status-sent{background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe}
.q-status-converted{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
.q-status-expired{background:#f3f4f6;color:#374151;border:1px solid #d1d5db}
.q-status-cancelled{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
</style>

<form class="q-filter" method="get" action="/admin/quotations">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Mã báo giá, tên khách hàng..." style="min-width:250px">
  <select name="status">
    <option value="">Tất cả trạng thái</option>
    <option value="pending" <?= $statusFilter==='pending'?'selected':'' ?>>Chờ duyệt / Nháp</option>
    <option value="sent" <?= $statusFilter==='sent'?'selected':'' ?>>Đã gửi KH</option>
    <option value="converted" <?= $statusFilter==='converted'?'selected':'' ?>>Đã chuyển đơn hàng</option>
    <option value="expired" <?= $statusFilter==='expired'?'selected':'' ?>>Đã hết hạn</option>
    <option value="cancelled" <?= $statusFilter==='cancelled'?'selected':'' ?>>Đã hủy</option>
  </select>
  <button class="btn btn-navy" type="submit">Lọc</button>
  <?php if($q||$statusFilter): ?>
  <a href="/admin/quotations" class="btn btn-outline">Xóa lọc</a>
  <?php endif; ?>
</form>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="q-table">
  <thead>
    <tr>
      <th>Mã báo giá</th>
      <th>Khách hàng</th>
      <th style="text-align:right">Tổng giá trị</th>
      <th>Trạng thái</th>
      <th>Ngày hết hạn</th>
      <th>Ghi chú</th>
      <th>Người tạo</th>
      <th>Ngày tạo</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($quotations as $qt): ?>
    <tr>
      <td style="font-family:monospace;font-weight:700">#<?= e($qt['code'] ?? '') ?></td>
      <td>
        <strong style="color:#1f365b"><?= e($qt['customer_name'] ?? '—') ?></strong>
        <div style="font-size:11px;color:#718096;margin-top:2px"><?= e($qt['customer_phone'] ?? '') ?></div>
      </td>
      <td style="text-align:right;font-weight:700;color:#1e3a8a"><?= number_format((int)($qt['grand_total'] ?? 0)) ?> đ</td>
      <td>
        <span class="q-status q-status-<?= e($qt['status'] ?? 'pending') ?>">
          <?= ['pending'=>'Chờ duyệt','sent'=>'Đã gửi KH','converted'=>'Đã xuất đơn','expired'=>'Hết hạn','cancelled'=>'Đã hủy'][$qt['status'] ?? 'pending'] ?? e($qt['status'] ?? '') ?>
        </span>
      </td>
      <td style="color:#e11d48;font-weight:500"><?= e(substr((string)($qt['expires_at'] ?? ''),0,10)) ?></td>
      <td style="font-size:12px;color:#6b7280;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($qt['note'] ?? '') ?>"><?= e(!empty($qt['note']) ? $qt['note'] : '—') ?></td>
      <td style="font-size:12px;color:#6b7280"><?= e($qt['creator_name'] ?? 'System') ?></td>
      <td style="font-size:12px;color:#9ca3af"><?= !empty($qt['created_at']) ? date('d/m/Y H:i', strtotime($qt['created_at'])) : '—' ?></td>
      <td>
        <div style="display:flex;gap:5px">
          <a href="/admin/quotations/<?= (int)$qt['id'] ?>" class="btn btn-outline" style="padding:4px 8px;font-size:12px">Chi tiết</a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$quotations): ?>
    <tr><td colspan="9" style="padding:30px;text-align:center;color:#9ca3af">Chưa có bản báo giá nào.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if($totalPages > 1): $base = ['q'=>$q,'status'=>$statusFilter?:null]; ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/quotations?<?= e(http_build_query(array_filter($base+['page'=>$page-1]))) ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/quotations?<?= e(http_build_query(array_filter($base+['page'=>$page+1]))) ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
