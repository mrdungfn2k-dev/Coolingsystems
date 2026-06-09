<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<h1 style="font-size:22px;font-weight:800;color:var(--navy);margin-bottom:20px">Quản lý yêu cầu trả hàng</h1>

<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
  <a href="/admin/returns" class="btn <?= empty($currentStatus) ? 'btn-navy' : 'btn-outline-navy' ?> btn-sm">
    Tất cả (<?= $counts['all'] ?>)
  </a>
  <a href="/admin/returns?status=pending" class="btn <?= $currentStatus==='pending' ? 'btn-navy' : 'btn-outline-navy' ?> btn-sm">
    Chờ duyệt (<?= $counts['pending'] ?>)
  </a>
  <a href="/admin/returns?status=approved" class="btn <?= $currentStatus==='approved' ? 'btn-navy' : 'btn-outline-navy' ?> btn-sm">
    Đã duyệt (<?= $counts['approved'] ?>)
  </a>
  <a href="/admin/returns?status=rejected" class="btn <?= $currentStatus==='rejected' ? 'btn-navy' : 'btn-outline-navy' ?> btn-sm">
    Từ chối (<?= $counts['rejected'] ?>)
  </a>
</div>

<?php if (empty($returns)): ?>
  <div style="text-align:center;padding:60px 20px;color:var(--ink-3);font-size:14px">
    Chưa có yêu cầu trả hàng nào<?= $currentStatus ? ' ở trạng thái này' : '' ?>.
  </div>
<?php else: ?>

<?php foreach ($returns as $r): ?>
<?php
  $statusMap = [
    'pending'=>['Chờ duyệt','#f59e0b','#fffbeb','#fde68a'],
    'approved'=>['Đã duyệt','#059669','#ecfdf5','#a7f3d0'],
    'rejected'=>['Từ chối','#dc2626','#fef2f2','#fca5a5']
  ];
  $st = $statusMap[$r['status']] ?? ['—','#666','#f5f5f5','#ddd'];
  $refund = intval($r['refund_amount'] ?: ($r['grand_total'] ?? 0));
