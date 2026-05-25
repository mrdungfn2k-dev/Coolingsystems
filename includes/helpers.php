<?php
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function vnd(int $amount): string {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

function numFmt(int $n): string {
    return number_format($n, 0, ',', '.');
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
    if (!$user || $user['role'] !== 'customer') return ['cnt' => 0, 'total' => 0];
    $r = dbGet('SELECT COUNT(*) AS cnt, COALESCE(SUM(p.price * ci.quantity),0) AS total FROM cart_items ci INNER JOIN products p ON p.id = ci.product_id WHERE ci.user_id = ?', [$user['id']]);
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

function getRxnEmoji($type) {
    $map = [
        'like' => '👍', 'love' => '❤️', 'haha' => '😂',
        'wow' => '😮', 'sad' => '😢', 'angry' => '😡',
        'helpful' => '🙏', 'thanks' => '🤝'
    ];
    return $map[$type] ?? '👍';
}
