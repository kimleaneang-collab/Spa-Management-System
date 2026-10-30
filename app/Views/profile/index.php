<?php
declare(strict_types=1);

$currentUser = $_SESSION['user'] ?? [];
$userName = (string) ($profileUser['full_name'] ?? $profileUser['username'] ?? 'User');
$userRole = strtolower((string) ($profileUser['role_name'] ?? $currentUser['role'] ?? ''));
$nameParts = preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$userInitials = $nameParts === []
    ? '?'
    : strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
$profileImageUrl = profile_image_url((string) ($profileUser['profile_image'] ?? ''));
$therapistRoles = ['staff', 'therapist'];
$sidebarRoutes = match ($userRole) {
    'manager' => [
        ['dashboard', 'Dashboard', '⌂'],
        ['appointments', 'Appointments', '☰'],
        ['customers', 'Customers', '◉'],
        ['calendar', 'Calendar', '▦'],
        ['rooms', 'Rooms', '◫'],
        ['services', 'Services', '✦'],
        ['pos', 'POS & Billing', '▤'],
        ['products', 'Inventory', '▣'],
        ['reports', 'Reports & Analytics', '▥'],
        ['users-and-roles', 'Users & Roles', '⚙'],
        ['settings', 'Settings', '⚙'],
        ['profile', 'My Profile', '◎'],
    ],
    'receptionist' => [
        ['dashboard', 'Dashboard', '⌂'],
        ['appointments', 'Appointments', '☷'],
        ['customers', 'Customers', '◉'],
        ['calendar', 'Calendar', '▦'],
        ['rooms', 'Rooms', '◫'],
        ['services', 'Services', '✦'],
        ['memberships', 'Memberships', '◌'],
        ['customer-history', 'Customer History', '◍'],
        ['pos', 'POS & Billing', '▤'],
        ['new-appointment', 'New Appointment', '＋'],
        ['register-customer', 'Register Customer', '♙'],
        ['profile', 'My Profile', '◎'],
    ],
    'cashier' => [
        ['dashboard', 'Dashboard', '⌂'],
        ['profile', 'My Profile', '◎'],
    ],
    'staff', 'therapist' => [
        ['dashboard', 'Dashboard', '⌂'],
        ['appointments', 'My Appointments', '☷'],
        ['customers', 'My Customer', '♙'],
        ['customer-history', 'Treatment History', '▣'],
        ['calendar', 'My Schedule', '▦'],
        ['services', 'Service', '♨'],
        ['products', 'Products', '▣'],
    ],
    default => [
        ['dashboard', 'Dashboard', '⌂'],
        ['appointments', 'Appointments', '☰'],
        ['services', 'Treatments', '✦'],
        ['therapists', 'Therapists', '◎'],
        ['rooms', 'Rooms', '◫'],
        ['customers', 'Customers', '◉'],
        ['memberships', 'Memberships', '◌'],
        ['customer-history', 'Customer History', '◍'],
        ['products', 'Products', '▣'],
        ['stock-management', 'Stock Management', '▤'],
        ['suppliers', 'Supplier', '◧'],
        ['reports', 'Reports & Analytics', '▥'],
        ['users-and-roles', 'Staff Management', '⚙'],
        ['settings', 'Settings', '⚙'],
        ['profile', 'My Profile', '◎'],
    ],
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page account-profile-page" data-role="<?= e($userRole) ?>">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section">
                    <?php foreach ($sidebarRoutes as [$route, $label, $icon]): ?>
                        <?php if (in_array($userRole, $therapistRoles, true) && $route === 'profile') { continue; } ?>
                        <a class="nav-item<?= $route === 'profile' ? ' active' : '' ?>" href="<?= e(APP_URL) ?>/?route=<?= e($route) ?>"><span class="nav-icon"><?= e($icon) ?></span><span><?= e($label) ?></span></a>
                    <?php endforeach; ?>
                </div>
                <?php if (in_array($userRole, $therapistRoles, true)): ?>
                    <?= therapist_account_nav('profile') ?>
                <?php endif; ?>
            </nav>
            <?= sidebar_role_footer(!in_array($userRole, $therapistRoles, true)) ?>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">My account</div></div>
                <div class="topbar-actions">
                    <div class="user-chip">
                        <?= profile_avatar($profileUser, 'user-name', $userInitials) ?>
                        <span class="user-role"><?= e(role_label($userRole)) ?></span>
                    </div>
                </div>
            </header>

            <div class="content-wrap account-profile-content">
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">MY PROFILE</h1>
                        <div class="page-date">View and update your personal contact details.</div>
                    </div>
                </div>

                <?php if ($formError !== ''): ?>
                    <div class="account-profile-message error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>
                <?php if ($formSuccess !== ''): ?>
                    <div class="account-profile-message success" role="status"><?= e($formSuccess) ?></div>
                <?php endif; ?>

                <section class="account-profile-card" aria-label="Profile information">
                    <div class="account-profile-identity">
                        <?= profile_avatar($profileUser, 'account-profile-avatar', $userInitials) ?>
                        <div>
                            <h2><?= e($userName) ?></h2>
                            <p><?= e(role_label($userRole)) ?> account</p>
                        </div>
                    </div>

                    <form class="account-profile-form" method="post" action="<?= e(APP_URL) ?>/?route=profile" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="user-profile-photo-field account-profile-photo-field">
                            <div class="user-profile-photo-preview">
                                <img
                                    id="user-profile-photo-image"
                                    src="<?= e($profileImageUrl) ?>"
                                    alt="Profile photo preview"
                                    <?= $profileImageUrl === '' ? 'hidden' : '' ?>
                                >
                                <span id="user-profile-photo-initials" <?= $profileImageUrl !== '' ? 'hidden' : '' ?>><?= e($userInitials) ?></span>
                            </div>
                            <div class="user-profile-photo-controls">
                                <strong>Profile picture</strong>
                                <label class="user-profile-photo-button" for="user-profile-photo-input">Choose picture</label>
                                <input
                                    class="user-profile-photo-input"
                                    id="user-profile-photo-input"
                                    type="file"
                                    name="profile_image"
                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                >
                                <small id="user-profile-photo-filename">JPG, PNG, GIF, or WEBP. Maximum 5 MB. Leave empty to keep your current picture.</small>
                            </div>
                        </div>
                        <div class="account-profile-fields">
                            <label>
                                <span>Full name</span>
                                <input type="text" name="full_name" value="<?= e($userName) ?>" minlength="2" maxlength="150" autocomplete="name" required>
                            </label>
                            <label>
                                <span>Email</span>
                                <input type="email" name="email" value="<?= e((string) ($profileUser['email'] ?? '')) ?>" maxlength="150" autocomplete="email">
                            </label>
                            <label>
                                <span>Phone</span>
                                <input type="tel" name="phone" value="<?= e((string) ($profileUser['phone'] ?? '')) ?>" pattern="[0-9+()\-\s]{7,30}" maxlength="30" autocomplete="tel">
                            </label>
                            <label>
                                <span>Username</span>
                                <input type="text" value="<?= e((string) ($profileUser['username'] ?? '')) ?>" readonly>
                            </label>
                            <label>
                                <span>Role</span>
                                <input type="text" value="<?= e(role_label($userRole)) ?>" readonly>
                            </label>
                        </div>
                        <p class="account-profile-hint">Your username and role are managed by an administrator and cannot be changed here.</p>
                        <button class="primary-button" type="submit">Save changes</button>
                    </form>
                </section>

                <section class="account-profile-card account-password-card" aria-labelledby="account-password-title">
                    <h2 id="account-password-title">Change password</h2>
                    <p class="account-profile-password-description">Use a unique password you do not use for other accounts.</p>
                    <?php if ($passwordError !== ''): ?>
                        <div class="account-profile-message error" role="alert"><?= e($passwordError) ?></div>
                    <?php endif; ?>
                    <?php if ($passwordSuccess !== ''): ?>
                        <div class="account-profile-message success" role="status"><?= e($passwordSuccess) ?></div>
                    <?php endif; ?>
                    <form class="account-profile-form" method="post" action="<?= e(APP_URL) ?>/?route=profile">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="change_password">
                        <div class="account-profile-fields">
                            <label>
                                <span>Current password</span>
                                <input type="password" name="current_password" autocomplete="current-password" maxlength="255" required>
                            </label>
                            <label>
                                <span>New password</span>
                                <input type="password" name="new_password" autocomplete="new-password" minlength="16" maxlength="255" pattern="(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s]).{16,255}" title="Use at least 16 characters, including a letter, a number, and a symbol." required>
                            </label>
                            <label>
                                <span>Confirm new password</span>
                                <input type="password" name="confirm_password" autocomplete="new-password" minlength="16" maxlength="255" required>
                            </label>
                        </div>
                        <p class="account-profile-hint">Use at least 16 characters, including a letter, a number, and a symbol.</p>
                        <button class="primary-button" type="submit">Update password</button>
                    </form>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
