<?php $title = $title ?? 'Sản phẩm'; require __DIR__ . '/../partials/head.php'; ?>
<?php
if (!function_exists('renderCustomSelect')) {
  function renderCustomSelect($name, $label, $opts, $id = '') {
    $selVal = '';
    $selLabel = isset($opts[0]) ? $opts[0]['label'] : '';
    foreach ($opts as $o) {
      if (!empty($o['sel'])) {
        $selVal = $o['val'];
        $selLabel = $o['label'];
        break;
      }
    }
    echo '<div class="filter-field custom-cdd-field" style="position:relative">';
    echo '<label>'.e($label).'</label>';
    echo '<input type="hidden" name="'.e($name).'" id="'.e($id ? $id : 'input_'.$name).'" value="'.e($selVal).'">';
    echo '<button type="button" class="cdd-trigger" onclick="toggleCustomSelect(this)">';
    echo '<span class="cdd-label">'.e($selLabel).'</span>';
    echo '<svg class="cdd-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>';
    echo '</button>';
    echo '<div class="cdd-panel">';
    foreach ($opts as $o) {
      $isSel = !empty($o['sel']);
      echo '<div class="cdd-opt'.($isSel?' sel':'').'" data-value="'.e($o['val']).'" onclick="pickCustomSelect(this)">'.e($o['label']).'</div>';
    }
    echo '</div></div>';
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
    .filter-card { background:linear-gradient(180deg,#fbfcfe 0%,#fff 100%); border:1px solid var(--line); border-radius:14px; padding:18px 20px; margin-bottom:20px; box-shadow:0 1px 4px rgba(15,35,66,.05); position:relative; z-index:10; }
    .filter-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px 16px; }
    .filter-field { display:flex; flex-direction:column; gap:6px; min-width:0; }
    .filter-field > label { font-size:11px; font-weight:700; color:var(--ink-3); text-transform:uppercase; letter-spacing:.05em; }
    .filter-input { width:100%; height:44px; padding:0 14px; border:1.5px solid var(--line); border-radius:10px; font-size:13.5px; font-weight:500; color:var(--navy-dark); background-color:#fff; transition:border-color .15s,box-shadow .15s; box-sizing:border-box; }
    .filter-input:hover { border-color:#b9c4d6; }
    .filter-input:focus { outline:none; border-color:var(--navy); box-shadow:0 0 0 3px rgba(26,50,88,.12); }
    .filter-submit-btn { width:100%; height:44px; border-radius:10px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; border:none; background:var(--navy); color:#fff; cursor:pointer; transition:background .15s,transform .1s; }
    .filter-submit-btn:hover { background:#122543; }
    .filter-submit-btn:active { transform:scale(0.98); }

    /* Custom Select Styling - Khung vuông tròn & Luôn xổ xuống dưới */
    .custom-cdd-field { position:relative; }
    .cdd-trigger { width:100%; height:44px; padding:0 12px 0 14px; display:flex; align-items:center; justify-content:space-between; gap:8px; border:1.5px solid var(--line); border-radius:10px; background:#fff; color:var(--navy-dark); font-size:13.5px; font-weight:500; cursor:pointer; transition:border-color .15s,box-shadow .15s; font-family:inherit; box-sizing:border-box; }
    .cdd-trigger:hover { border-color:#b9c4d6; }
    .custom-cdd-field.open .cdd-trigger { border-color:var(--navy); box-shadow:0 0 0 3px rgba(26,50,88,.12); }
    .cdd-label { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; text-align:left; }
    .cdd-arrow { flex-shrink:0; color:#1a3258; transition:transform .2s; }
    .custom-cdd-field.open .cdd-arrow { transform:rotate(180deg); }
    .cdd-panel { display:none; position:absolute; top:calc(100% + 6px); left:0; width:100%; background:#fff; border:1.5px solid var(--line); border-radius:12px; box-shadow:0 12px 32px rgba(15,35,66,.18); max-height:250px; overflow-y:auto; z-index:99999; padding:6px; box-sizing:border-box; }
    .custom-cdd-field.open .cdd-panel { display:block; }
    .cdd-opt { padding:10px 12px; border-radius:8px; font-size:13.5px; color:var(--navy-dark); cursor:pointer; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; transition:background .12s,color .12s; }
    .cdd-opt:hover { background:#f1f5fb; color:var(--navy); }
    .cdd-opt.sel { background:var(--navy); color:#fff; font-weight:600; }

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
          <?php
          $optsBrand = [['val'=>'', 'label'=>'Tất cả hãng xe', 'sel'=>empty($_GET['brand_id'])]];
          foreach (($brands??[]) as $b) {
            $optsBrand[] = ['val'=>$b['id'], 'label'=>$b['name'], 'sel'=>((string)($_GET['brand_id']??'') === (string)$b['id'])];
          }
          renderCustomSelect('brand_id', 'Hãng xe', $optsBrand, 'input_brand_id');
          ?>

          <!-- 2. Loại xe / Dòng xe -->
          <?php
          $optsModel = [['val'=>'', 'label'=>'Tất cả dòng xe', 'sel'=>empty($_GET['model_id'])]];
          foreach (($carModels??[]) as $m) {
            $optsModel[] = ['val'=>$m['id'], 'label'=>$m['name'], 'sel'=>((string)($_GET['model_id']??'') === (string)$m['id'])];
          }
          renderCustomSelect('model_id', 'Loại xe / Dòng xe', $optsModel, 'input_model_id');
          ?>

          <!-- 3. Đời xe -->
          <?php
          $optsYear = [['val'=>'', 'label'=>'Tất cả đời xe', 'sel'=>empty($_GET['year'])]];
          foreach (($years??[]) as $y) {
            $optsYear[] = ['val'=>$y, 'label'=>(string)$y, 'sel'=>((string)($_GET['year']??'') === (string)$y)];
          }
          renderCustomSelect('year', 'Đời xe', $optsYear, 'input_year');
          ?>

          <!-- 4. Mã OEM (tối đa 20 ký tự) -->
          <div class="filter-field">
            <label>Mã OEM</label>
            <input type="text" name="oem" maxlength="20" placeholder="Nhập mã OEM" value="<?= e($_GET['oem'] ?? '') ?>" class="filter-input">
          </div>

          <!-- 5. Danh mục -->
          <?php
          $optsCat = [['val'=>'', 'label'=>'Tất cả danh mục', 'sel'=>empty($_GET['cat'])]];
          foreach ($categories as $c) {
            $optsCat[] = ['val'=>$c['slug'], 'label'=>$c['name'], 'sel'=>(($_GET['cat']??'') === $c['slug'])];
          }
          renderCustomSelect('cat', 'Danh mục', $optsCat, 'input_cat');
          ?>

          <!-- 6. Thương hiệu -->
          <?php
          $optsPb = [['val'=>'', 'label'=>'Tất cả thương hiệu', 'sel'=>empty($_GET['pb'])]];
          foreach (($productBrands??[]) as $pbr) {
            $optsPb[] = ['val'=>$pbr['name'], 'label'=>$pbr['name'], 'sel'=>(($_GET['pb']??'') === $pbr['name'])];
          }
          renderCustomSelect('pb', 'Thương hiệu', $optsPb, 'input_pb');
          ?>

          <!-- 7. Sắp xếp -->
          <?php
          $optsSort = [];
          $sortMap = ['newest'=>'Mới nhất','bestseller'=>'Bán chạy','price_asc'=>'Giá thấp đến cao','price_desc'=>'Giá cao đến thấp','rating'=>'Đánh giá cao'];
          foreach ($sortMap as $k => $vv) {
            $optsSort[] = ['val'=>$k, 'label'=>$vv, 'sel'=>(($_GET['sort']??'newest') === $k)];
          }
          renderCustomSelect('sort', 'Sắp xếp', $optsSort, 'input_sort');
          ?>

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
function closeAllCustomSelects() {
  document.querySelectorAll('.custom-cdd-field.open').forEach(f => f.classList.remove('open'));
}

function toggleCustomSelect(btn) {
  const field = btn.closest('.custom-cdd-field');
  const wasOpen = field.classList.contains('open');
  closeAllCustomSelects();
  if (!wasOpen) field.classList.add('open');
}

function pickCustomSelect(opt) {
  const field = opt.closest('.custom-cdd-field');
  const val = opt.getAttribute('data-value');
  const label = opt.textContent;
  
  const hiddenInput = field.querySelector('input[type="hidden"]');
  if (hiddenInput) {
    hiddenInput.value = val;
    if (hiddenInput.name === 'brand_id' && typeof onBrandChange === 'function') {
      onBrandChange(val);
    }
  }
  
  const labelEl = field.querySelector('.cdd-label');
  if (labelEl) labelEl.textContent = label;
  
  field.querySelectorAll('.cdd-opt').forEach(o => o.classList.remove('sel'));
  opt.classList.add('sel');
  field.classList.remove('open');
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('.custom-cdd-field')) closeAllCustomSelects();
});
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeAllCustomSelects();
});

const allCarModels = <?= json_encode($allModels ?? []) ?>;
const curModelId = "<?= e($_GET['model_id'] ?? '') ?>";

function onBrandChange(brandId) {
  const modelInput = document.getElementById('input_model_id');
  if (!modelInput) return;
  const modelField = modelInput.closest('.custom-cdd-field');
  if (!modelField) return;
  
  const labelEl = modelField.querySelector('.cdd-label');
  const panel = modelField.querySelector('.cdd-panel');
  
  const filtered = brandId ? allCarModels.filter(m => String(m.brand_id) === String(brandId)) : allCarModels;
  
  panel.innerHTML = '';
  
  const defOpt = document.createElement('div');
  defOpt.className = 'cdd-opt' + (!curModelId ? ' sel' : '');
  defOpt.setAttribute('data-value', '');
  defOpt.textContent = 'Tất cả dòng xe';
  defOpt.onclick = function() { pickCustomSelect(this); };
  panel.appendChild(defOpt);
  
  let foundSel = false;
  filtered.forEach(m => {
    const isSel = String(m.id) === String(curModelId);
    if (isSel) foundSel = true;
    const opt = document.createElement('div');
    opt.className = 'cdd-opt' + (isSel ? ' sel' : '');
    opt.setAttribute('data-value', m.id);
    opt.textContent = m.name;
    opt.onclick = function() { pickCustomSelect(this); };
    panel.appendChild(opt);
  });
  
  if (!foundSel) {
    modelInput.value = '';
    if (labelEl) labelEl.textContent = 'Tất cả dòng xe';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const brandInput = document.getElementById('input_brand_id');
  if (brandInput && brandInput.value) {
    onBrandChange(brandInput.value);
  }
});
</script>
<?php require __DIR__ . '/../partials/foot.php'; ?>
