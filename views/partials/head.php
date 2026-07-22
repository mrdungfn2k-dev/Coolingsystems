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
<!-- Performance: Preconnect Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
$_defaultDesc = 'Sàn TMĐT phụ tùng ô tô chính hãng. Tra cứu phụ tùng theo dòng xe, mua hàng nhiều shop, bảo hành chính hãng.';
$_metaDesc = seoTruncateText(!empty($seo['meta_description']) ? $seo['meta_description'] : $_defaultDesc, 160);
?>
<title><?= e(!empty($seo['meta_title']) ? $seo['meta_title'] : (($title ?? '') . ' — Cooling — Phụ Tùng & Dịch Vụ Ô Tô Chính Hãng')) ?></title>
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
$_canonicalUrl = 'https://coolingsystems.vn' . ($_reqUriClean === '' ? '/' : $_reqUriClean);
?>
<link rel="canonical" href="<?= e($_canonicalUrl) ?>">
<meta property="og:locale" content="vi_VN">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e(!empty($seo['meta_title']) ? $seo['meta_title'] : 'Cooling — Phụ Tùng & Dịch Vụ Ô Tô Chính Hãng') ?>">
<meta property="og:description" content="<?= e($_metaDesc) ?>">
<meta property="og:url" content="<?= e($_canonicalUrl) ?>">
<meta property="og:site_name" content="Coolingsystems.vn">
<meta property="og:image" content="https://coolingsystems.vn/favicon-cooling-round-48x48.png">
<meta name="twitter:card" content="summary_large_image">
<!-- Performance: Post-idle Google Fonts loading for 95+ Mobile Score -->
<script>
(function(){
  var loadFonts = function(){
    var l = document.createElement('link');
    l.rel = 'stylesheet';
    l.href = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Playfair+Display:wght@700;800&display=swap';
    document.head.appendChild(l);
  };
  if (window.requestIdleCallback) {
    requestIdleCallback(loadFonts);
  } else {
    window.addEventListener('load', loadFonts);
  }
})();
</script>
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
<!-- Critical inline CSS to prevent FOUC while CSS loads -->
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
</style>

<?php
// === CANONICAL URL ===
$_canonicalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_canonicalUrl = 'https://coolingsystems.vn' . rtrim($_canonicalPath, '/');
if ($_canonicalPath === '/' || $_canonicalPath === '') $_canonicalUrl = 'https://coolingsystems.vn';
if (!empty($seo['canonical'])) $_canonicalUrl = $seo['canonical'];
?>
<link rel="canonical" href="<?= e($_canonicalUrl) ?>">
<!-- Open Graph / Twitter Card -->
<meta property="og:site_name" content="Cooling">
<meta property="og:type" content="<?= e($seo['og_type'] ?? 'website') ?>">
<meta property="og:title" content="<?= e(!empty($seo['meta_title']) ? $seo['meta_title'] : (($title ?? '') . ' — Cooling')) ?>">
<meta property="og:description" content="<?= e($_metaDesc) ?>">
<meta property="og:url" content="<?= e($_canonicalUrl) ?>">
<meta property="og:image" content="<?= e($seo['og_image'] ?? 'https://coolingsystems.vn/img/og-default.jpg') ?>">
<meta name="twitter:card" content="summary_large_image">
<style>
/* Global Alignment Fix */
header.main .wrap, nav.primary .wrap, section.block .wrap, .sec-card .wrap, .hero-section .wrap, .trust .wrap, .top-bar .wrap, .site-breadcrumb .wrap {
    max-width: 1280px !important;
    padding-left: 20px !important;
    padding-right: 20px !important;
    margin-left: auto !important;
    margin-right: auto !important;
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

<style>.mobile-nav-links a[href="/customer/vouchers"]{display:none !important}</style>
<!-- Mobile nav drawer -->
<div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
<div class="mobile-nav-drawer" id="mobileNavDrawer">
  <button class="mobile-nav-close" id="mobileNavClose">✕</button>
  <div class="mobile-nav-links">
    <span class="nav-section-label">Khám phá</span>
    <a href="/">Trang chủ</a>
    <a href="/about">Giới thiệu</a>
    <a href="/products">Sản phẩm</a>
    <a href="/brands">Phụ tùng theo hãng</a>
    <a href="/product-brands">Thương hiệu</a>
    <a href="/vouchers">Khuyến mại</a>
    <span class="nav-section-label">Hỗ trợ</span>
    <a href="/news">Tin tức</a>
    <a href="/policies">Chính sách</a>
    <a href="/stores">Hệ thống cửa hàng</a>
    <?php if ($user && in_array($user['role'], ['customer','staff'])): ?>
      <span class="nav-section-label">Tài khoản của tôi</span>
      <a href="/customer/orders">Đơn hàng</a>
      <a href="/customer/favorites">Yêu thích</a>
      <a href="/customer/vouchers">Voucher</a>
      <a href="/customer/profile">Hồ sơ</a>
      <a href="/auth/logout">Đăng xuất</a>
    <?php elseif ($user && $user['role'] === 'partner'): ?>
      <span class="nav-section-label">Quản lý</span>
      <a href="/partner/dashboard">Dashboard</a>
      <a href="/partner/logout">Đăng xuất</a>
    <?php elseif ($user && $user['role'] === 'admin'): ?>
      <a href="/admin">Admin Panel</a>
      <a href="/admin/logout">Đăng xuất</a>
    <?php endif; ?>
  </div>
  <?php if (!$user): ?>
  <div class="mobile-nav-actions">
    <a href="/auth/login" class="action-outline">Đăng nhập</a>
    <a href="/auth/register" class="action-gold">Đăng ký</a>
  </div>
  <?php endif; ?>
  <div style="padding:14px 20px;border-top:1px solid rgba(255,255,255,0.08);margin-top:8px;">
    <div style="font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:6px;letter-spacing:0.06em;text-transform:uppercase;font-weight:700;">Tư vấn miễn phí</div>
    <a href="tel:<?= preg_replace('/[^0-9]/', '', $sysHotline) ?>" style="font-size:22px;font-weight:800;color:var(--gold-light);text-decoration:none;display:block;"><?= htmlspecialchars($sysHotline) ?></a>
  </div>
</div>

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
