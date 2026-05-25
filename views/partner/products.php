<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Sản phẩm</h1><a href="/partner/products/new" class="btn btn-gold">+ Đăng SP mới</a></div>
<div class="panel"><table class="tbl"><thead><tr><th>Tên SP</th><th>Mã OEM</th><th>Giá</th><th>Kho</th><th>TT</th><th>Đã bán</th></tr></thead><tbody>
<?php foreach($products as $p):?><tr><td><?=truncate(e($p['name']),40)?></td><td class="fs-12"><?=e($p['oem_code']??'')?></td><td><?=vnd($p['price'])?></td><td><?=$p['stock']?></td><td><span class="badge-status <?=e($p['status'])?>"><?=e($p['status'])?></span></td><td><?=$p['sold_count']?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>