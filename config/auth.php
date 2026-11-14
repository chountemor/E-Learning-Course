<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function app_base_url(): string {
    $folder = '/' . basename(dirname(__DIR__));
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $pos = strpos($script, $folder);
    return $pos === false ? '' : substr($script, 0, $pos + strlen($folder));
}

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'] ?? (app_base_url() . '/index.php');
        header('Location: ' . app_base_url() . '/Login/login.php');
        exit;
    }
}

function is_admin(): bool {
    return !empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin';
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . app_base_url() . '/Login/login.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function verify_csrf(?string $token): bool {
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