?>
<div style="background:#fff;border:1px solid var(--line);border-radius:10px;padding:20px;margin-bottom:16px">
  <!-- Header row -->
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px">
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
      <span style="font-size:13px;color:var(--ink-3);font-weight:600">#<?= $r['id'] ?></span>
      <a href="/admin/orders/<?= $r['order_id'] ?>" style="font-weight:700;color:var(--navy);font-size:15px;text-decoration:underline">
        Đơn #<?= e($r['order_code'] ?? '') ?>
      </a>
      <span style="font-size:13px;color:var(--ink-3)"><?= function_exists('vnd') ? vnd($r['grand_total'] ?? 0) : number_format($r['grand_total'] ?? 0, 0, ',', '.') . ' đ' ?></span>
      <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;background:<?= $st[2] ?>;color:<?= $st[1] ?>;border:1px solid <?= $st[3] ?>">
        <?= $st[0] ?>
      </span>
    </div>
    <span style="font-size:12px;color:#999"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></span>
  </div>

  <!-- Content grid -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
    <!-- Col 1: Khách hàng + Lý do -->
    <div>
      <div style="font-size:11px;color:var(--ink-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;font-weight:600">Khách hàng</div>
      <div style="font-weight:600;font-size:14px;margin-bottom:2px"><?= e($r['full_name'] ?? '') ?></div>
      <div style="font-size:12px;color:#666;margin-bottom:10px"><?= e($r['user_email'] ?? '') ?></div>
      
      <div style="font-size:11px;color:var(--ink-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;font-weight:600">Lý do trả hàng</div>
      <div style="font-size:13px;color:var(--ink);line-height:1.5;word-break:break-word"><?= nl2br(e($r['reason'] ?? '')) ?></div>
      
      <?php if(!empty($r['image_path'])): ?>
      <div style="margin-top:10px">
        <div style="font-size:11px;color:var(--ink-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;font-weight:600">Ảnh minh chứng</div>
        <a href="<?= strpos($r['image_path'], '/uploads/') === 0 ? e($r['image_path']) : '/uploads/returns/' . e($r['image_path']) ?>" target="_blank">
          <img src="<?= strpos($r['image_path'], '/uploads/') === 0 ? e($r['image_path']) : '/uploads/returns/' . e($r['image_path']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #ddd" onerror="this.style.display='none'">
        </a>
      </div>
      <?php endif; ?>
      <?php if(!empty($r['video_path'])): ?>
      <div style="margin-top:10px">
        <div style="font-size:11px;color:var(--ink-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;font-weight:600">Video minh chứng</div>
        <video controls style="max-width:200px;max-height:150px;border-radius:8px;border:1px solid #ddd">
          <source src="<?= strpos($r['video_path'], '/uploads/') === 0 ? e($r['video_path']) : '/uploads/returns/' . e($r['video_path']) ?>" type="video/mp4">
        </video>
      </div>
      <?php endif; ?>
    </div>

    <!-- Col 2: Liên hệ -->
    <div>
      <div style="font-size:11px;color:var(--ink-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;font-weight:600">Liên hệ</div>
      <div style="font-size:13px;margin-bottom:3px">SĐT: <strong><?= e($r['contact_phone'] ?? '') ?></strong></div>
      <?php if(!empty($r['contact_email'])): ?>
        <div style="font-size:12px;color:#666;margin-bottom:3px">Email: <?= e($r['contact_email']) ?></div>
      <?php endif; ?>
      <?php if(!empty($r['contact_address'])): ?>
        <div style="font-size:12px;color:#666">Địa chỉ: <?= e($r['contact_address']) ?></div>
      <?php endif; ?>
    </div>

    <!-- Col 3: Thông tin hoàn tiền -->
    <div>
      <div style="font-size:11px;color:var(--ink-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;font-weight:600">Thông tin hoàn tiền</div>
      <?php if(!empty($r['bank_account'])): ?>
        <div style="font-size:13px;margin-bottom:3px">Số tài khoản: <strong><?= e($r['bank_account']) ?></strong></div>
        <div style="font-size:13px;margin-bottom:3px">Chủ tài khoản: <strong><?= e($r['bank_holder'] ?? '') ?></strong></div>
        <div style="font-size:14px;font-weight:700;color:#dc2626;margin-top:6px">Số tiền hoàn: <?= function_exists('vnd') ? vnd($refund) : number_format($refund, 0, ',', '.') . ' đ' ?></div>
      <?php else: ?>
        <div style="font-size:13px;color:#999">Chưa cung cấp thông tin ngân hàng</div>
        <div style="font-size:14px;font-weight:700;color:#dc2626;margin-top:6px">Giá trị đơn: <?= function_exists('vnd') ? vnd($r['grand_total'] ?? 0) : number_format($r['grand_total'] ?? 0, 0, ',', '.') . ' đ' ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Actions -->
  <?php if ($r['status'] === 'pending'): ?>
  <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;padding-top:14px;border-top:1px solid var(--line)">
    <form method="post" action="/admin/returns/<?= $r['id'] ?>/approve" style="margin:0">
      <?= csrfField() ?>
      <button type="submit" class="btn btn-sm" style="background:#059669;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-weight:700;font-size:13px" onclick="return csConfirmBtn(this,'Duyệt yêu cầu trả hàng và trừ doanh thu?')">Duyệt trả hàng</button>
    </form>
    <form method="post" action="/admin/returns/<?= $r['id'] ?>/reject" style="margin:0">
      <?= csrfField() ?>
      <button type="submit" class="btn btn-sm" style="background:#dc2626;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-weight:700;font-size:13px" onclick="return csConfirmBtn(this,'Từ chối yêu cầu trả hàng này?')">Từ chối</button>
    </form>
    <form method="post" action="/admin/returns/<?= $r['id'] ?>/delete" style="margin:0" onsubmit="return csConfirmForm(this,'Bạn có chắc muốn xóa yêu cầu trả hàng #<?= $r['id'] ?> ?')">
      <?= csrfField() ?>
      <button type="submit" class="btn btn-sm" style="background:#fff;color:#666;border:1px solid #ddd;padding:8px 16px;border-radius:6px;font-weight:600;font-size:13px;cursor:pointer" title="Xóa yêu cầu">🗑 Xóa</button>
    </form>
  </div>
  <?php elseif ($r['status'] === 'approved'): ?>
  <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--line);font-size:13px;color:#059669;font-weight:600;display:flex;justify-content:space-between;align-items:center">
    <span>Đã duyệt</span>
    <form method="post" action="/admin/returns/<?= $r['id'] ?>/delete" style="margin:0" onsubmit="return csConfirmForm(this,'Xóa yêu cầu trả hàng đã duyệt này?')">
      <?= csrfField() ?>
      <button type="submit" style="background:none;border:1px solid #ddd;color:#999;padding:4px 12px;border-radius:5px;font-size:11px;cursor:pointer">🗑 Xóa</button>
    </form>
  </div>
  <?php elseif ($r['status'] === 'rejected'): ?>
  <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--line);font-size:13px;color:#dc2626;font-weight:600;display:flex;justify-content:space-between;align-items:center">
    <span>Đã từ chối</span>
    <form method="post" action="/admin/returns/<?= $r['id'] ?>/delete" style="margin:0" onsubmit="return csConfirmForm(this,'Xóa yêu cầu trả hàng đã từ chối này?')">
      <?= csrfField() ?>
      <button type="submit" style="background:none;border:1px solid #ddd;color:#999;padding:4px 12px;border-radius:5px;font-size:11px;cursor:pointer">🗑 Xóa</button>
    </form>
  </div>
  <?php endif; ?>
</div>
<?php endforeach; ?>

<?php endif; ?>


<?php if (($totalPages ?? 0) > 1): ?>
<div style="display:flex;justify-content:center;gap:8px;margin-top:24px;padding:16px 0">
    <?php
    require_once __DIR__.'/../partials/pagination.php';
    renderPagination($page, $totalPages, '/admin/returns', $_GET);
    ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
