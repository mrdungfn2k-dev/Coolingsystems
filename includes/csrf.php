<?php
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrfToken()) . '">';
}

function csrfVerify(): bool {
    $token = $_POST['_csrf'] ?? '';
    return hash_equals(csrfToken(), $token);
}

function csrfCheck(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrfVerify()) {
        flash('error', 'Phiên làm việc đã hết hạn, vui lòng thử lại.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
}
