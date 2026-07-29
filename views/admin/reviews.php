<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>

<div class="dash-head dash-head--framed" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
  <div>
    <h1 class="h1--framed" style="margin:0; padding:0; border:none; background:none; box-shadow:none;">Kiểm Duyệt Đánh Giá Sản Phẩm</h1>
    <p style="margin:4px 0 0; color:#718096; font-size:13px">Tiếp nhận, kiểm duyệt, ẩn hoặc xóa các phản hồi đánh giá sản phẩm từ khách hàng.</p>
  </div>
</div>

<?php $fl = getFlash(); if (!empty($fl)): foreach($fl as $f): ?>
  <div style="background:<?= $f['type']==='error'?'#fef2f2':'#dcfce7' ?>; color:<?= $f['type']==='error'?'#991b1b':'#15803d' ?>; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:700; font-size:14px">
    <?= e($f['message']) ?>
  </div>
<?php endforeach; endif; ?>

<!-- Bộ lọc tìm kiếm đánh giá -->
<div style="background:#fff; border-radius:12px; border:1px solid #cbd5e1; padding:16px 20px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
  <form method="get" action="/admin/reviews" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
    <div style="display:flex; align-items:center; gap:8px;">
      <label style="font-size:13px; font-weight:700; color:#0b1d3a;">Số sao:</label>
      <select name="rating" onchange="this.form.submit()" style="height:38px; border-radius:8px; border:1px solid #cbd5e1; padding:0 12px; font-size:13px; font-weight:600; color:#0b1d3a;">
        <option value="0">— Tất cả số sao —</option>
        <?php for ($s = 5; $s >= 1; $s--): ?>
          <option value="<?= $s ?>" <?= ($rating ?? 0) == $s ? 'selected' : '' ?>><?= $s ?> sao ⭐</option>
        <?php endfor; ?>
      </select>
    </div>

    <div style="display:flex; align-items:center; gap:8px;">
      <label style="font-size:13px; font-weight:700; color:#0b1d3a;">Danh mục:</label>
      <select name="category_id" onchange="this.form.submit()" style="height:38px; border-radius:8px; border:1px solid #cbd5e1; padding:0 12px; font-size:13px; font-weight:600; color:#0b1d3a;">
        <option value="0">— Tất cả danh mục —</option>
        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= ($categoryId ?? 0) == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; endif; ?>
      </select>
    </div>

    <?php if (!empty($rating) || !empty($categoryId)): ?>
      <a href="/admin/reviews" style="font-size:13px; color:#ef4444; font-weight:600; text-decoration:none;">✕ Xóa bộ lọc</a>
    <?php endif; ?>
  </form>
</div>

