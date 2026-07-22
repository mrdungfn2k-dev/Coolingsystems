<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Quản lý Khiếu nại & Hỗ trợ KH (Tickets)</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Tiếp nhận khiếu nại, phản hồi của khách hàng và phân công nhân viên xử lý.</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <a href="/admin/crm/tickets/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
    <button onclick="document.getElementById('newTicketModal').style.display='flex'" class="btn btn-navy">+ Tiếp nhận khiếu nại mới</button>
  </div>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.tk-table{width:100%;border-collapse:collapse;background:#fff}
.tk-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.tk-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.tk-prio{padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700}
.tk-prio-low{background:#f1f5f9;color:#475569}
.tk-prio-medium{background:#fef9c3;color:#854d0e}
.tk-prio-high{background:#ffedd5;color:#c2410c}
.tk-prio-urgent{background:#fee2e2;color:#b91c1c}
.tk-status{padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700}
.tk-status-open{background:#fef9c3;color:#854d0e}
.tk-status-in_progress{background:#eff6ff;color:#1e40af}
.tk-status-resolved{background:#dcfce7;color:#166534}
.tk-status-closed{background:#f3f4f6;color:#4b5563}
</style>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="tk-table">
  <thead>
    <tr>
      <th>Mã Ticket</th>
      <th>Khách hàng</th>
      <th>Tiêu đề khiếu nại</th>
      <th>Mức độ ưu tiên</th>
      <th>Trạng thái</th>
      <th>Ngày tạo</th>
      <th>Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($tickets as $t): ?>
    <tr>
      <td style="font-family:monospace;font-weight:700">#<?= e($t['code']) ?></td>
      <td><strong style="color:#1a3258"><?= e($t['customer_name'] ?? 'Khách hàng') ?></strong></td>
      <td>
        <strong style="color:#1e3a8a"><?= e($t['title']) ?></strong>
        <?php if(!empty($t['description'])): ?>
        <div style="font-size:12px;color:#64748b;margin-top:2px"><?= e($t['description']) ?></div>
        <?php endif; ?>
      </td>
      <td>
        <?php 
          $pr = $t['priority'] ?? 'medium';
          $prLbl = ['low'=>'Thấp','medium'=>'Trung bình','high'=>'Cao','urgent'=>'🔴 Khẩn cấp'][$pr] ?? $pr;
        ?>
        <span class="tk-prio tk-prio-<?= e($pr) ?>"><?= e($prLbl) ?></span>
      </td>
      <td>
        <?php 
          $st = $t['status'] ?? 'open';
          $stLbl = ['open'=>'Mới tiếp nhận','in_progress'=>'Đang xử lý','resolved'=>'Đã giải quyết','closed'=>'Đã đóng'][$st] ?? $st;
        ?>
        <span class="tk-status tk-status-<?= e($st) ?>"><?= e($stLbl) ?></span>
      </td>
      <td style="font-size:12px;color:#9ca3af"><?= !empty($t['created_at']) ? date('d/m/Y H:i', strtotime($t['created_at'])) : '—' ?></td>
      <td>
        <?php if($st !== 'resolved' && $st !== 'closed'): ?>
        <form method="post" action="/admin/crm/tickets/<?= (int)$t['id'] ?>/status" style="display:inline">
          <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
          <input type="hidden" name="status" value="resolved">
          <button type="submit" class="btn btn-outline" style="padding:3px 8px;font-size:11px;color:#16a34a">✔ Giải quyết</button>
        </form>
        <?php else: ?>
        <span style="color:#16a34a;font-size:12px;font-weight:700">✔ Xong</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$tickets): ?>
    <tr><td colspan="7" style="padding:30px;text-align:center;color:#9ca3af">Chưa có ticket khiếu nại nào. Bấm nút "+ Tiếp nhận khiếu nại mới" để tạo.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if(isset($totalPages) && $totalPages > 1): ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/crm/tickets?page=<?= $page-1 ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/crm/tickets?page=<?= $page+1 ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<div id="newTicketModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/crm/tickets" style="background:#fff;padding:24px;border-radius:10px;max-width:500px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 16px;color:#1a3258">Tiếp nhận ticket hỗ trợ / khiếu nại</h3>

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
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Tiêu đề khiếu nại / hỗ trợ <span style="color:#e11d48">*</span></label>
      <input type="text" name="title" required placeholder="Ví dụ: Phụ tùng giao bị trầy xước, lỗi lắp đặt lốc lạnh..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Mức độ ưu tiên</label>
      <select name="priority" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px;background:#fff">
        <option value="low">Thấp</option>
        <option value="medium" selected>Trung bình</option>
        <option value="high">Cao</option>
        <option value="urgent">🔴 Khẩn cấp</option>
      </select>
    </div>

    <div style="margin-bottom:20px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Nội dung chi tiết</label>
      <textarea name="description" rows="3" placeholder="Mô tả sự cố của khách hàng..." style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px;font-size:13px"></textarea>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('newTicketModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Tạo ticket</button>
    </div>
  </form>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
