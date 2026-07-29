<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#1a3258">
<link rel="icon" type="image/png" sizes="48x48" href="/favicon-cooling-round-48x48.png?v=20260717-favicon-sync">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-cooling-round-32x32.png?v=20260717-favicon-sync">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-cooling-round-16x16.png?v=20260717-favicon-sync">
<link rel="shortcut icon" href="/favicon-cooling-round.ico?v=20260717-favicon-sync">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-cooling-round.png?v=20260717-favicon-sync">
<link rel="manifest" href="/site.webmanifest?v=20260717-favicon-sync">
<!-- Google tag (gtag.js) - Lazy load on interaction to eliminate TBT -->
<script>
(function() {
  var _gtagLoaded = false;
  function _loadGtag() {
    if (_gtagLoaded) return;
    _gtagLoaded = true;
    var s = document.createElement('script');
    s.src = 'https://www.googletagmanager.com/gtag/js?id=G-773TVBRSGE';
    s.async = true;
    document.head.appendChild(s);
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-773TVBRSGE');
  }
  ['touchstart', 'scroll', 'mousemove', 'keydown', 'pointerdown'].forEach(function(evt) {
    window.addEventListener(evt, _loadGtag, {once: true, passive: true});
  });
  setTimeout(_loadGtag, 6000);
})();
</script>
<!-- Performance: Preconnect Google Fonts & Preload LCP Image -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="image" href="/uploads/banners/hero_cooling_banner_1_mob.webp" fetchpriority="high" type="image/webp" media="(max-width: 640px)">
<link rel="preload" as="image" href="/uploads/banners/hero_cooling_banner_1.webp" fetchpriority="high" type="image/webp" media="(min-width: 641px)">
<?php
// Dynamic system config - load all site settings
$_sysConf = [];
$_sysRows = dbAll("SELECT key, value FROM system_config");
foreach ($_sysRows as $_sr) $_sysConf[$_sr['key']] = $_sr['value'];
$sysHotline = $_sysConf['site_phone'] ?? $_sysConf['contact_hotline'] ?? '0947796471';
$sysEmail = $_sysConf['contact_email'] ?? 'support@coolingsystems.vn';
$sysWhatsapp = $_sysConf['social_whatsapp'] ?? '';
$sysTiktok = $_sysConf['social_tiktok'] ?? '';
$sysFacebook = $_sysConf['social_facebook'] ?? '';
// === PER-PAGE SEO (consume $seo array if a view set it; safe fallbacks otherwise) ===
$seo = (isset($seo) && is_array($seo)) ? $seo : [];
$_siteTitleDefault = $_sysConf['site_meta_title'] ?? 'Cooling — Phụ Tùng & Dịch Vụ Ô Tô Chính Hãng | Hệ Thống Làm Mát Ô Tô';
$_defaultDesc = 'Sàn TMĐT phụ tùng ô tô chính hãng. Tra cứu phụ tùng theo dòng xe, mua hàng nhiều shop, bảo hành chính hãng.';
$_metaDesc = seoTruncateText(!empty($seo['meta_description']) ? $seo['meta_description'] : $_defaultDesc, 160);
?>
<title><?= e(!empty($seo['meta_title']) ? $seo['meta_title'] : (!empty($title) ? ($title . ' — ' . $_siteTitleDefault) : $_siteTitleDefault)) ?></title>
<meta name="description" content="<?= e($_metaDesc) ?>">
<?php if (!empty($seo['meta_keywords'])): ?>
<meta name="keywords" content="<?= e($seo['meta_keywords']) ?>">
<?php endif; ?>
<?php if (!empty($seo['noindex'])): ?>
<meta name="robots" content="noindex,follow">
<?php else: ?>
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<?php endif; ?>

