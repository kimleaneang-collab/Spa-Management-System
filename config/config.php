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

// Build a cache key from the file's modification time and content.
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

$isProduction = strtolower((string) (getenv('APP_ENV') ?: 'local')) === 'production';
ini_set('log_errors', '1');
ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('display_startup_errors', $isProduction ? '0' : '1');

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
ini_set('session.cookie_lifetime', '0');
ini_set('session.gc_maxlifetime', '1800');

$secureCookie = (
    !empty($_SERVER['HTTPS'])
    && strtolower((string) $_SERVER['HTTPS']) !== 'off'
);

if (strtolower((string) (getenv('APP_ENV') ?: 'local')) === 'production' && !$secureCookie) {
    http_response_code(400);
    exit('HTTPS must be enabled for production.');
}

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

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self'");
header('Cache-Control: no-store, private');
if ($secureCookie) {
    header('Strict-Transport-Security: max-age=31536000');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!empty($_SESSION['user']['id'])) {
    $now = time();
    $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);
    $authenticatedAt = (int) ($_SESSION['authenticated_at'] ?? 0);
    if (
        $lastActivity === 0
        || $authenticatedAt === 0
        || $now - $lastActivity > 1800
        || $now - $authenticatedAt > 28800
    ) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $_SESSION['last_activity'] = $now;
    }
}

/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

// Escape a value so it can be safely shown in HTML.
function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// Create the hidden form field that carries the current CSRF token.
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e((string) ($_SESSION['csrf_token'] ?? '')) . '">';
}

// Check the submitted CSRF token and stop the request if it is invalid.
function require_valid_csrf_token(): void
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (
        empty($_SESSION['csrf_token'])
        || !hash_equals((string) $_SESSION['csrf_token'], $token)
    ) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

// Save an audit record with the user, action, and optional record changes.
function write_audit_log(
    PDO $db,
    string $action,
    ?string $tableName = null,
    ?int $recordId = null,
    ?array $oldValues = null,
    ?array $newValues = null
): void {
    $statement = $db->prepare(
        'INSERT INTO audit_logs
            (user_id, action, table_name, record_id, old_values, new_values, ip_address)
         VALUES
            (:user_id, :action, :table_name, :record_id, :old_values, :new_values, :ip_address)'
    );
    $statement->execute([
        ':user_id' => isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null,
        ':action' => $action,
        ':table_name' => $tableName,
        ':record_id' => $recordId,
        ':old_values' => $oldValues !== null ? json_encode($oldValues, JSON_THROW_ON_ERROR) : null,
        ':new_values' => $newValues !== null ? json_encode($newValues, JSON_THROW_ON_ERROR) : null,
        ':ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
    ]);
}

// Return a public URL only when the stored profile image path is safe.
function profile_image_url(string $imagePath): string
{
    $imagePrefix = 'public/uploads/user_profiles/';

    if (str_starts_with($imagePath, $imagePrefix)) {
        $filename = substr($imagePath, strlen($imagePrefix));
        if ($filename !== '' && basename($filename) === $filename) {
            return APP_URL . '/' . $imagePrefix . rawurlencode($filename);
        }
    }

    return '';
}

// Build avatar HTML from a user's saved image or fallback text.
function profile_avatar(array $user, string $class, string $fallback): string
{
    $imageUrl = profile_image_url((string) ($user['profile_image'] ?? ''));
    $content = $imageUrl !== ''
        ? '<img src="' . e($imageUrl) . '" alt="" aria-hidden="true">'
        : e($fallback);

    return '<span class="' . e($class) . '">' . $content . '</span>';
}

