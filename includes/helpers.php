<?php
function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function vnd(int $amount): string {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

function numFmt(int $n): string {
    return number_format($n, 0, ',', '.');
}

// Đọc số tiền thành chữ tiếng Việt (cho hóa đơn)
function docSoThanhChu($number) {
    $number = (int) round((float)$number);
    if ($number === 0) return 'Không đồng';
    $neg = $number < 0; $number = abs($number);
    $u = ['không','một','hai','ba','bốn','năm','sáu','bảy','tám','chín'];
    $group = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ', 'tỷ tỷ'];
    $readTriple = function($n, $full) use ($u) {
        $tram = intdiv($n, 100); $chuc = intdiv($n % 100, 10); $dv = $n % 10; $s = '';
        if ($tram > 0) { $s .= $u[$tram] . ' trăm'; }
        elseif ($full) { $s .= 'không trăm'; }
        if ($chuc > 1) {
            $s .= ' ' . $u[$chuc] . ' mươi';
            if ($dv === 1) $s .= ' mốt'; elseif ($dv === 5) $s .= ' lăm'; elseif ($dv > 0) $s .= ' ' . $u[$dv];
        } elseif ($chuc === 1) {
            $s .= ' mười';
            if ($dv === 5) $s .= ' lăm'; elseif ($dv > 0) $s .= ' ' . $u[$dv];
        } else {
            if ($dv > 0) { if ($tram > 0 || $full) $s .= ' lẻ'; $s .= ' ' . $u[$dv]; }
        }
        return trim($s);
    };
    $groups = []; $n = $number;
    while ($n > 0) { $groups[] = $n % 1000; $n = intdiv($n, 1000); }
    $cnt = count($groups); $parts = [];
    for ($i = $cnt - 1; $i >= 0; $i--) {
        $g = $groups[$i];
        if ($g === 0) continue;
        $full = ($i < $cnt - 1);
        $parts[] = trim($readTriple($g, $full) . ' ' . $group[$i]);
    }
    $res = trim(preg_replace('/\s+/', ' ', implode(' ', $parts)));
    $res = mb_strtoupper(mb_substr($res, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($res, 1, null, 'UTF-8');
    $res .= ' đồng';
    if ($number % 1000 === 0) $res .= ' chẵn';
    if ($neg) $res = 'Âm ' . $res;
    return $res;
}

function relTime(string $iso): string {
    $ts = strtotime($iso);
    $diff = time() - $ts;
    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff/60) . ' phút trước';
    if ($diff < 86400) return floor($diff/3600) . ' giờ trước';
    if ($diff < 604800) return floor($diff/86400) . ' ngày trước';
    return date('d/m/Y', $ts);
}

// Relative time for notifications. Notifications are stored in UTC (admin_notifications plain,
// user_notifications ISO-Z); orders/local timestamps pass $storedLocal=true (Vietnam +7 wall-clock).
function agoVN($ts, $storedLocal = false): string {
    if (!$ts) return '';
    $ts = trim($ts);
    if ($storedLocal) {
        $epoch = strtotime($ts);
        $now = time() + 7 * 3600;
        $dispEpoch = $epoch;
    } else {
        $hasTz = (substr($ts, -1) === 'Z' || strpos($ts, '+') !== false);
        $epoch = strtotime($hasTz ? $ts : $ts . ' UTC');
        $now = time();
        $dispEpoch = ($epoch !== false) ? $epoch + 7 * 3600 : false;
    }
    if ($epoch === false) return '';
    $s = $now - $epoch; if ($s < 0) $s = 0;
    if ($s < 60) return 'vừa xong';
    if ($s < 3600) return floor($s / 60) . ' phút trước';
    if ($s < 86400) return floor($s / 3600) . ' giờ trước';
    if ($s < 2592000) return floor($s / 86400) . ' ngày trước';
    return gmdate('d/m/Y', $dispEpoch);
}

function truncate(string $s, int $len = 40): string {
    return mb_strlen($s) > $len ? mb_substr($s, 0, $len) . '...' : $s;
}

function stars(int $n): string {
    return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
}

function orderStatus(string $s): string {
    $map = [
        'pending_payment' => 'Chờ thanh toán',
        'awaiting_confirm' => 'Chờ xác nhận',
        'processing' => 'Đang chuẩn bị',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
        'refunded' => 'Đã hoàn tiền',
    ];
    return $map[$s] ?? $s;
}

function currentPath(): string {
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}

function isActive(string $path): string {
    return currentPath() === $path ? 'active' : '';
}

function startsWith(string $haystack, string $needle): bool {
    return str_starts_with($haystack, $needle);
}

function view(string $template, array $data = []): void {
    $data['user'] = currentUser();
    $data['flash'] = getFlash();
    $data['csrf'] = csrfToken();
    $data['path'] = currentPath();
    $data['query'] = $_GET;
    extract($data);
    require __DIR__ . '/../views/' . $template . '.php';
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Cart helper
function cartInfo(): array {
    $user = currentUser();
    if (!$user || $user['role'] !== 'customer') {
        if (!empty($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart'])) {
            $cnt = 0;
            $total = 0;
            foreach ($_SESSION['guest_cart'] as $pid => $qty) {
                $p = dbGet('SELECT CASE WHEN is_on_sale=1 AND sale_price>0 AND sale_price<price THEN sale_price ELSE price END AS final_price FROM products WHERE id=?', [(int)$pid]);
                if ($p) {
                    $cnt += $qty;
                    $total += $p['final_price'] * $qty;
                }
            }
            return ['cnt' => $cnt, 'total' => $total];
        }
        return ['cnt' => 0, 'total' => 0];
    }
    $r = dbGet('SELECT COALESCE(SUM(ci.quantity),0) AS cnt, COALESCE(SUM((CASE WHEN p.is_on_sale=1 AND p.sale_price>0 AND p.sale_price<p.price THEN p.sale_price ELSE p.price END) * ci.quantity),0) AS total FROM cart_items ci INNER JOIN products p ON p.id = ci.product_id WHERE ci.user_id = ?', [$user['id']]);
    return $r ?: ['cnt' => 0, 'total' => 0];
}

function favCount(): int {
    $user = currentUser();
    if (!$user) return 0;
    $r = dbGet('SELECT COUNT(*) AS n FROM favorites WHERE user_id = ?', [$user['id']]);
    return $r ? (int)$r['n'] : 0;
}

function removeAccents($str) {
    $str = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $str);
    $str = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $str);
    $str = preg_replace('/[ìíịỉĩ]/u', 'i', $str);
    $str = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $str);
    $str = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $str);
    $str = preg_replace('/[ỳýỵỷỹ]/u', 'y', $str);
    $str = preg_replace('/đ/u', 'd', $str);
    $str = preg_replace('/[ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴ]/u', 'A', $str);
    $str = preg_replace('/[ÈÉẸẺẼÊỀẾỆỂỄ]/u', 'E', $str);
    $str = preg_replace('/[ÌÍỊỈĨ]/u', 'I', $str);
    $str = preg_replace('/[ÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠ]/u', 'O', $str);
    $str = preg_replace('/[ÙÚỤỦŨƯỪỨỰỬỮ]/u', 'U', $str);
    $str = preg_replace('/[ỲÝỴỶỸ]/u', 'Y', $str);
    $str = preg_replace('/Đ/u', 'D', $str);
    return $str;
}

