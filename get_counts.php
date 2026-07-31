<?php
require_once __DIR__ . '/includes/db.php';

$total = dbGet("SELECT COUNT(*) as c FROM products")['c'] ?? 0;
$pub = dbGet("SELECT COUNT(*) as c FROM products WHERE status = 'published'")['c'] ?? 0;
$draft = dbGet("SELECT COUNT(*) as c FROM products WHERE status = 'draft'")['c'] ?? 0;

echo "Total: " . $total . "\n";
echo "Published: " . $pub . "\n";
echo "Draft: " . $draft . "\n";
