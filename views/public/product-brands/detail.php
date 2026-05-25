<?php require __DIR__.'/../../partials/head.php'; ?>
<section class="block">
<div class="wrap">
  <nav class="breadcrumb"><a href="/">Trang ch&#x1EE7;</a><span class="sep">›</span><a href="/product-brands">Th&#x01B0;&#x01A1;ng hi&#x1EC7;u</a><span class="sep">›</span><span><?= e($brand['name']) ?></span></nav>
  <div class="sec-head" style="display:flex;align-items:center;gap:16px">
    <?php if(!empty($brand['logo'])): ?>
      <img src="/uploads/product-brands/<?= e($brand['logo']) ?>" style="height:50px;object-fit:contain">
    <?php endif; ?>
    <div class="title"><span class="bar"></span><h1 style="font-size:22px"><?= e($brand['name']) ?></h1></div>
  </div>
  <?php if(!empty($brand['description'])): ?>
    <p style="color:#555;font-size:14px;margin:8px 0 20px"><?= e($brand['description']) ?></p>
  <?php endif; ?>

  <?php if(empty($products)): ?>
    <div style="text-align:center;padding:60px 0;color:#888">
      <div style="font-size:40px;margin-bottom:12px"></div>
      <p>Ch&#x01B0;a c&#xF3; s&#x1EA3;n ph&#x1EA9;m n&#xE0;o c&#x1EE7;a th&#x01B0;&#x01A1;ng hi&#x1EC7;u n&#xE0;y.</p>
    </div>
  <?php else: ?>
  <div class="prod-grid" style="margin-top:16px">
    <?php foreach($products as $p): require __DIR__.'/../../public/partials/prod-card.php'; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
</section>
<?php require __DIR__.'/../../partials/foot.php'; ?>
