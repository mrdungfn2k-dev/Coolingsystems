<?php 
$title = 'Trang chủ'; 

$heroBg = dbGet("SELECT value FROM settings WHERE key='hero_bg_image'")['value'] ?? '';
$heroShowText = dbGet("SELECT value FROM settings WHERE key='hero_show_text'")['value'] ?? '0';
$heroBannerLink = dbGet("SELECT value FROM settings WHERE key='hero_banner_link'")['value'] ?? '';

$rawBannersList = json_decode(dbGet("SELECT value FROM settings WHERE key='home_banners_list'")['value'] ?? '[]', true);
if (empty($rawBannersList) && !empty($heroBg)) {
    $rawBannersList = [['img' => $heroBg, 'link' => $heroBannerLink]];
}
if (empty($rawBannersList)) {
    $rawBannersList = [
        ['img' => 'hero_cooling_banner_1.webp', 'link' => '/products'],
        ['img' => 'hero_cooling_banner_2.webp', 'link' => '/contact']
    ];
}

// Chuyển sang ảnh WebP nếu có sẵn tệp nén để tối ưu LCP < 1.0s
foreach ($rawBannersList as &$bnItem) {
    $webpName = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $bnItem['img']);
    $localWebp = __DIR__ . '/../../public/uploads/banners/' . $webpName;
    if (file_exists($localWebp)) {
        $bnItem['img'] = $webpName;
    }
}
unset($bnItem);

$firstBannerImg = $rawBannersList[0]['img'] ?? '';
$_customMetaTitle = (dbGet("SELECT value FROM system_config WHERE key='site_meta_title'") ?: [])['value'] ?? 'Cooling — Phụ Tùng & Dịch Vụ Ô Tô Chính Hãng | Hệ Thống Làm Mát Ô Tô';
$seo = [
    'meta_title' => $_customMetaTitle,
    'meta_description' => 'Cooling Systems - Chuyên cung cấp phụ tùng hệ thống điện lạnh và làm mát xe ô tô chính hãng toàn quốc: Dàn lạnh, dàn nóng, lốc điều hòa, quạt gió ô tô. Bảo hành uy tín.',
    'preload_image' => !empty($firstBannerImg) ? '/uploads/banners/' . $firstBannerImg : ''
];

