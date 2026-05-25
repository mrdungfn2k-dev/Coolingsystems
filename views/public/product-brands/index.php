<?php require __DIR__.'/../../partials/head.php'; ?>
<section class="block">
<div class="wrap">
  <nav class="breadcrumb"><a href="/">Trang ch&#x1EE7;</a><span class="sep">›</span><span>Th&#x01B0;&#x01A1;ng hi&#x1EC7;u</span></nav>
  <div class="sec-head"><div class="title"><span class="bar"></span><h1 style="font-size:22px">Th&#x01B0;&#x01A1;ng hi&#x1EC7;u s&#x1EA3;n ph&#x1EA9;m</h1></div></div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;margin-top:24px">
    <?php foreach($productBrands as $pb): ?>
    <a href="/product-brands/<?= e($pb['slug'] ?: $pb['id']) ?>" style="display:flex;flex-direction:column;align-items:center;gap:12px;padding:24px 16px;background:#fff;border:1px solid #e0e6f0;border-radius:10px;text-decoration:none;transition:all 0.2s;box-shadow:0 2px 8px rgba(0,0,0,0.04)" onmouseover="this.style.borderColor='var(--gold)';this.style.boxShadow='0 4px 16px rgba(0,0,0,0.1)'" onmouseout="this.style.borderColor='#e0e6f0';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.04)'">
      <div style="width:80px;height:60px;display:flex;align-items:center;justify-content:center">
        <?php if(!empty($pb['logo'])): ?>
          <img src="/uploads/product-brands/<?= e($pb['logo']) ?>" style="max-width:80px;max-height:60px;object-fit:contain">
        <?php else: ?>
          <div style="font-size:28px;font-weight:800;color:var(--navy);letter-spacing:-1px"><?= mb_substr(e($pb['name']),0,2) ?></div>
        <?php endif; ?>
      </div>
      <div style="font-size:13px;font-weight:700;color:var(--navy);text-align:center"><?= e($pb['name']) ?></div>
      <?php
        $cnt = dbGet("SELECT COUNT(*) as n FROM products WHERE (part_brand=? OR part_brand LIKE ? OR part_brand LIKE ? OR part_brand LIKE ?) AND status='published'", [$pb['name'], $pb['name'].',%', '%, '.$pb['name'].',%', '%, '.$pb['name']])['n'] ?? 0;
      ?>
      <div style="font-size:11px;color:#888"><?= $cnt ?> s&#x1EA3;n ph&#x1EA9;m</div>
    </a>
    <?php endforeach; ?>
    <?php if(empty($productBrands)): ?>
      <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#888">Ch&#x01B0;a c&#xF3; th&#x01B0;&#x01A1;ng hi&#x1EC7;u n&#xE0;o.</div>
    <?php endif; ?>
  </div>
</div>
</section>
<?php require __DIR__.'/../../partials/foot.php'; ?>
