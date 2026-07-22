<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Phân khúc khách hàng (CRM Segments)</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Phân nhóm khách hàng theo dòng xe sở hữu, lịch sử mua hàng và hạn mức chi tiêu.</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <a href="/admin/crm/segments/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
    <button onclick="document.getElementById('newSegmentModal').style.display='flex'" class="btn btn-navy">+ Thêm phân khúc mới</button>
  </div>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.seg-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:20px}
.seg-card{background:#fff;padding:20px;border:1px solid #e6ebf1;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,0.02)}
.seg-card h3{margin:0 0 6px;color:#1a3258;font-size:16px}
.seg-card p{margin:0 0 14px;color:#64748b;font-size:13px}
.seg-count{font-size:24px;font-weight:800;color:#0284c7}
</style>

<div class="seg-grid">
  <?php foreach($segments as $seg): ?>
  <div class="seg-card">
    <h3><?= e($seg['name']) ?></h3>
    <p><?= e($seg['description'] ?: 'Không có mô tả') ?></p>
    <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid #edf2f7;padding-top:12px">
      <div>
        <span style="font-size:11px;color:#94a3b8;text-transform:uppercase;display:block">Số khách hàng</span>
        <div class="seg-count"><?= number_format((int)($seg['user_count'] ?? 0)) ?> KH</div>
      </div>
      <form method="post" action="/admin/crm/segments/<?= (int)$seg['id'] ?>/delete" onsubmit="return confirm('Bạn có chắc muốn xóa phân khúc này?')">
        <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
        <button type="submit" class="btn btn-outline" style="padding:4px 10px;font-size:11px;color:#dc2626">Xóa</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if(!$segments): ?>
  <div style="grid-column:1/-1;padding:40px;background:#fff;border:1px solid #e6ebf1;border-radius:10px;text-align:center;color:#94a3b8">
    Chưa có phân khúc khách hàng nào. Bấm nút "+ Thêm phân khúc mới" để tạo nhóm đầu tiên.
  </div>
  <?php endif; ?>
</div>

<?php if(isset($totalPages) && $totalPages > 1): ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/crm/segments?page=<?= $page-1 ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/crm/segments?page=<?= $page+1 ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<div id="newSegmentModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/crm/segments" style="background:#fff;padding:24px;border-radius:10px;max-width:450px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 16px;color:#1a3258">Tạo phân khúc khách hàng mới</h3>

    <div style="margin-bottom:14px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Tên phân khúc <span style="color:#e11d48">*</span></label>
      <input type="text" name="name" required placeholder="Ví dụ: Khách hàng đi xe Mazda, Khách VIP..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:20px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Mô tả tiêu chí phân loại</label>
      <textarea name="description" rows="3" placeholder="Mô tả nhóm khách hàng mục tiêu..." style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px;font-size:13px"></textarea>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('newSegmentModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Lưu phân khúc</button>
    </div>
  </form>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
