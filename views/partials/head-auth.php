<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#1a3258">
<link rel="icon" type="image/png" sizes="48x48" href="/favicon-cooling-round-48x48.png?v=20260717-favicon-sync">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-cooling-round-32x32.png?v=20260717-favicon-sync">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-cooling-round-16x16.png?v=20260717-favicon-sync">
<link rel="shortcut icon" href="/favicon-cooling-round.ico?v=20260717-favicon-sync">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-cooling-round.png?v=20260717-favicon-sync">
<link rel="manifest" href="/site.webmanifest?v=20260717-favicon-sync">
<title><?= e($title ?? '') ?> — Cooling</title>
<meta name="description" content="<?= e($title ?? 'Tài khoản') ?> — Cooling Phụ tùng & Dịch vụ ô tô.">
<link rel="stylesheet" href="/css/cooling.css?v=1780930000">
<link rel="stylesheet" href="/css/mobile.css?v=1780900000">
<?php
$_canonicalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_canonicalUrl = 'https://coolingsystems.vn' . rtrim($_canonicalPath, '/');
?>
<link rel="canonical" href="<?= htmlspecialchars($_canonicalUrl) ?>">
<meta name="robots" content="noindex, nofollow">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Trang chủ","item":"https://coolingsystems.vn"},{"@type":"ListItem","position":2,"name":"<?= addslashes(e($title ?? 'Tài khoản')) ?>","item":"<?= htmlspecialchars($_canonicalUrl) ?>"}]}
</script>
</head>
<body>
<?php require __DIR__ . '/svg-logo.php'; ?>
<?php $flash = $flash ?? []; if (!empty($flash)): ?>
<div class="flash-stack">
  <?php foreach ($flash as $f): ?>
    <div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<main>
