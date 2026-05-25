<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Tổng quan gian hàng</h1><span class="fs-13 text-muted"><?=e($partner['shop_name'])?></span></div>
<div class="kpi-grid"><div class="kpi"><div class="num"><?=vnd($partnerWallet['balance_available']??0)?></div><div class="lbl">Số dư khả dụng</div></div>
<div class="kpi"><div class="num"><?=$stats['active_products']?></div><div class="lbl">SP đang bán</div></div>
<div class="kpi"><div class="num"><?=$stats['active_orders']?></div><div class="lbl">Đơn cần xử lý</div></div>
<div class="kpi"><div class="num"><?=vnd($stats['total_revenue'])?></div><div class="lbl">Tổng doanh thu</div></div></div>
<div class="panel"><div class="sec-head"><div class="title"><span class="bar"></span><h2>Đơn hàng gần đây</h2></div></div>
<?php if(empty($recentOrders)):?><p class="text-muted" style="padding:16px">Chưa có đơn hàng</p>
<?php else:?><table class="tbl"><thead><tr><th>Mã</th><th>Khách</th><th>Tổng</th><th>TT</th><th>Ngày</th></tr></thead><tbody>
<?php foreach($recentOrders as $o):?><tr><td><?=e($o['code'])?></td><td><?=e($o['customer'])?></td><td><?=vnd($o['grand_total'])?></td><td><span class="badge-status <?=e($o['status'])?>"><?=orderStatus($o['status'])?></span></td><td class="fs-12"><?=relTime($o['created_at'])?></td></tr><?php endforeach;?></tbody></table><?php endif;?></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>