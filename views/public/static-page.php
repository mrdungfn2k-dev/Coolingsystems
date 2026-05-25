<?php $title = $page['title'] ?? 'Trang'; require __DIR__ . '/../partials/head.php'; ?>
<style>
.full-article { padding: 28px 0 48px; }
.full-article .wrap { max-width: 100% !important; padding: 0 40px !important; }
.full-article .article-body { font-size: 16px; line-height: 1.9; color: #333; }
.full-article .article-body img { max-width:100%; height:auto; border-radius:8px; margin:16px 0; }
.full-article .article-body table { width:100%; border-collapse:collapse; margin:20px 0; }
.full-article .article-body th, .full-article .article-body td { padding:12px 16px; border:1px solid var(--line); text-align:left; }
.full-article .article-body th { background:var(--navy); color:#fff; font-weight:700; }
@media(max-width:768px) {
  .full-article .wrap { padding: 0 16px !important; }
  .full-article .article-body { font-size: 15px; }
}
</style>
<section class="block full-article">
  <div class="wrap">
    <div class="sec-head" style="margin-bottom:20px"><div class="title"><span class="bar"></span><h2><?= e($page['title']) ?></h2></div></div>
    <div class="article-body">
      <?= $page['content'] ?? $page['body'] ?? '' ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../partials/foot.php'; ?>
