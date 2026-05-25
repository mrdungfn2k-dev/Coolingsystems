<?php require __DIR__.'/../partials/head.php'; ?>
<section class="block"><div class="wrap"><div class="sec-card">
  <div class="sec-head"><div class="title"><span class="bar"></span><h2>Garage của tôi</h2></div><span class="fs-12 text-muted"><?=count($garages)?>/<?=$max?> xe</span></div>
  <?php if(empty($garages)):?><div class="empty-state"><h3>Chưa có xe nào trong garage</h3><p>Thêm xe để dễ dàng tìm phụ tùng tương thích.</p></div>
  <?php else:?><div class="garage-grid"><?php foreach($garages as $g):?>
    <div class="garage-card <?=$g['is_default']?'active':''?>"><div class="name"><?=e($g['brand_name'].' '.$g['model_name'])?> <?=e($g['year'])?></div>
      <?php if($g['label']):?><div class="fs-12 text-muted"><?=e($g['label'])?></div><?php endif;?>
      <a href="/products?brand_id=<?=$g['brand_id']?>&model_id=<?=$g['model_id']?>" class="btn btn-outline-navy btn-sm" style="margin-top:8px">Lọc phụ tùng →</a>
    </div><?php endforeach;?></div><?php endif;?>
</div></div></section>
<?php require __DIR__.'/../partials/foot.php'; ?>