<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
/* ─────────────────────────────────────────────────
   ADMIN: Quản lý Danh mục (Categories)
   ───────────────────────────────────────────────── */

$parentCats = dbAll("SELECT c.*, COUNT(ch.id) AS child_count FROM categories c LEFT JOIN categories ch ON ch.parent_id=c.id WHERE c.parent_id IS NULL GROUP BY c.id ORDER BY c.sort_order, c.name");
$activeParent = null;
$childCats = [];
if (!empty($_GET['parent_id'])) {
    $activeParent = dbGet("SELECT * FROM categories WHERE id=?", [intval($_GET['parent_id'])]);
    if ($activeParent) {
        $childCats = dbAll("SELECT * FROM categories WHERE parent_id=? ORDER BY sort_order, name", [$activeParent['id']]);
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
</style>

<?php $flash = getFlash(); foreach($flash as $f): ?>
<div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>

<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <h1 style="font-size:20px;font-weight:800;color:var(--navy);margin:0;font-style:italic">Quản lý Danh mục</h1>
    <div style="display:flex;gap:8px;align-items:center">
        <div style="position:relative;display:inline-block" class="csv-dropdown-wrap">
      <button class="btn btn-navy btn-sm" onclick="this.parentElement.classList.toggle('open')" style="display:flex;align-items:center;gap:4px">↓ XUẤT CSV <span style="font-size:10px">▼</span></button>
      <div class="csv-dropdown-menu">
        <a href="/admin/categories/export-csv" class="csv-dropdown-item">Xuất tất cả</a>
        <a href="#" onclick="exportSelected('categories','/admin/categories/export-csv');return false" class="csv-dropdown-item">Xuất đã chọn</a>
      </div>
    </div>
        <button type="button" onclick="document.getElementById('csvImportCats').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↑ Nhập CSV</button>
    </div>
</div>

<div class="catalog-wrap">
    <!-- Left: Parent categories -->
    <div class="catalog-panel" style="flex:0 0 340px">
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
                <a href="/admin/categories?parent_id=<?= $cat['id'] ?>" style="text-decoration:none;flex:1">
                    <div class="panel-item-name">
                        <?= e($cat['name']) ?>
                        <?php if ($cat['is_featured']): ?><span class="featured-badge">Nổi bật</span><?php endif; ?>
                    </div>
                    <div class="panel-item-sub"><?= $cat['child_count'] ?> danh mục con · <?= $cat['product_count'] ?> sản phẩm</div>
                </a>
                <div class="panel-item-actions">
                    <button class="btn-icon" onclick="openCatEditModal(<?= $cat['id'] ?>, null, '<?= e($cat['name']) ?>', '<?= e($cat['slug']) ?>', <?= $cat['sort_order'] ?>, <?= $cat['is_featured'] ?>)">Sửa</button>
                    <form method="post" action="/admin/categories/<?= $cat['id'] ?>/delete" style="margin:0" onsubmit="return confirm('Xóa danh mục này và toàn bộ danh mục con?')">
                        <?= csrfField() ?>
                        <button type="submit" class="btn-icon red">Xóa</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Child categories -->
    <div class="catalog-panel" style="flex:1">
        <?php if ($activeParent): ?>
        <div class="panel-header">
            <h3>Danh mục con – <?= e($activeParent['name']) ?></h3>
            <button class="add-btn" onclick="openCatModal(<?= $activeParent['id'] ?>, '<?= e($activeParent['name']) ?>')">+ Thêm danh mục con</button>
        </div>
        <div class="panel-list">
            <?php if (empty($childCats)): ?>
                <div class="empty-state">Chưa có danh mục con nào</div>
            <?php else: ?>
            <table class="tbl" style="width:100%">
                <thead>
                    <tr>
                        <th style="width:36px;padding:12px"><input type="checkbox" id="checkAll" onchange="document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked)"></th>
                        <th style="padding:12px 20px;text-align:left">Tên danh mục</th>
                        <th style="padding:12px;text-align:left">Slug</th>
                        <th style="padding:12px;text-align:center">Thứ tự</th>
                        <th style="padding:12px;text-align:center">Sản phẩm</th>
                        <th style="padding:12px;text-align:center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($childCats as $child): ?>
                <tr style="border-bottom:1px solid #f5f5f5">
                    <td style="padding:12px 20px;font-weight:600"><?= e($child['name']) ?></td>
                    <td style="padding:12px;font-size:12px;color:#888"><?= e($child['slug']) ?></td>
                    <td style="padding:12px;text-align:center"><?= $child['sort_order'] ?></td>
                    <td style="padding:12px;text-align:center"><?= $child['product_count'] ?></td>
                    <td style="padding:12px;text-align:center">
                        <div style="display:flex;gap:6px;justify-content:center">
                            <button class="btn-icon" onclick="openCatEditModal(<?= $child['id'] ?>, <?= $activeParent['id'] ?>, '<?= e($child['name']) ?>', '<?= e($child['slug']) ?>', <?= $child['sort_order'] ?>, 0)">Sửa</button>
                            <form method="post" action="/admin/categories/<?= $child['id'] ?>/delete" style="margin:0" onsubmit="return confirm('Xóa danh mục con này?')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn-icon red">Xóa</button>
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
            Chọn danh mục cha bên trái để xem và quản lý danh mục con
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Thêm / Sửa Danh mục -->
<div class="modal-overlay" id="catModal">
    <div class="modal-box">
        <h3 id="catModalTitle">Thêm danh mục</h3>
        <form method="post" id="catForm" action="/admin/categories/add">
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
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>Thứ tự hiển thị</label>
                    <input type="number" name="sort_order" id="catSort" value="100">
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

<script>
function slugify(s) { return s.toLowerCase().replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g,'a').replace(/[èéẹẻẽêềếệểễ]/g,'e').replace(/[ìíịỉĩ]/g,'i').replace(/[òóọỏõôồốộổỗơờớợởỡ]/g,'o').replace(/[ùúụủũưừứựửữ]/g,'u').replace(/[ỳýỵỷỹ]/g,'y').replace(/đ/g,'d').replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''); }

