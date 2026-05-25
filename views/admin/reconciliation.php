<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Đối soát phí sàn 5%</h1></div>
<div class="kpi-grid"><div class="kpi"><div class="num"><?=vnd($summary['total_gmv']??0)?></div><div class="lbl">Tổng GMV</div></div>
<div class="kpi"><div class="num"><?=vnd($summary['total_fee']??0)?></div><div class="lbl">Phí sàn thu</div></div>
<div class="kpi"><div class="num"><?=vnd($summary['total_net']??0)?></div><div class="lbl">Shop nhận</div></div>
<div class="kpi"><div class="num"><?=$summary['earn_count']??0?></div><div class="lbl">Giao dịch</div></div></div>
<div class="panel"><div class="sec-head"><div class="title"><span class="bar"></span><h2>Chi tiết theo đối tác</h2></div></div>
<table class="tbl"><thead><tr><th>Shop</th><th>GD</th><th>GMV</th><th>Phí 5%</th><th>Shop nhận</th></tr></thead><tbody>
<?php foreach($byPartner as $p):?><tr><td><?=e($p['shop_name'])?></td><td><?=$p['txn']?></td><td><?=vnd($p['gmv'])?></td><td><?=vnd($p['fee'])?></td><td><?=vnd($p['net'])?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>