// Check and save an uploaded profile photo, returning its path and any error.
function store_profile_image(?array $upload): array
{
    if ($upload === null) {
        return ['', ''];
    }

    $uploadError = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
    if (!is_scalar($uploadError)) {
        return ['', 'The selected profile photo is invalid. Please try again.'];
    }
    if ((int) $uploadError === UPLOAD_ERR_NO_FILE) {
        return ['', ''];
    }
    if ((int) $uploadError !== UPLOAD_ERR_OK) {
        return ['', 'The profile photo could not be uploaded. Please select it again.'];
    }

    $uploadSize = $upload['size'] ?? null;
    if (!is_scalar($uploadSize) || !is_numeric($uploadSize)) {
        return ['', 'The selected profile photo is invalid. Please try again.'];
    }
    if ((int) $uploadSize > 5 * 1024 * 1024) {
        return ['', 'The profile photo must be no larger than 5 MB.'];
    }

    $temporaryPath = $upload['tmp_name'] ?? '';
    if (!is_string($temporaryPath) || $temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
        return ['', 'The selected profile photo could not be verified. Please try again.'];
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    if (!is_string($mimeType) || !isset($extensions[$mimeType])) {
        return ['', 'Choose a JPG, PNG, GIF, or WEBP profile photo.'];
    }
    if (getimagesize($temporaryPath) === false) {
        return ['', 'The selected profile photo is not a valid image.'];
    }

    $directory = APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'user_profiles';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create the profile photo upload directory.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
    $destination = $directory . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($temporaryPath, $destination)) {
        return ['', 'The profile photo could not be saved. Please try again.'];
    }

    return ['public/uploads/user_profiles/' . $filename, ''];
}

// Remove a saved profile photo after checking that its path is safe.
function remove_profile_image(string $imagePath): void
{
    $prefix = 'public/uploads/user_profiles/';
    if (!str_starts_with($imagePath, $prefix)) {
        return;
    }

    $filename = substr($imagePath, strlen($prefix));
    if ($filename === '' || basename($filename) !== $filename) {
        return;
    }

    $absolutePath = APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'user_profiles' . DIRECTORY_SEPARATOR . $filename;
    if (is_file($absolutePath) && !unlink($absolutePath)) {
        error_log('Unable to remove profile image: ' . $absolutePath);
    }
}

// Check and save an uploaded image, optionally placing it in a subfolder.
function store_image_upload(?array $upload, string $subdirectory = ''): array
{
    if ($upload === null) {
        return ['', ''];
    }

    $uploadError = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
    if (!is_scalar($uploadError)) {
        return ['', 'The selected image is invalid. Please try again.'];
    }
    if ((int) $uploadError === UPLOAD_ERR_NO_FILE) {
        return ['', ''];
    }
    if ((int) $uploadError !== UPLOAD_ERR_OK) {
        return ['', 'The image could not be uploaded. Please select it again.'];
    }

    $temporaryPath = $upload['tmp_name'] ?? '';
    $uploadSize = $upload['size'] ?? null;
    if (
        !is_string($temporaryPath)
        || $temporaryPath === ''
        || !is_uploaded_file($temporaryPath)
        || !is_scalar($uploadSize)
        || !is_numeric($uploadSize)
        || (int) $uploadSize > 5 * 1024 * 1024
        || (int) filesize($temporaryPath) > 5 * 1024 * 1024
    ) {
        return ['', 'Choose a valid image no larger than 5 MB.'];
    }

    if ($subdirectory !== '' && preg_match('/^[A-Za-z0-9_-]+$/', $subdirectory) !== 1) {
        throw new InvalidArgumentException('Invalid image upload directory.');
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $imageInfo = getimagesize($temporaryPath);
    if (
        !is_string($mimeType)
        || !isset($extensions[$mimeType])
        || $imageInfo === false
        || $imageInfo[0] < 1
        || $imageInfo[1] < 1
        || $imageInfo[0] > 8000
        || $imageInfo[1] > 8000
        || $imageInfo[0] * $imageInfo[1] > 40000000
    ) {
        return ['', 'Choose a valid JPG, PNG, GIF, or WEBP image.'];
    }

    $uploadRoot = APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
    $directory = $subdirectory === ''
        ? $uploadRoot
        : $uploadRoot . DIRECTORY_SEPARATOR . $subdirectory;
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create the image upload directory.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
    $destination = $directory . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($temporaryPath, $destination)) {
        return ['', 'The image could not be saved. Please try again.'];
    }

    $relativePath = 'public/uploads/';
    if ($subdirectory !== '') {
        $relativePath .= $subdirectory . '/';
    }

    return [$relativePath . $filename, ''];
}

