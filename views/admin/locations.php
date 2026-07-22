<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Sơ đồ & Vị trí kho (Kệ / Tầng / Khay)</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Định nghĩa vị trí lưu trữ phụ tùng giúp nhân viên kho định vị và lấy hàng nhanh chóng.</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <button type="button" onclick="document.getElementById('importLocModal').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↑ Nhập CSV</button>
    <a href="/admin/locations/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
    <button onclick="document.getElementById('newLocationModal').style.display='flex'" class="btn btn-navy">+ Thêm vị trí mới</button>
  </div>
</div>

<div id="importLocModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/locations/import-csv" enctype="multipart/form-data" style="background:#fff;padding:24px;border-radius:10px;max-width:450px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 14px;color:#1a3258">Nhập Vị trí kho từ CSV</h3>
    <div style="margin-bottom:14px;font-size:12px;color:#64748b">
      Định dạng CSV: <code>Location_Code, Area_Name, Shelf_Name, Bin_Name, Note</code>
    </div>
    <div style="margin-bottom:18px">
      <input type="file" name="csv_file" accept=".csv" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('importLocModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Tải lên & Nhập</button>
    </div>
  </form>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.loc-table{width:100%;border-collapse:collapse;background:#fff}
.loc-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.loc-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.loc-badge{background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:4px;font-family:monospace;font-weight:700}
</style>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="loc-table">
  <thead>
    <tr>
      <th>Mã vị trí</th>
      <th>Khu vực / Kệ</th>
      <th>Tầng</th>
      <th>Khay / Ô</th>
      <th>Số phụ tùng đang xếp</th>
      <th>Ghi chú</th>
      <th>Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($locations as $loc): ?>
    <tr>
      <td><span class="loc-badge"><?= e($loc['code'] ?? '') ?></span></td>
      <td style="font-weight:700;color:#1a3258"><?= e($loc['area_name'] ?? '') ?></td>
      <td><?= e($loc['shelf_name'] ?: '—') ?></td>
      <td><?= e($loc['bin_name'] ?: '—') ?></td>
      <td style="font-weight:700;color:#0284c7"><?= number_format((int)($loc['product_count'] ?? 0)) ?> mã SP</td>
      <td style="font-size:12px;color:#64748b"><?= e($loc['note'] ?: '—') ?></td>
      <td>
        <form method="post" action="/admin/locations/<?= (int)$loc['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn xóa vị trí này?')">
          <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
          <button type="submit" class="btn btn-outline" style="padding:3px 8px;font-size:11px;color:#dc2626">Xóa</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$locations): ?>
    <tr><td colspan="7" style="padding:30px;text-align:center;color:#9ca3af">Chưa có vị trí kho nào. Bấm nút "+ Thêm vị trí mới" để tạo.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if(isset($totalPages) && $totalPages > 1): ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/locations?page=<?= $page-1 ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/locations?page=<?= $page+1 ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<div id="newLocationModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/locations" style="background:#fff;padding:24px;border-radius:10px;max-width:450px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 16px;color:#1a3258">Thêm vị trí kho mới</h3>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Mã vị trí (VD: KE-A-T1-K05) <span style="color:#e11d48">*</span></label>
      <input type="text" name="code" required placeholder="Mã viết liền không dấu..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Khu vực / Kệ <span style="color:#e11d48">*</span></label>
      <input type="text" name="area_name" required placeholder="Ví dụ: Kệ A, Kệ B, Khu Vực 1..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
      <div>
        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Tầng</label>
        <input type="text" name="shelf_name" placeholder="Tầng 1, Tầng 2..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Khay / Ô</label>
        <input type="text" name="bin_name" placeholder="Khay 05, Ô 12..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
      </div>
    </div>

    <div style="margin-bottom:20px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Ghi chú</label>
      <input type="text" name="note" placeholder="Mô tả loại phụ tùng lưu ở đây..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('newLocationModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Lưu vị trí</button>
    </div>
  </form>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
