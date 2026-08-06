<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
/* ─────────────────────────────────────────────────
   ADMIN: Quản lý Danh mục (Categories)
   ───────────────────────────────────────────────── */

$parentCats = dbAll("SELECT c.*, (SELECT COUNT(*) FROM categories ch WHERE ch.parent_id=c.id) AS child_count, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS product_count FROM categories c WHERE c.parent_id IS NULL ORDER BY c.sort_order, c.name");
$activeParent = null;
$childCats = [];
if (!empty($_GET['parent_id'])) {
    $activeParent = dbGet("SELECT * FROM categories WHERE id=?", [intval($_GET['parent_id'])]);
    if ($activeParent) {
        $childCats = dbAll("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS product_count FROM categories c WHERE parent_id=? ORDER BY sort_order, name", [$activeParent['id']]);
    }
}
?>
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
.btn-icon { border:none; background:none; cursor:pointer; padding:4px 8px; border-radius:4px; font-size:12px; color:#666; transition:all 0.15s; }
.btn-icon:hover { background:#e2e8f0; color:#333; }
.btn-icon.red:hover { background:#fee2e2; color:#dc2626; }
.empty-state { padding:40px; text-align:center; color:#aaa; font-size:13px; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.show { display:flex; }
.modal-box { background:#fff; border-radius:14px; padding:28px; width:440px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-box h3 { margin:0 0 20px; font-size:16px; font-weight:700; color:var(--navy); }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:12px; font-weight:700; color:#555; margin-bottom:6px; text-transform:uppercase; }
.form-group input, .form-group select, .form-group textarea { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:6px; font-size:14px; box-sizing:border-box; }
.form-group input:focus, .form-group select:focus { border-color:var(--navy); outline:none; }
.modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
.add-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:var(--navy); color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; }
.add-btn:hover { opacity:0.9; }
.add-btn.secondary { background:#fff; color:var(--navy); border:1px solid var(--navy); }
.featured-badge { background:#fef3c7; color:#d97706; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; }
/* CSV dropdown fix */
.csv-export-wrap { position:relative; display:inline-block; }
.csv-export-wrap .csv-export-menu { display:none; position:absolute; top:100%; right:0; background:#fff; border:1px solid #e0e0e0; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); z-index:200; min-width:180px; margin-top:4px; overflow:hidden; }
.csv-export-wrap.open .csv-export-menu { display:block; }
.csv-export-menu a { display:block; padding:10px 16px; color:#333; text-decoration:none; font-size:13px; transition:background 0.15s; }
.csv-export-menu a:hover { background:#f5f7fa; }
.cat-toggle-switch { position: relative; display: inline-block; width: 36px; height: 20px; vertical-align: middle; }
.cat-toggle-switch input { opacity: 0; width: 0; height: 0; }
.cat-slider { position: absolute; cursor: pointer; inset: 0; background-color: #cbd5e1; transition: .2s; border-radius: 20px; }
.cat-slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%; }
.cat-toggle-switch input:checked + .cat-slider { background-color: #10b981; }
.cat-toggle-switch input:checked + .cat-slider:before { transform: translateX(16px); }
.hidden-badge { background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; margin-left:6px; }
</style>

<?php $flash = getFlash(); foreach($flash as $f): ?>
<div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>

<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <h1 style="margin:0">Quản lý Danh mục</h1>
    <div style="display:flex;gap:8px;align-items:center">
        <div class="csv-export-wrap" id="catExportDropdown">
            <button type="button" class="btn btn-navy btn-sm" onclick="toggleCatExport()" style="display:inline-flex;align-items:center;gap:4px">
                ↓ XUẤT CSV ▾
            </button>
            <div class="csv-export-menu">
                <a href="#" onclick="csColPick({section:'categories',url:'/admin/categories/export-csv',title:'Tất cả danh mục'});return false"> Xuất tất cả</a>
                <a href="#" onclick="exportSelectedCats();return false"> Xuất đã chọn</a>
            </div>
        </div>
        <button type="button" onclick="document.getElementById('csvImportCats').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↑ Nhập CSV</button>
    </div>
</div>

<div class="catalog-wrap">
    <!-- Left: Parent categories -->
    <div class="catalog-panel" style="flex:1">
        <div class="panel-header">
            <h3>Danh mục cha (<?= count($parentCats) ?>)</h3>
            <button class="add-btn" onclick="openCatModal(null, null)">+ Thêm danh mục</button>
        </div>
        <div class="panel-list">
            <?php if (empty($parentCats)): ?>
                <div class="empty-state">Chưa có danh mục nào</div>
            <?php else: ?>
            <?php foreach ($parentCats as $cat): ?>
            <div class="panel-item <?= ($activeParent && $activeParent['id'] == $cat['id']) ? 'active' : '' ?>">
                <div style="display:flex;align-items:center;gap:8px;flex:1">
                    <input type="checkbox" class="cat-parent-tick" value="<?= $cat['id'] ?>" onchange="updateCatCount()">
                    <a href="/admin/categories?parent_id=<?= $cat['id'] ?>" style="text-decoration:none;flex:1;display:flex;align-items:center;gap:10px">
                        <?php if(!empty($cat['icon'])): ?><img src="/uploads/categories/<?= e($cat['icon']) ?>" style="width:46px;height:46px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;flex-shrink:0"><?php else: ?><span style="display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:8px;border:1px dashed #cbd5e1;background:#f1f5f9;color:#9aa7bd;font-size:9px;flex-shrink:0">Ảnh</span><?php endif; ?>
                        <div style="flex:1;min-width:0">
                            <div class="panel-item-name">
                                <?= e($cat['name']) ?>
                                <?php if ($cat['is_featured']): ?><span class="featured-badge">Nổi bật</span><?php endif; ?>
                                <?php if (isset($cat['is_active']) && intval($cat['is_active']) === 0): ?><span class="hidden-badge">Đã ẩn</span><?php endif; ?>
                            </div>
                            <div class="panel-item-sub"><?= $cat['product_count'] ?> sản phẩm</div>
                        </div>
                    </a>
                </div>
                <div class="panel-item-actions" style="align-items:center;gap:10px">
                    <label class="cat-toggle-switch" title="Gạt để Ẩn / Hiện danh mục" onclick="event.stopPropagation()">
                        <input type="checkbox" <?= (isset($cat['is_active']) && intval($cat['is_active']) === 0) ? '' : 'checked' ?> onchange="toggleCatActive(<?= $cat['id'] ?>, this.checked)">
                        <span class="cat-slider"></span>
                    </label>
                    <button class="adm-edit" onclick="openCatEditModal(<?= $cat['id'] ?>, null, '<?= e($cat['name']) ?>', '<?= e($cat['slug']) ?>', <?= $cat['sort_order'] ?>, <?= $cat['is_featured'] ?>, '<?= e($cat['icon']??'') ?>', <?= intval($cat['is_active']??1) ?>)">Sửa</button>
                    <form method="post" action="/admin/categories/<?= $cat['id'] ?>/delete" style="margin:0" onsubmit="return csConfirmForm(this,'Xóa danh mục này và toàn bộ danh mục con?')">
                        <?= csrfField() ?>
                        <button type="submit" class="adm-del">Xóa</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
<div style="font-size:12px;color:#888;margin-top:8px" id="catSelectedCount"></div>

<!-- Modal: Thêm / Sửa Danh mục -->
<div class="modal-overlay" id="catModal">
    <div class="modal-box">
        <h3 id="catModalTitle">Thêm danh mục</h3>
        <form method="post" id="catForm" action="/admin/categories/add" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="cat_id" id="catId" value="">
            <input type="hidden" name="parent_id" id="catParentId" value="">
            <div class="form-group">
                <label>Tên danh mục *</label>
                <input type="text" name="name" id="catName" required placeholder="VD: Phụ tùng động cơ...">
            </div>
            <div class="form-group">
                <label>Slug (URL) *</label>
                <input type="text" name="slug" id="catSlug" required placeholder="phu-tung-dong-co">
            </div>
            <div class="form-group">
                <label>Ảnh đại diện</label>
                <input type="hidden" name="current_image" id="catCurrentImage" value="">
                <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                    <div style="width:92px;height:92px;border-radius:10px;border:1px solid #d6deea;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                        <img id="catImgPreview" src="" style="width:100%;height:100%;object-fit:cover;display:none">
                        <span id="catImgPlaceholder" style="font-size:11px;color:#9aa7bd;text-align:center;padding:4px">Chưa có ảnh</span>
                    </div>
                    <div>
                        <label for="catImageInput" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:1px dashed #c3cfe2;border-radius:8px;background:#f8fafc;font-size:13px;cursor:pointer;color:#0a192f;font-weight:600">Chọn ảnh</label>
                        <input type="file" id="catImageInput" name="image" accept="image/*" style="display:none" onchange="catImgPick(this)">
                        <div id="catImgName" style="font-size:12px;color:#888;margin-top:8px">Chưa chọn ảnh</div>
                    </div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>Thứ tự hiển thị</label>
                    <input type="number" name="sort_order" id="catSort" min="0" value="100" oninput="if(this.value < 0) this.value = 0;">
                </div>
                <div class="form-group">
                    <label>Nổi bật?</label>
                    <select name="is_featured" id="catFeatured">
                        <option value="0">Không</option>
                        <option value="1">Có</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="add-btn secondary" onclick="closeCatModal()">Hủy</button>
                <button type="submit" class="add-btn">Lưu</button>
            </div>
        </form>
    </div>
</div>

<!-- Import CSV Modal -->
<div id="csvImportCats" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
<div style="background:#fff;border-radius:12px;padding:28px;max-width:500px;width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px"><h3 style="margin:0;color:var(--navy)"> Nhập danh mục từ CSV</h3><button onclick="document.getElementById('csvImportCats').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer">&times;</button></div>
<form method="post" action="/admin/categories/import-csv" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
<div style="margin-bottom:12px;font-size:12px;color:#666;background:#f0f4ff;padding:10px;border-radius:6px"><b>Cột:</b> name, slug, parent_id, sort_order, is_featured</div>
<div class="csv-dropzone" id="dz-categories">
  <input type="file" name="csv_file" accept=".csv" required>
  <span class="dz-icon">📂</span>
  <div class="dz-text">Kéo thả file CSV vào đây hoặc nhấn để chọn file</div>
  <div class="dz-subtext">Chỉ chấp nhận file <strong>.csv</strong> — Không hỗ trợ Word (.docx), Excel (.xlsx), PDF...</div>
  <div class="dz-filename"></div>
  <div class="dz-error"></div>
</div>
<button type="submit" class="btn btn-gold" style="width:100%"> Nhập</button><script>
(function() {
  var dz = document.getElementById('dz-categories');
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

<script>
function slugify(s) { return s.toLowerCase().replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g,'a').replace(/[èéẹẻẽêềếệểễ]/g,'e').replace(/[ìíịỉĩ]/g,'i').replace(/[òóọỏõôồốộổỗơờớợởỡ]/g,'o').replace(/[ùúụủũưừứựửữ]/g,'u').replace(/[ỳýỵỷỹ]/g,'y').replace(/đ/g,'d').replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''); }

document.getElementById('catName').addEventListener('input', function() {
    const sl = document.getElementById('catSlug');
    if (!sl.dataset.manual) sl.value = slugify(this.value);
});
document.getElementById('catSlug').addEventListener('input', function() { this.dataset.manual = '1'; });

function catImgPick(inp){ var p=document.getElementById('catImgPreview'),n=document.getElementById('catImgName'),ph=document.getElementById('catImgPlaceholder'); if(inp.files&&inp.files[0]){ p.src=URL.createObjectURL(inp.files[0]); p.style.display='block'; if(ph)ph.style.display='none'; n.textContent=inp.files[0].name; } }
function openCatModal(parentId, parentName) {
    document.getElementById('catModalTitle').textContent = parentName ? 'Thêm danh mục con – ' + parentName : 'Thêm danh mục cha';
    document.getElementById('catForm').action = '/admin/categories/add';
    document.getElementById('catId').value = '';
    document.getElementById('catParentId').value = parentId || '';
    document.getElementById('catName').value = '';
    document.getElementById('catSlug').value = '';
    document.getElementById('catSlug').removeAttribute('data-manual');
    document.getElementById('catSort').value = '100';
    document.getElementById('catFeatured').value = '0';
    document.getElementById('catImageInput').value=''; document.getElementById('catCurrentImage').value=''; document.getElementById('catImgPreview').style.display='none'; document.getElementById('catImgPlaceholder').style.display='block'; document.getElementById('catImgName').textContent='Chưa chọn ảnh';
    document.getElementById('catModal').classList.add('show');
}
function openCatEditModal(id, parentId, name, slug, sort, featured, image) {
    document.getElementById('catModalTitle').textContent = 'Sửa danh mục';
    document.getElementById('catForm').action = '/admin/categories/'+id+'/edit';
    document.getElementById('catId').value = id;
    document.getElementById('catParentId').value = parentId || '';
    document.getElementById('catName').value = name;
    document.getElementById('catSlug').value = slug;
    document.getElementById('catSlug').dataset.manual = '1';
    document.getElementById('catSort').value = sort;
    document.getElementById('catFeatured').value = featured;
    document.getElementById('catImageInput').value=''; document.getElementById('catCurrentImage').value=image||''; var _cp=document.getElementById('catImgPreview'),_cn=document.getElementById('catImgName'),_ph=document.getElementById('catImgPlaceholder'); if(image){_cp.src='/uploads/categories/'+image;_cp.style.display='block';if(_ph)_ph.style.display='none';_cn.textContent='Ảnh hiện tại';}else{_cp.style.display='none';if(_ph)_ph.style.display='block';_cn.textContent='Chưa chọn ảnh';}
    document.getElementById('catModal').classList.add('show');
}
function closeCatModal() { document.getElementById('catModal').classList.remove('show'); }

// CSV Export dropdown
function toggleCatExport() {
    document.getElementById('catExportDropdown').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    var dd = document.getElementById('catExportDropdown');
    if (dd && !dd.contains(e.target)) dd.classList.remove('open');
});

function exportSelectedCats() {
    var parentChecked = document.querySelectorAll('.cat-parent-tick:checked');
    var childChecked = document.querySelectorAll('.cat-child-tick:checked');
    var ids = [];
    parentChecked.forEach(function(c) { ids.push(c.value); });
    childChecked.forEach(function(c) { ids.push(c.value); });
    if (ids.length === 0) { alert('Vui lòng chọn ít nhất 1 danh mục để xuất'); return; }
    csColPick({section:'categories',url:'/admin/categories/export-csv',title:'Danh mục đã chọn',extra:{ids:ids.join(',')}});
}

function updateCatCount() {
    var parentCount = document.querySelectorAll('.cat-parent-tick:checked').length;
    var childCount = document.querySelectorAll('.cat-child-tick:checked').length;
    var total = parentCount + childCount;
    var el = document.getElementById('catSelectedCount');
    if (el) el.textContent = total > 0 ? total + ' danh mục đã chọn' : '';
}

function toggleCatActive(catId, isActive) {
    fetch('/admin/categories/' + catId + '/toggle-active', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'is_active=' + (isActive ? 1 : 0)
    }).then(r => r.json()).then(data => {
        if (!data.success) alert(data.error || 'Có lỗi xảy ra');
        else location.reload();
    }).catch(e => {
        console.error(e);
        location.reload();
    });
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
