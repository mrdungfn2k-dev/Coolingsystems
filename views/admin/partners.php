<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Quản lý đối tác</h1></div>
<div class="panel"><div class="sec-head" style="border:none"><div class="sec-tabs">
  <?php foreach(['all'=>'Tất cả','pending'=>'Chờ duyệt','active'=>'Hoạt động','rejected'=>'Từ chối'] as $k=>$v):?>
  <a href="?status=<?=$k?>" class="<?=$filterStatus===$k?'active':''?>"><?=$v?></a><?php endforeach;?></div></div>
<table class="tbl"><thead><tr><th>Shop</th><th>Email</th><th>Loại</th><th>TT</th><th>Ngày</th></tr></thead><tbody>
<?php foreach($partners as $p):?><tr><td><strong><?=e($p['shop_name'])?></strong></td><td class="fs-12"><?=e($p['email'])?></td><td class="fs-12"><?=e($p['type']??'')?></td><td><span class="badge-status <?=e($p['status'])?>"><?=e($p['status'])?></span></td><td class="fs-12"><?=relTime($p['created_at'])?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>