<?php require __DIR__ . '/../partials/head.php'; ?>
<style>
.full-article { padding: 28px 0 48px; }
.full-article .wrap { max-width: 100% !important; padding: 0 40px !important; }
.full-article .sec-head { margin-bottom: 20px; }
.full-article .article-body { font-size: 16px; line-height: 1.9; color: #333; }
.full-article .article-body img { max-width:100%; height:auto; border-radius:8px; margin:16px 0; }
.full-article .article-body h1 { font-size:30px; color:var(--navy-dark); margin:28px 0 14px; font-weight:900; }
.full-article .article-body h2 { font-size:24px; color:var(--navy); margin:24px 0 12px; font-weight:800; }
.full-article .article-body h3 { font-size:20px; color:var(--navy); margin:20px 0 10px; font-weight:700; }
.full-article .article-body h4 { font-size:17px; color:var(--ink-1); margin:18px 0 8px; font-weight:700; }
.full-article .article-body h5 { font-size:15px; color:var(--ink-2); margin:14px 0 6px; font-weight:700; }
.full-article .article-body h6 { font-size:14px; color:var(--ink-3); margin:12px 0 6px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
.full-article .article-body p { margin:10px 0; }
.full-article .article-body ul, .full-article .article-body ol { margin:10px 0; padding-left:28px; }
.full-article .article-body li { margin:5px 0; }
.full-article .article-body blockquote { border-left:4px solid var(--gold-warm); padding:14px 20px; margin:20px 0; background:#faf8f3; border-radius:0 8px 8px 0; font-style:italic; color:var(--ink-2); }
.full-article .article-body table { width:100%; border-collapse:collapse; margin:20px 0; }
.full-article .article-body th, .full-article .article-body td { padding:12px 16px; border:1px solid var(--line); text-align:left; font-size:15px; }
.full-article .article-body th { background:var(--navy); color:#fff; font-weight:700; }
@media(max-width:768px) {
  .full-article .wrap { padding: 0 16px !important; }
  .full-article .article-body { font-size: 15px; }
  .full-article .article-body h1 { font-size:24px; }
  .full-article .article-body h2 { font-size:20px; }
  .full-article .article-body h3 { font-size:17px; }
}
</style>
<section class="block full-article"><div class="wrap">
  <div class="sec-head"><div class="title"><span class="bar"></span><h2><?= e($title) ?></h2></div></div>
  <div class="article-body">
    <?php
      $slug = $page ?? '';
      $staticRow = dbGet("SELECT content FROM static_pages WHERE slug=?", [$slug]);
      if ($staticRow && !empty(trim($staticRow['content']))): ?>
        <?= $staticRow['content'] ?>
    <?php else: ?>
      <p>Nội dung trang <strong><?= e($title) ?></strong> đang được cập nhật. Vui lòng quay lại sau.</p>
      <p>Mọi thắc mắc xin liên hệ hotline: <strong><?= $sysHotline ?? '0705070526' ?></strong></p>
    <?php endif; ?>
  </div>
</div></section>

<script>
// Auto Table of Contents
(function() {
  var body = document.querySelector('.article-body');
  if (!body) return;
  var headings = body.querySelectorAll('h2, h3, h4');
  if (headings.length < 3) return; // Only show TOC for 3+ headings
  
  // Build TOC
  var tocHtml = '<div class="auto-toc" style="background:#f8f9fc;border:1px solid #e2e5ea;border-radius:8px;padding:18px 24px;margin-bottom:24px">';
  tocHtml += '<div style="font-weight:800;font-size:15px;color:#0b1d3a;margin-bottom:10px;display:flex;align-items:center;gap:8px;cursor:pointer" onclick="this.parentElement.classList.toggle('toc-collapsed')">';
  tocHtml += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>';
  tocHtml += 'Mục lục bài viết <span style="font-size:11px;color:#999;font-weight:400">(' + headings.length + ' mục)</span></div>';
  tocHtml += '<ol class="toc-list" style="margin:0;padding-left:20px;font-size:14px;line-height:2">';
  
  headings.forEach(function(h, i) {
    var id = 'toc-' + i;
    h.id = id;
    var indent = h.tagName === 'H3' ? 'padding-left:16px' : (h.tagName === 'H4' ? 'padding-left:32px' : '');
    var weight = h.tagName === 'H2' ? 'font-weight:600' : 'font-weight:400';
    tocHtml += '<li style="' + indent + ';' + weight + '"><a href="#' + id + '" style="color:#0b1d3a;text-decoration:none" onmouseover="this.style.color='#c9a14a'" onmouseout="this.style.color='#0b1d3a'">' + h.textContent + '</a></li>';
  });
  
  tocHtml += '</ol></div>';
  tocHtml += '<style>.toc-collapsed .toc-list{display:none}</style>';
  
  // Insert TOC before first heading
  headings[0].insertAdjacentHTML('beforebegin', tocHtml);
})();
</script>

<?php require __DIR__ . '/../partials/foot.php'; ?>
