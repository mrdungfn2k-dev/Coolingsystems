<?php

require '/opt/cooling-php/includes/helpers.php';

$error = null;
$ok = normalizeProductImageFile(
    '/tmp/product-blue-background.jpg',
    '/tmp/product-blue-normalized.webp',
    $error
);

echo json_encode([
    'ok' => $ok,
    'error' => $error,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