require __DIR__ . '/../partials/head.php'; 
?>
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
    ?>

    <?php if ($heroShowText === '0'): ?>
      <!-- Chế độ TẮT chữ: Slidesshow Banner Đồ Họa Tự Động Chuyển Tiếp -->
      <div class="banner pure-image-banner hero-slider-wrap" id="heroSliderWrap" style="padding:0;overflow:hidden;background:#0f172a;position:relative;border-radius:12px;width:100%;aspect-ratio:16/7;min-height:220px">
        <div class="hero-slides-container" style="position:relative;width:100%;height:100%">
          <?php foreach ($rawBannersList as $idx => $bn): 
            $mobImg = preg_replace('/\.webp$/i', '_mob.webp', $bn['img']);
            if (!file_exists(__DIR__ . '/../../public/uploads/banners/' . $mobImg)) {
                $mobImg = $bn['img'];
            }
          ?>
            <div class="hero-slide-item <?= $idx === 0 ? 'active' : '' ?>" style="position:absolute;inset:0;opacity:<?= $idx === 0 ? '1' : '0' ?>;transition:opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s ease;z-index:<?= $idx === 0 ? '2' : '1' ?>;pointer-events:<?= $idx === 0 ? 'auto' : 'none' ?>">
              <?php if (!empty($bn['link'])): ?>
                <a href="<?= e($bn['link']) ?>" style="display:block;width:100%;height:100%">
                  <picture>
                    <source media="(max-width: 640px)" srcset="/uploads/banners/<?= e($mobImg) ?>" type="image/webp">
                    <source media="(min-width: 641px)" srcset="/uploads/banners/<?= e($bn['img']) ?>" type="image/webp">
                    <img src="/uploads/banners/<?= e($mobImg) ?>" alt="Banner phụ tùng làm mát <?= $idx + 1 ?>" width="640" height="280" <?= $idx === 0 ? 'fetchpriority="high" loading="eager"' : 'loading="lazy"' ?> style="width:100%;height:100%;object-fit:cover;object-position:center;display:block">
                  </picture>
                </a>
              <?php else: ?>
                <picture>
                  <source media="(max-width: 640px)" srcset="/uploads/banners/<?= e($mobImg) ?>" type="image/webp">
                  <source media="(min-width: 641px)" srcset="/uploads/banners/<?= e($bn['img']) ?>" type="image/webp">
                  <img src="/uploads/banners/<?= e($mobImg) ?>" alt="Banner phụ tùng làm mát <?= $idx + 1 ?>" width="640" height="280" <?= $idx === 0 ? 'fetchpriority="high" loading="eager"' : 'loading="lazy"' ?> style="width:100%;height:100%;object-fit:cover;object-position:center;display:block">
                </picture>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (count($rawBannersList) > 1): ?>
          <!-- Chấm tròn chuyển slide -->
          <div class="hero-slider-dots" style="position:absolute;bottom:14px;left:50%;transform:translateX(-50%);z-index:10;display:flex;gap:8px">
            <?php foreach ($rawBannersList as $idx => $bn): ?>
              <button type="button" onclick="setHeroSlide(<?= $idx ?>)" aria-label="Chuyển sang banner số <?= $idx + 1 ?>" class="hero-dot-btn <?= $idx === 0 ? 'active' : '' ?>" style="width:<?= $idx === 0 ? '28px' : '10px' ?>;height:6px;border-radius:4px;border:none;background:<?= $idx === 0 ? '#ffffff' : 'rgba(255,255,255,0.7)' ?>;cursor:pointer;transition:all 0.3s ease"></button>
            <?php endforeach; ?>
          </div>
          <!-- Nút bấm qua trái / qua phải -->
          <button type="button" onclick="prevHeroSlide()" aria-label="Xem banner trước đó" class="hero-nav-btn prev" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);z-index:10;width:36px;height:36px;border-radius:50%;background:rgba(15,23,42,0.65);color:#fff;border:none;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s">&lsaquo;</button>
          <button type="button" onclick="nextHeroSlide()" aria-label="Xem banner tiếp theo" class="hero-nav-btn next" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);z-index:10;width:36px;height:36px;border-radius:50%;background:rgba(15,23,42,0.65);color:#fff;border:none;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s">&rsaquo;</button>
        <?php endif; ?>
      </div>

      <script>
      (function(){
        var slides = document.querySelectorAll('.hero-slide-item');
        var dots = document.querySelectorAll('.hero-dot-btn');
        if (!slides.length || slides.length <= 1) return;
        var current = 0;
        var timer = null;

        window.setHeroSlide = function(idx) {
          current = idx;
          slides.forEach(function(s, i) {
            if (i === current) {
              s.style.opacity = '1';
              s.style.zIndex = '2';
              s.style.pointerEvents = 'auto';
            } else {
              s.style.opacity = '0';
              s.style.zIndex = '1';
              s.style.pointerEvents = 'none';
            }
          });
          dots.forEach(function(d, i) {
            d.style.background = i === current ? '#ffffff' : 'rgba(255,255,255,0.4)';
            d.style.width = i === current ? '28px' : '10px';
          });
        };

        window.nextHeroSlide = function() {
          var next = (current + 1) % slides.length;
          setHeroSlide(next);
        };

        window.prevHeroSlide = function() {
          var prev = (current - 1 + slides.length) % slides.length;
          setHeroSlide(prev);
        };

        function startAuto() { stopAuto(); timer = setInterval(nextHeroSlide, 4500); }
        function stopAuto() { if (timer) clearInterval(timer); }

        var wrap = document.getElementById('heroSliderWrap');
        if (wrap) {
          wrap.addEventListener('mouseenter', stopAuto);
          wrap.addEventListener('mouseleave', startAuto);
        }
        startAuto();
      })();
      </script>
    <?php else: ?>
      <!-- Chế độ BẬT chữ đè lên banner -->
      <div class="banner" style="overflow:hidden<?= $heroBg ? ';background-image:url(/uploads/banners/'.$heroBg.');background-size:cover;background-position:center' : '' ?>">
        <span class="badge"><?= $heroBadge ?></span>
        <h1><?= $heroHeading ?></h1>
        <p><?= $heroSubtext ?></p>
        <div class="actions"><a href="<?= e($heroBtn1Url) ?>" class="btn btn-gold btn-lg"><?= e($heroBtn1) ?></a><a href="<?= e($heroBtn2Url) ?>" class="btn btn-outline-light btn-lg"><?= e($heroBtn2) ?></a></div>
      </div>
    <?php endif; ?>
    <aside class="vs-card">
      <div class="head"><h2>Tìm phụ tùng cho xe của bạn</h2><div class="sub" style="color:#0f172a;font-weight:600;font-size:12.5px;margin-top:2px">Tìm theo Danh mục, Hãng xe & Thương hiệu</div></div>
      <form method="get" action="/products" id="vs-form">
        <?php
          $curU = currentUser();
          $userGaragesList = [];
          if ($curU) {
              $userGaragesList = dbAll("SELECT g.*, b.name AS brand_name, m.name AS model_name FROM garages g LEFT JOIN brands b ON b.id=g.brand_id LEFT JOIN car_models m ON m.id=g.model_id WHERE g.user_id=? ORDER BY g.is_default DESC, g.id DESC", [$curU['id']]);
          }
          $isGarageUser = $curU && (!empty($curU['garage_name']) || !empty($curU['garage_tier']) || !empty($userGaragesList));
        ?>
        <?php if ($isGarageUser && !empty($userGaragesList)): ?>
          <div class="vs-field">
            <label style="display:flex; align-items:center; justify-content:space-between; width:100%;">
              <span>XE ĐANG SỬA (GARA CỦA TÔI)</span>
              <a href="/customer/profile" style="font-size:11px; font-weight:600; color:#2563eb; text-decoration:none; text-transform:none;">+ Quản lý xe</a>
            </label>
            <?php
              $activeCarId = $_SESSION['active_garage_car_id'] ?? 0;
              $activeCarLabel = '— Chọn xe đang sửa trong Gara —';
              foreach ($userGaragesList as $c) {
                  if ($c['id'] == $activeCarId || (empty($activeCarId) && !empty($c['is_default']))) {
                      $activeCarLabel = $c['brand_name'] . ' ' . $c['model_name'] . ' (' . $c['year'] . ')';
                      break;
                  }
              }
            ?>
            <input type="hidden" name="garage_car_id" id="vsi-garage-car" value="<?= $activeCarId ?>">
            <div class="cdd" data-target="vsi-garage-car">
              <button type="button" class="cdd-trigger" onclick="vsCddToggle(this)" aria-label="Chọn xe đang sửa trong Gara"><span class="cdd-label"><?= e($activeCarLabel) ?></span><svg class="cdd-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg></button>
              <div class="cdd-panel">
                <div class="cdd-opt" data-val="" onclick="setActiveGarageCarForm(0, 0); vsCddPick(this);">— Tất cả xe Gara —</div>
                <?php foreach ($userGaragesList as $c): ?>
                  <div class="cdd-opt <?= ($c['id'] == $activeCarId) ? 'sel' : '' ?>" data-val="<?= $c['id'] ?>" onclick="setActiveGarageCarForm(<?= $c['id'] ?>, <?= $c['brand_id'] ?>); vsCddPick(this);">
                    <?= e($c['brand_name']) ?> <?= e($c['model_name']) ?> (<?= e($c['year']) ?>)
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <script>
          function setActiveGarageCarForm(carId, brandId) {
            if (brandId > 0) {
              var brandOpt = document.querySelector('#vs-form .cdd[data-target="vsi-brand"] .cdd-opt[data-val="' + brandId + '"]');
              if (brandOpt) vsCddPick(brandOpt);
            }
            var fd = new FormData();
            fd.append('_csrf', '<?= csrfToken() ?>');
            fd.append('car_id', carId);
            fetch('/api/set-active-garage-car', {method: 'POST', body: fd});
          }
          </script>
        <?php endif; ?>
        <div class="vs-field">
          <label>Từ khóa / Tên sản phẩm</label>
          <input type="text" name="q" placeholder="Nhập tên phụ tùng, mã OEM..." class="vs-input" aria-label="Nhập tên phụ tùng hoặc mã OEM">
        </div>
        <div class="vs-field">
          <label>Danh mục</label>
          <input type="hidden" name="cat" id="vsi-cat" value="">
          <div class="cdd" data-target="vsi-cat">
            <button type="button" class="cdd-trigger" onclick="vsCddToggle(this)" aria-label="Chọn Danh mục phụ tùng"><span class="cdd-label">— Tất cả danh mục —</span><svg class="cdd-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg></button>
            <div class="cdd-panel">
              <div class="cdd-opt sel" data-val="" onclick="vsCddPick(this)">— Tất cả danh mục —</div>
              <?php foreach ($sidebarCategories as $c): ?><div class="cdd-opt" data-val="<?= e($c['slug']) ?>" onclick="vsCddPick(this)"><?= e($c['name']) ?></div><?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="vs-field">
          <label>Thương hiệu SP</label>
          <input type="hidden" name="pb" id="vsi-pb" value="">
          <div class="cdd" data-target="vsi-pb">
            <button type="button" class="cdd-trigger" onclick="vsCddToggle(this)" aria-label="Chọn Thương hiệu sản phẩm"><span class="cdd-label">— Tất cả thương hiệu —</span><svg class="cdd-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg></button>
            <div class="cdd-panel">
              <div class="cdd-opt sel" data-val="" onclick="vsCddPick(this)">— Tất cả thương hiệu —</div>
              <?php if(!empty($productBrands)): foreach ($productBrands as $pbr): ?><div class="cdd-opt" data-val="<?= e($pbr['name']) ?>" onclick="vsCddPick(this)"><?= e($pbr['name']) ?></div><?php endforeach; endif; ?>
            </div>
          </div>
        </div>
        <div class="vs-field">
          <label>Hãng xe</label>
          <input type="hidden" name="brand_id" id="vsi-brand" value="">
          <div class="cdd" data-target="vsi-brand">
            <button type="button" class="cdd-trigger" onclick="vsCddToggle(this)" aria-label="Chọn Hãng xe"><span class="cdd-label">— Tất cả hãng xe —</span><svg class="cdd-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg></button>
            <div class="cdd-panel">
              <div class="cdd-opt sel" data-val="" onclick="vsCddPick(this)">— Tất cả hãng xe —</div>
              <?php foreach ($brands as $b): ?><div class="cdd-opt" data-val="<?= $b['id'] ?>" onclick="vsCddPick(this)"><?= e($b['name']) ?></div><?php endforeach; ?>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-navy btn-block" style="margin-top:14px;height:44px;font-size:14px">Tìm kiếm phụ tùng</button>
      </form>
    </aside>
  </div>
