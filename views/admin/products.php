<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
  <h1 style="margin:0">Quản lý sản phẩm</h1>
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <div style="position:relative;display:inline-block" id="exportDropdown">
      <button type="button" onclick="toggleExportMenu()" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        Xuất CSV ▾
      </button>
      <div id="exportMenu" style="display:none;position:absolute;top:100%;right:0;background:#fff;border:1px solid #e0e0e0;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:100;min-width:200px;margin-top:4px;overflow:hidden">
        <div style="position:relative;display:inline-block" class="csv-dropdown-wrap">
      <button class="btn btn-navy btn-sm" onclick="this.parentElement.classList.toggle('open')" style="display:flex;align-items:center;gap:4px">↓ XUẤT CSV <span style="font-size:10px">▼</span></button>
      <div class="csv-dropdown-menu">
        <a href="/admin/products/export-csv" class="csv-dropdown-item">Xuất tất cả</a>
        <a href="#" onclick="exportSelected('products','/admin/products/export-csv');return false" class="csv-dropdown-item">Xuất đã chọn</a>
      </div>
    </div>
        <a href="#" onclick="exportSelected();return false" style="display:block;padding:10px 16px;color:#333;text-decoration:none;font-size:13px" onmouseover="this.style.background='#f5f7fa'" onmouseout="this.style.background='#fff'">☑️ Xuất SP đã chọn</a>
      </div>
    </div>
    <button type="button" onclick="document.getElementById('csvImportModal').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
      Nhập CSV
    </button>
    <a href="/admin/products/new" class="btn btn-gold btn-sm">+ Đăng SP mới</a>
  </div>
</div>
<!-- Import CSV Modal -->
<div id="csvImportModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:12px;padding:28px;max-width:600px;width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
      <h3 style="margin:0;color:var(--navy)">📥 Nhập sản phẩm từ file dữ liệu</h3>
      <button onclick="document.getElementById('csvImportModal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#999">&times;</button>
    </div>
    <div style="background:#f0f4ff;border:1px solid #d0d8f0;border-radius:8px;padding:14px;margin-bottom:16px;font-size:12px;line-height:1.6;color:#555">
      <b>📋 Định dạng CSV:</b> Hàng đầu tiên là tiêu đề. Mã hóa UTF-8.<br>
      <a href="/admin/products/export-csv?template=1" style="color:var(--navy);font-weight:600">⬇ Tải file mẫu CSV</a>
    </div>
    <form method="post" action="/admin/products/import-csv" enctype="multipart/form-data">
      <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
      
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div>
          <label style="display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:8px">Xử lý trùng mã SKU?</label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:6px;cursor:pointer">
            <input type="radio" name="dup_sku" value="skip" checked> Báo lỗi và dừng import
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="radio" name="dup_sku" value="update"> Cập nhật SP cũ bằng dữ liệu mới
          </label>
        </div>
        <div>
          <label style="display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:8px">Trạng thái mặc định?</label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:6px;cursor:pointer">
            <input type="radio" name="default_status" value="draft" checked> Bản nháp
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="radio" name="default_status" value="published"> Xuất bản
          </label>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div>
          <label style="display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:8px">Cập nhật tồn kho?</label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:6px;cursor:pointer">
            <input type="radio" name="update_stock" value="no" checked> Không
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="radio" name="update_stock" value="yes"> Có
          </label>
        </div>
        <div>
          <label style="display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:8px">Cập nhật giá?</label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:6px;cursor:pointer">
            <input type="radio" name="update_price" value="no" checked> Không
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="radio" name="update_price" value="yes"> Có
          </label>
        </div>
      </div>

      <div style="margin-bottom:16px">
        <label style="display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:6px">Chọn file CSV</label>
        <input type="file" name="csv_file" accept=".csv" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-gold" style="flex:1">📤 Nhập sản phẩm</button>
        <button type="button" onclick="document.getElementById('csvImportModal').style.display='none'" class="btn btn-outline-navy" style="flex:1">Hủy</button>
      </div>
    </form>
  </div>
