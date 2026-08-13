<nav class="primary" id="primaryNav">
  <div class="wrap" id="navScrollWrap">
  <style>
  #navScrollWrap {
    overflow-x: auto;
    cursor: grab;
    user-select: none;
    -webkit-overflow-scrolling: touch;
    display: flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    padding-bottom: 0px;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.35) rgba(255,255,255,0.08);
  }
  #navScrollWrap .nav-link {
    padding: 0 11px !important;
    font-size: 11.5px !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
    letter-spacing: 0.02em !important;
  }
  #navScrollWrap::-webkit-scrollbar {
    height: 5px;
  }
  #navScrollWrap::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.08);
    border-radius: 3px;
  }
  #navScrollWrap::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.35);
    border-radius: 3px;
  }
  #navScrollWrap::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.55);
  }
  #navScrollWrap.dragging { cursor: grabbing; }
  #navScrollWrap .nav-link, #navScrollWrap .all-cats { flex-shrink: 0; }
  #navScrollWrap .all-cats-wrap { display: none !important; }

  @media (max-width: 768px) {
    nav.primary { background: #1a3258 !important; padding: 0 !important; overflow: hidden !important; width: 100% !important; }
    nav.primary .wrap, #navScrollWrap {
      display: flex !important;
      justify-content: flex-start !important;
      align-items: center !important;
      padding: 4px 8px 6px 8px !important;
      gap: 4px !important;
      scrollbar-width: none !important;
      width: 100% !important;
      box-sizing: border-box !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
      overflow-x: auto !important;
      -webkit-overflow-scrolling: touch !important;
    }
    #navScrollWrap::-webkit-scrollbar { display: none !important; }
    #navScrollWrap .all-cats-wrap { display: none !important; }
    #navScrollWrap .nav-link { display: inline-flex !important; padding: 6px 10px !important; font-size: 12px !important; font-weight: 700 !important; color: #fff !important; white-space: nowrap !important; flex: 0 0 auto !important; opacity: 1 !important; visibility: visible !important; margin: 0 !important; }
  }
  </style>

    <div class="all-cats-wrap" style="position:relative">
    <a href="javascript:void(0)" class="all-cats" style="text-decoration:none;color:#fff" onclick="this.parentElement.classList.toggle('open')">
      <span class="lines"><span></span><span></span><span></span></span>
      <span class="txt-desktop">Danh mục sản phẩm</span>
      <span class="txt-mobile">📁 Danh mục</span>
      <span class="arrow">▾</span>
    </a>
    <div class="cat-dropdown" style="display:none;position:absolute;top:100%;left:0;min-width:280px;background:#fff;border-radius:0 0 8px 8px;box-shadow:0 8px 24px rgba(0,0,0,0.18);z-index:999;max-height:420px;overflow-y:auto">
      <?php
        $navCats = dbAll("SELECT * FROM categories WHERE parent_id IS NULL AND (is_active=1 OR is_active IS NULL) ORDER BY is_featured DESC, sort_order, id");
        foreach($navCats as $nc):
          $iconVal = trim($nc['icon'] ?? '');
          $isImg = !empty($iconVal) && (preg_match('/\.(png|jpg|jpeg|webp|gif|svg)$/i', $iconVal) || str_starts_with($iconVal, 'cat_'));
      ?>
      <a href="/products?cat=<?= e($nc['slug']) ?>" style="display:flex;align-items:center;padding:11px 18px;color:#1a3258;text-decoration:none;font-size:13px;font-weight:600;border-bottom:1px solid #f0f0f0;transition:all 0.15s">
        <?php if(!empty($iconVal)): ?>
          <?php if($isImg): ?>
            <?php $imgSrc = str_starts_with($iconVal, '/') ? $iconVal : (str_starts_with($iconVal, 'uploads/') ? '/' . $iconVal : '/uploads/categories/' . $iconVal); ?>
            <img src="<?= e($imgSrc) ?>" alt="" style="width:20px;height:20px;object-fit:contain;margin-right:10px;flex-shrink:0" onerror="this.style.display='none'">
          <?php else: ?>
            <span style="margin-right:10px;font-size:16px"><?= e($iconVal) ?></span>
          <?php endif; ?>
        <?php endif; ?>
        <span><?= e($nc['name']) ?></span>
        <span style="margin-left:auto;color:#bbb;font-size:12px">›</span>
      </a>
      <?php endforeach; ?>
      <a href="/products" style="display:block;padding:12px 18px;text-align:center;color:#c8a951;font-weight:700;font-size:13px;text-decoration:none;background:#fafafa;border-top:2px solid #f0f0f0;border-radius:0 0 8px 8px">Xem tất cả sản phẩm →</a>
    </div>
  </div>
    <?php
      $visRows = dbAll("SELECT key, value FROM settings WHERE key LIKE 'page_visible_%'");
      $pVisNav = [];
      foreach ($visRows as $vr) {
          $pVisNav[str_replace('page_visible_', '', $vr['key'])] = $vr['value'];
      }
    ?>
    <a href="/" class="nav-link <?= isActive('/') ?>">Trang chủ</a>
    <?php if (($pVisNav['gioi-thieu'] ?? '1') !== '0'): ?>
      <a href="/about" class="nav-link <?= isActive('/about') ?>">Giới thiệu</a>
    <?php endif; ?>
    <a href="/products" class="nav-link <?= isActive('/products') ?>">Sản phẩm</a>
    <a href="/brands" class="nav-link <?= isActive('/brands') ?>">Phụ tùng theo xe</a>
    <a href="/product-brands" class="nav-link <?= isActive('/product-brands') ?>">Thương hiệu</a>
    <a href="/vouchers" class="nav-link <?= isActive('/vouchers') ?>">Khuyến mại</a>
    <?php if (($pVisNav['tin-tuc-tong-hop'] ?? '1') !== '0'): ?>
      <a href="/news" class="nav-link <?= isActive('/news') ?>">Tin tức</a>
    <?php endif; ?>
    <?php if (($pVisNav['he-thong-cua-hang'] ?? '1') !== '0'): ?>
      <a href="/stores" class="nav-link <?= isActive('/stores') ?>">Hệ thống cửa hàng</a>
    <?php endif; ?>
    <?php if (($pVisNav['chinh-sach-bao-hanh'] ?? '1') !== '0'): ?>
      <a href="/warranty/lookup" class="nav-link <?= isActive('/warranty/lookup') ?>">Tra bảo hành</a>
    <?php endif; ?>

  </div>
