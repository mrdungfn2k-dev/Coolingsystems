<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <h1>Quản lý Tin tức</h1>
  <a href="/admin/news/new" class="btn btn-gold btn-sm">+ Viết bài mới</a>
</div>
<div class="panel">
  <table class="tbl">
    <thead><tr><th>Tiêu đề</th><th>Trạng thái</th><th>Ngày đăng</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($articles as $a): ?>
    <tr>
      <td><a href="/admin/news/<?= $a['id'] ?>/edit" class="text-navy fw-600"><?= e($a['title']) ?></a></td>
      <td><span class="badge-status <?= e($a['status']) ?>"><?= $a['status']==='published'?'Đã đăng':'Nháp' ?></span></td>
      <td class="fs-12"><?= $a['published_at'] ? date('d/m/Y H:i', strtotime($a['published_at'])) : '—' ?></td>
      <td style="display:flex;gap:6px">
        <a href="/news/<?= e($a['slug']) ?>" target="_blank" class="btn btn-outline-navy btn-sm"> Xem</a>
        <a href="/admin/news/<?= $a['id'] ?>/edit" class="btn btn-gold btn-sm"> Sửa</a>
        <form method="post" action="/admin/news/<?= $a['id'] ?>/delete" onsubmit="return confirm('Xóa bài viết này?')">
          <?= csrfField() ?>
          <button class="btn btn-danger btn-sm"> Xóa</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$articles): ?><tr><td colspan="4" class="text-center text-muted" style="padding:40px">Chưa có bài viết nào</td></tr><?php endif; ?>
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