/**
 * Build a stable ASCII slug for product URLs.
 */
function seoSlug(string $value): string {
    $value = mb_strtolower(removeAccents(trim($value)), 'UTF-8');
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim((string)$value, '-');
}

/**
 * Reproduce the legacy slug algorithm so old malformed URLs can be redirected.
 */
function legacyProductSlug(string $value): string {
    $value = preg_replace('/\s+/', '-', strtolower(trim($value)));
    return trim((string)preg_replace('/[^a-z0-9\-]/', '', $value), '-');
}

function uniqueProductSlug(string $value, int $excludeId = 0): string {
    $base = seoSlug($value);
    if ($base === '') $base = 'san-pham';

    $slug = $base;
    $suffix = 2;
    while (dbGet('SELECT id FROM products WHERE slug=? AND id<>?', [$slug, $excludeId])) {
        $slug = $base . '-' . $suffix;
        $suffix++;
    }
    return $slug;
}

function rememberProductSlugRedirect(int $productId, string $oldSlug, string $newSlug): void {
    $oldSlug = trim($oldSlug);
    $newSlug = trim($newSlug);
    if ($productId < 1 || $oldSlug === '' || $oldSlug === $newSlug) return;

    dbRun("CREATE TABLE IF NOT EXISTS product_slug_redirects (
        slug TEXT PRIMARY KEY,
        product_id INTEGER NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    dbRun('INSERT OR REPLACE INTO product_slug_redirects (slug, product_id) VALUES (?,?)', [$oldSlug, $productId]);
}

function productPath(array $product): string {
    $slug = trim((string)($product['slug'] ?? ''));
    $identifier = $slug !== '' ? $slug : (string)(int)($product['id'] ?? 0);
    return '/products/' . rawurlencode($identifier);
}

function productCanonicalUrl(array $product): string {
    return 'https://coolingsystems.vn' . productPath($product);
}

/**
 * Convert rich text (including text that was entity-encoded more than once)
 * to clean, single-line text suitable for metadata and JSON-LD.
 */
function seoPlainText(?string $value): string {
    $text = strip_tags((string)$value);
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) break;
        $text = $decoded;
    }
    $text = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $text);
    $text = preg_replace('/[\s\x{00A0}]+/u', ' ', (string)$text);
    return trim((string)$text);
}

