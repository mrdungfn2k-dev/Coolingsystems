<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="<?= csrfToken() ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Admin') ?> — Cooling Admin</title>
<link rel="stylesheet" href="/css/cooling.css?v=1779620797">
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