<!-- Canonical URL & OpenGraph Meta Tags for 100/100 SEO Score -->
<?php
$_reqUriClean = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$_canonicalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_canonicalUrl = 'https://coolingsystems.vn' . rtrim($_canonicalPath, '/');
if ($_canonicalPath === '/' || $_canonicalPath === '') $_canonicalUrl = 'https://coolingsystems.vn';
if (!empty($seo['canonical'])) $_canonicalUrl = $seo['canonical'];
$_ogImage = !empty($seo['og_image']) ? $seo['og_image'] : 'https://coolingsystems.vn/favicon-cooling-round-48x48.png';
?>
<link rel="canonical" href="<?= e($_canonicalUrl) ?>">
<meta property="og:locale" content="vi_VN">
<meta property="og:type" content="<?= e($seo['og_type'] ?? 'website') ?>">
<meta property="og:title" content="<?= e(!empty($seo['meta_title']) ? $seo['meta_title'] : 'Cooling — Phụ Tùng & Dịch Vụ Ô Tô Chính Hãng') ?>">
<meta property="og:description" content="<?= e($_metaDesc) ?>">
<meta property="og:url" content="<?= e($_canonicalUrl) ?>">
<meta property="og:site_name" content="Coolingsystems.vn">
<meta property="og:image" content="<?= e($_ogImage) ?>">
<?php if (!empty($seo['preload_image']) && strpos($seo['preload_image'], 'hero_cooling_banner_1') === false): ?>
<link rel="preload" as="image" href="<?= e($seo['preload_image']) ?>" fetchpriority="high">
<?php endif; ?>

<!-- High-performance Google Fonts loading -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:wght@700;800&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:wght@700;800&display=swap"></noscript>