function seoTruncateText(?string $value, int $limit): string {
    $text = seoPlainText($value);
    if ($limit < 1 || mb_strlen($text, 'UTF-8') <= $limit) return $text;

    $short = mb_substr($text, 0, $limit + 1, 'UTF-8');
    $short = preg_replace('/\s+\S*$/u', '', $short);
    if ($short === '' || mb_strlen($short, 'UTF-8') < (int)($limit * 0.6)) {
        $short = mb_substr($text, 0, $limit, 'UTF-8');
    }
    return rtrim((string)$short, " \t\n\r\0\x0B,.;:-");
}

function productMetaTitle(array $product, int $limit = 65): string {
    $customTitle = !empty($product['seo_title']) ? $product['seo_title'] : ($product['meta_title'] ?? '');
    $source = seoPlainText($customTitle !== '' ? $customTitle : ($product['name'] ?? ''));
    $source = preg_replace('/\s+[—\-]\s*CoolingSystem\s*$/iu', '', $source);
    if (mb_strlen((string)$source, 'UTF-8') <= $limit) return (string)$source;

    $oem = seoPlainText($product['oem_code'] ?? '');
    if ($oem !== '' && mb_stripos((string)$source, $oem, 0, 'UTF-8') !== false) {
        $withoutOem = trim((string)preg_replace('/\s*' . preg_quote($oem, '/') . '\s*/iu', ' ', (string)$source, 1));
        $prefixLimit = max(20, $limit - mb_strlen($oem, 'UTF-8') - 1);
        return trim(seoTruncateText($withoutOem, $prefixLimit) . ' ' . $oem);
    }
    return seoTruncateText((string)$source, $limit);
}

function productMetaDescription(array $product, int $limit = 155): string {
    $customDescription = !empty($product['seo_description']) ? $product['seo_description'] : ($product['meta_description'] ?? '');
    $custom = seoPlainText($customDescription);
    if ($custom !== '') return seoTruncateText($custom, $limit);

    $name = seoPlainText($product['name'] ?? '');
    $price = max(0, (int)($product['display_price'] ?? $product['price'] ?? 0));
    $stockText = (int)($product['stock'] ?? 0) > 0 ? 'Còn hàng' : 'Tạm hết hàng';
    $warranty = (int)($product['warranty_months'] ?? 0);
    $parts = [$name];
    if ($price > 0) $parts[] = 'Giá ' . number_format($price, 0, ',', '.') . ' đ';
    $parts[] = $stockText;
    if ($warranty > 0) $parts[] = 'Bảo hành ' . $warranty . ' tháng';

    return seoTruncateText(implode('. ', array_filter($parts)) . ' tại CoolingSystem.', $limit);
}

function jsonLd(array $data): string {
    $json = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
        | JSON_INVALID_UTF8_SUBSTITUTE
    );
    return $json === false ? '{}' : $json;
}

