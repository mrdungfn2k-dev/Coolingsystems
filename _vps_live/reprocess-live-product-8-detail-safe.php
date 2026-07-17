<?php

require '/opt/cooling-php/includes/helpers.php';

$base = 'dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000';
$jobs = [
    44 => [$base . '-20260717092538-9ce5764b-original.png', $base . '-detail-safe2-20260717.webp'],
    45 => [$base . '-2-20260717092542-42a9de3a-original.png', $base . '-2-detail-safe2-20260717.webp'],
    46 => [$base . '-3-20260717092546-77266565-original.png', $base . '-3-detail-safe2-20260717.webp'],
    47 => [$base . '-4-20260717092550-b3b4901b-original.png', $base . '-4-detail-safe2-20260717.webp'],
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
