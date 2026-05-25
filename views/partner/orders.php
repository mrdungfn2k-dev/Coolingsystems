<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Đơn hàng</h1></div>
<div class="panel"><table class="tbl"><thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Tổng</th><th>Trạng thái</th><th>Ngày</th></tr></thead><tbody>
<?php foreach($orders as $o):?><tr><td><?=e($o['order_code'])?></td><td><?=e($o['customer'])?></td><td><?=vnd($o['grand_total'])?></td><td><span class="badge-status <?=e($o['status'])?>"><?=orderStatus($o['status'])?></span></td><td class="fs-12"><?=relTime($o['created_at'])?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>