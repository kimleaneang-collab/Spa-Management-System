<?php
declare(strict_types=1);

$nameParts = preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$initials = $nameParts === []
    ? '?'
    : strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page therapist-dashboard-page" data-role="<?= e(strtolower((string) ($_SESSION['user']['role'] ?? 'staff'))) ?>">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark">
                    <span class="brand-leaf">⌁</span>
                    <div class="brand-text"><div class="brand-name">Relax</div><div class="brand-subtitle">SPA</div></div>
                </div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>My Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>My Customer</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">▣</span><span>Treatment History</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>My Schedule</span></a>
                </div>
                <div class="nav-section" data-label="Work">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">♨</span><span>Service</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                </div>
                <?= therapist_account_nav('change-password') ?>
            </nav>
            <?= sidebar_role_footer(false) ?>
            <p class="therapist-sidebar-note">Stay calm &amp; relax.<br>You're doing great today!</p>
        </aside>
        <main class="main-panel">
            <header class="topbar therapist-topbar">
                <div class="search-box search-input-wrap">
                    <span class="search-icon" aria-hidden="true">⌕</span>
                    <input class="search-input" type="search" placeholder="Search customer, appointments, services..." aria-label="Search">
                </div>
                <div class="topbar-actions">
                    <span class="therapist-notification" aria-hidden="true">♧</span>
                    <a class="therapist-calendar-shortcut" href="<?= e(APP_URL) ?>/?route=calendar" aria-label="Open my schedule">▦</a>
                    <div class="user-chip therapist-user-chip">
                        <?= profile_avatar($_SESSION['user'] ?? [], 'user-name', $initials) ?>
                        <span class="therapist-user-details"><strong><?= e($userName) ?></strong><small>Therapist</small></span>
                    </div>
                </div>
            </header>
            <div class="content-wrap therapist-profile-content">
                <div class="therapist-profile-page-heading">
                    <h1>Change Password</h1>
                    <p>Keep your account secure with a strong password</p>
                </div>

                <?php if ($formError !== ''): ?>
                    <div class="therapist-password-message therapist-password-error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>
                <?php if ($formSuccess !== ''): ?>
                    <div class="therapist-password-message therapist-password-success" role="status"><?= e($formSuccess) ?></div>
                <?php endif; ?>

                <section class="therapist-profile-card therapist-password-card">
                    <form method="post" action="<?= e(APP_URL) ?>/?route=change-password" class="therapist-password-form">
                        <input type="hidden" name="csrf_token" value="<?= e((string) $_SESSION['csrf_token']) ?>">
                        <label>
                            <span>Current Password</span>
                            <input type="password" name="current_password" autocomplete="current-password" maxlength="255" required>
                        </label>
                        <label>
                            <span>New Password</span>
                            <input type="password" name="new_password" autocomplete="new-password" minlength="16" maxlength="255" required>
                            <small>At least 16 characters, with a letter, a number, and a symbol.</small>
                        </label>
                        <label>
                            <span>Confirm New Password</span>
                            <input type="password" name="confirm_password" autocomplete="new-password" minlength="16" maxlength="255" required>
                        </label>
                        <button class="therapist-profile-save" type="submit">Update Password</button>
                    </form>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