</section>

<?php
/* ===== Banner carousel trang chủ (admin quản lý ở /admin/banners) ===== */
$__bnRaw = dbGet("SELECT value FROM system_config WHERE key='home_banners'")['value'] ?? '[]';
$__homeBanners = array_values(array_filter(json_decode($__bnRaw, true) ?: [], function($b){ return !empty($b['active']) && !empty($b['img']); }));
?>
<?php if(!empty($__homeBanners)): ?>
<section class="home-banners"><div class="wrap">
  <div class="hbc" id="homeBannerCarousel">
    <div class="hbc-track">
      <?php foreach($__homeBanners as $__banIdx => $b): 
        $imgName = $b['img'];
        $bWebp = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $imgName);
        $webpPath = __DIR__ . '/../../uploads/banners/' . $bWebp;
        $finalImg = is_file($webpPath) ? $bWebp : $imgName;
        $fullPath = __DIR__ . '/../../uploads/banners/' . $finalImg;
        $src = '/uploads/banners/' . e($finalImg) . (is_file($fullPath) ? '?v=' . filemtime($fullPath) : '');
        $alt = e($b['title'] ?? ''); 
      ?>
      <div class="hbc-slide">
        <?php if(!empty($b['link'])): ?><a href="<?= e($b['link']) ?>"><img src="<?= $src ?>" alt="<?= $alt ?>" width="800" height="200" loading="lazy" decoding="async"></a>
        <?php else: ?><img src="<?= $src ?>" alt="<?= $alt ?>" width="800" height="200" loading="lazy" decoding="async"><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if(count($__homeBanners) > 1): ?>
    <button class="hbc-nav prev" type="button" onclick="hbcMove(-1)" aria-label="Banner trước">&#8249;</button>
    <button class="hbc-nav next" type="button" onclick="hbcMove(1)" aria-label="Banner sau">&#8250;</button>
    <div class="hbc-dots"><?php foreach($__homeBanners as $i=>$b): ?><span class="hbc-dot<?= $i===0?' on':'' ?>" onclick="hbcGo(<?= $i ?>)"></span><?php endforeach; ?></div>
    <?php endif; ?>
  </div>