</div>
<form method="get" action="/admin/products" style="background:#fff;padding:16px 20px;border-radius:12px;border:1px solid #eaeaea;box-shadow:0 2px 8px rgba(0,0,0,0.02);margin-bottom:20px;display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end">
  <div style="flex:1;min-width:200px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">TÌM KIẾM</label>
    <input type="text" name="q" class="frm-input" value="<?= e($_GET['q']??'') ?>" placeholder="Tên SP, SKU, OEM..." style="width:100%;height:38px;border-radius:6px">
  </div>
  <div style="min-width:160px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">DANH MỤC</label>
    <select name="cat" class="frm-input" style="height:38px;border-radius:6px;width:100%">
      <option value="">Tất cả danh mục</option>
      <?php foreach($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= ($_GET['cat']??'')==$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div style="min-width:140px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">TRẠNG THÁI</label>
    <select name="tab" class="frm-input" style="height:38px;border-radius:6px;width:100%">
      <option value="all" <?= ($_GET['tab']??'')==='all'?'selected':'' ?>>Tất cả</option>
      <option value="published" <?= ($_GET['tab']??'')==='published'?'selected':'' ?>>Đã xuất bản</option>
      <option value="draft" <?= ($_GET['tab']??'')==='draft'?'selected':'' ?>>Bản nháp</option>
    </select>
  </div>
  <div style="min-width:140px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">HÃNG XE</label>
    <select name="brand_id" style="width:100%;height:38px;padding:0 10px;border:1px solid #d0d5e0;border-radius:6px;font-size:13px">
      <option value="">Tất cả hãng xe</option>
      <?php if(!empty($carBrands)): foreach($carBrands as $cb): ?>
        <option value="<?= $cb['id'] ?>" <?= ($filterBrandId??0)==$cb['id']?'selected':'' ?>><?= e($cb['name']) ?></option>
      <?php endforeach; endif; ?>
    </select>
  </div>
  <div style="min-width:140px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">THƯƠNG HIỆU SP</label>
    <select name="part_brand" style="width:100%;height:38px;padding:0 10px;border:1px solid #d0d5e0;border-radius:6px;font-size:13px">
      <option value="">Tất cả thương hiệu</option>
      <?php if(!empty($partBrands)): foreach($partBrands as $pb): ?>
        <option value="<?= e($pb['part_brand']) ?>" <?= ($filterPartBrand??'')==$pb['part_brand']?'selected':'' ?>><?= e($pb['part_brand']) ?></option>
      <?php endforeach; endif; ?>
    </select>
  </div>
  <div style="display:flex;gap:8px">
    <button type="submit" class="btn btn-navy" style="height:38px;border-radius:6px;padding:0 20px;display:flex;align-items:center;gap:6px">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      Lọc
    </button>
    <a href="/admin/products" class="btn btn-outline-navy" style="height:38px;border-radius:6px;padding:0 16px;display:flex;align-items:center">Đặt lại</a>
  </div>
  <div style="font-size:13px;color:#666;align-self:center;margin-left:auto;font-weight:500">Tìm thấy: <strong class="text-navy"><?= $total ?? 0 ?></strong> sản phẩm</div>
</form>
<div class="panel">
  <div class="sec-head" style="border:none"><div class="sec-tabs">
  <?php foreach(['all'=>'Tất cả','draft'=>'Bản nháp','published'=>'Đã xuất bản'] as $k=>$v):?>
  <a href="?tab=<?=$k?>" class="<?=$tab===$k?'active':''?>"><?=$v?></a><?php endforeach;?></div></div>
<table class="tbl"><thead><tr>
  <th style="width:36px"><input type="checkbox" id="checkAll" onchange="toggleAllCheckboxes(this)"></th>
        <th>SKU</th>
  <th>Tên SP</th>
  <th>Giá bán (sau VAT)</th>
  <th>Kho</th>
  <th>Trạng thái</th>
  <th style="text-align:center;min-width:220px">Thao tác</th>
</tr></thead><tbody>
<?php foreach($products as $p):?><tr>
  <td><input type="checkbox" class="row-check" value="<?=$p['id']?>"></td>
  <td class="fs-12"><?=e($p['sku']??'—')?></td>
  <td><a href="/admin/products/<?=$p['id']?>/edit" class="text-navy"><?=truncate(e($p['name']),40)?></a></td>
  <td><?=vnd($p['price'])?></td>
  <td><?=$p['stock']?></td>
  <td><span class="badge-status <?=e($p['status'])?>"><?= $p['status']==='published' ? 'Xuất bản' : ($p['status']==='draft' ? 'Bản nháp' : e($p['status'])) ?></span></td>
  <td>
    <div style="display:flex;gap:6px;justify-content:center;align-items:center">
      <!-- Sửa -->
      <a href="/admin/products/<?=$p['id']?>/edit"
         style="display:inline-flex;align-items:center;height:30px;padding:0 14px;border-radius:6px;font-size:12px;font-weight:700;background:#1a3258;color:#fff;text-decoration:none;border:none;cursor:pointer;white-space:nowrap">
        Sửa
      </a>
      <!-- Ngừng / Cho phép KD -->
      <?php if($p['status'] === 'published'): ?>
        <form method="post" action="/admin/products/<?=$p['id']?>/toggle-status" style="margin:0">
          <?= csrfField() ?>
          <input type="hidden" name="status" value="hidden">
          <button type="submit"
            style="display:inline-flex;align-items:center;height:30px;padding:0 14px;border-radius:6px;font-size:12px;font-weight:700;background:#fff3e0;color:#c0621f;border:1.5px solid #e8985c;cursor:pointer;white-space:nowrap">
            Ngừng KD
          </button>
        </form>
      <?php else: ?>
        <form method="post" action="/admin/products/<?=$p['id']?>/toggle-status" style="margin:0">
          <?= csrfField() ?>
          <input type="hidden" name="status" value="published">
          <button type="submit"
            style="display:inline-flex;align-items:center;height:30px;padding:0 14px;border-radius:6px;font-size:12px;font-weight:700;background:#e8f8f5;color:#1a8c5b;border:1.5px solid #7ddaba;cursor:pointer;white-space:nowrap">
            Cho phép KD
          </button>
        </form>
      <?php endif; ?>
      <!-- Xóa -->
      <form method="post" action="/admin/products/<?=$p['id']?>/delete" style="margin:0"
            onsubmit="return confirm('Xóa sản phẩm này? Hành động không thể hoàn tác.')">
        <?= csrfField() ?>
        <button type="submit"
          style="display:inline-flex;align-items:center;height:30px;padding:0 14px;border-radius:6px;font-size:12px;font-weight:700;background:#fdecea;color:#c0392b;border:1.5px solid #e9a0a0;cursor:pointer;white-space:nowrap">
          Xóa
        </button>
      </form>
    </div>
  </td>
</tr><?php endforeach;?></tbody></table>

<?php if (!empty($totalPages) && $totalPages > 1): ?>
<div style="display:flex;justify-content:center;gap:8px;margin-top:20px;padding:16px 0;border-top:1px solid #f0f0f0;">
    <?php for($i=1;$i<=$totalPages;$i++): ?>
        <?php $qs=$_GET; $qs['page']=$i; $qString=http_build_query($qs); ?>
        <a href="?<?= $qString ?>" class="btn <?= $i==$page?'btn-navy':'btn-outline-navy' ?> btn-sm"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
</div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>

<script>

function toggleExportMenu() {
  var menu = document.getElementById('exportMenu');
  menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
function exportSelected() {
  var checked = document.querySelectorAll('.row-check:checked');
  if (checked.length === 0) { alert('Vui lòng chọn ít nhất 1 sản phẩm để xuất'); return; }
  var ids = Array.from(checked).map(function(c){ return c.value; }).join(',');
  window.location.href = '/admin/products/export-csv?ids=' + ids;
}
// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
  var dd = document.getElementById('exportDropdown');
  if (dd && !dd.contains(e.target)) {
    document.getElementById('exportMenu').style.display = 'none';
  }
});

function toggleAllCheckboxes(master) {
  document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = master.checked; });
}
</script>
