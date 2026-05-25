<?php require __DIR__.'/../partials/head.php'; ?>
<section class="block"><div class="wrap"><div class="sec-card">
  <div class="sec-head"><div class="title"><span class="bar"></span><h2>Voucher của tôi</h2></div></div>
  <?php if(empty($vouchers)):?><div class="empty-state"><h3>Chưa có voucher</h3></div>
  <?php else:?><div class="voucher-grid"><?php foreach($vouchers as $v):?>
    <div class="voucher-card"><div class="code"><?=e($v['code'])?></div><div class="desc"><?=e($v['description']??'')?></div><div class="exp fs-12 text-muted">HSD: <?=date('d/m/Y',strtotime($v['end_date']))?></div></div>
  <?php endforeach;?></div><?php endif;?>
</div></div></section>
<?php require __DIR__.'/../partials/foot.php'; ?>