</div></section>
<script>
(function(){
  var car = document.getElementById('homeBannerCarousel'); if(!car) return;
  var track = car.querySelector('.hbc-track');
  var slides = car.querySelectorAll('.hbc-slide');
  var dots = car.querySelectorAll('.hbc-dot');
  var n = slides.length, cur = 0, timer = null;
  window.hbcGo = function(i){ cur = (i % n + n) % n; if(track) track.style.transform = 'translateX(-' + (cur*100) + '%)'; for(var k=0;k<dots.length;k++) dots[k].classList.toggle('on', k===cur); };
  window.hbcMove = function(d){ hbcGo(cur + d); start(); };
  function start(){ if(timer){ clearInterval(timer); } if(n > 1){ timer = setInterval(function(){ if(!document.getElementById('homeBannerCarousel')){ clearInterval(timer); return; } hbcGo(cur + 1); }, 5000); } }
  start();
})();
</script>
<?php endif; ?>

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

<?php 
  $curU = currentUser();
  $isVerifiedGara = !empty($curU['is_verified_garage']) || !empty($curU['garage_name']) || $curU['role'] === 'garage';
  if (!$isVerifiedGara && !empty($curU['id'])) {
    $approvedReg = dbGet("SELECT id FROM garage_registrations WHERE user_id=? AND status='approved' LIMIT 1", [$curU['id']]);
    if (!empty($approvedReg)) $isVerifiedGara = true;
  }
?>

