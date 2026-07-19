<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(2);
}

function textValue(?string $value): string
{
    $text = strip_tags((string)$value);
    for ($index = 0; $index < 3; $index++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) {
            break;
        }
        $text = $decoded;
    }
    return trim((string)preg_replace('/\s+/u', ' ', $text));
}

function htmlValue(?string $value): string
{
    return htmlspecialchars(textValue($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function removeGeneratedBlock(?string $html, string $section): string
{
    $pattern = '~\s*<!-- stage4-seo-' . preg_quote($section, '~') . ':start -->.*?'
        . '<!-- stage4-seo-' . preg_quote($section, '~') . ':end -->\s*~s';
    return trim((string)preg_replace($pattern, '', (string)$html));
}

function appendGeneratedBlock(?string $html, string $section, string $block): string
{
    $original = removeGeneratedBlock($html, $section);
    $generated = '<!-- stage4-seo-' . $section . ':start -->' . $block
        . '<!-- stage4-seo-' . $section . ':end -->';
    return trim($original . "\n" . $generated);
}

function truncateWords(string $value, int $limit): string
{
    $value = textValue($value);
    if (mb_strlen($value, 'UTF-8') <= $limit) {
        return $value;
    }
    $short = mb_substr($value, 0, $limit + 1, 'UTF-8');
    $short = preg_replace('/\s+\S*$/u', '', $short);
    return rtrim((string)$short, " \t\n\r\0\x0B,.;:-");
}

function seoDescription(array $product, string $categoryName): string
{
    $name = textValue($product['name'] ?? '');
    $code = textValue($product['sku'] ?? '') ?: textValue($product['oem_code'] ?? '');
    $category = textValue($categoryName) ?: 'phụ tùng ô tô';
    $parts = [$name];
    if ($code !== '' && mb_stripos($name, $code, 0, 'UTF-8') === false) {
        $parts[] = 'Mã ' . $code;
    }
    $description = implode('. ', $parts)
        . '. ' . ucfirst($category)
        . ' tại CoolingSystem, hỗ trợ kiểm tra mã sản phẩm, tư vấn lắp đặt và giao hàng toàn quốc.';
    if (mb_strlen($description, 'UTF-8') < 140) {
        $description .= ' Thông tin rõ ràng, thuận tiện tra cứu.';
    }
    return truncateWords($description, 158) . '.';
}

function imageUrl(string $filePath): string
{
    $segments = array_map('rawurlencode', explode('/', str_replace('\\', '/', $filePath)));
    return '/uploads/products/' . implode('/', $segments);
}

function buildContent(array $product, string $categoryName, ?string $mainImage): array
{
    $name = htmlValue($product['name'] ?? '');
    $keyword = htmlValue($product['seo_keyword'] ?? '');
    $sku = htmlValue($product['sku'] ?? '');
    $oem = htmlValue($product['oem_code'] ?? '');
    $brand = htmlValue($product['part_brand'] ?? '');
    $category = htmlValue($categoryName);
    $code = $sku !== '' ? $sku : $oem;

    $descriptionBlock = '<section class="product-seo-details">'
        . '<h2>Thông tin ' . $keyword . '</h2>'
        . '<p><strong>' . $keyword . '</strong> được nhận diện theo tên sản phẩm và mã tham chiếu đang lưu trên hệ thống. '
        . 'Khách hàng có thể sử dụng tên hoặc mã sản phẩm để tra cứu và đối chiếu trước khi đặt hàng.</p>'
        . '<h3>Thông tin nhận diện sản phẩm</h3><ul>'
        . '<li><strong>Tên sản phẩm:</strong> ' . $name . '</li>'
        . '<li><strong>Mã sản phẩm:</strong> ' . ($code !== '' ? $code : 'Đang cập nhật') . '</li>'
        . ($oem !== '' ? '<li><strong>Mã OEM:</strong> ' . $oem . '</li>' : '')
        . ($category !== '' ? '<li><strong>Danh mục:</strong> ' . $category . '</li>' : '')
        . ($brand !== '' ? '<li><strong>Thương hiệu:</strong> ' . $brand . '</li>' : '')
        . '</ul>';
    if ($mainImage !== null && $mainImage !== '') {
        $src = htmlspecialchars(imageUrl($mainImage), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $descriptionBlock .= '<figure><img src="' . $src . '" alt="' . $name
            . ' - ' . $code . '" loading="lazy"><figcaption>' . $name . '</figcaption></figure>';
    } else {
        $descriptionBlock .= '<p>Trước khi đặt hàng, khách hàng nên đối chiếu mã sản phẩm, mã OEM, '
            . 'dòng xe và thông tin lắp đặt thực tế. Các dữ liệu nhận diện trên trang được dùng để hỗ trợ '
            . 'tra cứu; bộ phận tư vấn sẽ kiểm tra lại thông tin phù hợp trước khi xác nhận đơn hàng.</p>';
    }
    $descriptionBlock .= '</section>';

    $featuresBlock = '<section class="product-seo-features"><h3>Đặc điểm nhận diện</h3><ul>'
        . '<li><strong>Sản phẩm:</strong> ' . $name . '</li>'
        . '<li><strong>Mã tham chiếu:</strong> ' . ($code !== '' ? $code : 'Đang cập nhật') . '</li>'
        . '<li><strong>Thông tin tra cứu:</strong> ' . $keyword . '</li>'
        . '</ul></section>';

    $specificationBlock = '<section class="product-seo-specifications"><h3>Thông số tra cứu</h3>'
        . '<table><tbody>'
        . '<tr><th scope="row">Tên sản phẩm</th><td>' . $name . '</td></tr>'
        . '<tr><th scope="row">Mã sản phẩm</th><td>' . ($code !== '' ? $code : 'Đang cập nhật') . '</td></tr>'
        . ($oem !== '' ? '<tr><th scope="row">Mã OEM</th><td>' . $oem . '</td></tr>' : '')
        . ($category !== '' ? '<tr><th scope="row">Danh mục</th><td>' . $category . '</td></tr>' : '')
        . ($brand !== '' ? '<tr><th scope="row">Thương hiệu</th><td>' . $brand . '</td></tr>' : '')
        . '</tbody></table></section>';

    return [
        appendGeneratedBlock($product['description'] ?? '', 'description', $descriptionBlock),
        appendGeneratedBlock($product['features'] ?? '', 'features', $featuresBlock),
        appendGeneratedBlock($product['specifications'] ?? '', 'specifications', $specificationBlock),
    ];
}

function scoreProduct(array $product, int $imageCount): int
{
    $name = textValue($product['name'] ?? '');
    $keyword = mb_strtolower(textValue($product['seo_keyword'] ?? ''), 'UTF-8');
    $descriptionHtml = (string)($product['description'] ?? '');
    $featuresHtml = (string)($product['features'] ?? '');
    $specificationsHtml = (string)($product['specifications'] ?? '');
    $description = textValue($descriptionHtml);
    $features = textValue($featuresHtml);
    $specifications = textValue($specificationsHtml);
    $title = textValue($product['seo_title'] ?? '') ?: $name;
    $score = 0;
    $score += mb_strlen($title, 'UTF-8') >= 20 && mb_strlen($title, 'UTF-8') <= 70 ? 5 : 0;
    $score += !empty($product['category_id']) ? 5 : 0;
    $score += mb_strlen($description, 'UTF-8') >= 150 ? 8 : 0;
    $score += mb_strlen($description, 'UTF-8') >= 300 ? 5 : 0;
    $headingCount = preg_match_all('/<h[23]/i', $descriptionHtml);
    $score += $headingCount >= 1 ? 7 : 0;
    $score += $headingCount >= 2 ? 5 : 0;
    $score += preg_match('/<(ul|ol)/i', $descriptionHtml) ? 5 : 0;
    $score += preg_match('/<img/i', $descriptionHtml) ? 5 : 0;
    $score += mb_strlen($features, 'UTF-8') >= 50 ? 5 : 0;
    $score += preg_match('/<(ul|ol)/i', $featuresHtml) ? 3 : 0;
    $score += preg_match_all('/<(strong|b)>/i', $featuresHtml) >= 2 ? 2 : 0;
    $score += mb_strlen($specifications, 'UTF-8') >= 30 ? 5 : 0;
    $score += preg_match('/<table/i', $specificationsHtml) || mb_strlen($specifications, 'UTF-8') >= 80 ? 5 : 0;
    if ($keyword !== '') {
        $descriptionLower = mb_strtolower($description, 'UTF-8');
        $featuresLower = mb_strtolower($features, 'UTF-8');
        $allText = mb_strtolower($name . ' ' . $description . ' ' . $features . ' ' . $specifications, 'UTF-8');
        $score += mb_stripos($name, $keyword, 0, 'UTF-8') !== false ? 8 : 0;
        $score += mb_stripos($descriptionLower, $keyword, 0, 'UTF-8') !== false ? 7 : 0;
        $score += mb_stripos($featuresLower, $keyword, 0, 'UTF-8') !== false ? 3 : 0;
        $matches = preg_match_all('/' . preg_quote($keyword, '/') . '/iu', $allText);
        $wordCount = count(preg_split('/\s+/u', $allText, -1, PREG_SPLIT_NO_EMPTY));
        $density = $wordCount > 0 ? ($matches / $wordCount * 100) : 0;
        $score += $matches >= 3 && $density < 5 ? 7 : 0;
    } elseif (mb_strlen($name, 'UTF-8') >= 15) {
        $score += 25;
    }
    $score += $imageCount > 0 ? 7 : 0;
    $score += $imageCount >= 2 ? 3 : 0;
    return $score;
}

$apply = in_array('--apply', $argv, true);
$databasePath = '/var/lib/cooling/cooling.db';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--db=')) {
        $databasePath = substr($argument, 5);
    }
}

$pdo = new PDO('sqlite:' . $databasePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$products = $pdo->query(
    "SELECT p.*,c.name AS category_name,
        (SELECT file_path FROM product_images i WHERE i.product_id=p.id ORDER BY i.is_main DESC,i.sort_order,i.id LIMIT 1) AS main_image,
        (SELECT COUNT(*) FROM product_images i WHERE i.product_id=p.id) AS image_count
     FROM products p LEFT JOIN categories c ON c.id=p.category_id
     WHERE p.id NOT IN (2,3,4,5,6,8) AND p.status='draft' ORDER BY p.id"
)->fetchAll();
if (count($products) !== 1947) {
    throw new RuntimeException('Expected 1947 restored draft products, found ' . count($products));
}

$updates = [];
$scoreCounts = ['80_plus' => 0, '90_plus' => 0, '100' => 0];
$minimumScore = 100;
$maximumScore = 0;
$scoreTotal = 0;
foreach ($products as $product) {
    [$description, $features, $specifications] = buildContent(
        $product,
        (string)($product['category_name'] ?? ''),
        $product['main_image'] !== null ? (string)$product['main_image'] : null
    );
    $product['description'] = $description;
    $product['features'] = $features;
    $product['specifications'] = $specifications;
    $product['seo_description'] = seoDescription($product, (string)($product['category_name'] ?? ''));
    $score = scoreProduct($product, (int)$product['image_count']);
    $minimumScore = min($minimumScore, $score);
    $maximumScore = max($maximumScore, $score);
    $scoreTotal += $score;
    $scoreCounts['80_plus'] += $score >= 80 ? 1 : 0;
    $scoreCounts['90_plus'] += $score >= 90 ? 1 : 0;
    $scoreCounts['100'] += $score === 100 ? 1 : 0;
    $updates[] = [
        'id' => (int)$product['id'],
        'description' => $description,
        'features' => $features,
        'specifications' => $specifications,
        'seo_description' => $product['seo_description'],
        'score' => $score,
    ];
}

if ($apply) {
    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            "UPDATE products SET description=?,features=?,specifications=?,seo_description=?,updated_at=datetime('now','localtime')
             WHERE id=? AND status='draft'"
        );
        foreach ($updates as $update) {
            $statement->execute([
                $update['description'],
                $update['features'],
                $update['specifications'],
                $update['seo_description'],
                $update['id'],
            ]);
            if ($statement->rowCount() !== 1) {
                throw new RuntimeException('Concurrent product update detected for product ' . $update['id']);
            }
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

echo json_encode([
    'ok' => true,
    'applied' => $apply,
    'products' => count($updates),
    'score_80_plus' => $scoreCounts['80_plus'],
    'score_90_plus' => $scoreCounts['90_plus'],
    'score_100' => $scoreCounts['100'],
    'score_min' => $minimumScore,
    'score_max' => $maximumScore,
    'score_average' => round($scoreTotal / count($updates), 2),
], JSON_UNESCAPED_UNICODE) . PHP_EOL;
