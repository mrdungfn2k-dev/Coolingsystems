<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Yêu cầu rút tiền</h1></div>
<div class="panel"><table class="tbl"><thead><tr><th>Shop</th><th>Số tiền</th><th>Ngân hàng</th><th>STK</th><th>TT</th><th>Ngày</th></tr></thead><tbody>
<?php foreach($items as $w):?><tr><td><?=e($w['shop_name'])?></td><td><?=vnd($w['amount'])?></td><td class="fs-12"><?=e($w['bank_name']??'')?></td><td class="fs-12"><?=e($w['bank_account_number']??'')?></td><td><span class="badge-status <?=e($w['status'])?>"><?=e($w['status'])?></span></td><td class="fs-12"><?=relTime($w['created_at'])?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>