<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']),
        'samesite' => 'Lax',
    ]);
    session_start();
}
require_once __DIR__ . '/../config.php';

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}