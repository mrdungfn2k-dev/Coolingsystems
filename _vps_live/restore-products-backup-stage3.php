<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

$options = getopt('', ['db:', 'backup-dir:']);
$dbPath = $options['db'] ?? '';
$backupDir = rtrim($options['backup-dir'] ?? '', '/');

if ($dbPath === '' || $backupDir === '' || !is_file($dbPath) || !is_dir($backupDir)) {
    fwrite(STDERR, "Usage: php restore-products-backup-stage3.php --db=/path/cooling.db --backup-dir=/path/csv\n");
    exit(1);
}

function readCsv(string $path): array
{
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        throw new RuntimeException("Cannot read CSV: {$path}");
    }

    $header = fgetcsv($handle);
    if ($header === false) {
        fclose($handle);
        return [];
    }
    if (isset($header[0])) {
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$header[0]);
    }

    $rows = [];
    while (($values = fgetcsv($handle)) !== false) {
        if (count($values) === 1 && trim((string)$values[0]) === '') {
            continue;
        }
        if (count($values) !== count($header)) {
            throw new RuntimeException(
                sprintf('Malformed CSV row in %s: expected %d fields, got %d', $path, count($header), count($values))
            );
        }
        $rows[] = array_combine($header, $values);
    }
    fclose($handle);
    return $rows;
}

function cp1252ByteForCodepoint(int $codepoint): ?int
{
    if ($codepoint >= 0 && $codepoint <= 255) {
        return $codepoint;
    }
    $map = [
        0x20AC => 0x80, 0x201A => 0x82, 0x0192 => 0x83, 0x201E => 0x84,
        0x2026 => 0x85, 0x2020 => 0x86, 0x2021 => 0x87, 0x02C6 => 0x88,
        0x2030 => 0x89, 0x0160 => 0x8A, 0x2039 => 0x8B, 0x0152 => 0x8C,
        0x017D => 0x8E, 0x2018 => 0x91, 0x2019 => 0x92, 0x201C => 0x93,
        0x201D => 0x94, 0x2022 => 0x95, 0x2013 => 0x96, 0x2014 => 0x97,
        0x02DC => 0x98, 0x2122 => 0x99, 0x0161 => 0x9A, 0x203A => 0x9B,
        0x0153 => 0x9C, 0x017E => 0x9E, 0x0178 => 0x9F,
    ];
    return $map[$codepoint] ?? null;
}

function repairMojibake(string $value): string
{
    $bytes = '';
    $length = mb_strlen($value, 'UTF-8');
    for ($i = 0; $i < $length; $i++) {
        $character = mb_substr($value, $i, 1, 'UTF-8');
        $byte = cp1252ByteForCodepoint(mb_ord($character, 'UTF-8'));
        if ($byte === null) {
            return $value;
        }
        $bytes .= chr($byte);
    }
    return mb_check_encoding($bytes, 'UTF-8') ? $bytes : $value;
}

function plainText(?string $value): string
{
    $text = strip_tags((string)$value);
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) {
            break;
        }
        $text = $decoded;
    }
    $text = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $text);
    $text = preg_replace('/[\s\x{00A0}]+/u', ' ', (string)$text);
    return trim((string)$text);
}

