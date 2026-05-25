<?php $title = $page['title'] ?? 'Trang'; require __DIR__ . '/../partials/head.php'; ?>
<section class="block" style="padding:24px 0 40px">
  <div class="wrap">
    <div class="sec-head" style="margin-bottom:20px"><div class="title"><span class="bar"></span><h2><?= e($page['title']) ?></h2></div></div>
    <div class="static-content" style="line-height:1.8;font-size:15px;color:var(--ink-1)">
      <?= $page['content'] ?? $page['body'] ?? '' ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/foot.php'; ?>