</nav>

<script>
(function(){
  var el = document.getElementById('navScrollWrap');
  if(!el) return;

  // Auto scroll active link into view on page load
  function scrollActiveIntoView() {
    var activeLink = el.querySelector('.nav-link.active');
    if (activeLink) {
      activeLink.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
  }

  // Preserve & restore navbar scroll position across clicks
  try {
    var savedPos = sessionStorage.getItem('navScrollPos');
    if (savedPos !== null && !el.querySelector('.nav-link.active')) {
      el.scrollLeft = parseInt(savedPos, 10);
    } else {
      scrollActiveIntoView();
    }
  } catch(e){
    scrollActiveIntoView();
  }

  window.addEventListener('load', scrollActiveIntoView);

  el.querySelectorAll('a').forEach(function(link){
    link.addEventListener('click', function(){
      try { sessionStorage.setItem('navScrollPos', el.scrollLeft); } catch(e){}
    });
  });

  el.addEventListener('scroll', function(){
    try { sessionStorage.setItem('navScrollPos', el.scrollLeft); } catch(e){}
  }, {passive:true});

  var isDragging=false, startX=0, scrollLeft=0;
  el.addEventListener('mousedown',function(e){
    isDragging=true; startX=e.pageX-el.offsetLeft; scrollLeft=el.scrollLeft;
    el.classList.add('dragging');
  });
  document.addEventListener('mouseup',function(){isDragging=false;el.classList.remove('dragging');});
  el.addEventListener('mouseleave',function(){isDragging=false;el.classList.remove('dragging');});
  el.addEventListener('mousemove',function(e){
    if(!isDragging)return; e.preventDefault();
    var x=e.pageX-el.offsetLeft; el.scrollLeft=scrollLeft-(x-startX);
  });
  // Touch support
  el.addEventListener('touchstart',function(e){startX=e.touches[0].pageX-el.offsetLeft;scrollLeft=el.scrollLeft;},{passive:true});
  el.addEventListener('touchmove',function(e){
    var x=e.touches[0].pageX-el.offsetLeft; el.scrollLeft=scrollLeft-(x-startX);
  },{passive:true});
})();
</script>