document.getElementById('catName').addEventListener('input', function() {
    const sl = document.getElementById('catSlug');
    if (!sl.dataset.manual) sl.value = slugify(this.value);
});
document.getElementById('catSlug').addEventListener('input', function() { this.dataset.manual = '1'; });

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
    document.getElementById('catModal').classList.add('show');
}
function openCatEditModal(id, parentId, name, slug, sort, featured) {
    document.getElementById('catModalTitle').textContent = 'Sửa danh mục';
    document.getElementById('catForm').action = '/admin/categories/'+id+'/edit';
    document.getElementById('catId').value = id;
    document.getElementById('catParentId').value = parentId || '';
    document.getElementById('catName').value = name;
    document.getElementById('catSlug').value = slug;
    document.getElementById('catSlug').dataset.manual = '1';
    document.getElementById('catSort').value = sort;
    document.getElementById('catFeatured').value = featured;
    document.getElementById('catModal').classList.add('show');
}
function closeCatModal() { document.getElementById('catModal').classList.remove('show'); }
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>

<div id="csvImportCats" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
<div style="background:#fff;border-radius:12px;padding:28px;max-width:500px;width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px"><h3 style="margin:0;color:var(--navy)">📥 Nhập danh mục từ CSV</h3><button onclick="document.getElementById('csvImportCats').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer">&times;</button></div>
<form method="post" action="/admin/categories/import-csv" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
<div style="margin-bottom:12px;font-size:12px;color:#666;background:#f0f4ff;padding:10px;border-radius:6px">Cột: name, slug, parent_id, sort_order, is_featured</div>
<input type="file" name="csv_file" accept=".csv" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:12px">
<button type="submit" class="btn btn-gold" style="width:100%">📤 Nhập</button></form></div></div>
