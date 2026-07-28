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
    .filter-card { background:linear-gradient(180deg,#fbfcfe 0%,#fff 100%); border:1px solid var(--line); border-radius:14px; padding:16px 18px; margin-bottom:20px; box-shadow:0 1px 4px rgba(15,35,66,.05); }
    .filter-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px 16px; }
    .filter-field { display:flex; flex-direction:column; gap:7px; min-width:0; }
    .filter-field > label { font-size:11px; font-weight:700; color:var(--ink-3); text-transform:uppercase; letter-spacing:.05em; }
    .filter-select { width:100%; height:44px; padding:0 40px 0 14px; border:1.5px solid var(--line); border-radius:10px; font-size:13.5px; font-weight:500; color:var(--navy-dark); background-color:#fff; background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%231a3258' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 13px center; background-size:15px; -webkit-appearance:none; -moz-appearance:none; appearance:none; cursor:pointer; transition:border-color .15s,box-shadow .15s,background-color .15s; text-overflow:ellipsis; }
    .filter-select:hover { border-color:#b9c4d6; background-color:#fcfdff; }
    .filter-select:focus { outline:none; border-color:var(--navy); box-shadow:0 0 0 3px rgba(26,50,88,.12); }
    @media(max-width:900px) {
      .prod-layout-wrapper { grid-template-columns:1fr; }
      .cat-sidebar-wrapper { display:none; }
      .filter-grid { grid-template-columns:1fr 1fr; }
    }
    @media(max-width:560px) {
      .filter-grid { grid-template-columns:1fr; gap:12px; }
      .filter-card { padding:14px; border-radius:12px; }
    }
    .cdd { position:relative; }
    .cdd-trigger { width:100%; height:44px; padding:0 12px 0 14px; display:flex; align-items:center; justify-content:space-between; gap:8px; border:1.5px solid var(--line); border-radius:10px; background:#fff; color:var(--navy-dark); font-size:13.5px; font-weight:500; cursor:pointer; transition:border-color .15s,box-shadow .15s; font-family:inherit; }
    .cdd-trigger:hover { border-color:#b9c4d6; }
    .cdd.open .cdd-trigger { border-color:var(--navy); box-shadow:0 0 0 3px rgba(26,50,88,.12); }
    .cdd-label { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; text-align:left; }
    .cdd-arrow { flex-shrink:0; color:#1a3258; transition:transform .2s; }
    .cdd.open .cdd-arrow { transform:rotate(180deg); }
    .cdd-panel { display:none; position:fixed; background:#fff; border:1px solid var(--line); border-radius:10px; box-shadow:0 14px 38px rgba(15,35,66,.2); max-height:320px; overflow-y:auto; -webkit-overflow-scrolling:touch; z-index:9999; padding:6px; }
    .cdd.open .cdd-panel { display:block; }
    .cdd-opt { padding:11px 12px; border-radius:7px; font-size:13.5px; color:var(--navy-dark); cursor:pointer; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; transition:background .12s; }
    .cdd-opt:hover { background:#f1f5fb; }
    .cdd-opt.sel { background:var(--navy); color:#fff; font-weight:600; }
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
      <div class="filter-card">
        <div class="filter-grid">
          <?php
          $qa=$_GET; unset($qa['cat'],$qa['page']); $allUrl='/products'.(!empty($qa)?'?'.http_build_query($qa):'');
          $optsCat=[['url'=>$allUrl,'label'=>'Tất cả danh mục','sel'=>empty($_GET['cat'])]];
          foreach ($categories as $c){ $qc=$_GET; $qc['cat']=$c['slug']; unset($qc['page']); $optsCat[]=['url'=>'?'.http_build_query($qc),'label'=>$c['name'],'sel'=>(($_GET['cat']??'')===$c['slug'])]; }
          cdd('Danh mục',$optsCat);
          $qb=$_GET; unset($qb['brand_id'],$qb['page']); $allB='/products'.(!empty($qb)?'?'.http_build_query($qb):'');
          $optsBrand=[['url'=>$allB,'label'=>'Tất cả hãng xe','sel'=>empty($_GET['brand_id'])]];
          foreach (($brands??[]) as $b){ $qbb=$_GET; $qbb['brand_id']=$b['id']; unset($qbb['page']); $optsBrand[]=['url'=>'?'.http_build_query($qbb),'label'=>$b['name'],'sel'=>((string)($_GET['brand_id']??'')===(string)$b['id'])]; }
          cdd('Hãng xe',$optsBrand);
          $qp=$_GET; unset($qp['pb'],$qp['page']); $allP='/products'.(!empty($qp)?'?'.http_build_query($qp):'');
          $optsPb=[['url'=>$allP,'label'=>'Tất cả thương hiệu','sel'=>empty($_GET['pb'])]];
          foreach (($productBrands??[]) as $pbr){ $qpp=$_GET; $qpp['pb']=$pbr['name']; unset($qpp['page']); $optsPb[]=['url'=>'?'.http_build_query($qpp),'label'=>$pbr['name'],'sel'=>(($_GET['pb']??'')===$pbr['name'])]; }
          cdd('Thương hiệu',$optsPb);
          $optsSort=[];
          foreach (['newest'=>'Mới nhất','bestseller'=>'Bán chạy','price_asc'=>'Giá thấp','price_desc'=>'Giá cao','rating'=>'Đánh giá'] as $k=>$vv){ $q=$_GET; $q['sort']=$k; unset($q['page']); $optsSort[]=['url'=>'?'.http_build_query($q),'label'=>$vv,'sel'=>(($_GET['sort']??'newest')===$k)]; }
          cdd('Sắp xếp',$optsSort);
          ?>
        </div>
      </div>
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

</script>
<?php require __DIR__ . '/../partials/foot.php'; ?>
