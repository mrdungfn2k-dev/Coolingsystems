<?php require __DIR__ . '/../partials/head.php'; ?>
<section class="block" style="padding:24px 0 40px"><div class="wrap">
  <div class="sec-head" style="margin-bottom:20px"><div class="title"><span class="bar"></span><h2><?= e($title) ?></h2></div></div>
  <div class="static-content" style="font-size:15px;line-height:1.8;color:var(--ink-1)">
    <?php
      $slug = $page ?? '';
      $staticRow = dbGet("SELECT content FROM static_pages WHERE slug=?", [$slug]);
      if ($staticRow && !empty(trim($staticRow['content']))): ?>
        <?= $staticRow['content'] ?>
    <?php else: ?>
      <p>Nội dung trang <strong><?= e($title) ?></strong> đang được cập nhật. Vui lòng quay lại sau.</p>
      <p>Mọi thắc mắc xin liên hệ hotline: <strong><?= $sysHotline ?? '0947796471' ?></strong></p>
    <?php endif; ?>
  </div>
</div></section>
<style>
.static-content img { max-width:100%; height:auto; border-radius:8px; margin:12px 0; }
.static-content h1 { font-size:28px; color:var(--navy-dark); margin:24px 0 12px; font-weight:900; }
.static-content h2 { font-size:22px; color:var(--navy); margin:20px 0 10px; font-weight:800; }
.static-content h3 { font-size:18px; color:var(--navy); margin:18px 0 8px; font-weight:700; }
.static-content h4 { font-size:16px; color:var(--ink-1); margin:16px 0 8px; font-weight:700; }
.static-content h5 { font-size:14px; color:var(--ink-2); margin:14px 0 6px; font-weight:700; }
.static-content h6 { font-size:13px; color:var(--ink-3); margin:12px 0 6px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
.static-content p { margin:8px 0; }
.static-content ul, .static-content ol { margin:8px 0; padding-left:24px; }
.static-content li { margin:4px 0; }
.static-content blockquote { border-left:3px solid var(--gold-warm); padding:12px 16px; margin:16px 0; background:#faf8f3; border-radius:0 8px 8px 0; font-style:italic; color:var(--ink-2); }
.static-content table { width:100%; border-collapse:collapse; margin:16px 0; }
.static-content th, .static-content td { padding:10px 14px; border:1px solid var(--line); text-align:left; }
.static-content th { background:var(--navy); color:#fff; font-weight:700; }
@media(max-width:640px) {
  .static-content h1 { font-size:22px; }
  .static-content h2 { font-size:18px; }
  .static-content h3 { font-size:16px; }
}
</style>
<?php require __DIR__ . '/../partials/foot.php'; ?>