// Remove a saved upload only when it is inside the uploads folder.
function remove_uploaded_image(string $imagePath): void
{
    $prefix = 'public/uploads/';
    if (!str_starts_with($imagePath, $prefix)) {
        return;
    }

    $relativePath = substr($imagePath, strlen($prefix));
    if (
        $relativePath === ''
        || str_contains($relativePath, '..')
        || str_contains($relativePath, '\\')
    ) {
        return;
    }

    $uploadRoot = realpath(APP_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads');
    $absolutePath = realpath(
        APP_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imagePath)
    );
    if (
        $uploadRoot === false
        || $absolutePath === false
        || !str_starts_with($absolutePath, $uploadRoot . DIRECTORY_SEPARATOR)
        || !is_file($absolutePath)
    ) {
        return;
    }

    if (!unlink($absolutePath)) {
        error_log('Unable to remove uploaded image: ' . $absolutePath);
    }
}

// Turn an internal role name into the label shown to users.
function role_label(string $role): string
{
    return match (strtolower(trim($role))) {
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'staff' => 'Therapist',
        'receptionist' => 'Receptionist',
        'cashier' => 'Cashier',
        default => ucfirst($role),
    };
}

// Build the sidebar card showing the current role, avatar, and optional logout.
function sidebar_role_footer(bool $includeLogout = true): string
{
    $user = $_SESSION['user'] ?? [];
    $name = trim((string) ($user['full_name'] ?? $user['username'] ?? 'User'));
    $nameParts = preg_split('/\s+/', $name) ?: [];
    $initials = strtoupper(substr($nameParts[0] ?? 'U', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
    $role = role_label((string) ($user['role'] ?? ''));
    $roleCardClass = strtolower((string) ($user['role'] ?? '')) === 'admin' ? ' sidebar-role-admin' : '';

    $logout = $includeLogout
        ? '<form method="post" action="' . e(APP_URL) . '/?route=logout" class="sidebar-logout-form">'
            . '<input type="hidden" name="csrf_token" value="' . e((string) ($_SESSION['csrf_token'] ?? '')) . '">'
            . '<button class="sidebar-logout-button" type="submit"><span aria-hidden="true">↪</span>Logout</button>'
            . '</form>'
        : '';

    return '<div class="sidebar-role' . $roleCardClass . '">'
        . '<span>Current Role</span>'
        . '<div class="role-select">' . profile_avatar($user, 'role-avatar', $initials)
        . '<span>' . e($role) . '</span><span class="chevron" aria-hidden="true">⌄</span></div>'
        . $logout
        . '</div>';
}

// Build therapist account links and mark the current page as active.
function therapist_account_nav(string $activeRoute = ''): string
{
    $profileClass = $activeRoute === 'profile' ? ' active' : '';
    $passwordClass = $activeRoute === 'change-password' ? ' active' : '';

    return '<div class="nav-section" data-label="Account">'
        . '<a class="nav-item' . $profileClass . '" href="' . e(APP_URL) . '/?route=profile"><span class="nav-icon">♙</span><span>My Profile</span></a>'
        . '<a class="nav-item' . $passwordClass . '" href="' . e(APP_URL) . '/?route=change-password"><span class="nav-icon">♧</span><span>Change Password</span></a>'
        . '<form method="post" action="' . e(APP_URL) . '/?route=logout" class="therapist-sidebar-logout-form">'
        . '<input type="hidden" name="csrf_token" value="' . e((string) ($_SESSION['csrf_token'] ?? '')) . '">'
        . '<button class="nav-item therapist-sidebar-logout" type="submit"><span class="nav-icon" aria-hidden="true">↪</span><span>Logout</span></button>'
        . '</form>'
        . '</div>';
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

// Send the browser to the named application route and stop the request.
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

// Check that text has an allowed length and contains no angle brackets.
function valid_text(string $value, int $min = 1, int $max = 255): bool
{
    $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

    return $length >= $min && $length <= $max && preg_match('/[<>]/', $value) !== 1;
}

// Check that a username uses allowed characters and has a valid length.
function valid_username(string $value): bool
{
    return preg_match('/^[A-Za-z0-9@_.-]{3,100}$/', $value) === 1;
}

// Accept an optional blank email or check that a provided email is valid.
function valid_email(string $value, bool $required = false): bool
{
    return ($value === '' && !$required) || filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// Accept an optional blank phone or check its allowed characters and length.
function valid_phone(string $value, bool $required = false): bool
{
    return ($value === '' && !$required) || preg_match('/^[0-9+()\-\s]{7,30}$/', $value) === 1;
}

// Check password length and require letters, numbers, and a symbol.
function valid_password(string $value, bool $required = true): bool
{
    return ($value === '' && !$required)
        || (strlen($value) >= 16 && strlen($value) <= 255
            && preg_match('/[A-Za-z]/', $value) === 1
            && preg_match('/[0-9]/', $value) === 1
            && preg_match('/[^A-Za-z0-9\s]/', $value) === 1);
}

// Check that a date is blank only when allowed or matches YYYY-MM-DD.
function valid_date_value(string $value, bool $required = false): bool
{
    if ($value === '') {
        return !$required;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);

    return $date !== false && $date->format('Y-m-d') === $value;
}

// Check that a value is numeric and zero or greater.
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
    // Normalize each allowed role before comparing it with the current role.
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

    $adminOnly = ['backup', 'audit-logs'];
    $managerRoutes = ['users', 'users-and-roles', 'settings', 'therapists'];
    $staffRoutes = ['dashboard', 'appointments', 'calendar', 'services', 'rooms', 'customers', 'memberships', 'customer-history', 'history', 'products', 'stock', 'stock-management', 'suppliers', 'reports', 'report', 'sales', 'inventory-report'];
    $receptionistRoutes = ['dashboard', 'appointments', 'new-appointment', 'calendar', 'services', 'rooms', 'customers', 'register-customer', 'memberships', 'customer-history', 'history', 'pos'];
    $cashierRoutes = ['dashboard', 'pos', 'customers', 'receipts', 'services', 'products', 'memberships', 'cashier-profile'];

    if (in_array($route, $adminOnly, true)) {
        require_roles(['admin']);
    } elseif (in_array($route, $managerRoutes, true)) {
        require_roles(['admin', 'manager']);
    } elseif ($route === 'pos') {
        require_roles(['admin', 'manager', 'staff', 'receptionist', 'cashier']);
    } elseif (in_array($route, $cashierRoutes, true)
        && strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'cashier') {
        require_roles(['cashier']);
    } elseif ($route === 'profile') {
        require_roles(['admin', 'manager', 'staff', 'therapist', 'receptionist', 'cashier']);
    } elseif ($route === 'cashier-profile') {
        require_roles(['cashier']);
    } elseif ($route === 'change-password') {
        require_roles(['staff', 'therapist']);
    } elseif (in_array($route, $receptionistRoutes, true)) {
        require_roles(['admin', 'manager', 'staff', 'receptionist']);
    } elseif (in_array($route, $staffRoutes, true)) {
        require_roles(['admin', 'manager', 'staff']);
    } else {
        require_authentication();
    }
}

// Check that a login password is present and within the allowed length.
function valid_login_password(string $value): bool
{
    return $value !== '' && strlen($value) <= 255;
}