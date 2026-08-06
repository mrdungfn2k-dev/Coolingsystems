<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
/* ─────────────────────────────────────────────────
   ADMIN: Quản lý Thương hiệu sản phẩm  (bảng product_brands)
   LƯU Ý: "Hãng xe" là mục RIÊNG tại /admin/brands — không liên quan tới đây.
   ───────────────────────────────────────────────── */
$productBrands = $productBrands ?? dbAll("SELECT * FROM product_brands ORDER BY sort_order, name");
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px">
    <h1 style="margin:0;font-style:italic;color:var(--navy)">Quản lý Thương hiệu sản phẩm</h1>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <a href="#" onclick="csColPick({section:'product_brands',url:'/admin/product-brands/export-csv',title:'Thương hiệu SP'});return false" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
        <button type="button" onclick="document.getElementById('pbCsvModal').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↑ Nhập CSV</button>
        <button type="button" onclick="openPbModal()" class="btn btn-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">+ Thêm thương hiệu</button>
    </div>
</div>

<style>
.pb-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:16px}
.pb-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:18px;box-shadow:0 1px 3px rgba(0,0,0,.06);display:flex;flex-direction:column;align-items:center;text-align:center;transition:box-shadow .15s}
.pb-card:hover{box-shadow:0 4px 14px rgba(0,0,0,.10)}
.pb-logo{height:62px;display:flex;align-items:center;justify-content:center;margin-bottom:10px}
.pb-logo img{max-height:58px;max-width:150px;object-fit:contain}
.pb-noimg{width:58px;height:58px;border-radius:10px;background:var(--gold-soft,#f3e9d2);color:var(--navy);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:20px;text-transform:uppercase}
.pb-name{font-weight:700;color:var(--navy);font-size:15px}
.pb-slug{font-size:11px;color:#9aa;margin-top:2px;word-break:break-all}
.pb-sub{font-size:12px;color:#888;margin-top:5px}
.pb-actions{display:flex;gap:8px;margin-top:13px}
.pb-actions form{margin:0}
.pb-actions button{border:none;cursor:pointer;padding:5px 15px;border-radius:6px;font-size:13px;font-weight:600}
.pb-edit-btn{background:var(--navy);color:#fff}
.pb-edit-btn:hover{background:#0b1f40;color:#fff}
.pb-del-btn{background:#fff;color:#1a3258;border:1.5px solid #1a3258 !important}
.pb-del-btn:hover{background:#1a3258;color:#fff}
.pb-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:99990;align-items:center;justify-content:center;padding:16px}
.pb-modal-box{background:#fff;border-radius:14px;width:100%;max-width:440px;padding:24px;max-height:90vh;overflow:auto}
.pb-modal-box h3{margin:0 0 16px;color:var(--navy)}
.pb-modal-box label{display:block;font-size:13px;font-weight:600;color:#374151;margin:12px 0 4px}
.pb-modal-box input[type=text],.pb-modal-box input[type=number],.pb-modal-box textarea,.pb-modal-box input[type=file]{width:100%;padding:9px 11px;border:1px solid #d1d5db;border-radius:7px;font-family:inherit;font-size:14px;box-sizing:border-box}
.pb-modal-box textarea{resize:vertical;min-height:62px}
.pb-modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:18px}
.pb-empty{padding:50px;text-align:center;color:#aaa;background:#fff;border:1px dashed var(--line);border-radius:12px}
</style>

<form method="get" action="/admin/product-brands" style="margin-bottom:18px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;background:#fff;padding:12px 16px;border-radius:10px;border:1px solid var(--line);box-shadow:0 1px 2px rgba(0,0,0,0.03)">
    <div style="flex:1;min-width:200px">
        <input type="text" name="q" id="pbSearchInput" placeholder="Tìm tên, slug hoặc mô tả thương hiệu..." value="<?= e($q ?? '') ?>" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;box-sizing:border-box" onkeyup="filterPbCards(this.value)">
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <label style="font-size:13px;font-weight:600;color:#555;white-space:nowrap">Sắp xếp:</label>
        <select name="sort" onchange="this.form.submit()" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px">
            <option value="sort_order" <?= ($sortBy ?? '') === 'sort_order' ? 'selected' : '' ?>>Theo thứ tự hiển thị</option>
            <option value="name" <?= ($sortBy ?? '') === 'name' ? 'selected' : '' ?>>Theo tên (A-Z)</option>
            <option value="products" <?= ($sortBy ?? '') === 'products' ? 'selected' : '' ?>>Nhiều sản phẩm nhất</option>
        </select>
        <button type="submit" class="btn btn-navy btn-sm">Tìm kiếm</button>
        <?php if (!empty($q) || (!empty($sortBy) && $sortBy !== 'sort_order')): ?>
            <a href="/admin/product-brands" class="btn btn-outline-navy btn-sm">Xóa lọc</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($productBrands)): ?>
  <div class="pb-empty">Chưa có thương hiệu nào phù hợp. Bấm “+ Thêm thương hiệu” để tạo mới.</div>
<?php else: ?>
<div style="font-size:14px;font-weight:700;color:var(--navy);margin:2px 0 14px">Tổng: <?= $total ?? count($productBrands) ?> thương hiệu</div>
<div class="pb-grid">
  <?php foreach ($productBrands as $b):
      $cnt = (int)($b['product_count'] ?? 0);
  ?>
  <div class="pb-card">
    <div class="pb-logo">
      <?php if (!empty($b['logo'])): ?>
        <img src="/uploads/product-brands/<?= e($b['logo']) ?>" alt="<?= e($b['name']) ?>" loading="lazy">
      <?php else: ?>
        <div class="pb-noimg"><?= e(mb_substr($b['name'],0,2)) ?></div>
      <?php endif; ?>
    </div>
    <div class="pb-name"><?= e($b['name']) ?></div>
    <div class="pb-slug"><?= e($b['slug'] ?? '') ?></div>
    <div class="pb-sub"><?= $cnt ?> sản phẩm &nbsp;·&nbsp; <span style="display:inline-block;background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;padding:2px 6px;border-radius:4px;font-weight:700;font-size:11px" title="Thứ tự hiển thị (Tự động đảo vị trí khi đổi)">Thứ tự #<?= (int)($b['sort_order'] ?? 0) ?></span></div>
    <div class="pb-actions">
      <button type="button" class="adm-edit pb-edit-btn"
        data-id="<?= (int)$b['id'] ?>"
        data-name="<?= e($b['name']) ?>"
        data-desc="<?= e($b['description'] ?? '') ?>"
        data-sort="<?= (int)($b['sort_order'] ?? 0) ?>"
        data-logo="<?= e($b['logo'] ?? '') ?>">Sửa</button>
      <form method="post" action="/admin/product-brands/<?= (int)$b['id'] ?>/delete" onsubmit="return csConfirmForm(this,'Xóa thương hiệu “<?= e($b['name']) ?>”?')">
        <?= csrfField() ?>
        <button type="submit" class="adm-del">Xóa</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php if (!empty($totalPages) && $totalPages > 1): ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;background:#fff;padding:12px 16px;border-radius:10px;border:1px solid var(--line)">
    <div style="font-size:13px;color:#666">Hiển thị <b><?= count($productBrands) ?></b> / <b><?= $total ?></b> thương hiệu (Trang <?= $page ?> / <?= $totalPages ?>)</div>
    <div style="display:flex;gap:4px">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="/admin/product-brands?page=<?= $p ?>&q=<?= urlencode($q ?? '') ?>&sort=<?= urlencode($sortBy ?? '') ?>" class="btn btn-sm <?= $p === $page ? 'btn-navy' : 'btn-outline-navy' ?>" style="min-width:32px;text-align:center"><?= $p ?></a>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Modal: Thêm / Sửa thương hiệu -->
<div id="pbModal" class="pb-modal">
  <div class="pb-modal-box">
    <h3 id="pbModalTitle">Thêm thương hiệu</h3>
    <form id="pbForm" method="post" action="/admin/product-brands/new" enctype="multipart/form-data">
      <?= csrfField() ?>
      <label>Tên thương hiệu *</label>
      <input type="text" name="name" id="pbName" required placeholder="VD: MAGNETI MARELLI">
      <label>Mô tả</label>
      <textarea name="description" id="pbDesc" placeholder="Mô tả ngắn về thương hiệu"></textarea>
      <label>Thứ tự hiển thị (số nhỏ lên trước)</label>
      <input type="number" name="sort_order" id="pbSort" min="0" value="0" oninput="if(this.value < 0) this.value = 0;">
      <label>Logo (jpg, png, webp, svg)</label>
      <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg,image/*">
      <div id="pbCurLogo" style="margin-top:8px"></div>
      <div class="pb-modal-actions">
        <button type="button" onclick="closePbModal()" class="btn btn-outline-navy btn-sm">Hủy</button>
        <button type="submit" class="btn btn-navy btn-sm">Lưu</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Nhập CSV -->
<div id="pbCsvModal" class="pb-modal">
  <div class="pb-modal-box">
    <h3>Nhập thương hiệu từ CSV</h3>
    <p style="font-size:13px;color:#666;margin:0 0 12px">Các cột: <b>name, description, sort</b>. Tên đã tồn tại sẽ được bỏ qua.</p>
    <form method="post" action="/admin/product-brands/import-csv" enctype="multipart/form-data">
      <?= csrfField() ?>
      <input type="file" name="csv_file" accept=".csv" required>
      <div class="pb-modal-actions">
        <button type="button" onclick="document.getElementById('pbCsvModal').style.display='none'" class="btn btn-outline-navy btn-sm">Hủy</button>
        <button type="submit" class="btn btn-navy btn-sm">Nhập</button>
      </div>
    </form>
  </div>
</div>

<script>
function filterPbCards(val) {
  var term = val.toLowerCase().trim();
  document.querySelectorAll('.pb-card').forEach(function(card) {
    var text = card.textContent.toLowerCase();
    if (!term || text.indexOf(term) !== -1) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
}
function openPbModal(){
  document.getElementById('pbModalTitle').textContent='Thêm thương hiệu';
  document.getElementById('pbForm').action='/admin/product-brands/new';
  document.getElementById('pbName').value='';
  document.getElementById('pbDesc').value='';
  document.getElementById('pbSort').value='0';
  document.getElementById('pbCurLogo').innerHTML='';
  document.getElementById('pbModal').style.display='flex';
}
function closePbModal(){ document.getElementById('pbModal').style.display='none'; }
document.querySelectorAll('.pb-edit-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    var d=this.dataset;
    document.getElementById('pbModalTitle').textContent='Sửa thương hiệu';
    document.getElementById('pbForm').action='/admin/product-brands/'+d.id+'/edit';
    document.getElementById('pbName').value=d.name;
    document.getElementById('pbDesc').value=d.desc;
    document.getElementById('pbSort').value=d.sort;
    document.getElementById('pbCurLogo').innerHTML = d.logo
      ? '<div style="font-size:12px;color:#888;margin-bottom:4px">Logo hiện tại:</div><img src="/uploads/product-brands/'+d.logo+'" style="max-height:46px;max-width:140px;object-fit:contain;border:1px solid #eee;border-radius:6px;padding:4px;background:#fff">'
      : '<div style="font-size:12px;color:#aaa">Chưa có logo</div>';
    document.getElementById('pbModal').style.display='flex';
  });
});
document.querySelectorAll('.pb-modal').forEach(function(m){
  m.addEventListener('click', function(e){ if(e.target===m) m.style.display='none'; });
});
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
