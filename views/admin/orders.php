<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center">
  <h1>Quản lý đơn hàng</h1>
  <a href="/admin/orders/create" class="btn btn-navy">+ Tạo đơn hàng hộ</a>
</div>

<style>
.del-select {
  padding:4px 10px; border-radius:16px; font-size:11px; font-weight:700; border:1px solid; outline:none; cursor:pointer;
  appearance:none; padding-right:24px; text-align:center;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:right 6px center; background-size:12px;
}
.del-select:disabled { cursor:not-allowed; opacity:0.8; background-image:none; padding-right:10px; }
.pay-select {
  padding:3px 8px; border-radius:12px; font-size:11px; font-weight:700; border:1px solid; outline:none; cursor:pointer;
  appearance:none; padding-right:20px;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:right 4px center; background-size:10px;
}
.pay-select:disabled { cursor:not-allowed; opacity:0.8; background-image:none; padding-right:8px; }
.pay-badge { display:inline-block; font-size:11px; font-weight:700; white-space:nowrap; padding:3px 8px; border-radius:12px; }
.pay-unpaid { color:#1a3258; background:#eef3fb; border:1px solid #1a3258; }
.pay-partial { color:#1a3258; background:#eef3fb; border:1px solid #1a3258; }
.pay-paid { color:#1a3258; background:#eef3fb; border:1px solid #1a3258; }
.pay-refunded { color:#1a3258; background:#eef3fb; border:1px solid #1a3258; }
</style>

<!-- Filter bar -->
<form method="get" action="/admin/orders" class="admin-filter" style="background:#fff;padding:14px 16px;border-radius:8px;border:1px solid var(--line);margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
  <div style="flex:1;min-width:180px">
    <label style="font-size:11px;font-weight:700;color:#888;display:block;margin-bottom:4px">TÌM KIẾM</label>
    <input type="text" name="q" class="form-control" value="<?= e($_GET['q']??'') ?>" placeholder="Mã đơn, tên KH, SĐT..." style="width:100%">
  </div>
  <div style="min-width:140px">
    <label style="font-size:11px;font-weight:700;color:#888;display:block;margin-bottom:4px">GIAO HÀNG</label>
    <select name="delivery" class="form-control js-cdd" style="width:100%">
      <option value="">Tất cả</option>
      <option value="pending" <?= ($_GET['delivery']??'')==='pending'?'selected':'' ?>>Đang chờ</option>
      <option value="received" <?= ($_GET['delivery']??'')==='received'?'selected':'' ?>>Tiếp nhận</option>
      <option value="delivering" <?= ($_GET['delivery']??'')==='delivering'?'selected':'' ?>>Đang giao</option>
      <option value="delivered" <?= ($_GET['delivery']??'')==='delivered'?'selected':'' ?>>Đã giao</option>
      <option value="completed" <?= ($_GET['delivery']??'')==='completed'?'selected':'' ?>>Đã hoàn thành</option>
      <option value="cancelled" <?= ($_GET['delivery']??'')==='cancelled'?'selected':'' ?>>Hủy đơn</option>
    </select>
  </div>
  <div style="min-width:140px">
    <label style="font-size:11px;font-weight:700;color:#888;display:block;margin-bottom:4px">THANH TOÁN</label>
    <select name="payment" class="form-control js-cdd" style="width:100%">
      <option value="">Tất cả</option>
      <option value="unpaid" <?= ($_GET['payment']??'')==='unpaid'?'selected':'' ?>>Chưa thanh toán</option>
      <option value="partial_paid" <?= ($_GET['payment']??'')==='partial_paid'?'selected':'' ?>>TT một phần</option>
      <option value="paid" <?= ($_GET['payment']??'')==='paid'?'selected':'' ?>>Đã thanh toán</option>
    </select>
  </div>
  <button type="submit" class="btn btn-navy" style="height:42px;padding:0 24px">Lọc</button>
  <a href="/admin/orders" class="btn btn-outline-navy" style="height:42px;padding:0 20px;display:flex;align-items:center;">Đặt lại</a>
</form>

<div class="panel">
  <div style="padding:12px 16px;border-bottom:1px solid var(--line);font-size:13px;color:#888">
    Hiển thị <?= count($orders) ?>/<?= $total ?? 0 ?> đơn hàng (trang <?= $page ?? 1 ?>/<?= $totalPages ?? 1 ?>)
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Mã đơn</th>
        <th>Khách hàng</th>
        <th>Người tạo</th>
        <th>Tổng tiền</th>
        <th style="width:90px">Hình thức TT</th>
        <th style="width:140px">Giao hàng</th>
        <th style="width:140px">Trạng thái TT</th>
        <th style="width:90px">Ngày đặt</th>
        <th style="width:80px">Thao tác</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
    <?php
      $delStatus = $o['delivery_status'] ?? 'pending';
      $payStatus = $o['payment_status'] ?? 'unpaid';
      $payMethod = $o['payment_method'] ?? 'cod';
      $payType   = $o['payment_type'] ?? 'cod';
      $ptMap = ['cod'=>'COD','bank_transfer'=>'CK trước', 'bank'=>'CK trước','deposit_70'=>'Cọc 70%','full_prepay'=>'TT 100%'];

      // Delivery status config
      $dMap = [
        'pending'    => ['#eef3fb','#1a3258','Đang chờ'],
        'received'   => ['#eef3fb','#1a3258','Tiếp nhận'],
        'delivering' => ['#eef3fb','#1a3258','Đang giao'],
        'delivered'  => ['#eef3fb','#1a3258','Đã giao'],
        'completed'  => ['#eef3fb','#1a3258','Đã hoàn thành'],
        'cancelled'  => ['#eef3fb','#1a3258','Hủy đơn'],
        'returned'   => ['#eef3fb','#1a3258','Đã trả hàng']
      ];
      $dCfg = $dMap[$delStatus] ?? $dMap['pending'];

      // Forward-only delivery order
      $deliveryOrder = ['pending'=>0,'received'=>1,'delivering'=>2,'delivered'=>3,'completed'=>4];
      $currentLevel = $deliveryOrder[$delStatus] ?? -1;

      // Is this order locked?
      $isCompleted = in_array($delStatus, ['completed']);
      $isCancelled = in_array($delStatus, ['cancelled']);
      $isReturned  = in_array($delStatus, ['returned']);
      $isLocked    = $isCompleted || $isCancelled || $isReturned;

      // Payment locked if: completed, cancelled, returned, OR payment method is QR/bank_transfer (already paid)
      $isQrPayment = in_array($payMethod, ['bank_transfer', 'bank']) && $payType !== 'cod';
      $paymentLocked = $isLocked || $isQrPayment || $payStatus === 'paid' || $payStatus === 'refunded';
    ?>
    <tr>
      <td style="font-weight:700;color:var(--navy)"><?= e($o['code'] ?? $o['order_code'] ?? 'N/A') ?></td>
      <td>
        <div style="font-weight:600"><?= e($o['full_name'] ?? $o['shipping_full_name'] ?? 'N/A') ?></div>
        <div style="font-size:11px;color:#666"><?= e($o['phone']??'') ?></div>
      </td>
      <td>
        <?php if(!empty($o['created_by_staff'])): ?>
          <?php if(($o['staff_role']??'') === 'admin'): ?>
            <span style="font-size:11px;background:#fef2f2;border:1px solid #fecaca;padding:2px 6px;border-radius:4px;color:#b91c1c;font-weight:600">Admin</span>
          <?php else: ?>
            <span style="font-size:11px;background:#e0f2fe;border:1px solid #bae6fd;padding:2px 6px;border-radius:4px;color:#0369a1;font-weight:600"><?= e($o['staff_name']) ?></span>
          <?php endif; ?>
        <?php elseif(!empty($o['user_id'])): ?>
          <span style="font-size:11px;color:#059669;font-weight:600;background:#ecfdf5;padding:2px 6px;border-radius:4px;border:1px solid #a7f3d0;">Khách hàng</span>
        <?php else: ?>
          <span style="font-size:11px;color:#4b5563;font-weight:600;background:#f3f4f6;padding:2px 6px;border-radius:4px;border:1px solid #d1d5db;">Khách vãng lai</span>
        <?php endif; ?>
      </td>
      <td style="font-weight:700;color:#c0392b"><?= vnd($o['grand_total'] ?? 0) ?></td>
      <td style="font-size:12px;font-weight:600"><?= $ptMap[$payType] ?? 'COD' ?></td>
      <td>
        <?php if ($isLocked): ?>
          <!-- Locked delivery: show as static badge -->
          <span class="del-select" style="background-color:<?= $dCfg[0] ?>;color:<?= $dCfg[1] ?>;border-color:<?= $dCfg[1] ?>;cursor:default;display:inline-block;padding-right:10px;background-image:none;">
            <?= $dCfg[2] ?>
          </span>
        <?php else: ?>
          <!-- Active delivery dropdown: forward-only -->
          <form method="post" action="/admin/orders/<?= $o['id'] ?>/delivery-status">
            <?= csrfField() ?>
            <select name="status" class="del-select" style="background-color:<?= $dCfg[0] ?>;color:<?= $dCfg[1] ?>;border-color:<?= $dCfg[1] ?>;" onchange="this.form.submit()">
              <?php
              $allDel = [
                'pending'=>'Đang chờ','received'=>'Tiếp nhận','delivering'=>'Đang giao',
                'delivered'=>'Đã giao','completed'=>'Đã hoàn thành','cancelled'=>'Hủy đơn'
              ];
              foreach ($allDel as $val => $label):
                $optLevel = $deliveryOrder[$val] ?? 99;
                // Allow current, forward steps, and cancel (always available before completion)
                if ($val === $delStatus || $optLevel > $currentLevel || $val === 'cancelled'):
              ?>
                <option value="<?= $val ?>" <?= $delStatus===$val ? 'selected':'' ?>><?= $label ?></option>
              <?php endif; endforeach; ?>
            </select>
          </form>
        <?php endif; ?>
      </td>
      <td>
        <?php if ($paymentLocked): ?>
          <!-- Locked payment: show as static badge -->
          <?php
            $psLabel = ['unpaid'=>'Chưa thanh toán','partial_paid'=>'TT một phần','paid'=>'Đã thanh toán','refunded'=>'Đã hoàn tiền'];
            $psCls = ['unpaid'=>'pay-unpaid','partial_paid'=>'pay-partial','paid'=>'pay-paid','refunded'=>'pay-refunded'];
          ?>
          <span class="pay-badge <?= $psCls[$payStatus] ?? 'pay-unpaid' ?>"><?= $psLabel[$payStatus] ?? '—' ?></span>
        <?php else: ?>
          <!-- COD payment: show dropdown -->
          <form method="post" action="/admin/orders/<?= $o['id'] ?>/payment-status">
            <?= csrfField() ?>
            <select name="status" class="pay-select" style="background-color:<?= $payStatus==='paid'?'#ecfdf5':($payStatus==='partial_paid'?'#fdf2f8':'#fffbeb') ?>;color:<?= $payStatus==='paid'?'#059669':($payStatus==='partial_paid'?'#9d174d':'#d97706') ?>;border-color:<?= $payStatus==='paid'?'#a7f3d0':($payStatus==='partial_paid'?'#fbcfe8':'#fde68a') ?>;" onchange="this.form.submit()">
              <option value="unpaid" <?= $payStatus==='unpaid'?'selected':'' ?>>Chưa thanh toán</option>
              <option value="partial_paid" <?= $payStatus==='partial_paid'?'selected':'' ?>>TT một phần</option>
              <option value="paid" <?= $payStatus==='paid'?'selected':'' ?>>Đã thanh toán</option>
            </select>
          </form>
        <?php endif; ?>
        <?php if($payStatus==='partial_paid' && !empty($o['remaining_amount'])): ?>
          <div style="font-size:10px;color:#888;margin-top:2px">Còn: <?= vnd($o['remaining_amount']) ?></div>
        <?php endif; ?>
      </td>
      <td style="font-size:11px;color:#666"><?= date('d/m/Y', strtotime($o['created_at'])) ?><br><?= date('H:i', strtotime($o['created_at'])) ?></td>
      <td>
        <a href="/admin/orders/<?= $o['id'] ?>" class="btn btn-outline-navy btn-sm">Chi tiết</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$orders): ?>
    <tr><td colspan="9" style="text-align:center;padding:30px;color:#888">Không tìm thấy đơn hàng nào.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>

  <?php if (($totalPages??0) > 1): ?>
<div style="padding:12px 16px;display:flex;gap:6px;justify-content:center">
<?php
require_once __DIR__.'/../partials/pagination.php';
renderPagination($page, $totalPages, '', $_GET);
?>
</div>
<?php endif; ?>
</div>


<style>
.csv-dropdown-wrap .csv-dropdown-menu{display:none;position:absolute;top:100%;left:0;background:#fff;border:1px solid var(--line);border-radius:6px;box-shadow:var(--shadow-lg);z-index:100;min-width:160px;margin-top:4px}
.csv-dropdown-wrap.open .csv-dropdown-menu{display:block}
.csv-dropdown-item{display:block;padding:8px 14px;color:var(--navy);text-decoration:none;font-size:12px;white-space:nowrap}
.csv-dropdown-item:hover{background:var(--bg-soft)}
</style>
<script>
function updateCount(type){var c=document.querySelectorAll('.'+type.slice(0,-1)+'-tick:checked').length;var el=document.getElementById('selected-count-'+type);if(el)el.textContent=c>0?c+' mục đã chọn':'';}
function exportSelected(type,url){var checked=document.querySelectorAll('.'+type.slice(0,-1)+'-tick:checked');if(!checked.length){alert('Vui lòng chọn ít nhất 1 mục để xuất');return;}var ids=Array.from(checked).map(c=>c.value).join(',');window.location.href=url+'?ids='+ids;}
document.addEventListener('click',e=>{document.querySelectorAll('.csv-dropdown-wrap.open').forEach(el=>{if(!el.contains(e.target))el.classList.remove('open');});});
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>