<?php $title = $title ?? 'Sản phẩm'; require __DIR__ . '/../partials/head.php'; ?>
<?php
if (!function_exists('cdd')) {
  function cdd($label, $opts) {
    $sel = isset($opts[0]) ? $opts[0]['label'] : '';
    foreach ($opts as $o) { if (!empty($o['sel'])) { $sel = $o['label']; break; } }
    echo '<div class="filter-field"><label>'.e($label).'</label><div class="cdd">';
    echo '<button type="button" class="cdd-trigger" onclick="cddToggle(this)"><span class="cdd-label">'.e($sel).'</span><svg class="cdd-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg></button>';
    echo '<div class="cdd-panel">';
    foreach ($opts as $o) { echo '<div class="cdd-opt'.(!empty($o['sel'])?' sel':'').'" data-url="'.e($o['url']).'" onclick="cddPick(this)">'.e($o['label']).'</div>'; }
    echo '</div></div></div>';
  }
}
?>
<div class="container"><div class="breadcrumb"><a href="/">Trang chủ</a><span class="sep">›</span><span>Sản phẩm</span>
  <?php if (!empty($_GET['q'])): ?><span class="sep">›</span><span>Tìm: "<?= e($_GET['q']) ?>"</span><?php endif; ?>
</div></div>
<section class="block" style="padding-top:0"><div class="wrap">
  <style>
    .prod-layout-wrapper { display:grid; grid-template-columns:240px 1fr; gap:20px; }
    .pf-head { display:flex; align-items:center; justify-content:space-between; gap:12px; border-bottom:1px solid var(--line); padding:14px 0; margin-bottom:18px; flex-wrap:wrap; min-height:46px; }
    .filter-card { background:linear-gradient(180deg,#fbfcfe 0%,#fff 100%); border:1px solid var(--line); border-radius:14px; padding:18px 20px; margin-bottom:20px; box-shadow:0 1px 4px rgba(15,35,66,.05); }
    .filter-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px 16px; }
    .filter-field { display:flex; flex-direction:column; gap:6px; min-width:0; }
    .filter-field > label { font-size:11px; font-weight:700; color:var(--ink-3); text-transform:uppercase; letter-spacing:.05em; }
    .filter-select, .filter-input { width:100%; height:44px; padding:0 14px; border:1.5px solid var(--line); border-radius:10px; font-size:13.5px; font-weight:500; color:var(--navy-dark); background-color:#fff; transition:border-color .15s,box-shadow .15s; box-sizing:border-box; }
    .filter-select { padding-right:36px; background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%231a3258' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; background-size:14px; -webkit-appearance:none; -moz-appearance:none; appearance:none; cursor:pointer; text-overflow:ellipsis; }
    .filter-select:hover, .filter-input:hover { border-color:#b9c4d6; }
    .filter-select:focus, .filter-input:focus { outline:none; border-color:var(--navy); box-shadow:0 0 0 3px rgba(26,50,88,.12); }
    .filter-submit-btn { width:100%; height:44px; border-radius:10px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; border:none; background:var(--navy); color:#fff; cursor:pointer; transition:background .15s,transform .1s; }
    .filter-submit-btn:hover { background:#122543; }
    .filter-submit-btn:active { transform:scale(0.98); }
    @media(max-width:900px) {
      .prod-layout-wrapper { grid-template-columns:1fr; }
      .cat-sidebar-wrapper { display:none; }
      .filter-grid { grid-template-columns:repeat(2,1fr); }
    }
    @media(max-width:560px) {
      .filter-grid { grid-template-columns:1fr; gap:12px; }
      .filter-card { padding:14px; border-radius:12px; }
    }
  </style>
  <div class="prod-layout-wrapper">
    <aside class="cat-sidebar-wrapper">
      <div class="cat-sidebar"><div class="head"><span class="lines"><span></span><span></span><span></span></span><span>Danh mục</span></div>
        <ul><?php foreach ($categories as $c): ?><li class="<?= ($_GET['cat'] ?? '') === $c['slug'] ? 'featured' : '' ?>"><a href="/products?cat=<?= e($c['slug']) ?>"><span><?= e($c['name']) ?></span><span class="arr">›</span></a></li><?php endforeach; ?></ul>
      </div>
    </aside>
    <div id="product-list-container"><div class="sec-card">
      <div class="pf-head">
        <div class="title"><span class="bar"></span><h2 style="margin:0">Tìm thấy <?= numFmt($total) ?> sản phẩm</h2></div>
      </div>
      <form method="GET" action="/products" id="filterForm" class="filter-card">
        <div class="filter-grid">
          <!-- 1. Hãng xe -->
          <div class="filter-field">
            <label>Hãng xe</label>
            <select name="brand_id" id="filterBrandSelect" class="filter-select" onchange="onBrandChange(this.value)">
              <option value="">Tất cả hãng xe</option>
              <?php foreach (($brands??[]) as $b): ?>
                <option value="<?= $b['id'] ?>" <?= ((string)($_GET['brand_id']??'') === (string)$b['id']) ? 'selected' : '' ?>><?= e($b['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 2. Loại xe / Dòng xe -->
          <div class="filter-field">
            <label>Loại xe / Dòng xe</label>
            <select name="model_id" id="filterModelSelect" class="filter-select">
              <option value="">Tất cả dòng xe</option>
              <?php foreach (($carModels??[]) as $m): ?>
                <option value="<?= $m['id'] ?>" <?= ((string)($_GET['model_id']??'') === (string)$m['id']) ? 'selected' : '' ?>><?= e($m['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 3. Đời xe -->
          <div class="filter-field">
            <label>Đời xe</label>
            <select name="year" class="filter-select">
              <option value="">Tất cả đời xe</option>
              <?php foreach (($years??[]) as $y): ?>
                <option value="<?= $y ?>" <?= ((string)($_GET['year']??'') === (string)$y) ? 'selected' : '' ?>><?= $y ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 4. Mã OEM (tối đa 20 ký tự) -->
          <div class="filter-field">
            <label>Mã OEM</label>
            <input type="text" name="oem" maxlength="20" placeholder="Nhập mã OEM" value="<?= e($_GET['oem'] ?? '') ?>" class="filter-input">
          </div>

          <!-- 5. Danh mục -->
          <div class="filter-field">
            <label>Danh mục</label>
            <select name="cat" class="filter-select">
              <option value="">Tất cả danh mục</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= e($c['slug']) ?>" <?= (($_GET['cat']??'') === $c['slug']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 6. Thương hiệu -->
          <div class="filter-field">
            <label>Thương hiệu</label>
            <select name="pb" class="filter-select">
              <option value="">Tất cả thương hiệu</option>
              <?php foreach (($productBrands??[]) as $pbr): ?>
                <option value="<?= e($pbr['name']) ?>" <?= (($_GET['pb']??'') === $pbr['name']) ? 'selected' : '' ?>><?= e($pbr['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 7. Sắp xếp -->
          <div class="filter-field">
            <label>Sắp xếp</label>
            <select name="sort" class="filter-select">
              <?php foreach (['newest'=>'Mới nhất','bestseller'=>'Bán chạy','price_asc'=>'Giá thấp đến cao','price_desc'=>'Giá cao đến thấp','rating'=>'Đánh giá cao'] as $k=>$vv): ?>
                <option value="<?= $k ?>" <?= (($_GET['sort']??'newest') === $k) ? 'selected' : '' ?>><?= $vv ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 8. Nút Tìm kiếm -->
          <div class="filter-field" style="justify-content:flex-end">
            <label style="visibility:hidden">Tìm kiếm</label>
            <button type="submit" class="btn btn-navy filter-submit-btn">Tìm kiếm</button>
          </div>
        </div>
      </form>
      <?php if (empty($products)): ?>
        <div class="empty-state"><div class="em-icon">∅</div><h3>Chưa có phụ tùng cho lựa chọn này</h3><a href="/products" class="btn btn-outline-navy">Xem tất cả SP</a></div>
      <?php else: ?>
        <div class="prod-grid cols-4"><?php foreach ($products as $p): ?><?php require __DIR__ . '/partials/prod-card.php'; ?><?php endforeach; ?></div>
      <?php endif; ?>
      <?php if ($totalPages > 1): ?>
        <?php require_once __DIR__ . '/../partials/pagination.php'; renderPagination($page, $totalPages, '/products', $_GET); ?>
      <?php endif; ?>
    </div></div>
  </div>
</div></section>

<script>
function loadProducts(url){
  if(!url) return;
  history.pushState(null,'',url);
  var c=document.getElementById('product-list-container');
  if(c) c.style.opacity='0.5';
  fetch(url).then(function(r){return r.text();}).then(function(html){
    var doc=new DOMParser().parseFromString(html,'text/html');
    var nc=doc.getElementById('product-list-container');
    if(nc&&c){ c.innerHTML=nc.innerHTML; c.style.opacity='1'; } else { window.location.href=url; }
  }).catch(function(){ window.location.href=url; });
}
document.addEventListener('DOMContentLoaded', function() {
  const catLinks = document.querySelectorAll('.cat-sidebar a');
  catLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const url = this.getAttribute('href');
      
      document.querySelectorAll('.cat-sidebar li').forEach(li => li.classList.remove('featured'));
      this.parentElement.classList.add('featured');
      
      history.pushState(null, '', url);
      
      const container = document.getElementById('product-list-container');
      container.style.opacity = '0.5';
      
      fetch(url)
        .then(res => res.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newContent = doc.getElementById('product-list-container');
          if (newContent) {
            container.innerHTML = newContent.innerHTML;
            container.style.opacity = '1';
          } else {
            window.location.href = url;
          }
        })
        .catch(() => window.location.href = url);
    });
  });
});

// ===== custom dropdown (replaces native <select> filters) =====
function cddCloseAll(){ document.querySelectorAll('.cdd.open').forEach(function(d){ d.classList.remove('open'); }); }
function cddToggle(btn){
  var cdd=btn.closest('.cdd'); var wasOpen=cdd.classList.contains('open');
  cddCloseAll(); if(wasOpen) return;
  cdd.classList.add('open');
  var panel=cdd.querySelector('.cdd-panel'); var r=btn.getBoundingClientRect();
  panel.style.left=Math.round(r.left)+'px';
  panel.style.width=Math.round(r.width)+'px';
  panel.style.top=Math.round(r.bottom+6)+'px';
  var space=window.innerHeight-r.bottom-16;
  panel.style.maxHeight=Math.max(150,Math.min(330,space))+'px';
}
function cddPick(opt){ var u=opt.getAttribute('data-url'); cddCloseAll(); if(typeof loadProducts==='function') loadProducts(u); else window.location.href=u; }
document.addEventListener('click', function(e){ if(!e.target.closest('.cdd')) cddCloseAll(); });
window.addEventListener('scroll', function(e){ if(e.target && e.target.closest && e.target.closest('.cdd-panel')) return; cddCloseAll(); }, true);
window.addEventListener('resize', cddCloseAll);
document.addEventListener('keydown', function(e){ if(e.key==='Escape') cddCloseAll(); });

const allCarModels = <?= json_encode($allModels ?? []) ?>;
const curModelId = "<?= e($_GET['model_id'] ?? '') ?>";

function onBrandChange(brandId) {
  const modelSelect = document.getElementById('filterModelSelect');
  if (!modelSelect) return;
  modelSelect.innerHTML = '<option value="">Tất cả dòng xe</option>';
  const filtered = brandId ? allCarModels.filter(m => String(m.brand_id) === String(brandId)) : allCarModels;
  filtered.forEach(m => {
    const opt = document.createElement('option');
    opt.value = m.id;
    opt.textContent = m.name;
    if (String(m.id) === String(curModelId)) opt.selected = true;
    modelSelect.appendChild(opt);
  });
}

document.addEventListener('DOMContentLoaded', function() {
  const brandSelect = document.getElementById('filterBrandSelect');
  if (brandSelect && brandSelect.value) {
    onBrandChange(brandSelect.value);
  }
});
</script>
<?php require __DIR__ . '/../partials/foot.php'; ?>
