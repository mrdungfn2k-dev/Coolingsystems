<?php require __DIR__.'/../partials/head.php'; ?>
<section class="block"><div class="wrap"><div class="sec-card">
  <div class="sec-head"><div class="title"><span class="bar"></span><h2>Sản phẩm yêu thích</h2></div></div>
  <?php if(empty($items)):?><div class="empty-state"><h3>Chưa có sản phẩm yêu thích</h3></div>
  <?php else:?><div class="prod-grid"><?php foreach($items as $p):?><?php require __DIR__.'/../public/partials/prod-card.php';?><?php endforeach;?></div><?php endif;?>
</div></div></section>
<?php require __DIR__.'/../partials/foot.php'; ?>