function truncateText(?string $value, int $limit): string
{
    $text = plainText($value);
    if ($limit < 1 || mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    $short = mb_substr($text, 0, $limit + 1, 'UTF-8');
    $short = preg_replace('/\s+\S*$/u', '', $short);
    if ($short === '' || mb_strlen((string)$short, 'UTF-8') < (int)($limit * 0.6)) {
        $short = mb_substr($text, 0, $limit, 'UTF-8');
    }
    return rtrim((string)$short, " \t\n\r\0\x0B,.;:-");
}

function slugBase(string $value): string
{
    $value = str_replace(['đ', 'Đ'], ['d', 'D'], trim($value));
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $ascii = strtolower($ascii !== false ? $ascii : $value);
    $ascii = preg_replace('/[^a-z0-9]+/', '-', $ascii);
    $ascii = trim((string)$ascii, '-');
    return $ascii !== '' ? $ascii : 'san-pham';
}

function uniqueSlug(string $name, array &$usedSlugs): string
{
    $base = slugBase($name);
    $slug = $base;
    $suffix = 2;
    while (isset($usedSlugs[$slug])) {
        $slug = $base . '-' . $suffix;
        $suffix++;
    }
    $usedSlugs[$slug] = true;
    return $slug;
}

function seoTitle(array $product): string
{
    $name = plainText($product['name'] ?? '');
    $oem = plainText($product['oem_code'] ?? '');
    if (mb_strlen($name, 'UTF-8') <= 65) {
        return $name;
    }
    if ($oem !== '' && mb_stripos($name, $oem, 0, 'UTF-8') !== false) {
        $withoutOem = trim((string)preg_replace('/\s*' . preg_quote($oem, '/') . '\s*/iu', ' ', $name, 1));
        $prefixLimit = max(20, 64 - mb_strlen($oem, 'UTF-8'));
        return trim(truncateText($withoutOem, $prefixLimit) . ' ' . $oem);
    }
    return truncateText($name, 65);
}

function seoDescription(array $product): string
{
    $parts = [plainText($product['name'] ?? '')];
    $price = max(0, (int)($product['price'] ?? 0));
    $stock = (int)($product['stock'] ?? 0);
    $warranty = (int)($product['warranty_months'] ?? 0);
    if ($price > 0) {
        $parts[] = 'Giá ' . number_format($price, 0, ',', '.') . ' đ';
    }
    $parts[] = $stock > 0 ? 'Còn hàng' : 'Tạm hết hàng';
    if ($warranty > 0) {
        $parts[] = 'Bảo hành ' . $warranty . ' tháng';
    }
    return truncateText(implode('. ', $parts) . ' tại CoolingSystem.', 155);
}

function seoKeyword(array $product, array $categoryNames): string
{
    $name = mb_strtolower(plainText($product['name'] ?? ''), 'UTF-8');
    $category = mb_strtolower(plainText($categoryNames[(string)($product['category_id'] ?? '')] ?? ''), 'UTF-8');
    if ($category !== '' && mb_stripos($name, $category, 0, 'UTF-8') !== false) {
        return $category;
    }
    $words = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
    return implode(' ', array_slice($words ?: [], 0, 4));
}

function bindAndExecute(PDOStatement $statement, array $values): void
{
    foreach (array_values($values) as $index => $value) {
        $type = is_int($value) ? PDO::PARAM_INT : ($value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue($index + 1, $value, $type);
    }
    $statement->execute();
}

$requiredFiles = [
    'products.csv',
    'product_images.csv',
    'product_brand_map.csv',
];
foreach ($requiredFiles as $file) {
    if (!is_file($backupDir . '/' . $file)) {
        throw new RuntimeException("Missing backup file: {$file}");
    }
}

$products = readCsv($backupDir . '/products.csv');
$images = readCsv($backupDir . '/product_images.csv');
$brandMap = readCsv($backupDir . '/product_brand_map.csv');

if (count($products) !== 1947 || count($images) !== 5871 || count($brandMap) !== 1074) {
    throw new RuntimeException(sprintf(
        'Unexpected backup counts: products=%d images=%d brand_map=%d',
        count($products), count($images), count($brandMap)
    ));
}

$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA foreign_keys=ON');
$pdo->exec('PRAGMA busy_timeout=30000');

$existingProductCount = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$existingImageCount = (int)$pdo->query('SELECT COUNT(*) FROM product_images')->fetchColumn();
$existingBrandMapCount = (int)$pdo->query('SELECT COUNT(*) FROM product_brand_map')->fetchColumn();
if ($existingProductCount !== 6) {
    throw new RuntimeException("Expected 6 current products, found {$existingProductCount}. Refusing restore.");
}

$partnerExists = (int)$pdo->query('SELECT COUNT(*) FROM partners WHERE id=1')->fetchColumn();
if ($partnerExists !== 1) {
    throw new RuntimeException('Required partner_id=1 does not exist.');
}

$categoryNames = [];
foreach ($pdo->query('SELECT id, name FROM categories') as $category) {
    $categoryNames[(string)$category['id']] = (string)$category['name'];
}
$brandIds = [];
foreach ($pdo->query('SELECT id FROM brands') as $brand) {
    $brandIds[(string)$brand['id']] = true;
}

$usedIds = [];
$usedSkus = [];
$usedSlugs = [];
foreach ($pdo->query('SELECT id, partner_id, sku, slug FROM products') as $current) {
    $usedIds[(string)$current['id']] = true;
    $usedSkus[(string)$current['partner_id'] . '|' . mb_strtolower((string)$current['sku'], 'UTF-8')] = true;
    $usedSlugs[(string)$current['slug']] = true;
}

$imageRowsByProduct = [];
foreach ($images as $image) {
    $productId = (string)$image['product_id'];
    $imageRowsByProduct[$productId][] = $image;
}

$fixedMainImages = 0;
foreach ($imageRowsByProduct as &$productImages) {
    usort($productImages, static function (array $left, array $right): int {
        $sortCompare = ((int)$left['sort_order']) <=> ((int)$right['sort_order']);
        return $sortCompare !== 0 ? $sortCompare : ((int)$left['id'] <=> (int)$right['id']);
    });
    $mainCount = count(array_filter($productImages, static fn(array $image): bool => (int)$image['is_main'] === 1));
    if ($mainCount !== 1) {
        foreach ($productImages as $index => &$image) {
            $image['is_main'] = $index === 0 ? '1' : '0';
        }
        unset($image);
        $fixedMainImages++;
    }
}
unset($productImages);

$productColumns = [];
foreach ($pdo->query('PRAGMA table_info(products)') as $column) {
    $productColumns[(string)$column['name']] = true;
}
$csvColumns = array_keys($products[0]);
$insertColumns = array_values(array_filter($csvColumns, static fn(string $column): bool => isset($productColumns[$column])));
$quotedColumns = implode(',', array_map(static fn(string $column): string => '"' . str_replace('"', '""', $column) . '"', $insertColumns));
$productPlaceholders = implode(',', array_fill(0, count($insertColumns), '?'));
$insertProduct = $pdo->prepare("INSERT INTO products ({$quotedColumns}) VALUES ({$productPlaceholders})");
$insertImage = $pdo->prepare(
    'INSERT INTO product_images (product_id,file_path,sort_order,is_main,created_at,alt_text) VALUES (?,?,?,?,?,?)'
);
$insertBrandMap = $pdo->prepare('INSERT OR IGNORE INTO product_brand_map (product_id,brand_id) VALUES (?,?)');

$integerColumns = [
    'id', 'partner_id', 'category_id', 'price', 'original_price', 'stock', 'reserved_stock',
    'weight_g', 'width_cm', 'height_cm', 'depth_cm', 'is_admin_created', 'is_featured',
    'is_hot', 'is_new', 'sold_count', 'view_count', 'rating_count', 'warranty_months',
    'approved_by', 'is_indexed', 'price_before_tax', 'tax_amount', 'vat_rate', 'min_stock',
    'max_stock', 'car_brand_id', 'sale_price', 'is_on_sale', 'show_on_home', 'show_on_promo',
    'cost_price', 'total_import_value',
];
$nullableIntegerColumns = [
    'original_price', 'weight_g', 'width_cm', 'height_cm', 'depth_cm', 'approved_by', 'car_brand_id',
];

$restoredProducts = 0;
$restoredImages = 0;
$restoredBrandMap = 0;
$skuCorrections = 0;
$encodingRepairs = 0;
$slugSuffixes = 0;

$pdo->beginTransaction();
try {
    foreach ($products as $product) {
        $productId = (string)$product['id'];
        if (isset($usedIds[$productId])) {
            throw new RuntimeException("Product ID collision: {$productId}");
        }

        if ($productId === '2517') {
            if ((string)$product['sku'] !== '97109-3D000') {
                throw new RuntimeException('Unexpected source SKU for product 2517.');
            }
            $product['sku'] = '97109-38000';
            $skuCorrections++;
        }
        if ($productId === '2496' || $productId === '2497') {
            $product['name'] = repairMojibake((string)$product['name']);
            $product['description'] = repairMojibake((string)$product['description']);
            $encodingRepairs++;
        }

        $product['sku'] = trim((string)$product['sku']);
        if ($product['sku'] === '') {
            $product['sku'] = trim((string)$product['oem_code']);
        }
        if ($product['sku'] === '') {
            throw new RuntimeException("Missing SKU and OEM for product {$productId}");
        }
        $skuKey = '1|' . mb_strtolower($product['sku'], 'UTF-8');
        if (isset($usedSkus[$skuKey])) {
            throw new RuntimeException("Product SKU collision after mapping: {$product['sku']}");
        }
        $usedSkus[$skuKey] = true;
        $usedIds[$productId] = true;

        if (!isset($categoryNames[(string)$product['category_id']])) {
            throw new RuntimeException("Unknown category for product {$productId}: {$product['category_id']}");
        }
        if ($product['car_brand_id'] !== '' && !isset($brandIds[(string)$product['car_brand_id']])) {
            throw new RuntimeException("Unknown car brand for product {$productId}: {$product['car_brand_id']}");
        }

        $baseSlug = slugBase((string)$product['name']);
        $product['slug'] = uniqueSlug((string)$product['name'], $usedSlugs);
        if ($product['slug'] !== $baseSlug) {
            $slugSuffixes++;
        }
        $product['partner_id'] = '1';
        $product['status'] = 'draft';
        $product['is_indexed'] = '0';
        $product['published_at'] = '';
        $product['approved_by'] = '';
        $product['approved_at'] = '';
        $product['reject_reason'] = '';
        $product['show_on_home'] = '0';
        $product['show_on_promo'] = '0';
        $product['seo_title'] = seoTitle($product);
        $product['seo_description'] = seoDescription($product);
        $product['seo_keyword'] = seoKeyword($product, $categoryNames);
        $product['canonical_url'] = '';

        $values = [];
        foreach ($insertColumns as $column) {
            $value = $product[$column] ?? '';
            if (in_array($column, $integerColumns, true)) {
                if ($value === '' && in_array($column, $nullableIntegerColumns, true)) {
                    $value = null;
                } else {
                    $value = (int)$value;
                }
            } elseif ($value === '' && in_array($column, ['published_at', 'approved_at'], true)) {
                $value = null;
            }
            $values[] = $value;
        }
        bindAndExecute($insertProduct, $values);
        $restoredProducts++;

        foreach ($imageRowsByProduct[$productId] ?? [] as $index => $image) {
            $alt = plainText((string)$product['name']) . ($index > 0 ? ' - ảnh ' . ($index + 1) : '');
            bindAndExecute($insertImage, [
                (int)$productId,
                (string)$image['file_path'],
                (int)$image['sort_order'],
                (int)$image['is_main'],
                (string)$image['created_at'],
                $alt,
            ]);
            $restoredImages++;
        }
    }

    foreach ($brandMap as $mapping) {
        $productId = (string)$mapping['product_id'];
        $brandId = (string)$mapping['brand_id'];
        if (!isset($usedIds[$productId]) || !isset($brandIds[$brandId])) {
            throw new RuntimeException("Invalid product brand mapping: product={$productId}, brand={$brandId}");
        }
        bindAndExecute($insertBrandMap, [(int)$productId, (int)$brandId]);
        $restoredBrandMap += $insertBrandMap->rowCount();
    }

    $finalProductCount = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $finalImageCount = (int)$pdo->query('SELECT COUNT(*) FROM product_images')->fetchColumn();
    $finalBrandMapCount = (int)$pdo->query('SELECT COUNT(*) FROM product_brand_map')->fetchColumn();
    $restoredDraftCount = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE id >= 121 AND status="draft" AND is_indexed=0')->fetchColumn();
    $restoredPublishedCount = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE id >= 121 AND status="published"')->fetchColumn();

    $expectedProductCount = $existingProductCount + 1947;
    $expectedImageCount = $existingImageCount + 5871;
    $expectedBrandMapCount = $existingBrandMapCount + 1074;
    if (
        $restoredProducts !== 1947
        || $restoredImages !== 5871
        || $restoredBrandMap !== 1074
        || $finalProductCount !== $expectedProductCount
        || $finalImageCount !== $expectedImageCount
        || $finalBrandMapCount !== $expectedBrandMapCount
        || $restoredDraftCount !== 1947
        || $restoredPublishedCount !== 0
    ) {
        throw new RuntimeException(sprintf(
            'Post-import invariant failed: restored=%d/%d/%d final=%d/%d/%d draft=%d published=%d',
            $restoredProducts,
            $restoredImages,
            $restoredBrandMap,
            $finalProductCount,
            $finalImageCount,
            $finalBrandMapCount,
            $restoredDraftCount,
            $restoredPublishedCount
        ));
    }

    $foreignKeyIssues = $pdo->query('PRAGMA foreign_key_check')->fetchAll();
    $productForeignKeyIssues = array_values(array_filter(
        $foreignKeyIssues,
        static fn(array $issue): bool => in_array((string)($issue['table'] ?? ''), ['products', 'product_images', 'product_brand_map'], true)
    ));
    if ($productForeignKeyIssues !== []) {
        throw new RuntimeException('Product restore introduced foreign-key violations.');
    }

    $pdo->commit();

    $result = [
        'ok' => true,
        'database' => $dbPath,
        'restored_products' => $restoredProducts,
        'restored_images' => $restoredImages,
        'restored_brand_map' => $restoredBrandMap,
        'final_products' => $finalProductCount,
        'final_images' => $finalImageCount,
        'final_brand_map' => $finalBrandMapCount,
        'restored_drafts' => $restoredDraftCount,
        'restored_published' => $restoredPublishedCount,
        'sku_corrections' => $skuCorrections,
        'encoding_repairs' => $encodingRepairs,
        'slug_suffixes' => $slugSuffixes,
        'main_image_fixes' => $fixedMainImages,
        'pre_existing_foreign_key_issues' => count($foreignKeyIssues),
    ];
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}

