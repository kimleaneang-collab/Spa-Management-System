<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Application Configuration
|--------------------------------------------------------------------------
*/

define('APP_NAME', 'Relax Spa Management System');
define('APP_URL', '/Relax-Spa');
define('APP_ROOT', dirname(__DIR__));

function asset_version(string $assetPath): string
{
    $assetPath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $assetPath), DIRECTORY_SEPARATOR);
    $absolutePath = APP_ROOT . DIRECTORY_SEPARATOR . $assetPath;
    clearstatcache(true, $absolutePath);

    if (!is_file($absolutePath)) {
        return '1';
    }

    $contentHash = hash_file('crc32b', $absolutePath);

    return filemtime($absolutePath) . '-' . ($contentHash !== false ? $contentHash : filesize($absolutePath));
}

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
|
| SECURITY: Harden PHP sessions to reduce hijacking and fixation risks.
| - only cookies are used for session IDs
| - strict session mode is enabled
| - URL-based session IDs are disabled
| - cookies are marked HttpOnly and SameSite=Lax
|--------------------------------------------------------------------------
*/

// SECURITY: Force cookies-only sessions and disable session ID leakage via URLs.
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_trans_sid', '0');

$secureCookie = (
    isset($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== 'off'
);

// SECURITY: Set hard session cookie settings to reduce XSS/session theft exposure.
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);

// SECURITY: Start the session only once to keep the authenticated state consistent.
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

function profile_avatar(array $user, string $class, string $fallback): string
{
    $imagePath = (string) ($user['profile_image'] ?? '');
    $imageUrl = '';
    $imagePrefix = 'public/uploads/user_profiles/';

    if (str_starts_with($imagePath, $imagePrefix)) {
        $filename = substr($imagePath, strlen($imagePrefix));
        if ($filename !== '' && basename($filename) === $filename) {
            $imageUrl = APP_URL . '/' . $imagePrefix . rawurlencode($filename);
        }
    }

    $content = $imageUrl !== ''
        ? '<img src="' . e($imageUrl) . '" alt="" aria-hidden="true">'
        : e($fallback);

    return '<span class="' . e($class) . '">' . $content . '</span>';
}

function role_label(string $role): string
{
    return match (strtolower(trim($role))) {
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'staff' => 'Therapist',
        'receptionist' => 'Receptionist',
        default => ucfirst($role),
    };
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

function valid_username(string $value): bool
{
    return preg_match('/^[A-Za-z0-9@_.-]{3,100}$/', $value) === 1;
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
        || (strlen($value) >= 16 && strlen($value) <= 255
            && preg_match('/[A-Za-z]/', $value) === 1
            && preg_match('/[0-9]/', $value) === 1
            && preg_match('/[^A-Za-z0-9\s]/', $value) === 1);
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

// SECURITY: Enforce login before allowing access to protected routes.
function require_authentication(): void
{
    if (empty($_SESSION['user']['id'])) {
        redirect('login');
    }
}

// SECURITY: Enforce role-based authorization after login to protect admin/staff features.
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

// SECURITY: Route authorization gate to restrict access by role and prevent unauthorized pages.
function authorize_route(string $route): void
{
    if ($route === 'login' || $route === 'logout' || $route === '') {
        return;
    }

    $adminOnly = ['backup'];
    $managerRoutes = ['users', 'users-and-roles', 'settings'];
    $staffRoutes = ['dashboard', 'appointments', 'calendar', 'services', 'therapists', 'rooms', 'customers', 'memberships', 'customer-history', 'history', 'products', 'stock', 'stock-management', 'suppliers', 'pos', 'reports', 'report', 'sales', 'inventory-report'];
    $receptionistRoutes = ['dashboard', 'appointments', 'calendar', 'services', 'therapists', 'rooms', 'customers', 'memberships', 'customer-history', 'history', 'pos'];

    if (in_array($route, $adminOnly, true)) {
        require_roles(['admin']);
    } elseif (in_array($route, $managerRoutes, true)) {
        require_roles(['admin', 'manager']);
    } elseif (in_array($route, $receptionistRoutes, true)) {
        require_roles(['admin', 'manager', 'staff', 'receptionist']);
    } elseif (in_array($route, $staffRoutes, true)) {
        require_roles(['admin', 'manager', 'staff']);
    } else {
        require_authentication();
    }
}

function valid_login_password(string $value): bool
{
    return $value !== '' && strlen($value) <= 255;
}