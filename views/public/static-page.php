<?php $title = $page['title'] ?? 'Trang'; require __DIR__ . '/../partials/head.php'; ?>
<section class="block" style="padding:40px 0">
  <div class="wrap">
    <div class="sec-card">
      <div class="sec-head"><div class="title"><span class="bar"></span><h2><?= e($page['title']) ?></h2></div></div>
      <div class="content-body" style="padding:16px 0;line-height:1.8;font-size:15px;color:#333">
        <?= $page['content'] ?? $page['body'] ?? '' ?>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/foot.php'; ?>
