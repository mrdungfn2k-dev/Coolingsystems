<?php require __DIR__ . '/../partials/head.php'; ?>
<style>

.full-article .rich-content { font-size: 16px !important; }
.rich-content img { max-width: 100% !important; height: auto !important; border-radius: 8px; margin: 16px auto; display: inline-block; vertical-align: middle; }
.rich-content p[style*="text-align: center"], .rich-content div[style*="text-align: center"],
.rich-content p[style*="text-align:center"], .rich-content div[style*="text-align:center"] { text-align: center !important; }
.rich-content p[style*="text-align: center"] img, .rich-content div[style*="text-align: center"] img,
.rich-content p[style*="text-align:center"] img, .rich-content div[style*="text-align:center"] img { display: inline-block !important; margin-left: auto !important; margin-right: auto !important; }
.rich-content p[style*="text-align: right"], .rich-content div[style*="text-align: right"],
.rich-content p[style*="text-align:right"], .rich-content div[style*="text-align:right"] { text-align: right !important; }
.rich-content p[style*="text-align: right"] img, .rich-content div[style*="text-align: right"] img,
.rich-content p[style*="text-align:right"] img, .rich-content div[style*="text-align:right"] img { display: inline-block !important; }

/* Rich content embedded links styling */
.rich-content a {
  color: #1d4ed8 !important;
  text-decoration: underline !important;
  font-weight: 600 !important;
  word-break: break-word;
  transition: color 0.15s ease, background 0.15s ease;
}
.rich-content a:hover {
  color: #0b1d3a !important;
  text-decoration: underline !important;
  background: #eff6ff;
  border-radius: 2px;
}
@media(max-width:768px) {
  
}
</style>
<section class="block full-article"><div class="wrap">
  <div>
    <div style="padding:0">
      <div style="font-size:13px;color:var(--ink-4);margin-bottom:10px;display:flex;align-items:center;gap:6px">
        <span>📅 <?= $article['published_at'] ? date('d/m/Y H:i', strtotime($article['published_at'])) : '' ?></span>
        &nbsp;·&nbsp; <a href="/news" style="color:var(--navy);font-weight:600">← Tin tức</a>
      </div>
      <h1 style="font-size:28px;font-weight:800;color:var(--navy-dark);line-height:1.35;margin:0 0 16px"><?= e($article['title']) ?></h1>
      
      <?php if ($article['thumbnail']): ?>
        <div style="margin:0 0 24px;border-radius:12px;overflow:hidden;box-shadow:0 4px 14px rgba(0,0,0,0.06)">
          <img src="/uploads/news/<?= e($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" style="width:100%;max-height:480px;object-fit:cover;display:block">
        </div>
      <?php endif; ?>

      <?php if ($article['excerpt']): ?>
        <p style="font-size:16px;color:var(--ink-2);line-height:1.75;border-left:4px solid #1d4ed8;background:#f8fafc;padding:12px 16px;border-radius:0 8px 8px 0;margin-bottom:24px;font-style:italic"><?= e($article['excerpt']) ?></p>
      <?php endif; ?>
      <div class="rich-content" style="font-size:16px;line-height:1.9;color:var(--ink-1)">
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
