<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Lịch nhắc bảo dưỡng định kỳ</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Tự động nhắc lịch bảo trì phụ tùng (thay lốc lạnh, nạp ga, thay lọc gió) cho khách hàng.</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <a href="/admin/crm/maintenance/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
    <button onclick="document.getElementById('newMaintModal').style.display='flex'" class="btn btn-navy">+ Lập lịch bảo dưỡng mới</button>
  </div>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.maint-table{width:100%;border-collapse:collapse;background:#fff}
.maint-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.maint-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.maint-badge{padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700}
.maint-badge-pending{background:#fef9c3;color:#854d0e;border:1px solid #fde68a}
.maint-badge-notified{background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe}
.maint-badge-completed{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
</style>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="maint-table">
  <thead>
    <tr>
      <th>Khách hàng</th>
      <th>SĐT</th>
      <th>Phụ tùng bảo dưỡng</th>
      <th>Nội dung bảo trì</th>
      <th>Ngày đến hạn</th>
      <th>Trạng thái</th>
      <th>Ghi chú</th>
      <th>Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($schedules as $s): ?>
    <tr>
      <td><strong style="color:#1a3258"><?= e($s['customer_name'] ?? 'Khách vãng lai') ?></strong></td>
      <td style="font-size:12px;color:#64748b"><?= e($s['customer_phone'] ?: '—') ?></td>
      <td><span style="font-weight:600;color:#0284c7"><?= e($s['product_name']) ?></span></td>
      <td><?= e($s['service_name']) ?></td>
      <td><strong style="color:#e11d48"><?= date('d/m/Y', strtotime($s['next_due_date'])) ?></strong></td>
      <td>
        <?php 
          $st = $s['status'] ?? 'pending';
          $lbl = ['pending'=>'Chờ nhắc','notified'=>'Đã gửi thông báo','completed'=>'Đã hoàn thành'][$st] ?? $st;
        ?>
        <span class="maint-badge maint-badge-<?= e($st) ?>"><?= e($lbl) ?></span>
      </td>
      <td style="font-size:12px;color:#64748b"><?= e($s['note'] ?: '—') ?></td>
      <td>
        <?php if($st !== 'completed'): ?>
        <form method="post" action="/admin/crm/maintenance/<?= (int)$s['id'] ?>/status" style="display:inline">
          <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
          <input type="hidden" name="status" value="completed">
          <button type="submit" class="btn btn-outline" style="padding:3px 8px;font-size:11px;color:#16a34a">✔ Hoàn thành</button>
        </form>
        <?php else: ?>
        <span style="color:#16a34a;font-size:12px;font-weight:700">✔ Xong</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$schedules): ?>
    <tr><td colspan="8" style="padding:30px;text-align:center;color:#9ca3af">Chưa có lịch nhắc bảo dưỡng nào. Bấm nút "+ Lập lịch bảo dưỡng mới" để tạo.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if(isset($totalPages) && $totalPages > 1): ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/crm/maintenance?page=<?= $page-1 ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/crm/maintenance?page=<?= $page+1 ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<div id="newMaintModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/crm/maintenance" style="background:#fff;padding:24px;border-radius:10px;max-width:500px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 16px;color:#1a3258">Lập lịch nhắc bảo dưỡng mới</h3>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Chọn khách hàng <span style="color:#e11d48">*</span></label>
      <select name="user_id" required style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px;background:#fff">
        <option value="">-- Chọn khách hàng --</option>
        <?php foreach($customers as $c): ?>
        <option value="<?= (int)$c['id'] ?>"><?= e($c['full_name']) ?> (<?= e($c['phone'] ?: 'Không SĐT') ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Tên sản phẩm / Phụ tùng <span style="color:#e11d48">*</span></label>
      <input type="text" name="product_name" required placeholder="Ví dụ: Dàn lạnh Toyota Vios, Lốc lạnh Denso..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Dịch vụ bảo trì <span style="color:#e11d48">*</span></label>
      <input type="text" name="service_name" required placeholder="Ví dụ: Vệ sinh dàn lạnh, Nạp ga máy lạnh, Thay lọc gió..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:14px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Ngày đến hạn nhắc nhở <span style="color:#e11d48">*</span></label>
      <input type="date" name="next_due_date" required value="<?= date('Y-m-d', strtotime('+6 months')) ?>" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:20px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Ghi chú</label>
      <input type="text" name="note" placeholder="Dặn dò khách hàng..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('newMaintModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Lưu lịch bảo dưỡng</button>
    </div>
  </form>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
