<?php
function flash(string $type, string $message): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function setFlash(string $type, string $message): void {
    flash($type, $message);
}

function getFlash(): array {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $msgs = $_SESSION['flash'] ?? [];
    $_SESSION['flash'] = [];
    return $msgs;
}