// Giữ tên file ảnh người dùng đặt (chuẩn SEO), chỉ chuẩn hóa: bỏ dấu TV, khoảng trắng/ký tự lạ -> '-', và tránh trùng tên.
function seoImageName($originalName, $ext, $uploadDir) {
    $base = pathinfo((string)$originalName, PATHINFO_FILENAME);
    $slug = strtolower(removeAccents($base));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    if ($slug === '') { $slug = 'p-' . substr(md5(uniqid('', true)), 0, 10); }
    $dir = rtrim($uploadDir, '/') . '/';
    $fname = $slug . '.' . $ext;
    $i = 2;
    while (file_exists($dir . $fname)) { $fname = $slug . '-' . $i . '.' . $ext; $i++; }
    return $fname;
}

function resolveProductSku(?string $sku, ?string $oemCode): string {
    $sku = trim((string)$sku);
    return $sku !== '' ? $sku : trim((string)$oemCode);
}

function removeProductImageBackground(string $sourcePath, string $destinationPath, ?string &$error = null): bool {
    $error = null;
    $curl = '/usr/bin/curl';
    if (!is_executable($curl)) {
        $error = 'Máy chủ chưa sẵn sàng công cụ kết nối bộ tách nền.';
        return false;
    }

    $command = escapeshellcmd($curl)
        . ' --fail-with-body --silent --show-error'
        . ' --connect-timeout 2 --max-time 90'
        . ' --header ' . escapeshellarg('Content-Type: application/octet-stream')
        . ' --data-binary ' . escapeshellarg('@' . $sourcePath)
        . ' --output ' . escapeshellarg($destinationPath)
        . ' ' . escapeshellarg('http://127.0.0.1:7010/remove')
        . ' 2>&1';

    $output = [];
    $exitCode = 1;
    exec($command, $output, $exitCode);

    $imageInfo = is_file($destinationPath) ? @getimagesize($destinationPath) : false;
    if ($exitCode !== 0 || !$imageInfo || strtolower((string)($imageInfo['mime'] ?? '')) !== 'image/png') {
        $serviceMessage = trim(implode(' ', $output));
        if ($serviceMessage !== '') {
            error_log('Product background removal failed: ' . $serviceMessage);
        }
        @unlink($destinationPath);
        $error = 'AI tách nền chưa xử lý an toàn được ảnh này. Ảnh gốc vẫn được giữ để kiểm tra lại.';
        return false;
    }

    return true;
}

