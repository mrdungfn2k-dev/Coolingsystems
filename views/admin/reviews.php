<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Kiểm duyệt đánh giá</h1></div>
<div class="panel"><table class="tbl"><thead><tr><th>SP</th><th>Khách</th><th>Sao</th><th>Nội dung</th><th>Hình ảnh</th><th>TT</th><th>Ngày</th></tr></thead><tbody>
<?php if(empty($reviews)): ?>
<tr><td colspan="7" style="text-align:center;padding:30px;color:#888">Chưa có đánh giá nào.</td></tr>
<?php else: ?>
<?php foreach($reviews as $r):?>
<?php
  $reviewImages = [];
  if (!empty($r['image'])) {
    $reviewImages = array_filter(array_map('trim', explode(',', $r['image'])));
  }
  // Also check review_images table
  $dbImages = dbAll("SELECT file_path FROM review_images WHERE review_id=?", [$r['id']]);
  foreach($dbImages as $di) {
    if (!empty($di['file_path'])) $reviewImages[] = $di['file_path'];
  }
  $reviewImages = array_unique($reviewImages);
?>
<tr>
<td><?=truncate(e($r['product_name']),30)?></td>
<td><?=e($r['full_name'])?></td>
<td style="color:var(--gold-warm)"><?=stars($r['rating_overall'] ?? $r['rating'] ?? 0)?></td>
<td class="fs-12"><?=truncate(e($r['comment']??''),50)?></td>
<td>
  <?php if(!empty($reviewImages)): ?>
    <div style="display:flex;gap:4px;flex-wrap:wrap">
    <?php foreach($reviewImages as $img): ?>
      <img src="/uploads/reviews/<?= e($img) ?>" 
           style="width:48px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #ddd;cursor:pointer" 
           onclick="window.open(this.src,'_blank')" 
           onerror="this.style.display='none'" 
           title="<?= e($img) ?>">
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <span style="color:#ccc;font-size:11px">—</span>
  <?php endif; ?>
</td>
<td><span class="badge-status <?=e($r['status'])?>"><?=e($r['status'])?></span></td>
<td class="fs-12"><?=relTime($r['created_at'])?></td>
</tr>
<?php endforeach;?>
<?php endif; ?>
</tbody></table></div>
<?php if (($totalPages ?? 1) > 1): ?>
<div style="margin-top:18px">
  <?php require_once __DIR__.'/../partials/pagination.php'; renderPagination($page, $totalPages, '/admin/reviews', ['rating'=>$_GET['rating']??'','category_id'=>$_GET['category_id']??'']); ?>
</div>
<?php endif; ?>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
