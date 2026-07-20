<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'HEAD') {
    $method = 'GET';
}

// Static files
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|webp|woff2?)$/i', $uri)) {
    return false;
}

// Route matching
$routes = [];

function get(string $path, callable $handler): void {
    global $routes;
    $routes[] = ['GET', $path, $handler];
}

function post(string $path, callable $handler): void {
    global $routes;
    $routes[] = ['POST', $path, $handler];
}

function matchRoute(string $method, string $uri): ?array {
    global $routes;
    foreach ($routes as $route) {
        if ($route[0] !== $method) continue;
        $pattern = preg_replace('/\:([a-zA-Z_]+)/', '(?P<$1>[^/]+)', $route[1]);
        $pattern = '#^' . $pattern . '$#';
        if (preg_match($pattern, $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return ['handler' => $route[2], 'params' => $params];
        }
    }
    return null;
}

// Load route definitions
require_once __DIR__ . '/../routes/public.php';
require_once __DIR__ . '/../routes/auth.php';
require_once __DIR__ . '/../routes/customer.php';
require_once __DIR__ . '/../routes/partner.php';
require_once __DIR__ . '/../routes/admin.php';
require_once __DIR__ . '/../routes/api.php';

// Dispatch
$match = matchRoute($method, $uri);
if ($match) {
    try {
        call_user_func($match['handler'], $match['params']);
    } catch (\Throwable $e) {
        error_log('[COOLING_ERROR] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
        file_put_contents('/tmp/php_cooling_errors.log', date('Y-m-d H:i:s').' ['.$_SERVER['REQUEST_URI'].'] '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine()."\n".$e->getTraceAsString()."\n---\n", FILE_APPEND);
        http_response_code(500);
        view('errors/500', ['title' => 'Lỗi hệ thống', 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(404);
    view('errors/404', ['title' => 'Không tìm thấy trang']);
}
