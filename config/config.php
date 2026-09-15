<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Application Configuration
|--------------------------------------------------------------------------
*/

define('APP_NAME', 'Relax Spa Management System');
define('APP_URL', '/Relax-Spa');

/*
|--------------------------------------------------------------------------
| Timezone
|--------------------------------------------------------------------------
*/

date_default_timezone_set('Asia/Phnom_Penh');

/*
|--------------------------------------------------------------------------
| Session Security
|--------------------------------------------------------------------------
*/

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_trans_sid', '0');

$secureCookie = (
    isset($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== 'off'
);

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

/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
|
| Our application uses:
|
| /Relax-Spa/?route=login
| /Relax-Spa/?route=dashboard
|
*/

function redirect(string $route): never
{
    $route = trim($route, '/');

    header(
        'Location: ' .
        APP_URL .
        '/?route=' .
        urlencode($route)
    );

    exit;
}