<?php $title = $title ?? 'Sản phẩm'; require __DIR__ . '/../partials/head.php'; ?>
<div class="container"><div class="breadcrumb"><a href="/">Trang chủ</a><span class="sep">›</span><span>Sản phẩm</span>
  <?php if (!empty($_GET['q'])): ?><span class="sep">›</span><span>Tìm: "<?= e($_GET['q']) ?>"</span><?php endif; ?>
</div></div>
<section class="block" style="padding-top:0"><div class="wrap">
  <div style="display:grid;grid-template-columns:240px 1fr;gap:20px">
    <aside>
      <div class="cat-sidebar"><div class="head"><span class="lines"><span></span><span></span><span></span></span><span>Danh mục</span></div>
        <ul><?php foreach ($categories as $c): ?><li class="<?= ($_GET['cat'] ?? '') === $c['slug'] ? 'featured' : '' ?>"><a href="/products?cat=<?= e($c['slug']) ?>"><span><?= e($c['name']) ?></span><span class="arr">›</span></a></li><?php endforeach; ?></ul>
      </div>
    </aside>
    <div><div class="sec-card">
      <div class="sec-head">
        <div class="title"><span class="bar"></span><h2>Tìm thấy <?= numFmt($total) ?> sản phẩm</h2></div>
        <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
          <span style="font-size:12px;color:var(--ink-3)">Sắp xếp:</span>
          <select onchange="window.location.href=this.value" style="padding:6px 10px;border:1px solid var(--line);border-radius:3px;font-size:12px">
            <?php foreach (['newest'=>'Mới nhất','bestseller'=>'Bán chạy','price_asc'=>'Giá thấp','price_desc'=>'Giá cao','rating'=>'Đánh giá'] as $k=>$v): ?>
              <?php $q=$_GET; $q['sort']=$k; unset($q['page']); ?><option value="?<?= http_build_query($q) ?>" <?= ($_GET['sort'] ?? 'newest')===$k?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <?php if (empty($products)): ?>
        <div class="empty-state"><div class="em-icon">∅</div><h3>Chưa có phụ tùng cho lựa chọn này</h3><a href="/products" class="btn btn-outline-navy">Xem tất cả SP</a></div>
      <?php else: ?>
        <div class="prod-grid cols-4"><?php foreach ($products as $p): ?><?php require __DIR__ . '/partials/prod-card.php'; ?><?php endforeach; ?></div>
      <?php endif; ?>
      <?php if ($totalPages > 1): ?>
        <div class="pagination"><?php for ($i=1; $i<=$totalPages; $i++): ?><?php $q=$_GET; $q['page']=$i; ?><a href="?<?= http_build_query($q) ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a><?php endfor; ?></div>
      <?php endif; ?>
    </div></div>
  </div>
</div></section>
<?php require __DIR__ . '/../partials/foot.php'; ?>
