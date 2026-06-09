<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <h1>Quản lý Tin tức</h1>
  <a href="/admin/news/new" class="btn btn-gold btn-sm">+ Viết bài mới</a>
</div>
<div class="panel">
  <table class="tbl">
    <thead><tr><th style="width:74px">Ảnh</th><th>Tiêu đề</th><th>Trạng thái</th><th>Ngày đăng</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($articles as $a): ?>
    <tr>
      <td>
        <?php if(!empty($a['thumbnail'])): ?>
          <img src="/uploads/news/<?= e($a['thumbnail']) ?>" alt="" style="width:62px;height:44px;object-fit:cover;border-radius:6px;border:1px solid var(--line);display:block">
        <?php else: ?>
          <div style="width:62px;height:44px;border-radius:6px;background:#f1f4f9;display:flex;align-items:center;justify-content:center;color:#aab4c4"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="M21 15l-5-5L5 21"></path></svg></div>
        <?php endif; ?>
      </td>
      <td><a href="/admin/news/<?= $a['id'] ?>/edit" class="text-navy fw-600"><?= e($a['title']) ?></a></td>
      <td><span class="badge-status <?= e($a['status']) ?>"><?= $a['status']==='published'?'Đã đăng':'Nháp' ?></span></td>
      <td class="fs-12"><?= $a['published_at'] ? date('d/m/Y H:i', strtotime($a['published_at'])) : '—' ?></td>
      <td style="display:flex;gap:6px;align-items:center">
        <a href="/news/<?= e($a['slug']) ?>" target="_blank" class="btn btn-outline-navy btn-sm"> Xem</a>
        <a href="/admin/news/<?= $a['id'] ?>/edit" class="btn btn-gold btn-sm"> Sửa</a>
        <form method="post" action="/admin/news/<?= $a['id'] ?>/delete" onsubmit="return csConfirmForm(this,'Xóa bài viết này?')" style="margin:0;display:flex;align-items:center">
          <?= csrfField() ?>
          <button class="btn btn-outline-navy btn-sm"> Xóa</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$articles): ?><tr><td colspan="5" class="text-center text-muted" style="padding:40px">Chưa có bài viết nào</td></tr><?php endif; ?>
    </tbody>
  </table>
  
  <?php if (!empty($totalPages) && $totalPages > 1): ?>
  <div style="display:flex; justify-content:center; gap:8px; margin-top:20px; padding:16px 0;">
      <?php
require_once __DIR__.'/../partials/pagination.php';
renderPagination($page, $totalPages, '/admin/news', ['q' => $_GET['q'] ?? '']);
?>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
