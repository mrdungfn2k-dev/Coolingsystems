<?php
require __DIR__ . '/includes/helpers.php';

function testUrl($uri) {
    $_SERVER['REQUEST_URI'] = $uri;
    echo "URI: $uri => currentPath: " . currentPath() . "\n";
    echo "   isActive(/): " . var_export(isActive('/'), true) . "\n";
    echo "   isActive(/product-brands): " . var_export(isActive('/product-brands'), true) . "\n";
    echo "   isActive(/products): " . var_export(isActive('/products'), true) . "\n";
}

testUrl('/product-brands');
testUrl('/products');
testUrl('/');
