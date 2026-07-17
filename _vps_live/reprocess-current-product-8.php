<?php

require '/opt/cooling-php/includes/helpers.php';

$base = 'dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000';
$jobs = [
    40 => [$base . '-5-original-2.png', $base . '-5-detail-safe-20260717.webp'],
    41 => [$base . '-2-2-original-2.png', $base . '-2-2-detail-safe-20260717.webp'],
    42 => [$base . '-3-2-original-2.png', $base . '-3-2-detail-safe-20260717.webp'],
    43 => [$base . '-4-2-original-2.png', $base . '-4-2-detail-safe-20260717.webp'],
];

$results = [];
foreach ($jobs as $imageId => [$originalName, $outputName]) {
    $error = null;
    $ok = normalizeProductImageFile(
        '/var/lib/cooling/product-originals/' . $originalName,
        '/var/lib/cooling/uploads/products/' . $outputName,
        $error
    );
    $results[] = [
        'id' => $imageId,
        'ok' => $ok,
        'error' => $error,
        'file_path' => $outputName,
    ];
}

echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
