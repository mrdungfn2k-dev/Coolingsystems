<?php require __DIR__ . '/../partials/head.php'; ?>
<section class="block"><div class="wrap">
  <div class="sec-head"><div class="title"><span class="bar"></span><h1 style="font-size:22px">Tin tức Cooling</h1></div></div>
  <?php if ($articles): ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px;margin-top:16px">
    <?php foreach ($articles as $a): ?>
    <a href="/news/<?= e($a['slug']) ?>" style="text-decoration:none;color:inherit;display:block;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.07);transition:transform 0.2s,box-shadow 0.2s" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 6px 24px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.07)'">
      <?php if ($a['thumbnail']): ?>
        <img src="/uploads/news/<?= e($a['thumbnail']) ?>" alt="<?= e($a['title']) ?>" style="width:100%;height:180px;object-fit:cover">
      <?php else: ?>
        <div style="width:100%;height:180px;background:linear-gradient(135deg,#1a3258,#2c4a7c);display:flex;align-items:center;justify-content:center">
          <span style="font-size:36px"></span>
        </div>
      <?php endif; ?>
      <div style="padding:16px">
        <div style="font-size:11px;color:var(--ink-4);margin-bottom:6px"><?= $a['published_at'] ? date('d/m/Y', strtotime($a['published_at'])) : '' ?></div>
        <h2 style="font-size:15px;font-weight:700;color:var(--navy-dark);line-height:1.4;margin:0 0 8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= e($a['title']) ?></h2>
        <p style="font-size:13px;color:var(--ink-3);line-height:1.6;margin:0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden"><?= e($a['excerpt']) ?></p>
        <div style="margin-top:12px"><span style="color:var(--navy);font-size:12px;font-weight:700">Đọc tiếp →</span></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <!-- Phân trang -->
  <?php if ($totalPages > 1): ?>
  <div style="display:flex;justify-content:center;gap:6px;margin-top:32px">
    <?php
require_once __DIR__.'/../partials/pagination.php';
renderPagination($page, $totalPages, '/news', ['q' => $_GET['q'] ?? '', 'cat' => $_GET['cat'] ?? '']);
?>
  </div>
  <?php endif; ?>
  <?php else: ?>
    <p style="color:var(--ink-3);text-align:center;padding:40px">Chưa có bài viết nào. Quay lại sau nhé!</p>
  <?php endif; ?>
</div></section>
<?php require __DIR__ . '/../partials/foot.php'; ?>
