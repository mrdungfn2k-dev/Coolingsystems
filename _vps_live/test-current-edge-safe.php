<?php

require '/opt/cooling-php/includes/helpers.php';

$base = 'dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000';
$jobs = [
    [$base . '-20260717094651-f81a0424-original.png', '/tmp/edge-safe-side.webp'],
    [$base . '-3-20260717094700-a323d04e-original.png', '/tmp/edge-safe-top.webp'],
];

$results = [];
foreach ($jobs as [$source, $output]) {
    $error = null;
    $ok = normalizeProductImageFile('/var/lib/cooling/product-originals/' . $source, $output, $error);
    $results[] = ['ok' => $ok, 'error' => $error, 'output' => $output];
}
echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
