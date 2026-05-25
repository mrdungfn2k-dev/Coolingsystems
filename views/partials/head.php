<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#1a3258">
<title><?= e($title ?? '') ?>
<?php
// Dynamic system config - load all site settings
$_sysConf = [];
$_sysRows = dbAll("SELECT key, value FROM system_config");
foreach ($_sysRows as $_sr) $_sysConf[$_sr['key']] = $_sr['value'];
$sysHotline = $_sysConf['site_phone'] ?? $_sysConf['contact_hotline'] ?? '0947796471';
$sysEmail = $_sysConf['contact_email'] ?? 'support@coolingsystem.vn';
$sysWhatsapp = $_sysConf['social_whatsapp'] ?? '';
$sysTiktok = $_sysConf['social_tiktok'] ?? '';
$sysFacebook = $_sysConf['social_facebook'] ?? '';
?>
 — Cooling — Phụ Tùng & Dịch Vụ Ô Tô Chính Hãng</title>
<meta name="description" content="Sàn TMĐT phụ tùng ô tô chính hãng. Tra cứu phụ tùng theo dòng xe, mua hàng nhiều shop, bảo hành chính hãng.">
<link rel="stylesheet" href="/css/cooling.css?v=1779633952">
<link rel="stylesheet" href="/css/mobile.css?v=1779633952">

<?php
// === CANONICAL URL ===
$_canonicalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_canonicalUrl = 'https://coolingsystem.vn' . rtrim($_canonicalPath, '/');
if ($_canonicalPath === '/' || $_canonicalPath === '') $_canonicalUrl = 'https://coolingsystem.vn';
?>
<link rel="canonical" href="<?= e($_canonicalUrl) ?>">
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
    <a href="/products?cat=he-thong-lam-mat">Hệ thống làm mát</a>
    <a href="/promotions">Khuyến mại</a>
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
<nav class="site-breadcrumb" aria-label="Breadcrumb" style="max-width:100%;padding:10px 40px;font-size:13px;color:var(--ink-3);background:#f8f9fc;border-bottom:1px solid var(--line)">
  <a href="/" style="color:var(--navy);text-decoration:none;font-weight:600">Trang chủ</a>
  <?php foreach ($_bcItems as $bc): ?>
    <span style="margin:0 6px;color:#ccc">›</span>
    <?php if (!empty($bc[1])): ?>
      <a href="<?= $bc[1] ?>" style="color:var(--navy);text-decoration:none"><?= $bc[0] ?></a>
    <?php else: ?>
      <span style="color:var(--ink-2)"><?= $bc[0] ?></span>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>
<?php
// Breadcrumb Schema.org JSON-LD
$_schemaItems = [['name' => 'Trang chủ', 'url' => 'https://coolingsystem.vn']];
foreach ($_bcItems as $i => $bc) {
    $_schemaItems[] = ['name' => strip_tags($bc[0]), 'url' => !empty($bc[1]) ? 'https://coolingsystem.vn'.$bc[1] : $_canonicalUrl];
}
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    <?php foreach ($_schemaItems as $si => $item): ?>
    {
      "@type": "ListItem",
      "position": <?= $si + 1 ?>,
      "name": "<?= addslashes($item['name']) ?>",
      "item": "<?= $item['url'] ?>"
    }<?= $si < count($_schemaItems) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
  ]
}
</script>
<?php endif; ?>