<style>
@media (max-width: 768px) {
  .gara-home-banner {
    flex-direction: column !important;
    text-align: center !important;
    padding: 16px !important;
  }
  .gara-home-banner a, .gara-home-banner button {
    width: 100% !important;
    justify-content: center !important;
  }
  .gara-modal-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>

<!-- Banner Cam Kết Giá Buôn Gara / Đại Lý -->
<section class="block" style="padding: 10px 0 0 0;">
  <div class="wrap">
    <?php if ($isVerifiedGara): ?>
      <div class="gara-home-banner" style="background: #0b1d3a; border-radius: 12px; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; box-shadow: 0 4px 15px rgba(11,29,58,0.15);">
        <div style="font-size: 15px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 0.5px;">
          TÀI KHOẢN CỦA BẠN ĐÃ XÁC THỰC GARA / ĐẠI LÝ — ĐÃ KÍCH HOẠT BẢNG GIÁ BUÔN GỐC
        </div>
        <a href="/customer/profile" style="background: #c9a14a; color: #0b1d3a; font-weight: 800; font-size: 13.5px; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; transition: transform 0.2s, background 0.2s;" onmouseover="this.style.background='#d4af5f';this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#c9a14a';this.style.transform='translateY(0)'">
          Xem hồ sơ &amp; danh sách xe &rarr;
        </a>
      </div>
    <?php else: ?>
      <div class="gara-home-banner" style="background: #0b1d3a; border-radius: 12px; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; box-shadow: 0 4px 15px rgba(11,29,58,0.15);">
        <div style="font-size: 16px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 0.5px;">
          Đăng ký tài khoản Gara / Đại lý — Nhận bảng giá chiết khấu buôn gốc
        </div>
        <button type="button" onclick="openGarageRegisterModal()" style="background: #c9a14a; color: #0b1d3a; font-weight: 800; font-size: 13.5px; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; transition: transform 0.2s, background 0.2s;" onmouseover="this.style.background='#d4af5f';this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#c9a14a';this.style.transform='translateY(0)'">
          Đăng ký Gara / Nhận giá buôn &rarr;
        </button>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Modal Đăng Ký Gara / Đại Lý Trực Tuyến -->
<div id="garageRegisterModal" style="display:none; position:fixed; inset:0; background:rgba(11,29,58,0.75); backdrop-filter:blur(4px); z-index:99999; align-items:center; justify-content:center; padding:16px; overflow-y:auto;">
  <div style="background:#fff; border-radius:16px; max-width:680px; width:100%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 50px rgba(0,0,0,0.3); position:relative; margin:auto;">
    
    <!-- Modal Header -->
    <div style="background:#0b1d3a; color:#fff; padding:18px 24px; border-radius:16px 16px 0 0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:10;">
      <div>
        <h3 style="margin:0; font-size:18px; color:#fff; font-weight:800;">ĐĂNG KÝ TÀI KHOẢN GARA / ĐẠI LÝ</h3>
        <p style="margin:4px 0 0; font-size:12.5px; color:#c9a14a;">Áp dụng Bảng giá buôn gốc &amp; Chính sách công nợ gối đầu</p>
      </div>
      <button type="button" onclick="closeGarageRegisterModal()" style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
    </div>

    <form method="post" action="/customer/garage-register" enctype="multipart/form-data" style="padding:24px;" onsubmit="return validateHomeGarageForm(this)">
      <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">

      <!-- Khối Điều kiện & Đặc quyền -->
      <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:16px; margin-bottom:20px; font-size:13px; color:#334155; line-height:1.6;">
        <div style="font-weight:800; color:#0b1d3a; font-size:14px; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">
          📋 Điều kiện xét duyệt &amp; Ưu đãi đặc quyền
        </div>
        <div style="margin-bottom:6px;">
          <strong style="color:#0b1d3a;">01. Điều kiện xét duyệt:</strong> Có địa chỉ Gara/Cửa hàng thực tế và cung cấp Mã số thuế / MS HKD (<span style="color:#dc2626; font-weight:700;">Bắt buộc</span>).
        </div>
        <div style="margin-bottom:6px;">
          <strong style="color:#0b1d3a;">03. Điều kiện duy trì:</strong> Doanh số đạt từ <strong>100 triệu đồng/tháng</strong>.
        </div>
        <div style="background:#eff6ff; border-left:3px solid #2563eb; padding:8px 12px; margin-top:8px; border-radius:0 4px 4px 0; color:#1e40af; font-size:12.5px;">
          🎁 <strong>Ưu đãi đặc quyền:</strong> Được áp dụng chính sách công nợ (gối đầu đơn hàng sau hoặc thanh toán định kỳ cuối mỗi tháng).
        </div>
      </div>

      <!-- Form Fields -->
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:5px;">Tên Gara / Cửa hàng <span style="color:#dc2626;">*</span></label>
          <input type="text" name="garage_name" required placeholder="VD: Gara Ô Tô Minh Phát" value="<?= e($curU['garage_name'] ?? '') ?>" style="width:100%; height:40px; border:1px solid #cbd5e1; border-radius:6px; padding:0 12px; font-size:13.5px; box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:5px;">Họ tên Chủ Gara / Đại diện <span style="color:#dc2626;">*</span></label>
          <input type="text" name="owner_name" required placeholder="VD: Nguyễn Văn Minh" value="<?= e($curU['full_name'] ?? '') ?>" style="width:100%; height:40px; border:1px solid #cbd5e1; border-radius:6px; padding:0 12px; font-size:13.5px; box-sizing:border-box;">
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:5px;">Số điện thoại liên hệ <span style="color:#dc2626;">*</span></label>
          <input type="tel" name="phone" required placeholder="VD: 0987654321" value="<?= e($curU['phone'] ?? '') ?>" style="width:100%; height:40px; border:1px solid #cbd5e1; border-radius:6px; padding:0 12px; font-size:13.5px; box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:5px;">Mã số thuế / MS HKD <span style="color:#dc2626;">* (Bắt buộc)</span></label>
          <input type="text" name="tax_code" required maxlength="14" placeholder="VD: 0101234567 hoặc 0101234567-001" style="width:100%; height:40px; border:1px solid #cbd5e1; border-radius:6px; padding:0 12px; font-size:13.5px; box-sizing:border-box;">
          <div style="font-size:11px; color:#64748b; margin-top:3px;">Cấu trúc 10 hoặc 13 chữ số (VD: 0101234567 hoặc 0101234567-001)</div>
        </div>
      </div>

      <div style="margin-bottom:18px;">
        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:5px;">Địa chỉ Gara / Cửa hàng thực tế <span style="color:#dc2626;">*</span></label>
        <input type="text" name="address" required placeholder="Số nhà, Tên đường, Phường/Xã, Quận/Huyện, Tỉnh/Thành..." value="<?= e($curU['address'] ?? '') ?>" style="width:100%; height:40px; border:1px solid #cbd5e1; border-radius:6px; padding:0 12px; font-size:13.5px; box-sizing:border-box;">
      </div>

      <!-- Uploads Section -->
      <div style="border-top:1px solid #e2e8f0; padding-top:16px; margin-top:16px;">
        <div style="font-weight:800; color:#0b1d3a; font-size:14px; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
          <span>📸</span> TẢI LÊN HỒ SƠ XÁC THỰC PHÁP LÝ
        </div>

        <div style="margin-bottom:14px;">
          <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:4px;">1. Ảnh bảng hiệu Cửa hàng / Gara <span style="color:#dc2626;">* (Bắt buộc)</span></label>
          <div style="font-size:11.5px; color:#64748b; margin-bottom:6px;">Chụp rõ tên Gara, địa chỉ &amp; SĐT trên bảng hiệu mặt tiền.</div>
          <input type="file" name="signboard_image" accept="image/*,.pdf" required onchange="previewSingleFile(this, 'homeSignboardPreview')" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#f8fafc; box-sizing:border-box;">
          <div id="homeSignboardPreview" style="margin-top:8px; display:none;"></div>
        </div>

        <div style="margin-bottom:14px;">
          <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:4px;">2. Giấy phép kinh doanh / Đăng ký HKD <span style="color:#dc2626;">* (Bắt buộc)</span></label>
          <div style="font-size:11.5px; color:#64748b; margin-bottom:6px;">Ảnh chụp hoặc file PDF Đăng ký kinh doanh / Mã số thuế HKD.</div>
          <input type="file" name="license_image" accept="image/*,.pdf" required onchange="previewSingleFile(this, 'homeLicensePreview')" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#f8fafc; box-sizing:border-box;">
          <div id="homeLicensePreview" style="margin-top:8px; display:none;"></div>
        </div>

        <div style="margin-bottom:18px;">
          <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:4px;">3. Tối thiểu 3 tấm ảnh chụp thực tế Cửa hàng / Gara <span style="color:#dc2626;">* (Bắt buộc ≥ 3 ảnh)</span></label>
          <div style="font-size:11.5px; color:#64748b; margin-bottom:6px;">Chụp các góc: Toàn cảnh xưởng, khu vực sửa chữa, kho hàng/kệ phụ tùng... (Để loại bỏ khách lẻ).</div>
          <input type="file" name="real_images[]" accept="image/*" multiple required id="homeRealImagesInput" onchange="previewMultiFiles(this, 'homeRealImagesPreview', 'homeRealImagesHint')" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#f8fafc; box-sizing:border-box;">
          <div id="homeRealImagesHint" style="font-size:12px; color:#dc2626; margin-top:4px; font-weight:600;"></div>
          <div id="homeRealImagesPreview" style="margin-top:10px; display:none; grid-template-columns:repeat(auto-fill, minmax(90px, 1fr)); gap:10px;"></div>
        </div>
      </div>

      <!-- Submit Footer -->
      <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px; border-top:1px solid #e2e8f0; padding-top:16px;">
        <button type="button" onclick="closeGarageRegisterModal()" class="btn btn-outline" style="padding:10px 20px; border:1px solid #cbd5e1; background:#fff; border-radius:6px; cursor:pointer;">Hủy</button>
        <button type="submit" class="btn btn-navy" style="padding:10px 24px; background:#0b1d3a; color:#fff; font-weight:800; border-radius:6px; border:none; cursor:pointer;">GỬI HỒ SƠ ĐĂNG KÝ GARA</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Popup: Cần Đăng Nhập (Design khớp 100% Ảnh 3 từ User) -->
<div id="requireLoginModal" style="display:none; position:fixed; inset:0; background:rgba(11,29,58,0.65); backdrop-filter:blur(4px); z-index:999999; align-items:center; justify-content:center; padding:16px;">
  <div style="background:#fff; border-radius:20px; max-width:420px; width:100%; padding:36px 28px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,0.25); margin:auto; position:relative;">
    
    <!-- User Icon Circle -->
    <div style="width:72px; height:72px; background:#e0f2fe; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#1a3258" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
    </div>

    <!-- Heading & Text -->
    <h2 style="font-size:22px; font-weight:800; color:#1a2b4c; margin:0 0 10px 0;">Cần đăng nhập</h2>
    <p style="color:#64748b; font-size:14.5px; margin:0 0 26px 0; line-height:1.5;">Bạn cần đăng nhập để đăng ký tài khoản Gara / Đại lý.</p>

    <!-- Action Buttons -->
    <div style="display:flex; gap:12px; justify-content:center;">
      <button type="button" onclick="closeRequireLoginModal()" style="flex:1; height:46px; border:1px solid #cbd5e1; background:#fff; color:#334155; font-weight:700; font-size:14.5px; border-radius:10px; cursor:pointer; transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
        Để sau
      </button>
      <a href="/auth/login?redirect=/customer/profile?action=garage-register" style="flex:1; height:46px; background:#1a3258; color:#fff; font-weight:800; font-size:14.5px; border-radius:10px; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; transition:background 0.2s;" onmouseover="this.style.background='#0b1d3a'" onmouseout="this.style.background='#1a3258'">
        Đăng nhập
      </a>
    </div>

  </div>
</div>

<script>
function openGarageRegisterModal() {
  <?php if (!empty($curU)): ?>
    var m = document.getElementById('garageRegisterModal');
    if (m) m.style.display = 'flex';
  <?php else: ?>
    var loginModal = document.getElementById('requireLoginModal');
    if (loginModal) loginModal.style.display = 'flex';
  <?php endif; ?>
}
function closeRequireLoginModal() {
  var modal = document.getElementById('requireLoginModal');
  if(modal) modal.style.display = 'none';
}
function closeGarageRegisterModal() {
  var modal = document.getElementById('garageRegisterModal');
  if(modal) modal.style.display = 'none';
}
function validateHomeGarageForm(form) {
  var garageName = form.querySelector('[name="garage_name"]');
  var ownerName = form.querySelector('[name="owner_name"]');
  var phone = form.querySelector('[name="phone"]');
  var taxCode = form.querySelector('[name="tax_code"]');
  var address = form.querySelector('[name="address"]');
  var signboard = form.querySelector('[name="signboard_image"]');
  var license = form.querySelector('[name="license_image"]');
  var realImages = form.querySelector('[name="real_images[]"]');

  if (!garageName || !garageName.value.trim()) {
    alert('⚠️ Vui lòng nhập Tên Gara / Cửa hàng!');
    if(garageName) garageName.focus();
    return false;
  }
  if (!ownerName || !ownerName.value.trim()) {
    alert('⚠️ Vui lòng nhập Họ tên Chủ Gara / Đại diện!');
    if(ownerName) ownerName.focus();
    return false;
  }
  if (!phone || !phone.value.trim()) {
    alert('⚠️ Vui lòng nhập Số điện thoại liên hệ!');
    if(phone) phone.focus();
    return false;
  }
  var taxCodeVal = taxCode ? taxCode.value.trim() : '';
  if (!taxCodeVal) {
    alert('⚠️ Vui lòng nhập Mã số thuế / MS HKD (Trường bắt buộc)!');
    if(taxCode) taxCode.focus();
    return false;
  }
  var taxClean = taxCodeVal.replace(/-/g, '');
  if (!/^\d{10}$|^\d{13}$/.test(taxClean)) {
    alert('⚠️ Mã số thuế / MS HKD không hợp lệ!\nMã số thuế do Cơ quan Thuế cấp phải gồm đúng 10 hoặc 13 chữ số.\nVí dụ hợp lệ: 0101234567 hoặc 0101234567-001');
    if(taxCode) taxCode.focus();
    return false;
  }
  if (!address || !address.value.trim()) {
    alert('⚠️ Vui lòng nhập Địa chỉ Gara / Cửa hàng thực tế!');
    if(address) address.focus();
    return false;
  }
  if (!signboard || !signboard.files || signboard.files.length === 0) {
    alert('⚠️ Vui lòng chọn Tải lên Ảnh bảng hiệu Cửa hàng / Gara!');
    if(signboard) signboard.focus();
    return false;
  }
  if (!license || !license.files || license.files.length === 0) {
    alert('⚠️ Vui lòng chọn Tải lên Giấy phép kinh doanh / Đăng ký HKD!');
    if(license) license.focus();
    return false;
  }
  if (!realImages || !realImages.files || realImages.files.length < 3) {
    alert('⚠️ Vui lòng tải lên tối thiểu 3 tấm ảnh chụp thực tế Cửa hàng / Gara!');
    if(realImages) realImages.focus();
    return false;
  }

  return true;
}
</script>

<!-- Khối 1: Sản phẩm Khuyến mại -->
<?php if (!empty($saleProducts)): ?>
<section class="block" id="sale-products"><div class="wrap"><div class="sec-card">
  <div class="sec-head">
    <div class="title"><span class="bar"></span><h2>SẢN PHẨM KHUYẾN MẠI</h2></div>
    <a href="/promotions" class="btn-link all-link">Xem tất cả &rarr;</a>
  </div>
  <div class="prod-grid" id="saleProdGrid">
    <?php foreach ($saleProducts as $p): ?><?php require __DIR__ . '/partials/prod-card.php'; ?><?php endforeach; ?>
  </div>
  <div class="sale-paging" id="salePaging" data-grid="saleProdGrid" data-total="<?= $saleTotal ?? 0 ?>" style="text-align:center;margin-top:16px"></div>
</div></div></section>
<?php endif; ?>

<!-- Khối 2: Sản phẩm Bán chạy -->
<?php if (!empty($bestSellers)): ?>
<section class="block" id="bestseller-products"><div class="wrap"><div class="sec-card">
  <div class="sec-head">
    <div class="title"><span class="bar"></span><h2>SẢN PHẨM BÁN CHẠY</h2></div>
    <a href="/products?sort=bestseller" class="btn-link all-link">Xem tất cả &rarr;</a>
  </div>
  <div class="prod-grid">
    <?php foreach ($bestSellers as $p): ?><?php require __DIR__ . '/partials/prod-card.php'; ?><?php endforeach; ?>
  </div>
</div></div></section>
<?php endif; ?>

<!-- Khối 3: Sản phẩm Mới -->
<?php if (!empty($featured)): ?>
<section class="block" id="new-products"><div class="wrap"><div class="sec-card">
  <div class="sec-head">
    <div class="title"><span class="bar"></span><h2>SẢN PHẨM MỚI</h2></div>
    <a href="/products?sort=newest" class="btn-link all-link">Xem tất cả &rarr;</a>
  </div>
  <div class="prod-grid">
    <?php foreach ($featured as $p): ?><?php require __DIR__ . '/partials/prod-card.php'; ?><?php endforeach; ?>
  </div>
</div></div></section>
<?php endif; ?>

<!-- Sản phẩm theo danh mục -->
<?php
$cats = dbAll("SELECT c.*, COUNT(p.id) AS cnt FROM categories c LEFT JOIN products p ON p.category_id=c.id AND p.status='published' WHERE (c.is_active=1 OR c.is_active IS NULL) GROUP BY c.id HAVING cnt > 0 ORDER BY cnt DESC LIMIT 6");
foreach ($cats as $cat):
  if (!isset($cat['cnt'])) {
    $cat['cnt'] = dbGet("SELECT COUNT(*) as n FROM products WHERE category_id=? AND status='published'", [$cat['id']])['n'] ?? 0;
  }
  $catProds = dbAll("SELECT p.*, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p WHERE p.category_id=? AND p.status='published' ORDER BY p.created_at DESC LIMIT 10", [$cat['id']]);
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
  
  <div class="brands-grid">
    <?php foreach ($brands as $b):
      $bCount = $b['real_count'] ?? $b['product_count'] ?? 0;
    ?>
    <a href="/products?brand_id=<?= $b['id'] ?>" class="brand-card <?= !empty($b['image']) ? 'has-image' : '' ?>">
      <div class="brand-img-wrap">
        <?php if (!empty($b['image'])): ?>
          <img src="/uploads/brands/<?= e($b['image']) ?>" alt="<?= e($b['name']) ?>" width="120" height="60" loading="lazy">
        <?php else: ?>
          <span class="brand-initial"><?= strtoupper(mb_substr($b['name'], 0, 3)) ?></span>
        <?php endif; ?>
      </div>
      <div class="brand-info">
        <div class="name"><?= e($b['name']) ?></div>
        <div class="count"><?= $bCount ?> sản phẩm</div>
      </div>
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
/* === Bộ lọc tìm phụ tùng (.cdd) trong vs-card === */
aside.vs-card { overflow: visible !important; position: relative !important; z-index: 20 !important; }
aside.vs-card form { overflow: visible !important; display: flex; flex-direction: column; position: relative; }
.vs-card .vs-field { position: relative !important; overflow: visible !important; margin-bottom: 12px; }
.vs-card .vs-field:nth-child(1) { z-index: 50 !important; }
.vs-card .vs-field:nth-child(2) { z-index: 40 !important; }
.vs-card .vs-field:nth-child(3) { z-index: 30 !important; }
.vs-card .vs-field:nth-child(4) { z-index: 20 !important; }
.vs-card .vs-field.open-field, .vs-card .vs-field:focus-within { z-index: 99999 !important; }

.vs-card .vs-field label { font-size:11.5px; font-weight:800 !important; color:#0f172a !important; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; display:block; }
.vs-card .vs-input, .vs-card .cdd-trigger { width:100%; height:42px; border:1.5px solid var(--line); border-radius:10px; background:#fff; color:var(--navy-dark); font-size:13.5px; font-weight:500; font-family:inherit; }
.vs-card .vs-input { padding:0 14px; transition:border-color .15s, box-shadow .15s; }
.vs-card .vs-input:hover { border-color:#b9c4d6; }
.vs-card .vs-input:focus { outline:none; border-color:var(--navy); box-shadow:0 0 0 3px rgba(26,50,88,.12); }

.vs-card .cdd { position:relative !important; z-index:10 !important; }
.vs-card .cdd.open { z-index:99999 !important; }
.vs-card .cdd-trigger { padding:0 12px 0 14px; display:flex; align-items:center; justify-content:space-between; gap:8px; cursor:pointer; transition:border-color .15s, box-shadow .15s; }
.vs-card .cdd-trigger:hover { border-color:#b9c4d6; }
.vs-card .cdd.open .cdd-trigger { border-color:var(--navy); box-shadow:0 0 0 3px rgba(26,50,88,.12); }
.vs-card .cdd-label { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; text-align:left; }
.vs-card .cdd-arrow { flex-shrink:0; color:#1a3258; transition:transform .2s; }
.vs-card .cdd.open .cdd-arrow { transform:rotate(180deg); }

.vs-card .cdd-panel {
  display: none !important;
  position: absolute !important;
  top: calc(100% + 4px) !important;
  left: 0 !important;
  right: 0 !important;
  width: 100% !important;
  background: #ffffff !important;
  border: 1px solid #cbd5e1 !important;
  border-radius: 10px !important;
  box-shadow: 0 14px 38px rgba(15,23,42,0.22) !important;
  overflow-y: auto !important;
  max-height: 230px !important;
  z-index: 100000 !important;
  padding: 6px !important;
  -webkit-overflow-scrolling: touch !important;
}
.vs-card .cdd.open .cdd-panel { display: block !important; }

.vs-card .cdd-opt { padding:10px 12px; border-radius:7px; font-size:13.5px; color:var(--navy-dark); cursor:pointer; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; transition:background .12s; }
.vs-card .cdd-opt:hover { background:#f1f5fb; }
.vs-card .cdd-opt.sel { background:var(--navy); color:#fff; font-weight:600; }

/* phân trang rút gọn (ellipsis) */
.prod-pager .pp-dots { min-width:24px; height:36px; display:inline-flex; align-items:center; justify-content:center; color:#9aa3b2; font-weight:700; }
.prod-pager button:disabled { opacity:.4; cursor:default; }
</style>
<script>
/* custom dropdown (form-mode) cho bộ lọc tìm phụ tùng trang chủ — mở tuyệt đối nổi bật không đè chữ */
function vsCddToggle(btn){
  var cdd = btn.closest('.cdd');
  var field = btn.closest('.vs-field');
  var open = cdd.classList.contains('open');
  document.querySelectorAll('.vs-card .cdd.open').forEach(function(x){ x.classList.remove('open'); });
  document.querySelectorAll('.vs-card .vs-field.open-field').forEach(function(x){ x.classList.remove('open-field'); });
  if(!open) {
    cdd.classList.add('open');
    if(field) field.classList.add('open-field');
  }
}
function vsCddPick(opt){
  var cdd = opt.closest('.cdd');
  var field = opt.closest('.vs-field');
  var t = document.getElementById(cdd.getAttribute('data-target'));
  if(t) t.value = opt.getAttribute('data-val') || '';
  var lbl = cdd.querySelector('.cdd-label'); if(lbl) lbl.textContent = opt.textContent;
  cdd.querySelectorAll('.cdd-opt').forEach(function(o){ o.classList.remove('sel'); });
  opt.classList.add('sel');
  cdd.classList.remove('open');
  if(field) field.classList.remove('open-field');
}
document.addEventListener('click', function(ev){
  if(!ev.target.closest('.vs-card .cdd')) {
    document.querySelectorAll('.vs-card .cdd.open').forEach(function(x){ x.classList.remove('open'); });
    document.querySelectorAll('.vs-card .vs-field.open-field').forEach(function(x){ x.classList.remove('open-field'); });
  }
});
</script>

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
    });
  });

  /* === B. Featured pagination === */
  function initFeaturedPagination() {
    var perPage = isMobile() ? 6 : 10;
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
    var perPage = isMobile() ? 6 : 10;
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

  /* === C2. Sale products AJAX pagination === */
  function initSalePagination() {
    var perPage = isMobile() ? 6 : 10;
    document.querySelectorAll('.sale-paging').forEach(function(pagingEl) {
      var gridId = pagingEl.getAttribute('data-grid');
      var grid = document.getElementById(gridId);
      if (!grid) return;
      var total = parseInt(pagingEl.getAttribute('data-total')) || 0;
      var totalPages = Math.ceil(total / perPage);
      if (totalPages <= 1) {
        pagingEl.innerHTML = '';
        return;
      }
      renderPager(pagingEl, 1, totalPages, function(page) {
        fetchSalePage(grid, page, perPage, pagingEl, totalPages);
      });
    });
  }

  function fetchSalePage(grid, page, limit, pagingEl, totalPages) {
    grid.style.opacity = '0.5';
    fetch('/api/homepage-products?type=sale&page=' + page + '&limit=' + limit)
      .then(function(r) { return r.json(); })
      .then(function(res) {
        grid.style.opacity = '';
        if (res.ok) {
          grid.innerHTML = res.html;
          renderPager(pagingEl, page, totalPages, function(p) {
            fetchSalePage(grid, p, limit, pagingEl, totalPages);
          });
        }
      }).catch(function() { grid.style.opacity = ''; });
  }

  /* === D. Shared pager === */
  function renderPager(el, active, total, onClick) {
    el.innerHTML = '';
    var div = document.createElement('div'); div.className = 'prod-pager';
    function mkBtn(label, page, isActive, isDisabled){
      var b = document.createElement('button');
      b.innerHTML = label;
      if (isActive) b.className = 'active';
      if (isDisabled) { b.disabled = true; }
      else { b.setAttribute('data-page', page); b.addEventListener('click', function(){ onClick(parseInt(this.getAttribute('data-page'), 10)); }); }
      div.appendChild(b);
    }
    function dots(){ var s = document.createElement('span'); s.className = 'pp-dots'; s.textContent = '\u2026'; div.appendChild(s); }
    mkBtn('\u2039', active - 1, false, active <= 1);
    var win = 2, pages = [1];
    if (active - win > 2) pages.push('...');
    for (var i = Math.max(2, active - win); i <= Math.min(total - 1, active + win); i++) pages.push(i);
    if (active + win < total - 1) pages.push('...');
    if (total > 1) pages.push(total);
    pages.forEach(function(p){ if (p === '...') dots(); else mkBtn(p, p, p === active, false); });
    mkBtn('\u203a', active + 1, false, active >= total);
    el.appendChild(div);
  }

  /* === E. Init (Yield main thread for 95+ Desktop TBT) === */
  var _runInit = function() {
    initFeaturedPagination();
    initCatPagination();
    initSalePagination();
  };
  if (typeof window.requestIdleCallback === 'function') {
    window.requestIdleCallback(_runInit, { timeout: 1000 });
  } else {
    setTimeout(_runInit, 120);
  }
  var lastM = isMobile();
  window.addEventListener('resize', function() {
    var now = isMobile();
    if (now !== lastM) { lastM = now; initFeaturedPagination(); initCatPagination(); initSalePagination(); }
  }, { passive: true });
});
</script>

<?php 


require __DIR__ . '/../partials/foot.php'; ?>



