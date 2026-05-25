<?php require __DIR__.'/../partials/head.php'; ?>
<section class="block"><div class="wrap"><div class="sec-card">
  <div class="sec-head"><div class="title"><span class="bar"></span><h2>Đơn hàng của tôi</h2></div></div>
  <?php if(empty($orders)):?><div class="empty-state"><h3>Chưa có đơn hàng</h3><a href="/products" class="btn btn-gold">Khám phá sản phẩm</a></div>
  <?php else:?>
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead><tr><th>Mã đơn</th><th>Ngày</th><th>Tổng tiền</th><th>Thanh toán</th><th>Giao hàng</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php foreach($orders as $o):
      $ds = $o['delivery_status'] ?? 'pending';
      $ps = $o['payment_status'] ?? 'unpaid';
      $statusMap = [
        'pending'=>['Chờ xác nhận','#f59e0b'],
        'confirmed'=>['Đã xác nhận','#3b82f6'],
        'shipping'=>['Đang giao','#8b5cf6'],
        'delivered'=>['Đã giao','#10b981'],
        'completed'=>['Hoàn thành','#059669'],
        'cancelled'=>['Đã hủy','#ef4444'],
        'returned'=>['Trả hàng','#6b7280']
      ];
      $sInfo = $statusMap[$ds] ?? ['Không rõ','#888'];
      $hasReturn = !empty($returnsByOrderId[$o['id']]);
    ?>
    <tr>
      <td data-label="Mã đơn"><a href="/customer/orders/<?= $o['id'] ?>" style="color:#1a3258;font-weight:700;text-decoration:none"><?= e($o['code']) ?></a></td>
      <td data-label="Ngày" class="fs-12"><?= relTime($o['created_at']) ?></td>
      <td data-label="Tổng tiền" style="font-weight:700;color:#c8962b"><?= vnd($o['grand_total']) ?></td>
      <td data-label="Thanh toán"><span style="font-size:11px;padding:3px 8px;border-radius:4px;background:<?= $ps==='paid'?'#d1fae5':'#fef3c7' ?>;color:<?= $ps==='paid'?'#059669':'#92400e' ?>;font-weight:600"><?= $ps==='paid'?'Đã TT':'Chưa TT' ?></span></td>
      <td data-label="Giao hàng"><span style="font-size:11px;padding:3px 8px;border-radius:4px;background:<?= $sInfo[1] ?>20;color:<?= $sInfo[1] ?>;font-weight:600"><?= $sInfo[0] ?></span></td>
      <td data-label="Thao tác" style="white-space:nowrap">
        <a href="/customer/orders/<?= $o['id'] ?>" style="font-size:12px;color:#1a3258;font-weight:600;text-decoration:none;margin-right:8px">Xem</a>
        <?php if ($ds === 'pending'): ?>
          <form method="post" action="/customer/orders/<?= $o['id'] ?>/cancel" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
            <?= csrfField() ?>
            <button type="submit" style="font-size:11px;padding:4px 10px;background:#ef4444;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:600">Hủy đơn</button>
          </form>
        <?php endif; ?>
        <?php if (in_array($ds, ['delivered','completed']) && !$hasReturn): ?>
          <a href="/customer/orders/<?= $o['id'] ?>?return=1" style="font-size:11px;padding:4px 10px;background:#f59e0b;color:#fff;border-radius:4px;text-decoration:none;font-weight:600">Trả hàng</a>
        <?php elseif ($hasReturn): ?>
          <span style="font-size:11px;color:#6b7280;font-weight:600">Đã yêu cầu trả</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div></div></section>

<style>
.tbl { width:100%; border-collapse:collapse; }
.tbl th { background:#1a3258; color:#fff; padding:10px 12px; text-align:left; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; }
.tbl td { padding:10px 12px; border-bottom:1px solid #eee; font-size:13px; vertical-align:middle; }
.tbl tr:hover { background:#f8f9fb; }
@media (max-width: 768px) {
  .tbl thead { display:none; }
  .tbl tr { display:block; border:1px solid #e5e7eb; border-radius:10px; margin-bottom:12px; padding:12px; background:#fff; }
  .tbl td { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
  .tbl td:last-child { border-bottom:none; }
  .tbl td::before { content:attr(data-label); font-weight:700; color:#1a3258; font-size:11px; text-transform:uppercase; min-width:100px; }
}
</style>
<?php require __DIR__.'/../partials/foot.php'; ?>