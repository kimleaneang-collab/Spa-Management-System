<?php
declare(strict_types=1);

/*
 * Application configuration.
 * Change APP_URL and database values for your local environment.
 */
define('APP_NAME', 'Relax Spa Management System');
define('APP_URL', '/Relax-Spa');

date_default_timezone_set('Asia/Phnom_Penh');

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');

$secureCookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
    exit;
}
