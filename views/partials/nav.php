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
    white-space: nowrap;
    padding-bottom: 6px;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.35) rgba(255,255,255,0.08);
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
  </style>
    <div class="all-cats-wrap" style="position:relative">
    <a href="javascript:void(0)" class="all-cats" style="text-decoration:none;color:#fff" onclick="this.parentElement.classList.toggle('open')">
      <span class="lines"><span></span><span></span><span></span></span>
      <span>Danh mục sản phẩm</span><span class="arrow">▾</span>
    </a>
    <div class="cat-dropdown" style="display:none;position:absolute;top:100%;left:0;min-width:280px;background:#fff;border-radius:0 0 8px 8px;box-shadow:0 8px 24px rgba(0,0,0,0.18);z-index:999;max-height:420px;overflow-y:auto">
      <?php
        $navCats = dbAll("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY is_featured DESC, sort_order LIMIT 15");
        foreach($navCats as $nc):
      ?>
      <a href="/products?cat=<?= e($nc['slug']) ?>" style="display:flex;align-items:center;padding:11px 18px;color:#1a3258;text-decoration:none;font-size:13px;font-weight:600;border-bottom:1px solid #f0f0f0;transition:all 0.15s">
        <?php if(!empty($nc['icon'])): ?><span style="margin-right:10px;font-size:16px"><?= $nc['icon'] ?></span><?php endif; ?>
        <span><?= e($nc['name']) ?></span>
        <span style="margin-left:auto;color:#bbb;font-size:12px">›</span>
      </a>
      <?php endforeach; ?>
      <a href="/products" style="display:block;padding:12px 18px;text-align:center;color:#c8a951;font-weight:700;font-size:13px;text-decoration:none;background:#fafafa;border-top:2px solid #f0f0f0;border-radius:0 0 8px 8px">Xem tất cả sản phẩm →</a>
    </div>
  </div>
    <a href="/" class="nav-link <?= isActive('/') ?>">Trang chủ</a>
    <a href="/about" class="nav-link <?= isActive('/about') ?>">Giới thiệu</a>
    <a href="/products" class="nav-link <?= isActive('/products') ?>">Sản phẩm</a>
    <a href="/brands" class="nav-link <?= isActive('/brands') ?>">Phụ tùng theo hãng</a>
    <a href="/product-brands" class="nav-link <?= isActive('/product-brands') ?>">Thương hiệu</a>
    <a href="/promotions" class="nav-link <?= isActive('/promotions') ?>">Khuyến mại</a>
    <a href="/news" class="nav-link <?= isActive('/news') ?>">Tin tức</a>
    <!-- Chính sách moved to footer -->
    <a href="/stores" class="nav-link <?= isActive('/stores') ?>">Hệ thống cửa hàng</a>

  </div>

<script>
(function(){
  var el = document.getElementById('navScrollWrap');
  if(!el) return;
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
  // Auto-scroll to active nav link so it stays visible on page load
  var activeLink = el.querySelector('.nav-link.active');
  if (activeLink) {
    var elRect = el.getBoundingClientRect();
    var linkRect = activeLink.getBoundingClientRect();
    var scrollTarget = activeLink.offsetLeft - (elRect.width / 2) + (linkRect.width / 2);
    el.scrollLeft = Math.max(0, scrollTarget);
  }
})();
</script>
</nav>
