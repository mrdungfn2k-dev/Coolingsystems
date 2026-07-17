<?php

require '/opt/cooling-php/includes/helpers.php';

$base = 'dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000';
$jobs = [
    48 => [$base . '-20260717094651-f81a0424-original.png', $base . '-edge-safe-20260717.webp'],
    49 => [$base . '-2-20260717094655-c848cafa-original.png', $base . '-2-edge-safe-20260717.webp'],
    50 => [$base . '-3-20260717094700-a323d04e-original.png', $base . '-3-edge-safe-20260717.webp'],
    51 => [$base . '-4-20260717094703-f350e995-original.png', $base . '-4-edge-safe-20260717.webp'],
];

$results = [];
foreach ($jobs as $imageId => [$originalName, $outputName]) {
    $error = null;
    $ok = normalizeProductImageFile(
        '/var/lib/cooling/product-originals/' . $originalName,
        '/var/lib/cooling/uploads/products/' . $outputName,
        $error
    );
    $results[] = ['id' => $imageId, 'ok' => $ok, 'error' => $error, 'file_path' => $outputName];
}
echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
