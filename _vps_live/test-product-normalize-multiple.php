<?php

require '/opt/cooling-php/includes/helpers.php';

$sources = [
    '/opt/cooling-php/uploads/products/p_6a1e5115a8eb5.jpg',
    '/opt/cooling-php/uploads/products/p_6a1701fe079ed_2.jpg',
    '/opt/cooling-php/uploads/products/97701-h8000-3.jpg',
    '/var/lib/cooling/product-originals/ket-nuoc-lam-mat-hyundai-2010-2016-kia-2012-2016-doowon-25310-22005-25310-22005-original.jpg',
];

$results = [];
$startedAt = microtime(true);
foreach ($sources as $index => $source) {
    $destination = '/tmp/product-multiple-' . ($index + 1) . '.webp';
    $error = null;
    $itemStartedAt = microtime(true);
    $ok = normalizeProductImageFile($source, $destination, $error);
    $results[] = [
        'source' => basename($source),
        'ok' => $ok,
        'error' => $error,
        'elapsed_seconds' => round(microtime(true) - $itemStartedAt, 2),
        'dimensions' => $ok ? getimagesize($destination)[0] . 'x' . getimagesize($destination)[1] : null,
    ];
}

echo json_encode([
    'ok' => !in_array(false, array_column($results, 'ok'), true),
    'elapsed_seconds' => round(microtime(true) - $startedAt, 2),
    'results' => $results,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