function normalizeProductImageFile(string $sourcePath, string $destinationPath, ?string &$error = null): bool {
    $error = null;
    if (!is_file($sourcePath) || !is_readable($sourcePath)) {
        $error = 'Không đọc được ảnh gốc.';
        return false;
    }

    $imageInfo = @getimagesize($sourcePath);
    if (!$imageInfo || empty($imageInfo[0]) || empty($imageInfo[1])) {
        $error = 'Tệp tải lên không phải là ảnh hợp lệ.';
        return false;
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    $mime = strtolower((string)($imageInfo['mime'] ?? ''));
    if (!in_array($mime, $allowedMimes, true)) {
        $error = 'Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.';
        return false;
    }

    $pixelCount = (int)$imageInfo[0] * (int)$imageInfo[1];
    if ($pixelCount > 80000000) {
        $error = 'Ảnh có kích thước điểm ảnh quá lớn.';
        return false;
    }

    $convert = '/usr/bin/convert';
    if (!is_executable($convert)) {
        $error = 'Máy chủ chưa sẵn sàng công cụ chuẩn hóa ảnh.';
        return false;
    }

    $destinationDir = dirname($destinationPath);
    if (!is_dir($destinationDir) && !@mkdir($destinationDir, 0775, true)) {
        $error = 'Không tạo được thư mục lưu ảnh.';
        return false;
    }

    $temporaryPath = $destinationDir . '/.'
        . pathinfo($destinationPath, PATHINFO_FILENAME)
        . '-processing-' . bin2hex(random_bytes(4)) . '.webp';
    $cutoutPath = $destinationDir . '/.'
        . pathinfo($destinationPath, PATHINFO_FILENAME)
        . '-cutout-' . bin2hex(random_bytes(4)) . '.png';

    if (!removeProductImageBackground($sourcePath, $cutoutPath, $error)) {
        return false;
    }

    $baseCommand = escapeshellcmd($convert)
        . ' -limit memory 256MiB -limit map 512MiB '
        . escapeshellarg($cutoutPath)
        . ' -auto-orient -strip -colorspace sRGB ';
    $finishCommand = ' -filter Lanczos -resize ' . escapeshellarg('1020x765')
        . ' -gravity center -background ' . escapeshellarg('#ffffff')
        . ' -alpha background -alpha off -extent ' . escapeshellarg('1200x900')
        . ' -unsharp ' . escapeshellarg('0x0.68+0.58+0.024')
        . ' -define webp:method=6 -define webp:alpha-quality=100'
        . ' -quality 93 ' . escapeshellarg($temporaryPath) . ' 2>&1';

    $output = [];
    $exitCode = 1;
    exec($baseCommand . ' -fuzz 4% -trim +repage ' . $finishCommand, $output, $exitCode);

    // Fall back to the complete original frame if conservative edge trimming fails.
    if ($exitCode !== 0 || !is_file($temporaryPath)) {
        @unlink($temporaryPath);
        $output = [];
        exec($baseCommand . $finishCommand, $output, $exitCode);
    }

    @unlink($cutoutPath);

    $normalizedInfo = is_file($temporaryPath) ? @getimagesize($temporaryPath) : false;
    if ($exitCode !== 0 || !$normalizedInfo || (int)$normalizedInfo[0] !== 1200 || (int)$normalizedInfo[1] !== 900) {
        @unlink($temporaryPath);
        $error = 'Không thể chuẩn hóa ảnh này.';
        return false;
    }

    if (!@rename($temporaryPath, $destinationPath)) {
        @unlink($temporaryPath);
        $error = 'Không thể lưu ảnh đã chuẩn hóa.';
        return false;
    }

    @chmod($destinationPath, 0664);
    return true;
}

function storeNormalizedProductUpload(array $file, string $seoBase, string $uploadDir, ?string &$error = null): ?string {
    $error = null;
    $tmpPath = (string)($file['tmp_name'] ?? '');
    $uploadError = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError !== UPLOAD_ERR_OK || $tmpPath === '' || !is_uploaded_file($tmpPath)) {
        $error = 'Ảnh tải lên không hợp lệ.';
        return null;
    }

    $imageInfo = @getimagesize($tmpPath);
    $mimeToExtension = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mime = strtolower((string)($imageInfo['mime'] ?? ''));
    if (!isset($mimeToExtension[$mime])) {
        $error = 'Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.';
        return null;
    }

    $uploadDir = rtrim($uploadDir, '/') . '/';
    $seoBase = mb_substr(trim($seoBase), 0, 120, 'UTF-8');
    $originalDir = '/var/lib/coolingsystems/product-originals/';
    if (!is_dir($originalDir) && !@mkdir($originalDir, 0770, true)) {
        $error = 'Không tạo được thư mục lưu ảnh gốc.';
        return null;
    }

    // A unique suffix prevents browsers/CDNs from reusing an older product
    // image after an administrator replaces the image set.
    $uploadVersion = gmdate('YmdHis') . '-' . bin2hex(random_bytes(4));
    $outputName = seoImageName($seoBase . '-' . $uploadVersion, 'webp', $uploadDir);
    $outputStem = pathinfo($outputName, PATHINFO_FILENAME);
    $originalName = seoImageName($outputStem . '-original', $mimeToExtension[$mime], $originalDir);
    $originalPath = $originalDir . $originalName;

    if (!move_uploaded_file($tmpPath, $originalPath)) {
        $error = 'Không thể lưu ảnh gốc.';
        return null;
    }
    @chmod($originalPath, 0660);

    if (!normalizeProductImageFile($originalPath, $uploadDir . $outputName, $error)) {
        return null;
    }

    return $outputName;
}

function getRxnEmoji($type) {
    $map = [
        'like' => '👍', 'love' => '❤️', 'haha' => '😂',
        'wow' => '😮', 'sad' => '😢', 'angry' => '😡',
        'helpful' => '🙏', 'thanks' => '🤝'
    ];
    return $map[$type] ?? '👍';
}

