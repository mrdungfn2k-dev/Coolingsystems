<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
/* ─────────────────────────────────────────────────
   ADMIN: Quản lý Hãng xe (Brands & Car Models)
   ───────────────────────────────────────────────── */

$brands = dbAll("SELECT b.*, 
  (SELECT COUNT(*) FROM car_models m WHERE m.brand_id = b.id) AS model_count, 
  (SELECT COUNT(DISTINCT p.id) FROM products p 
   LEFT JOIN product_fitments pf ON pf.product_id=p.id 
   LEFT JOIN product_brand_map pbm ON pbm.product_id=p.id 
   WHERE (p.car_brand_id=b.id OR pf.brand_id=b.id OR pbm.brand_id=b.id OR p.name LIKE '%' || b.name || '%')
  ) AS product_count 
  FROM brands b ORDER BY b.sort_order, b.name");
$activeBrand = null;
$models = [];
if (!empty($_GET['brand_id'])) {
    $activeBrand = dbGet("SELECT * FROM brands WHERE id=?", [intval($_GET['brand_id'])]);
    if ($activeBrand) {
        $models = dbAll("SELECT * FROM car_models WHERE brand_id=? ORDER BY name, year_from", [$activeBrand['id']]);
    }
}
?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <h1 style="margin:0">Quản lý Hãng xe</h1>
    <div style="display:flex;gap:8px;align-items:center">
        <a href="#" onclick="csColPick({section:'brands',url:'/admin/brands/export-csv',title:'Hãng xe & dòng xe'});return false" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
        <button type="button" onclick="document.getElementById('csvImportBrands').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↑ Nhập CSV</button>
        <button class="btn btn-navy btn-sm" onclick="openBrandModal()">+ Thêm hãng</button>
    </div>
</div>

<style>
.catalog-wrap { display:flex; gap:24px; }
.catalog-panel { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.06); overflow:hidden; }
.panel-header { padding:16px 20px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; }
.panel-header h3 { margin:0; font-size:15px; font-weight:700; color:var(--navy); }
.panel-list { min-height:300px; max-height:calc(100vh - 200px); overflow-y:auto; }
.panel-item { display:flex; align-items:center; justify-content:space-between; padding:12px 20px; border-bottom:1px solid #f5f5f5; transition:background 0.15s; }
.panel-item:hover { background:#f8f9fa; }
.panel-item.active { background:#eff6ff; }
.panel-item-name { font-size:14px; font-weight:600; color:#333; }
.panel-item-sub { font-size:11px; color:#888; margin-top:2px; }
.panel-item-actions { display:flex; gap:6px; }
.btn-icon { border:none; background:none; cursor:pointer; padding:4px 6px; border-radius:4px; font-size:12px; color:#666; transition:all 0.15s; }
.btn-icon:hover { background:#e2e8f0; color:#333; }
.btn-icon.red:hover { background:#fee2e2; color:#dc2626; }
.empty-state { padding:40px; text-align:center; color:#aaa; font-size:13px; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.show { display:flex; }
.modal-box { background:#fff; border-radius:14px; padding:28px; width:420px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-box h3 { margin:0 0 20px; font-size:16px; font-weight:700; color:var(--navy); }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:12px; font-weight:700; color:#555; margin-bottom:6px; text-transform:uppercase; }
.form-group input, .form-group select { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:6px; font-size:14px; box-sizing:border-box; }
.form-group input:focus, .form-group select:focus { border-color:var(--navy); outline:none; }
.modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }

.add-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:var(--navy); color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; }
.add-btn:hover { opacity:0.9; }
.add-btn.secondary { background:#fff; color:var(--navy); border:1px solid var(--navy); }
</style>

<?php $flash = getFlash(); foreach($flash as $f): ?>
<div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>



<div class="catalog-wrap">
    <!-- Left: Brands list -->
    <div class="catalog-panel" style="flex:0 0 320px">
        <div class="panel-header">
            <h3>Hãng xe (<?= count($brands) ?>)</h3>
            <label style="font-size:11px;display:flex;align-items:center;gap:4px;cursor:pointer"><input type="checkbox" onchange="document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked)"> Chọn tất cả</label>
        </div>
        <div class="panel-list">
            <?php if (empty($brands)): ?>
                <div class="empty-state">Chưa có hãng xe nào</div>
            <?php else: ?>
            <?php foreach ($brands as $b): ?>
            <div class="panel-item <?= ($activeBrand && $activeBrand['id'] == $b['id']) ? 'active' : '' ?>">
                <input type="checkbox" class="row-check" value="<?=$b['id']?>" style="margin-right:8px;flex-shrink:0" onclick="event.stopPropagation()">
                <a href="/admin/brands?brand_id=<?= $b['id'] ?>" onclick="return loadBrandModels(<?= $b['id'] ?>, this, event)" style="text-decoration:none;flex:1">
                    <div class="panel-item-name">
                        <?php if($b['image']): ?>
                            <img src="/uploads/brands/<?= e($b['image']) ?>" style="height:20px; width:40px; vertical-align:middle; margin-right:8px; object-fit:contain;">
                        <?php endif; ?>
                        <?= e($b['name']) ?>
                    </div>
                    <div class="panel-item-sub"><?= $b['model_count'] ?> dòng xe · <?= $b['product_count'] ?> sản phẩm</div>
                </a>
                <div class="panel-item-actions">
                    <button class="adm-edit" onclick="openBrandEditModal(<?= $b['id'] ?>, '<?= e($b['name']) ?>', '<?= e($b['slug']) ?>', <?= $b['sort_order'] ?>, '<?= e($b['image']??'') ?>')" title="Sửa">Sửa</button>
                    <form method="post" action="/admin/brands/<?= $b['id'] ?>/delete" style="margin:0" onsubmit="return csConfirmForm(this,'Xóa hãng xe này?')">
                        <?= csrfField() ?>
                        <button type="submit" class="adm-del" title="Xóa">Xóa</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Car models of selected brand -->
    <div class="catalog-panel" style="flex:1" id="brandModelsPanel">
        <?php if ($activeBrand): ?>
        <div class="panel-header">
            <h3>Dòng xe – <?= e($activeBrand['name']) ?></h3>
            <button class="add-btn" onclick="openModelModal(<?= $activeBrand['id'] ?>, '<?= e($activeBrand['name']) ?>')">+ Thêm dòng xe</button>
        </div>
        <div class="panel-list">
            <?php if (empty($models)): ?>
                <div class="empty-state">Chưa có dòng xe nào trong hãng này</div>
            <?php else: ?>
            <table class="tbl" style="width:100%">
                <thead>
                    <tr>
                        <th style="padding:12px 20px;text-align:left">Tên dòng xe</th>
                        <th style="padding:12px;text-align:left">Slug</th>
                        <th style="padding:12px;text-align:center">Năm bắt đầu</th>
                        <th style="padding:12px;text-align:center">Năm kết thúc</th>
                        <th style="padding:12px;text-align:center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($models as $m): ?>
                <tr style="border-bottom:1px solid #f5f5f5">
                    <td style="padding:12px 20px;font-weight:600"><?= e($m['name']) ?></td>
                    <td style="padding:12px;font-size:12px;color:#888"><?= e($m['slug']) ?></td>
                    <td style="padding:12px;text-align:center"><?= $m['year_from'] ?></td>
                    <td style="padding:12px;text-align:center"><?= $m['year_to'] ?: '—' ?></td>
                    <td style="padding:12px;text-align:center">
                        <div style="display:flex;gap:6px;justify-content:center">
                            <button class="adm-edit" onclick="openModelEditModal(<?= $m['id'] ?>, <?= $activeBrand['id'] ?>, '<?= e($m['name']) ?>', '<?= e($m['slug']) ?>', <?= $m['year_from'] ?>, <?= $m['year_to'] ?: 'null' ?>)">Sửa</button>
                            <form method="post" action="/admin/car-models/<?= $m['id'] ?>/delete" style="margin:0" onsubmit="return csConfirmForm(this,'Xóa dòng xe này?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="brand_id" value="<?= $activeBrand['id'] ?>">
                                <button type="submit" class="adm-del">Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding:80px">
            Chọn một hãng xe bên trái để xem và quản lý dòng xe
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Thêm / Sửa Hãng xe -->
<div class="modal-overlay" id="brandModal">
    <div class="modal-box">
        <h3 id="brandModalTitle">Thêm hãng xe</h3>
        <form method="post" id="brandForm" action="/admin/brands/add" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="brand_id" id="brandId" value="">
            <div class="form-group">
                <label>Tên hãng xe *</label>
                <input type="text" name="name" id="brandName" required placeholder="VD: Honda, Toyota...">
            </div>
            <div class="form-group">
                <label>Slug (URL) *</label>
                <input type="text" name="slug" id="brandSlug" required placeholder="honda">
            </div>
            <div class="form-group">
                <label>Thứ tự hiển thị</label>
                <input type="number" name="sort_order" id="brandSort" value="100">
            </div>
            <div class="form-group">
                <label>Hình ảnh (Logo hãng xe)</label>
                <input type="file" name="image" accept="image/*">
                <div id="brandImgPreview" style="margin-top:8px;"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="add-btn secondary" onclick="closeBrandModal()">Hủy</button>
                <button type="submit" class="add-btn">Lưu</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Thêm / Sửa Dòng xe -->
<div class="modal-overlay" id="modelModal">
    <div class="modal-box">
        <h3 id="modelModalTitle">Thêm dòng xe</h3>
        <form method="post" id="modelForm" action="/admin/car-models/add">
            <?= csrfField() ?>
            <input type="hidden" name="model_id" id="modelId" value="">
            <input type="hidden" name="brand_id" id="modelBrandId" value="">
            <div class="form-group">
                <label>Tên dòng xe *</label>
                <input type="text" name="name" id="modelName" required placeholder="VD: Civic, Camry...">
            </div>
            <div class="form-group">
                <label>Slug (URL) *</label>
                <input type="text" name="slug" id="modelSlug" required placeholder="civic">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>Năm bắt đầu *</label>
                    <input type="number" name="year_from" id="modelYearFrom" required placeholder="2010" min="1990" max="2030">
                </div>
                <div class="form-group">
                    <label>Năm kết thúc</label>
                    <input type="number" name="year_to" id="modelYearTo" placeholder="(để trống nếu còn SX)" min="1990" max="2030">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="add-btn secondary" onclick="closeModelModal()">Hủy</button>
                <button type="submit" class="add-btn">Lưu</button>
            </div>
        </form>
    </div>
</div>

<script>
function slugify(s) { return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); }
document.getElementById('brandName').addEventListener('input', function() {
    const sl = document.getElementById('brandSlug');
    if (!sl.dataset.manual) sl.value = slugify(this.value);
});
document.getElementById('brandSlug').addEventListener('input', function() { this.dataset.manual = '1'; });

document.getElementById('modelName').addEventListener('input', function() {
    const sl = document.getElementById('modelSlug');
    if (!sl.dataset.manual) sl.value = slugify(this.value);
});
document.getElementById('modelSlug').addEventListener('input', function() { this.dataset.manual = '1'; });

function openBrandModal() {
    document.getElementById('brandModalTitle').textContent = 'Thêm hãng xe';
    document.getElementById('brandForm').action = '/admin/brands/add';
    document.getElementById('brandId').value = '';
    document.getElementById('brandName').value = '';
    document.getElementById('brandSlug').value = '';
    document.getElementById('brandSlug').removeAttribute('data-manual');
    document.getElementById('brandSort').value = '100';
    document.getElementById('brandImgPreview').innerHTML = '';
    document.getElementById('brandModal').classList.add('show');
}
function openBrandEditModal(id, name, slug, sort, image) {
    document.getElementById('brandModalTitle').textContent = 'Sửa hãng xe';
    document.getElementById('brandForm').action = '/admin/brands/'+id+'/edit';
    document.getElementById('brandId').value = id;
    document.getElementById('brandName').value = name;
    document.getElementById('brandSlug').value = slug;
    document.getElementById('brandSlug').dataset.manual = '1';
    document.getElementById('brandSort').value = sort;
    const preview = document.getElementById('brandImgPreview');
    if(image) {
        preview.innerHTML = `<img src="/uploads/brands/${image}" style="height:40px;object-fit:contain;border-radius:4px;border:1px solid #ddd;padding:4px;background:#fff;">`;
    } else {
        preview.innerHTML = '';
    }
    document.getElementById('brandModal').classList.add('show');
}
function closeBrandModal() { document.getElementById('brandModal').classList.remove('show'); }

function openModelModal(brandId, brandName) {
    document.getElementById('modelModalTitle').textContent = 'Thêm dòng xe – ' + brandName;
    document.getElementById('modelForm').action = '/admin/car-models/add';
    document.getElementById('modelId').value = '';
    document.getElementById('modelBrandId').value = brandId;
    document.getElementById('modelName').value = '';
    document.getElementById('modelSlug').value = '';
    document.getElementById('modelSlug').removeAttribute('data-manual');
    document.getElementById('modelYearFrom').value = '';
    document.getElementById('modelYearTo').value = '';
    document.getElementById('modelModal').classList.add('show');
}
function openModelEditModal(id, brandId, name, slug, yearFrom, yearTo) {
    document.getElementById('modelModalTitle').textContent = 'Sửa dòng xe';
    document.getElementById('modelForm').action = '/admin/car-models/'+id+'/edit';
    document.getElementById('modelId').value = id;
    document.getElementById('modelBrandId').value = brandId;
    document.getElementById('modelName').value = name;
    document.getElementById('modelSlug').value = slug;
    document.getElementById('modelSlug').dataset.manual = '1';
    document.getElementById('modelYearFrom').value = yearFrom;
    document.getElementById('modelYearTo').value = yearTo == 'null' ? '' : yearTo;
    document.getElementById('modelModal').classList.add('show');
}
function closeModelModal() { document.getElementById('modelModal').classList.remove('show'); }
</script>

<script>
function loadBrandModels(id, aEl, e){
  if(e) e.preventDefault();
  document.querySelectorAll('.panel-item.active').forEach(function(x){x.classList.remove('active');});
  var item=aEl.closest('.panel-item'); if(item) item.classList.add('active');
  var panel=document.getElementById('brandModelsPanel'); if(panel) panel.style.opacity='0.5';
  fetch('/admin/brands?brand_id='+id, {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.text();})
    .then(function(html){
      var doc=new DOMParser().parseFromString(html,'text/html');
      var np=doc.getElementById('brandModelsPanel');
      if(np && panel){ panel.innerHTML=np.innerHTML; panel.style.opacity='1'; }
      try{ history.replaceState(null,'','/admin/brands?brand_id='+id); }catch(_){}
    }).catch(function(){ if(panel) panel.style.opacity='1'; });
  return false;
}
</script>

<div id="csvImportBrands" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
<div style="background:#fff;border-radius:12px;padding:28px;max-width:500px;width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px"><h3 style="margin:0;color:var(--navy)"> Nhập hãng xe từ CSV</h3><button onclick="document.getElementById('csvImportBrands').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer">&times;</button></div>
<form method="post" action="/admin/brands/import-csv" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
<div style="margin-bottom:12px;font-size:12px;color:#666;background:#f0f4ff;padding:10px;border-radius:6px">Cột: name, slug, country, sort_order</div>
<div class="csv-dropzone" id="dz-brands">
  <input type="file" name="csv_file" accept=".csv" required>
  <span class="dz-icon">📂</span>
  <div class="dz-text">Kéo thả file CSV vào đây hoặc nhấn để chọn file</div>
  <div class="dz-subtext">Chỉ chấp nhận file <strong>.csv</strong> — Không hỗ trợ Word (.docx), Excel (.xlsx), PDF...</div>
  <div class="dz-filename"></div>
  <div class="dz-error"></div>
</div>
<button type="submit" class="btn btn-gold" style="width:100%"> Nhập</button><script>
(function() {
  var dz = document.getElementById('dz-brands');
  var inp = dz ? dz.querySelector('input[type="file"]') : null;
  if (!dz || !inp) return;

  var fnEl = dz.querySelector('.dz-filename');
  var errEl = dz.querySelector('.dz-error');
  var txtEl = dz.querySelector('.dz-text');

  function validateFile(file) {
    if (!file) return false;
    var name = file.name.toLowerCase();
    var ext = name.split('.').pop();
    var allowedMimes = ['text/csv','text/plain','application/csv','application/vnd.ms-excel'];
    var mimeOk = allowedMimes.indexOf(file.type) > -1 || file.type === '' || file.type.indexOf('text') === 0;
    return ext === 'csv' && (mimeOk || file.type === '');
  }

  function showOk(file) {
    dz.classList.remove('drag-over','drag-reject');
    dz.classList.add('file-ok');
    if (fnEl) { fnEl.style.display='block'; fnEl.textContent = '✅ ' + file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)'; }
    if (errEl) errEl.style.display='none';
    if (txtEl) txtEl.style.opacity='0.5';
  }

  function showError(msg) {
    dz.classList.remove('drag-over','file-ok');
    dz.classList.add('drag-reject');
    if (errEl) { errEl.style.display='block'; errEl.textContent = msg; }
    if (fnEl) fnEl.style.display='none';
    if (txtEl) txtEl.style.opacity='1';
    inp.value = '';
    setTimeout(function(){ dz.classList.remove('drag-reject'); }, 1500);
  }

  // Sự kiện chọn file qua click
  inp.addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    if (validateFile(file)) {
      showOk(file);
    } else {
      showError('❌ Chỉ chấp nhận file CSV! File "' + file.name + '" không hợp lệ.');
    }
  });

  // Drag events
  dz.addEventListener('dragover', function(e) {
    e.preventDefault(); e.stopPropagation();
    var items = e.dataTransfer.items;
    var hasInvalid = false;
    for (var i=0; i<items.length; i++) {
      if (items[i].kind === 'file') {
        var t = items[i].type;
        if (t && t.indexOf('text') < 0 && t !== 'application/vnd.ms-excel' && t !== '') hasInvalid = true;
      }
    }
    dz.classList.toggle('drag-reject', hasInvalid);
    dz.classList.toggle('drag-over', !hasInvalid);
  });

  dz.addEventListener('dragleave', function(e) {
    e.preventDefault();
    dz.classList.remove('drag-over','drag-reject');
  });

  dz.addEventListener('drop', function(e) {
    e.preventDefault(); e.stopPropagation();
    dz.classList.remove('drag-over','drag-reject');
    var files = e.dataTransfer.files;
    if (!files || files.length === 0) return;
    var file = files[0];
    if (files.length > 1) {
      showError('❌ Chỉ được chọn 1 file CSV tại một thời điểm.');
      return;
    }
    if (!validateFile(file)) {
      var ext = file.name.split('.').pop().toLowerCase();
      showError('❌ Không chấp nhận file .' + ext + '! Chỉ được import file CSV.');
      return;
    }
    // Gán file vào input
    var dt = new DataTransfer();
    dt.items.add(file);
    inp.files = dt.files;
    showOk(file);
  });
})();
</script>
</form></div></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
