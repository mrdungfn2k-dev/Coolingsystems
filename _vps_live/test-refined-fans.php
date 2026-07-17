<?php

require '/opt/cooling-php/includes/helpers.php';

$base = 'dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000';
$originals = [
    $base . '-original.png',
    $base . '-2-original.png',
    $base . '-3-original.png',
    $base . '-4-original.png',
];

$results = [];
foreach ($originals as $index => $file) {
    $error = null;
    $output = '/tmp/refined-fan-' . ($index + 1) . '.webp';
    $ok = normalizeProductImageFile('/var/lib/cooling/product-originals/' . $file, $output, $error);
    $results[] = ['ok' => $ok, 'error' => $error, 'output' => $output];
}

echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
