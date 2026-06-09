<?php require __DIR__ . '/../partials/head.php'; ?>
<style>

.full-article .rich-content { font-size: 16px !important; }
@media(max-width:768px) {
  
}
</style>
<section class="block full-article"><div class="wrap">
  <div>
    <?php if ($article['thumbnail']): ?>
      <img src="/uploads/news/<?= e($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" style="width:100%;height:auto;border-radius:8px 8px 0 0;display:block">
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

<script>
// Auto Table of Contents
(function() {
  var body = document.querySelector('.rich-content');
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
