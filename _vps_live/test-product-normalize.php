<?php

require '/opt/cooling-php/includes/helpers.php';

$source = '/opt/cooling-php/uploads/products/p_6a1701fe079ed_2.jpg';
$destination = '/tmp/php-product-normalized-live.webp';
$error = null;
$startedAt = microtime(true);
$ok = normalizeProductImageFile($source, $destination, $error);

echo json_encode([
    'ok' => $ok,
    'error' => $error,
    'elapsed_seconds' => round(microtime(true) - $startedAt, 2),
    'output' => $destination,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