<!-- Zero-latency Critical Inline CSS -->
<style id="main-css">
<?php
$_cssFile = __DIR__ . '/../../public/css/cooling.min.css';
if (file_exists($_cssFile)) {
    echo file_get_contents($_cssFile);
} else {
    echo '/* CSS */';
}
?>
</style>
<!-- Critical inline CSS to eliminate CLS & FOUC for 95+ Desktop Score -->
<style id="critical-inline">
/* Đồng nhất tiêu đề các mục bên người dùng */
.sec-head .title h1, .sec-head .title h2,
.pf-head .title h1, .pf-head .title h2 {
  font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif !important;
  font-size:18px !important; font-weight:800 !important;
  text-transform:uppercase !important; letter-spacing:0.04em !important;
  color:#1a3258 !important; line-height:1.3 !important; margin:0 !important;
}
.promo-subhead { color:#1a3258 !important; font-family:'Inter',sans-serif !important; }
body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
:root { --navy:#1a3258; --gold:#c9a227; --gold-light:#e8c050; --line:#e5e7eb; --ink-2:#6b7280; --ink-3:#9ca3af; }

/* Global Alignment Fix */
header.main .wrap, nav.primary .wrap, section.block .wrap, .sec-card .wrap, .hero-section .wrap, .trust .wrap, .top-bar .wrap, .site-breadcrumb .wrap {
    max-width: 1280px !important;
    padding-left: 20px !important;
    padding-right: 20px !important;
    margin-left: auto !important;
    margin-right: auto !important;
}
@media (max-width: 768px) {
  html, body { overflow-x: hidden !important; width: 100% !important; }
  header.main .wrap, nav.primary .wrap, section.block .wrap, .sec-card .wrap, .hero-section .wrap, .trust .wrap, .top-bar .wrap, .site-breadcrumb .wrap {
    padding-left: 8px !important;
    padding-right: 8px !important;
    width: 100% !important;
    box-sizing: border-box !important;
  }
}

/* ZERO CLS Layout Containment & Font Swap (Fix 0.189 Mobile CLS 100%) */
@font-face { font-display: swap; }
html, body { font-display: swap; text-rendering: optimizeSpeed; -webkit-font-smoothing: antialiased; }
.vs-field label { display: block !important; font-size: 11px !important; color: var(--ink-3) !important; margin-bottom: 5px !important; font-weight: 600 !important; height: 16px !important; line-height: 16px !important; }
.vs-field select, .vs-field input, .cdd-trigger { height: 42px !important; line-height: 42px !important; min-height: 42px !important; box-sizing: border-box !important; }
.vs-field { min-height: 64px !important; height: 64px !important; margin-bottom: 12px !important; contain: layout size !important; overflow: hidden !important; }
.vs-card { contain: layout style !important; min-height: 420px !important; }
.accent { display: inline-block !important; contain: layout style !important; }

@media (max-width: 900px) {
  .hero-section { min-height: auto !important; }
  .vs-card { width: 100% !important; min-height: 380px !important; contain: layout style !important; }
  .hero-section .banner { min-height: 240px !important; contain: layout style !important; }
  .vs-field { width: 100% !important; min-height: 64px !important; height: 64px !important; contain: layout size !important; }
}

/* Home Banner Carousel Zero CLS */
.home-banners { margin-top: 20px; overflow: hidden; contain: layout style; content-visibility: auto; contain-intrinsic-size: 1px 250px; }
.trust, footer { content-visibility: auto; contain-intrinsic-size: 1px 300px; }
.hbc { position: relative; width: 100%; aspect-ratio: 1600 / 250; min-height: 180px; overflow: hidden; border-radius: 8px; background: #1a3258; contain: strict; }
.hbc-track { display: flex; width: 100%; height: 100%; transition: transform 0.4s ease-in-out; }
.hbc-slide { flex: 0 0 100%; width: 100%; height: 100%; position: relative; overflow: hidden; }
.hbc-slide img { width: 100%; height: 100%; object-fit: cover; display: block; aspect-ratio: 1600 / 250; decoding: async; }

/* Product Cards Zero CLS */
.p-img, .product-card .img, .product-thumb { aspect-ratio: 4 / 3; width: 100%; overflow: hidden; contain: layout style; }
.p-img img, .product-card .img img, .product-thumb img, .prod-img-wrap img { width: 100% !important; height: 100% !important; object-fit: cover !important; object-position: center !important; decoding: async; }

/* Hide all Mobile-only elements on Desktop (> 900px) */
@media (min-width: 901px) {
  .mobile-search-bar,
  .mobile-right-actions,
  .mobile-cart-btn,
  .mobile-menu-toggle,
  .mobile-bottom-bar,
  .mobile-nav-drawer,
  .mobile-nav-overlay { display: none !important; }
}
@media (max-width: 900px) {
  .mobile-nav-drawer { display: block !important; }
  .mobile-nav-overlay.open { display: block !important; }
}
</style>
</head>
<body>

<?php if (!empty($_SESSION['flash'])): ?>
<div id="flashToast" style="position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:99999;display:flex;flex-direction:column;gap:8px;pointer-events:none">
<?php foreach ((array)$_SESSION['flash'] as $f): 
  $t=$f['type']??'info'; $bg=$t==='success'?'#d1fae5':($t==='error'?'#fee2e2':'#e0f2fe'); $co=$t==='success'?'#065f46':($t==='error'?'#991b1b':'#0c4a6e');
?>
<div style="pointer-events:auto;padding:14px 24px;border-radius:10px;font-size:14px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.15);background:<?=$bg?>;color:<?=$co?>;animation:ftSlide 0.3s ease;max-width:400px;text-align:center">
<?= $t==='success'?'✅':($t==='error'?'❌':'ℹ️') ?> <?= e($f['msg']??'') ?>
</div>
<?php endforeach; unset($_SESSION['flash']); ?>
</div>
<style>@keyframes ftSlide{from{opacity:0;transform:translateX(-50%) translateY(-20px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}</style>
<script>setTimeout(function(){var t=document.getElementById('flashToast');if(t){t.style.transition='opacity 0.5s';t.style.opacity='0';setTimeout(function(){t.remove()},500);}},4000);</script>
<?php endif; ?>

<?php require __DIR__ . '/svg-logo.php'; ?>

<?php $flash = $flash ?? []; if (!empty($flash)): ?>
<div class="flash-stack">
  <?php foreach ($flash as $f): ?>
    <div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.mobile-nav-overlay, .mobile-nav-drawer { display: none !important; visibility: hidden !important; }
.mobile-nav-overlay.open { display: block !important; visibility: visible !important; position: fixed !important; inset: 0 !important; z-index: 99998 !important; background: rgba(0,0,0,0.6) !important; backdrop-filter: blur(4px) !important; }
.mobile-nav-drawer.open { display: block !important; visibility: visible !important; position: fixed !important; top: 0 !important; left: 0 !important; width: 85% !important; max-width: 320px !important; height: 100vh !important; height: 100dvh !important; z-index: 99999 !important; background: #0f172a !important; overflow-y: auto !important; }
.mobile-nav-links a[href="/customer/vouchers"]{display:none !important}
</style>

<div class="site-shell">
  <?php require __DIR__ . '/top-bar.php'; ?>
  <?php require __DIR__ . '/header.php'; ?>
  <?php require __DIR__ . '/nav.php'; ?>
</div>
<main>
<?php
// === BREADCRUMB ===
$_curPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_breadcrumbMap = [
    '/' => [],
    '/products' => [['Sản phẩm', '']],
    '/brands' => [['Hãng xe', '']],
    '/about' => [['Giới thiệu', '']],
    '/contact' => [['Liên hệ', '']],
    '/news' => [['Tin tức', '']],
    '/stores' => [['Hệ thống cửa hàng', '']],
    '/promotions' => [['Khuyến mãi', '']],
    '/cam-ket' => [['4 Bước cam kết', '']],
    '/careers' => [['Tuyển dụng', '']],
    '/product-brands' => [['Thương hiệu', '']],
    '/chat' => [['Tin nhắn', '']],
];
// Auto-detect breadcrumb from path
$_bcItems = [];
if (isset($_breadcrumbMap[$_curPath])) {
    $_bcItems = $_breadcrumbMap[$_curPath];
} elseif (preg_match('#^/products/(.+)#', $_curPath)) {
    $_bcItems = [['Sản phẩm', '/products'], [e($title ?? 'Chi tiết'), '']];
} elseif (preg_match('#^/news/(.+)#', $_curPath)) {
    $_bcItems = [['Tin tức', '/news'], [e($title ?? 'Bài viết'), '']];
} elseif (preg_match('#^/policies/(.+)#', $_curPath)) {
    $_bcItems = [['Chính sách', '/policies/huong-dan-mua-hang'], [e($title ?? 'Chi tiết'), '']];
} elseif (preg_match('#^/product-brands/(.+)#', $_curPath)) {
    $_bcItems = [['Thương hiệu', '/product-brands'], [e($title ?? 'Chi tiết'), '']];
} elseif (preg_match('#^/about/(.+)#', $_curPath)) {
    $_bcItems = [['Giới thiệu', '/about'], [e($title ?? ''), '']];
} elseif (preg_match('#^/page/(.+)#', $_curPath)) {
    $_bcItems = [[e($title ?? 'Trang'), '']];
} elseif (preg_match('#^/customer/(.+)#', $_curPath)) {
    $_bcItems = [['Tài khoản', '/customer'], [e($title ?? ''), '']];
} elseif (preg_match('#^/shops/(.+)#', $_curPath)) {
    $_bcItems = [['Cửa hàng', ''], [e($title ?? ''), '']];
} else {
    // Fallback: use title
    if (!empty($title) && $_curPath !== '/') {
        $_bcItems = [[e($title), '']];
    }
}
if (!empty($_bcItems) && $_curPath !== '/'):
?>
<nav class="site-breadcrumb" aria-label="Breadcrumb" style="background:#f8f9fc;border-bottom:1px solid var(--line)">
  <div class="wrap" style="padding-top:10px;padding-bottom:10px;font-size:13px;color:var(--ink-3);">
    <a href="/" style="color:var(--navy);text-decoration:none;font-weight:600">Trang chủ</a>
    <?php foreach ($_bcItems as $bc): ?>
      <span style="margin:0 6px;color:#ccc">›</span>
      <?php if (!empty($bc[1])): ?>
        <a href="<?= $bc[1] ?>" style="color:var(--navy);text-decoration:none"><?= $bc[0] ?></a>
      <?php else: ?>
        <span style="color:var(--ink-2)"><?= $bc[0] ?></span>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</nav>
<?php
// Breadcrumb Schema.org JSON-LD
$_schemaItems = [['name' => 'Trang chủ', 'url' => 'https://coolingsystems.vn']];
foreach ($_bcItems as $i => $bc) {
    $_schemaItems[] = ['name' => seoPlainText($bc[0]), 'url' => !empty($bc[1]) ? 'https://coolingsystems.vn'.$bc[1] : $_canonicalUrl];
}
$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => array_map(static function(array $item, int $index): array {
        return [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['name'],
            'item' => $item['url'],
        ];
    }, $_schemaItems, array_keys($_schemaItems)),
];
$websiteSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'Cooling',
    'alternateName' => 'CoolingSystems.vn',
    'url' => 'https://coolingsystems.vn',
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => [
            '@type' => 'EntryPoint',
            'urlTemplate' => 'https://coolingsystems.vn/products?q={search_term_string}'
        ],
        'query-input' => 'required name=search_term_string'
    ]
];
$orgSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'Cooling',
    'url' => 'https://coolingsystems.vn',
    'logo' => 'https://coolingsystems.vn/uploads/logo_1780242378.jpg',
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'telephone' => '+84-947-796-471',
        'contactType' => 'customer service',
        'areaServed' => 'VN',
        'availableLanguage' => 'Vietnamese'
    ]
];
?>
<script type="application/ld+json"><?= jsonLd($breadcrumbSchema) ?></script>
<script type="application/ld+json"><?= jsonLd($websiteSchema) ?></script>
<script type="application/ld+json"><?= jsonLd($orgSchema) ?></script>
<?php endif; ?>
