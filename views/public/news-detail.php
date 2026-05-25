<?php require __DIR__ . '/../partials/head.php'; ?>
<style>
.full-article .wrap { max-width: 100% !important; padding: 0 40px !important; }
.full-article .rich-content { font-size: 16px !important; }
@media(max-width:768px) {
  .full-article .wrap { padding: 0 16px !important; }
}
</style>
<section class="block full-article"><div class="wrap">
  <div>
    <?php if ($article['thumbnail']): ?>
      <img src="/uploads/news/<?= e($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" style="width:100%;height:320px;object-fit:cover;border-radius:8px 8px 0 0">
    <?php endif; ?>
    <div style="padding:0">
      <div style="font-size:12px;color:var(--ink-4);margin-bottom:10px">
         <?= $article['published_at'] ? date('d/m/Y H:i', strtotime($article['published_at'])) : '' ?>
        &nbsp;·&nbsp; <a href="/news" style="color:var(--navy)">← Tin tức</a>
      </div>
      <h1 style="font-size:26px;font-weight:800;color:var(--navy-dark);line-height:1.3;margin:0 0 16px"><?= e($article['title']) ?></h1>
      <?php if ($article['excerpt']): ?>
        <p style="font-size:16px;color:var(--ink-2);line-height:1.7;border-left:3px solid var(--navy);padding-left:14px;margin-bottom:24px;font-style:italic"><?= e($article['excerpt']) ?></p>
      <?php endif; ?>
      <div class="rich-content" style="font-size:15px;line-height:1.9;color:var(--ink-1)">
        <?= $article['content'] ?>
      </div>
    </div>
  </div>
  <!-- Bài viết liên quan -->
  <?php if ($related): ?>
  <div class="sec-head" style="margin-top:32px"><div class="title"><span class="bar"></span><h2 style="font-size:18px">Bài viết liên quan</h2></div></div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;margin-top:12px">
    <?php foreach ($related as $r): ?>
    <a href="/news/<?= e($r['slug']) ?>" style="text-decoration:none;color:inherit;background:#fff;border-radius:8px;padding:12px;box-shadow:0 1px 6px rgba(0,0,0,0.07)">
      <div style="font-size:12px;color:var(--ink-4);margin-bottom:4px"><?= $r['published_at'] ? date('d/m/Y', strtotime($r['published_at'])) : '' ?></div>
      <div style="font-size:13px;font-weight:600;color:var(--navy-dark);line-height:1.4"><?= e($r['title']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></section>
<?php require __DIR__ . '/../partials/foot.php'; ?>
