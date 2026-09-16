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

function valid_text(string $value, int $min = 1, int $max = 255): bool
{
    $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

    return $length >= $min && $length <= $max && preg_match('/[<>]/', $value) !== 1;
}

function valid_email(string $value, bool $required = false): bool
{
    return ($value === '' && !$required) || filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function valid_phone(string $value, bool $required = false): bool
{
    return ($value === '' && !$required) || preg_match('/^[0-9+()\-\s]{7,30}$/', $value) === 1;
}

function valid_password(string $value, bool $required = true): bool
{
    return ($value === '' && !$required)
        || (strlen($value) >= 8 && strlen($value) <= 255
            && preg_match('/[A-Za-z]/', $value) === 1
            && preg_match('/[0-9]/', $value) === 1);
}

function valid_date_value(string $value, bool $required = false): bool
{
    if ($value === '') {
        return !$required;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);

    return $date !== false && $date->format('Y-m-d') === $value;
}

function valid_non_negative_number(string $value): bool
{
    return is_numeric($value) && (float) $value >= 0;
}

function require_authentication(): void
{
    if (empty($_SESSION['user']['id'])) {
        redirect('login');
    }
}

function require_roles(array $allowedRoles): void
{
    require_authentication();

    $role = strtolower(trim((string) ($_SESSION['user']['role'] ?? '')));
    $allowedRoles = array_map(static fn (string $value): string => strtolower(trim($value)), $allowedRoles);

    if (!in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        echo '403 - Access Denied';
        exit;
    }
}

function authorize_route(string $route): void
{
    if ($route === 'login' || $route === 'logout' || $route === '') {
        return;
    }

    $adminOnly = ['users', 'users-and-roles', 'settings', 'backup'];
    $staffRoutes = ['dashboard', 'appointments', 'services', 'therapists', 'rooms', 'customers', 'memberships', 'customer-history', 'history', 'products', 'stock', 'stock-management', 'suppliers', 'reports', 'report', 'sales', 'inventory-report'];
    $receptionistRoutes = ['dashboard', 'appointments', 'services', 'therapists', 'rooms', 'customers', 'memberships', 'customer-history', 'history'];

    if (in_array($route, $adminOnly, true)) {
        require_roles(['admin']);
    } elseif (in_array($route, $staffRoutes, true)) {
        require_roles(['admin', 'staff', 'receptionist']);
    } else {
        require_authentication();
    }
}

function valid_login_password(string $value): bool
{
    return $value !== '' && strlen($value) <= 255;
}