// --- Password-change audit (super-admin oversight). Records method + email snapshot; NEVER the password. ---
function logPasswordChange(int $targetUserId, string $method): void {
    $actor = function_exists('currentUser') ? currentUser() : null;
    $target = dbGet("SELECT email FROM users WHERE id=?", [$targetUserId]);
    $meta = json_encode([
        'method'       => $method,
        'target_email' => $target['email'] ?? '',
        'actor_email'  => $actor['email'] ?? '',
    ], JSON_UNESCAPED_UNICODE);
    dbRun(
        "INSERT INTO audit_logs (user_id, role, action, entity_type, entity_id, meta, ip, user_agent) VALUES (?,?,?,?,?,?,?,?)",
        [$actor['id'] ?? null, $actor['role'] ?? '', 'password_change', 'user', $targetUserId, $meta,
         $_SERVER['REMOTE_ADDR'] ?? '', substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)]
    );
}

function fmtVnDateTime($ts) {
    if (!$ts) return '—';
    try { $d = new DateTime((string)$ts); $d->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh')); return $d->format('d/m/Y H:i'); }
    catch (\Exception $e) { return (string)$ts; }
}

// ===== Phí vận chuyển theo vùng miền + cân nặng (kiểu Viettel Post) =====
function vnProvinceList(): array {
    return ['An Giang','Bà Rịa - Vũng Tàu','Bạc Liêu','Bắc Giang','Bắc Kạn','Bắc Ninh','Bến Tre','Bình Dương','Bình Định','Bình Phước','Bình Thuận','Cà Mau','Cao Bằng','Cần Thơ','Đà Nẵng','Đắk Lắk','Đắk Nông','Điện Biên','Đồng Nai','Đồng Tháp','Gia Lai','Hà Giang','Hà Nam','Hà Nội','Hà Tĩnh','Hải Dương','Hải Phòng','Hậu Giang','Hòa Bình','TP. Hồ Chí Minh','Hưng Yên','Khánh Hòa','Kiên Giang','Kon Tum','Lai Châu','Lâm Đồng','Lạng Sơn','Lào Cai','Long An','Nam Định','Nghệ An','Ninh Bình','Ninh Thuận','Phú Thọ','Phú Yên','Quảng Bình','Quảng Nam','Quảng Ngãi','Quảng Ninh','Quảng Trị','Sóc Trăng','Sơn La','Tây Ninh','Thái Bình','Thái Nguyên','Thanh Hóa','Thừa Thiên Huế','Tiền Giang','Trà Vinh','Tuyên Quang','Vĩnh Long','Vĩnh Phúc','Yên Bái'];
}
function vnProvinceOptions(string $selected=''): string {
    $h = '<option value="">-- Chọn tỉnh/thành --</option>';
    foreach(vnProvinceList() as $p){
        $h .= '<option value="'.htmlspecialchars($p, ENT_QUOTES).'"'.($p===$selected?' selected':'').'>'.htmlspecialchars($p).'</option>';
    }
    return $h;
}
function vnNormProvince(string $name): string {
    $n = str_replace(['đ','Đ'], ['d','D'], $name);
    $n = strtolower(trim(removeAccents($n)));
    $n = preg_replace('/^(tp\.?|thanh pho|tinh)\s+/', '', $n);
    $n = preg_replace('/[^a-z0-9 ]+/', ' ', $n);
    $n = preg_replace('/\s+/', ' ', trim($n));
    return $n;
}
function vnProvinceRegion(string $name): string {
    static $map = null;
    if ($map === null) {
        $bac = ['Hà Nội','Hà Giang','Cao Bằng','Bắc Kạn','Tuyên Quang','Lào Cai','Điện Biên','Lai Châu','Sơn La','Yên Bái','Hòa Bình','Thái Nguyên','Lạng Sơn','Quảng Ninh','Bắc Giang','Phú Thọ','Vĩnh Phúc','Bắc Ninh','Hải Dương','Hải Phòng','Hưng Yên','Thái Bình','Hà Nam','Nam Định','Ninh Bình'];
        $trung = ['Thanh Hóa','Nghệ An','Hà Tĩnh','Quảng Bình','Quảng Trị','Thừa Thiên Huế','Đà Nẵng','Quảng Nam','Quảng Ngãi','Bình Định','Phú Yên','Khánh Hòa','Ninh Thuận','Bình Thuận','Kon Tum','Gia Lai','Đắk Lắk','Đắk Nông','Lâm Đồng'];
        $nam = ['Bình Phước','Bình Dương','Đồng Nai','Tây Ninh','Bà Rịa - Vũng Tàu','TP. Hồ Chí Minh','Long An','Tiền Giang','Bến Tre','Trà Vinh','Vĩnh Long','Đồng Tháp','An Giang','Kiên Giang','Cần Thơ','Hậu Giang','Sóc Trăng','Bạc Liêu','Cà Mau'];
        $map = [];
        foreach($bac as $p) $map[vnNormProvince($p)]='bac';
        foreach($trung as $p) $map[vnNormProvince($p)]='trung';
        foreach($nam as $p) $map[vnNormProvince($p)]='nam';
    }
    return $map[vnNormProvince($name)] ?? '';
}
function shippingZoneCode(string $origin, string $dest): string {
    $no = vnNormProvince($origin); $nd = vnNormProvince($dest);
    if ($no !== '' && $no === $nd) return 'noi_tinh';
    $oReg = vnProvinceRegion($origin); $dReg = vnProvinceRegion($dest);
    if ($oReg === '' || $dReg === '') return '';
    if ($oReg === $dReg) return 'noi_mien';
    if ($oReg === 'trung' || $dReg === 'trung') return 'can_mien';
    return 'lien_mien';
}
function shippingZoneLabel(string $zone): string {
    $m = ['noi_tinh'=>'Nội tỉnh','noi_mien'=>'Nội miền','can_mien'=>'Cận miền','lien_mien'=>'Liên miền'];
    return $m[$zone] ?? '—';
}
function calcShippingFee(string $destProvince, int $totalWeightG, ?array $cfg = null): int {
    if ($cfg === null) {
        $rows = dbAll("SELECT key, value FROM system_config WHERE key IN ('shipping_origin_province','shipping_rates','default_shipping_fee')");
        $cfg = []; foreach($rows as $r) $cfg[$r['key']] = $r['value'];
    }
    $fallback = intval($cfg['default_shipping_fee'] ?? 30000);
    $origin = (string)($cfg['shipping_origin_province'] ?? '');
    $rates = json_decode((string)($cfg['shipping_rates'] ?? '[]'), true);
    if (!is_array($rates) || empty($rates) || $origin === '') return $fallback;
    $zone = shippingZoneCode($origin, $destProvince);
    if ($zone === '') return $fallback;
    $r = null; foreach($rates as $row){ if(($row['zone'] ?? '')===$zone){ $r=$row; break; } }
    if (!$r) return $fallback;
    $bw = max(1, intval($r['base_weight'] ?? 1000));
    $bp = max(0, intval($r['base_price'] ?? 0));
    $sw = max(1, intval($r['step_weight'] ?? 500));
    $sp = max(0, intval($r['step_price'] ?? 0));
    $w = $totalWeightG > 0 ? $totalWeightG : $bw;
    if ($w <= $bw) return $bp;
    $steps = (int)ceil(($w - $bw) / $sw);
    return $bp + $steps * $sp;
}

// ===== CSV import helpers (header-based, accept Vietnamese or English headers) =====
function csvNorm($s){ $s = strtolower(trim((string)$s)); $s = str_replace(['đ','Đ'], ['d','d'], $s); $s = removeAccents($s); $s = preg_replace('/\(.*?\)/', '', $s); $s = preg_replace('/[^a-z0-9]+/', ' ', $s); return trim(preg_replace('/\s+/', ' ', $s)); }
function csvColMap($headerRow, array $aliases){ $m = []; if (is_array($headerRow)) { foreach ($headerRow as $idx=>$h) { $nh = csvNorm($h); foreach ($aliases as $k=>$vs) { if (!isset($m[$k]) && in_array($nh, $vs, true)) { $m[$k] = $idx; break; } } } } return $m; }
function csvGet($row, $map, $key, $default=''){ return (isset($map[$key]) && isset($row[$map[$key]])) ? $row[$map[$key]] : $default; }
function csvInt($v){ return (int)preg_replace('/[^0-9-]/', '', (string)$v); }

// ===== Sidebar menu icons (Feather-style line icons, gray via .sb-ic CSS) =====
function sbIcon($n){
    $m = [
        'home'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'box'=>'<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/>',
        'plus'=>'<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'folder'=>'<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
        'tag'=>'<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
        'truck'=>'<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        'cart'=>'<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'undo'=>'<path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>',
        'shield'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'users'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user'=>'<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'filetext'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'file'=>'<path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/>',
        'message'=>'<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'star'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'mail'=>'<rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,6 12,13 2,6"/>',
        'pin'=>'<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'list'=>'<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
        'gift'=>'<polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
        'ticket'=>'<path d="M3 7v2a3 3 0 1 1 0 6v2a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-2a3 3 0 1 1 0-6V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"/>',
        'gear'=>'<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        'logout'=>'<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
    ];
    $p = $m[$n] ?? $m['file'];
    return '<svg class="sb-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$p.'</svg>';
}


/* ===== CSV EXPORT — chọn cột (column picker) ===== */
function exportColMeta($section) {
    $m = [
        'products' => ['name'=>'Tên sản phẩm','sku'=>'Mã SKU','oem'=>'Mã OEM','category'=>'Danh mục','brand'=>'Thương hiệu','car_brand'=>'Hãng xe','cost'=>'Giá nhập','price'=>'Giá bán','original'=>'Giá gốc','stock'=>'Tồn kho','weight'=>'Khối lượng (g)','dims'=>'Kích thước (cm)','warranty'=>'Bảo hành (tháng)','features'=>'Đặc điểm sản phẩm','specs'=>'Thông số kỹ thuật','description'=>'Mô tả','image'=>'Ảnh đại diện','status'=>'Trạng thái','featured'=>'Nổi bật'],
        'orders' => ['code'=>'Mã đơn','customer'=>'Khách hàng','phone'=>'SĐT','email'=>'Email','address'=>'Địa chỉ','total'=>'Tổng tiền','delivery'=>'Trạng thái giao','payment'=>'Trạng thái thanh toán','paytype'=>'Hình thức TT','created'=>'Ngày tạo'],
        'users' => ['name'=>'Họ và tên','email'=>'Email','phone'=>'SĐT','role'=>'Vai trò','status'=>'Trạng thái','address'=>'Địa chỉ','created'=>'Ngày tạo','inv_type'=>'Loại hóa đơn','inv_name'=>'Tên người mua / Công ty','inv_tax'=>'Mã số thuế','inv_addr'=>'Địa chỉ hóa đơn','inv_prov'=>'Tỉnh/TP (HĐ)','inv_cccd'=>'Số CCCD/CMND','inv_email'=>'Email hóa đơn','inv_phone'=>'SĐT hóa đơn','inv_bank'=>'Ngân hàng','inv_acct'=>'Số TK ngân hàng'],
        'categories' => ['id'=>'ID','name'=>'Tên danh mục','slug'=>'Đường dẫn','parent'=>'Danh mục cha','sort'=>'Thứ tự','featured'=>'Nổi bật'],
        'brands' => ['brand'=>'Hãng xe','model'=>'Dòng xe','model_slug'=>'Đường dẫn (dòng xe)','year_from'=>'Năm bắt đầu','year_to'=>'Năm kết thúc'],
        'product_brands' => ['id'=>'ID','name'=>'Tên thương hiệu','description'=>'Mô tả','sort'=>'Thứ tự'],
    ];
    return $m[$section] ?? [];
}
function exportPickKeys($allKeys) {
    $sel = array_values(array_filter(array_map('trim', explode(',', $_GET['cols'] ?? ''))));
    $k = $sel ? array_values(array_intersect($sel, $allKeys)) : $allKeys;
    return $k ?: $allKeys;
}
function csvHeadSel($out, $section) {
    $lbl = exportColMeta($section); $keys = exportPickKeys(array_keys($lbl));
    fputcsv($out, array_map(function($k) use($lbl){ return $lbl[$k]; }, $keys));
    return $keys;
}
function csvRowSel($out, $keys, $row) {
    fputcsv($out, array_map(function($k) use($row){ return $row[$k] ?? ''; }, $keys));
}
