<?php require __DIR__ . '/../partials/head.php'; ?>
<section class="block"><div class="wrap">
  <div class="sec-card">
    <div class="sec-head"><div class="title"><span class="bar"></span><h2><?= e($partner['shop_name']) ?></h2></div></div>
    <div class="panel-body">
      <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px">
        <div><span class="text-muted fs-12">Loại hình:</span> <strong><?= e($partner['type'] ?? 'Cá nhân') ?></strong></div>
        <div><span class="text-muted fs-12">Sản phẩm:</span> <strong><?= count($products) ?></strong></div>
        <div><span class="text-muted fs-12">Tham gia:</span> <strong><?= date('m/Y', strtotime($pUser['created_at'] ?? 'now')) ?></strong></div>
      </div>
    </div>
    <div class="sec-head"><div class="title"><h3>Sản phẩm của shop</h3></div></div>
    <?php if(empty($products)):?><div class="empty-state"><h3>Chưa có sản phẩm</h3></div>
    <?php else:?><div class="prod-grid"><?php foreach($products as $p):?><?php require __DIR__.'/partials/prod-card.php';?><?php endforeach;?></div><?php endif;?>
  </div>
</div></section>
<?php require __DIR__ . '/../partials/foot.php'; ?>