<!-- Bảng danh sách đánh giá -->
<div style="background:#fff; border-radius:12px; border:1px solid #cbd5e1; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.03);">
  <div class="tbl-scroll" style="overflow-x:auto;">
    <table class="tbl" style="width:100%; border-collapse:collapse; font-size:13.5px; text-align:left;">
      <thead>
        <tr style="background:#0b1d3a; color:#fff;">
          <th style="padding:14px 16px; width:60px;">Mã</th>
          <th style="padding:14px 16px;">Sản Phẩm</th>
          <th style="padding:14px 16px;">Khách Hàng</th>
          <th style="padding:14px 16px; text-align:center;">Đánh Giá</th>
          <th style="padding:14px 16px;">Nội Dung Đánh Giá</th>
          <th style="padding:14px 16px;">Hình Ảnh</th>
          <th style="padding:14px 16px; text-align:center;">Trạng Thái</th>
          <th style="padding:14px 16px;">Ngày Đăng</th>
          <th style="padding:14px 16px; text-align:center;">Thao Tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($reviews)): ?>
          <?php $__rvSt = ['published'=>'Đã duyệt', 'approved'=>'Đã duyệt', 'active'=>'Đã duyệt', 'visible'=>'Hiển thị', 'pending'=>'Chờ duyệt', 'hidden'=>'Đã ẩn', 'rejected'=>'Từ chối', 'draft'=>'Nháp']; ?>
          <?php foreach ($reviews as $r): ?>
            <?php
              $reviewImages = [];
              if (!empty($r['image'])) {
                $reviewImages = array_filter(array_map('trim', explode(',', $r['image'])));
              }
              $dbImages = dbAll("SELECT file_path FROM review_images WHERE review_id=?", [$r['id']]);
              foreach ($dbImages as $di) {
                if (!empty($di['file_path'])) $reviewImages[] = $di['file_path'];
              }
              $reviewImages = array_unique($reviewImages);
              $isPublished = in_array($r['status'], ['published', 'approved', 'active', 'visible'], true);
            ?>
            <tr style="border-bottom:1px solid #e2e8f0;">
              <td style="padding:12px 16px; font-weight:700; color:#0b1d3a;">#<?= $r['id'] ?></td>
              <td style="padding:12px 16px; max-width:200px;">
                <div style="font-weight:700; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= e($r['product_name']) ?>">
                  <?= e($r['product_name']) ?>
                </div>
              </td>
              <td style="padding:12px 16px; font-weight:600; color:#334155;"><?= e($r['full_name']) ?></td>
              <td style="padding:12px 16px; text-align:center; color:#d97706; font-weight:700; white-space:nowrap;">
                <?= stars($r['rating_overall'] ?? $r['rating'] ?? 0) ?>
              </td>
              <td style="padding:12px 16px; color:#334155; max-width:240px; line-height:1.4;">
                <?= e($r['comment'] ?? '—') ?>
              </td>
              <td style="padding:12px 16px;">
                <?php if (!empty($reviewImages)): ?>
                  <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <?php foreach ($reviewImages as $img): ?>
                      <img src="/uploads/reviews/<?= e($img) ?>" 
                           style="width:42px; height:42px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1; cursor:pointer;" 
                           onclick="window.open(this.src,'_blank')" 
                           onerror="this.style.display='none'" 
                           title="Bấm để phóng to">
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <span style="color:#94a3b8; font-size:12px;">Không có</span>
                <?php endif; ?>
              </td>
              <td style="padding:12px 16px; text-align:center;">
                <?php if ($isPublished): ?>
                  <span style="background:#dcfce7; color:#15803d; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:12px; display:inline-block;">
                    Đã Duyệt
                  </span>
                <?php else: ?>
                  <span style="background:#fef3c7; color:#b45309; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:12px; display:inline-block;">
                    Đã Ẩn / Chờ
                  </span>
                <?php endif; ?>
              </td>
              <td style="padding:12px 16px; font-size:12px; color:#64748b; white-space:nowrap;">
                <?= agoVN($r['created_at']) ?>
              </td>
              <td style="padding:12px 16px; text-align:center; white-space:nowrap;">
                <div style="display:inline-flex; gap:6px; align-items:center;">
                  <form method="post" action="/admin/reviews/<?= $r['id'] ?>/status" style="margin:0;">
                    <?= csrfField() ?>
                    <input type="hidden" name="status" value="<?= $isPublished ? 'hidden' : 'published' ?>">
                    <button type="submit" class="btn" style="background:<?= $isPublished ? '#f59e0b' : '#16a34a' ?>; color:#fff; border:none; padding:5px 10px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer;">
                      <?= $isPublished ? 'Ẩn Đánh Giá' : 'Duyệt Đánh Giá' ?>
                    </button>
                  </form>
                  <form method="post" action="/admin/reviews/<?= $r['id'] ?>/delete" style="margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này không?')">
                    <?= csrfField() ?>
                    <button type="submit" class="btn" style="background:#dc2626; color:#fff; border:none; padding:5px 10px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer;">
                      Xóa
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" style="padding:36px; text-align:center; color:#64748b; font-style:italic;">
              Chưa có đánh giá nào phù hợp với bộ lọc.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (($totalPages ?? 1) > 1): ?>
  <div style="margin-top:20px;">
    <?php require_once __DIR__ . '/../partials/pagination.php'; renderPagination($page, $totalPages, '/admin/reviews', ['rating'=>$_GET['rating']??'','category_id'=>$_GET['category_id']??'']); ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
