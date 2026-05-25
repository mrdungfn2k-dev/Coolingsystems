<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Ví của tôi</h1></div>
<div class="kpi-grid"><div class="kpi"><div class="num"><?=vnd($wallet['balance_available'])?></div><div class="lbl">Khả dụng</div></div>
<div class="kpi"><div class="num"><?=vnd($wallet['balance_pending']??0)?></div><div class="lbl">Đang chờ</div></div>
<div class="kpi"><div class="num"><?=vnd($wallet['total_earned'])?></div><div class="lbl">Tổng thu</div></div>
<div class="kpi"><div class="num"><?=vnd($wallet['total_withdrawn'])?></div><div class="lbl">Đã rút</div></div></div>
<div class="panel"><div class="sec-head"><div class="title"><span class="bar"></span><h2>Lịch sử giao dịch</h2></div></div>
<table class="tbl"><thead><tr><th>Loại</th><th>Số tiền</th><th>Mô tả</th><th>Ngày</th></tr></thead><tbody>
<?php foreach($txns as $t):?><tr><td><?=$t['type']==='credit'?'<span style="color:green">+</span>':'<span style="color:red">-</span>'?> <?=e($t['type'])?></td><td><?=vnd($t['amount'])?></td><td class="fs-12"><?=e($t['description']??'')?></td><td class="fs-12"><?=relTime($t['created_at'])?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>