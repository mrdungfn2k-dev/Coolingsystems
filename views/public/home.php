<?php $title = 'Trang chủ'; require __DIR__ . '/../partials/head.php'; ?>
<section class="hero-section">
  <div class="wrap">
    <aside class="cat-sidebar">
      <div class="head"><span class="lines"><span></span><span></span><span></span></span><span>Danh mục phụ tùng</span></div>
      <ul>
        <?php foreach ($sidebarCategories as $c): ?>
          <li class="<?= ($c['is_featured'] ?? 0) ? 'featured' : '' ?>"><a href="/products?cat=<?= e($c['slug']) ?>"><span><?= e($c['name']) ?></span><span class="arr">›</span></a></li>
        <?php endforeach; ?>
      </ul>
    </aside>
    <?php
      $heroBadge = dbGet("SELECT value FROM settings WHERE key='hero_badge'")['value'] ?? 'Phụ tùng & Dịch vụ Ô tô — Est. 2026';
      $heroHeading = dbGet("SELECT value FROM settings WHERE key='hero_heading'")['value'] ?? 'Phụ tùng <span class="accent">chính hãng</span><br>cho mọi hành trình.';
      $heroSubtext = dbGet("SELECT value FROM settings WHERE key='hero_subtext'")['value'] ?? '';
      $heroBtn1 = dbGet("SELECT value FROM settings WHERE key='hero_btn1_text'")['value'] ?? 'Khám phá sản phẩm';
      $heroBtn1Url = dbGet("SELECT value FROM settings WHERE key='hero_btn1_url'")['value'] ?? '/products';
      $heroBtn2 = dbGet("SELECT value FROM settings WHERE key='hero_btn2_text'")['value'] ?? 'Tư vấn miễn phí';
      $heroBtn2Url = dbGet("SELECT value FROM settings WHERE key='hero_btn2_url'")['value'] ?? '/contact';
      $heroBg = dbGet("SELECT value FROM settings WHERE key='hero_bg_image'")['value'] ?? '';
    ?>
    <div class="banner"<?= $heroBg ? ' style="background-image:url(/uploads/banners/'.$heroBg.');background-size:cover;background-position:center"' : '' ?>>
      <span class="badge"><?= $heroBadge ?></span>
      <h1><?= $heroHeading ?></h1>
      <p><?= $heroSubtext ?></p>
      <div class="actions"><a href="<?= e($heroBtn1Url) ?>" class="btn btn-gold btn-lg"><?= e($heroBtn1) ?></a><a href="<?= e($heroBtn2Url) ?>" class="btn btn-outline-light btn-lg"><?= e($heroBtn2) ?></a></div>
    </div>
    <aside class="vs-card">
      <div class="head"><h3>Tìm phụ tùng cho xe của bạn</h3><div class="sub">Chọn theo Hãng — Dòng — Năm</div></div>
      <form method="get" action="/products" id="vs-form">
        <div class="vs-field" style="margin-bottom:12px"><label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px">Từ khóa / Tên sản phẩm</label>
          <input type="text" name="q" placeholder="Nhập tên phụ tùng, mã OEM..." style="width:100%;height:38px;padding:8px 12px;border:1px solid #d0d5e0;border-radius:6px;font-size:13px">
        </div>
        <div class="vs-field" style="margin-bottom:12px"><label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px">Danh mục</label>
          <select name="cat" style="width:100%;height:38px;padding:8px 12px;border:1px solid #d0d5e0;border-radius:6px;font-size:13px">
            <option value="">— Tất cả danh mục —</option>
            <?php foreach ($sidebarCategories as $c): ?><option value="<?= e($c['slug']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="vs-field" style="margin-bottom:12px"><label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px">Thương hiệu SP</label>
          <select name="pb" style="width:100%;height:38px;padding:8px 12px;border:1px solid #d0d5e0;border-radius:6px;font-size:13px">
            <option value="">— Tất cả thương hiệu —</option>
            <?php if(!empty($productBrands)): foreach ($productBrands as $pb): ?><option value="<?= e($pb['name']) ?>"><?= e($pb['name']) ?></option><?php endforeach; endif; ?>
          </select>
        </div>
        <div class="vs-field" style="margin-bottom:12px"><label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px">Hãng xe</label>
          <select name="brand_id" id="vs-brand" style="width:100%;height:38px;padding:8px 12px;border:1px solid #d0d5e0;border-radius:6px;font-size:13px"><option value="">— Tất cả hãng xe —</option>
            <?php foreach ($brands as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="vs-field" style="margin-bottom:12px"><label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px">Dòng xe</label><select name="model_id" id="vs-model" disabled style="width:100%;height:38px;padding:8px 12px;border:1px solid #d0d5e0;border-radius:6px;font-size:13px;background:#f8f9fa"><option value="">— Chọn hãng trước —</option></select></div>
        <div class="vs-field" style="margin-bottom:12px"><label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px">Năm SX</label><select name="year" id="vs-year" disabled style="width:100%;height:38px;padding:8px 12px;border:1px solid #d0d5e0;border-radius:6px;font-size:13px;background:#f8f9fa"><option value="">— Chọn dòng trước —</option></select></div>
        <button type="submit" class="btn btn-navy btn-block" style="margin-top:12px;height:42px;font-size:14px">Tìm kiếm phụ tùng</button>
      </form>
    </aside>
  </div>
</section>

<section class="trust" id="cam-ket"><div class="wrap"><div class="trust-grid">
<?php
$trustSteps = dbAll("SELECT * FROM trust_steps WHERE is_active=1 ORDER BY sort_order ASC, id ASC");
if (empty($trustSteps)) {
    $trustSteps = [
        ['title'=>'Chính hãng OEM','description'=>'Đối tác xác minh giấy phép & nguồn hàng'],
        ['title'=>'Giao 24h toàn quốc','description'=>'Miễn phí ship đơn từ 2 triệu đồng'],
        ['title'=>'Bảo hành 6 — 24 tháng','description'=>'Theo tiêu chuẩn nhà sản xuất'],
        ['title'=>'Thanh toán linh hoạt','description'=>'COD, CK, Momo, ZaloPay, VNPay'],
    ];
}
$num = 1;
foreach ($trustSteps as $step):
    $title = $step['title'] ?? '';
    $desc = $step['description'] ?? $step['desc'] ?? '';
    $numStr = str_pad($num, 2, '0', STR_PAD_LEFT);
?>
<div class="trust-item"><div class="num"><?= $numStr ?></div><div><div class="ttl"><?= e($title) ?></div><div class="desc"><?= e($desc) ?></div></div></div>
<?php $num++; endforeach; ?>
</div></div></section>

<section class="block" id="products"><div class="wrap"><div class="sec-card">
  <div class="sec-head">
    <div class="title"><span class="bar"></span><h2>Sản phẩm nổi bật</h2></div>
    <div class="sec-tabs"><button class="active" data-target="featured">Sản phẩm mới</button><button data-target="bestseller">Bán chạy</button></div>
    <a href="/products?sort=newest" class="btn-link all-link" id="featuredViewAll">Xem tất cả  →</a>
  </div>
  <div class="prod-grid" data-tab="featured">
    <?php foreach ($featured as $p): ?><?php require __DIR__ . '/partials/prod-card.php'; ?><?php endforeach; ?>
  </div>
  <div class="prod-grid" data-tab="bestseller" style="display:none">
    <?php if (empty($bestSellers)): ?>
      <div style="grid-column:1/-1;text-align:center;padding:40px 20px;color:var(--ink-3)">
        <div style="font-size:36px;margin-bottom:8px">📊</div>
        <p style="font-size:14px;margin:0">Chưa có sản phẩm bán chạy (cần từ 10 đơn trở lên).</p>
        <p style="font-size:12px;color:var(--ink-4);margin-top:4px">Dữ liệu sẽ cập nhật khi có đủ đơn hàng.</p>
      </div>
    <?php else: ?>
      <?php foreach ($bestSellers as $p): ?><?php require __DIR__ . '/partials/prod-card.php'; ?><?php endforeach; ?>
    <?php endif; ?>
  </div>
</div></div></section>

<!-- Sản phẩm theo danh mục -->
<?php
$cats = dbAll("SELECT c.*, COUNT(p.id) AS cnt FROM categories c LEFT JOIN products p ON p.category_id=c.id AND p.status='published' GROUP BY c.id HAVING cnt > 0 ORDER BY cnt DESC LIMIT 4");
foreach ($cats as $cat):
  if (!isset($cat['cnt'])) {
    $cat['cnt'] = dbGet("SELECT COUNT(*) as n FROM products WHERE category_id=? AND status='published'", [$cat['id']])['n'] ?? 0;
  }
  $catProds = dbAll("SELECT p.*, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p WHERE p.category_id=? AND p.status='published' ORDER BY p.created_at DESC LIMIT 30", [$cat['id']]);
  if (empty($catProds)) continue;
?>
<section class="block"><div class="wrap"><div class="sec-card">
  <div class="sec-head">
    <div class="title"><span class="bar"></span><h2><?= e($cat['name']) ?></h2></div>
    <a href="/products?cat=<?= e($cat['slug']) ?>" class="btn-link all-link">Xem tất cả</a>
  </div>
  <div class="prod-grid cat-prod-grid" id="catGrid<?= $cat['id'] ?>" data-cat-id="<?= $cat['id'] ?>">
    <?php foreach ($catProds as $p): ?><?php require __DIR__ . '/partials/prod-card.php'; ?><?php endforeach; ?>
  </div>
  <div class="cat-paging" data-grid="catGrid<?= $cat['id'] ?>" data-cat-id="<?= $cat['id'] ?>" data-total="<?= $cat['cnt'] ?>" style="text-align:center;margin-top:12px"></div>
</div></div></section>
<?php endforeach; ?>


<!-- Phụ tùng theo hãng xe -->
<section class="block" id="brands-section"><div class="wrap"><div class="sec-card">
  <div class="sec-head">
    <div class="title"><span class="bar"></span><h2>Phụ tùng theo hãng xe</h2></div>
    <a href="/brands" class="btn-link all-link">Xem tất cả</a>
  </div>
  <style>
    .brands-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:16px; padding:8px 0; }
    .brand-card { display:flex; flex-direction:column; align-items:center; text-decoration:none; background:#fff; border:1px solid #e8ecf1; border-radius:12px; overflow:hidden; transition:all 0.3s; }
    .brand-card:hover { border-color:var(--navy); box-shadow:0 6px 20px rgba(26,50,88,0.12); transform:translateY(-3px); }
    .brand-card .brand-img-wrap { width:100%; height:120px; overflow:hidden; background:linear-gradient(135deg,#f0f4f8,#e2e8f0); display:flex; align-items:center; justify-content:center; }
    .brand-card .brand-img-wrap img { width:100%; height:100%; object-fit:cover; }
    .brand-card .brand-initial { font-size:28px; font-weight:900; color:#1a3258; opacity:0.6; letter-spacing:2px; }
    .brand-card .brand-name { padding:10px 8px 4px; font-size:14px; font-weight:700; color:#1a3258; text-align:center; }
    .brand-card .brand-count { padding:0 8px 10px; font-size:11px; color:#888; text-align:center; }
    @media(max-width:900px) { .brands-grid { grid-template-columns:repeat(3,1fr); gap:10px; } .brand-card .brand-img-wrap { height:90px; } }
    @media(max-width:480px) { .brands-grid { grid-template-columns:repeat(2,1fr); gap:8px; } }
  </style>
  <div class="brands-grid">
    <?php foreach ($brands as $b):
      $bCount = dbGet("SELECT COUNT(*) as n FROM products WHERE car_brand_id=? AND status='published'", [$b['id']])['n'] ?? 0;
    ?>
    <a href="/products?brand_id=<?= $b['id'] ?>" class="brand-card">
      <?php if (!empty($b['image'])): ?>
        <div class="brand-img-wrap"><img src="/uploads/brands/<?= e($b['image']) ?>" alt="<?= e($b['name']) ?>" loading="lazy"></div>
      <?php else: ?>
        <div class="brand-img-wrap"><span class="brand-initial"><?= strtoupper(mb_substr($b['name'], 0, 3)) ?></span></div>
      <?php endif; ?>
      <div class="brand-name"><?= e($b['name']) ?></div>
      <div class="brand-count"><?= $bCount ?> sản phẩm</div>
    </a>
    <?php endforeach; ?>
  </div>
</div></div></section>




<!-- JS: AJAX pagination for category product grids -->






<!-- Pager + Tab CSS -->
<style>
.hidden-card { display: none !important; }
.prod-pager { display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; padding: 8px 0; }
.prod-pager button { min-width: 36px; height: 36px; border: 1px solid #d0d5e0; border-radius: 6px; background: #fff; font-size: 13px; font-weight: 600; cursor: pointer; color: #1a3258; transition: all 0.2s; }
.prod-pager button.active { background: #1a3258; color: #fff; border-color: #1a3258; }
.prod-pager button:hover:not(.active) { background: #f0f2f7; border-color: #1a3258; }
.featured-paging { text-align: center; margin-top: 12px; }

/* Mobile price fix */
@media (max-width: 640px) {
  .prod-card .prod-price-row {
    flex-wrap: nowrap;
  }
  .prod-card .prod-price-row span[style*="font-size:15px"] {
    font-size: 13px !important;
  }
  .prod-card .prod-price-row span[style*="font-size:11px"] {
    font-size: 10px !important;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var isMobile = function() { return window.innerWidth <= 640; };
  
  /* === A. Tab switching === */
  document.querySelectorAll('.sec-tabs button[data-target]').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var target = this.getAttribute('data-target');
      var card = this.closest('.sec-card');
      if (!card) return;
      card.querySelectorAll('.sec-tabs button').forEach(function(b) { b.classList.remove('active'); });
      this.classList.add('active');
      card.querySelectorAll('.prod-grid[data-tab]').forEach(function(g) {
        g.style.display = (g.getAttribute('data-tab') === target) ? '' : 'none';
      });
      card.querySelectorAll('.featured-paging').forEach(function(fp) {
        fp.style.display = (fp.getAttribute('data-for-tab') === target) ? '' : 'none';
      });
      // Update "Xem tất cả" link based on active tab
      var viewAllLink = card.querySelector('#featuredViewAll');
      if (viewAllLink) {
        if (target === 'bestseller') {
          viewAllLink.href = '/products?sort=bestseller';
          viewAllLink.textContent = 'Xem tất cả  \u2192';
        } else {
          viewAllLink.href = '/products?sort=newest';
          viewAllLink.textContent = 'Xem tất cả  \u2192';
        }
      }
    });
  });

  /* === B. Featured pagination === */
  function initFeaturedPagination() {
    var perPage = isMobile() ? 4 : 10;
    document.querySelectorAll('.prod-grid[data-tab]').forEach(function(grid) {
      var tab = grid.getAttribute('data-tab');
      var cards = Array.from(grid.querySelectorAll('.prod-card'));
      var totalPages = Math.ceil(cards.length / perPage);
      var pagingEl = grid.parentNode.querySelector('.featured-paging[data-for-tab="' + tab + '"]');
      if (!pagingEl) {
        pagingEl = document.createElement('div');
        pagingEl.className = 'featured-paging';
        pagingEl.setAttribute('data-for-tab', tab);
        grid.parentNode.insertBefore(pagingEl, grid.nextSibling);
      }
      if (grid.style.display === 'none') pagingEl.style.display = 'none';
      if (totalPages <= 1) { pagingEl.innerHTML = ''; cards.forEach(function(c){c.classList.remove('hidden-card');}); return; }
      function showPage(page) {
        cards.forEach(function(c, i) {
          if (i >= (page-1)*perPage && i < page*perPage) c.classList.remove('hidden-card');
          else c.classList.add('hidden-card');
        });
        renderPager(pagingEl, page, totalPages, showPage);
      }
      showPage(1);
    });
  }

  /* === C. Category AJAX pagination === */
  function initCatPagination() {
    var perPage = isMobile() ? 4 : 10;
    document.querySelectorAll('.cat-paging').forEach(function(pagingEl) {
      var gridId = pagingEl.getAttribute('data-grid');
      var grid = document.getElementById(gridId);
      if (!grid) return;
      var catId = pagingEl.getAttribute('data-cat-id');
      var total = parseInt(pagingEl.getAttribute('data-total')) || 0;
      if (total <= 0) {
        // Fallback: count children in grid
        total = grid.querySelectorAll('.prod-card').length;
      }
      var totalPages = Math.ceil(total / perPage);
      if (totalPages <= 1) {
        pagingEl.innerHTML = '';
        Array.from(grid.querySelectorAll('.prod-card')).forEach(function(c) { c.classList.remove('hidden-card'); });
        return;
      }
      // Page 1: show first perPage items, hide the rest
      var cards = Array.from(grid.querySelectorAll('.prod-card'));
      cards.forEach(function(c, i) {
        if (i < perPage) c.classList.remove('hidden-card');
        else c.classList.add('hidden-card');
      });
      renderPager(pagingEl, 1, totalPages, function(page) {
        if (page === 1 && cards.length >= perPage) {
          // Show from DOM
          cards.forEach(function(c, i) {
            if (i < perPage) c.classList.remove('hidden-card');
            else c.classList.add('hidden-card');
          });
          renderPager(pagingEl, 1, totalPages, arguments.callee);
        } else {
          fetchCatPage(grid, catId, page, perPage, pagingEl, totalPages);
        }
      });
    });
  }

  function fetchCatPage(grid, catId, page, limit, pagingEl, totalPages) {
    grid.style.opacity = '0.5';
    fetch('/api/homepage-products?cat_id=' + catId + '&page=' + page + '&limit=' + limit)
      .then(function(r) { return r.json(); })
      .then(function(res) {
        grid.style.opacity = '';
        if (res.ok) {
          grid.innerHTML = res.html;
          renderPager(pagingEl, page, totalPages, function(p) {
            fetchCatPage(grid, catId, p, limit, pagingEl, totalPages);
          });
        }
      }).catch(function() { grid.style.opacity = ''; });
  }

  /* === D. Shared pager === */
  function renderPager(el, active, total, onClick) {
    el.innerHTML = '';
    var div = document.createElement('div'); div.className = 'prod-pager';
    for (var i = 1; i <= total; i++) {
      var btn = document.createElement('button');
      btn.textContent = i; btn.className = (i === active) ? 'active' : '';
      btn.setAttribute('data-page', i);
      btn.addEventListener('click', function() { onClick(parseInt(this.getAttribute('data-page'))); });
      div.appendChild(btn);
    }
    el.appendChild(div);
  }

  /* === E. Init === */
  initFeaturedPagination();
  initCatPagination();
  var lastM = isMobile();
  window.addEventListener('resize', function() {
    var now = isMobile();
    if (now !== lastM) { lastM = now; initFeaturedPagination(); initCatPagination(); }
  });
});
</script>

<?php 


require __DIR__ . '/../partials/foot.php'; ?>



