<?php $title = 'Phụ tùng theo hãng xe'; require __DIR__ . '/../partials/head.php'; ?>
<section class="block"><div class="wrap">
  <div class="sec-card"><div class="sec-head"><div class="title"><span class="bar"></span><h2>Phụ tùng theo hãng xe</h2></div></div>
    <div class="brands-grid"><?php foreach ($brands as $b): ?>
      <a href="/products?brand_id=<?= $b['id'] ?>" class="brand-card <?= !empty($b['image']) ? 'has-image' : '' ?>">
        <?php if (!empty($b['image'])): ?>
          <div class="brand-img-wrap"><img src="/uploads/brands/<?= e($b['image']) ?>" alt="<?= e($b['name']) ?>"></div>
        <?php endif; ?>
        <div class="brand-info">
          <div class="name"><?= e($b['name']) ?></div>
          <div class="count"><?= $b['real_count'] ?? $b['product_count'] ?? 0 ?> sản phẩm</div>
        </div>
      </a>
    <?php endforeach; ?></div>
  </div>
</div></section>
<?php require __DIR__ . '/../partials/foot.php'; ?>