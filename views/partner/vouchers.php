<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Voucher Shop</h1><a href="/partner/vouchers/new" class="btn btn-gold">+ Tạo voucher</a></div>
<div class="panel"><table class="tbl"><thead><tr><th>Mã</th><th>Giảm</th><th>Đã dùng</th><th>Còn lại</th><th>HSD</th></tr></thead><tbody>
<?php foreach($vouchers as $v):?><tr><td><strong><?=e($v['code'])?></strong></td><td><?=$v['discount_type']==='percent'?$v['discount_value'].'%':vnd($v['discount_value'])?></td><td><?=$v['used_count']?></td><td><?=$v['max_uses']-$v['used_count']?></td><td class="fs-12"><?=date('d/m/Y',strtotime($v['end_date']))?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>