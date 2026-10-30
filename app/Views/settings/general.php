<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Treatments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <?php if (strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'admin'): ?>
                        <a class="nav-item" href="<?= e(APP_URL) ?>/?route=audit-logs"><span class="nav-icon">▤</span><span>Audit Logs</span></a>
                        <a class="nav-item" href="<?= e(APP_URL) ?>/?route=backup"><span class="nav-icon">▤</span><span>Database Backup</span></a>
                    <?php endif; ?>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">◍</span><span>Customer History</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=stock-management"><span class="nav-icon">▤</span><span>Stock Management</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=suppliers"><span class="nav-icon">◧</span><span>Supplier</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>
        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Admin')) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <div class="user-chip"><?= profile_avatar($_SESSION['user'] ?? [], 'user-name', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'SV'), 0, 2))) ?><span class="user-role"><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'admin'))) ?></span><span class="user-chevron">▾</span></div>
                </div>
            </header>
            <div class="content-wrap">
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">SETTINGS</h1>
                        <div class="page-date">System preferences</div>
                    </div>
                </div>
                <section class="settings-layout">
                    <article class="module-panel settings-panel">
                        <div class="settings-heading">
                            <?= profile_avatar(
                                $_SESSION['user'] ?? [],
                                'manager-profile-avatar',
                                strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'M'), 0, 1))
                            ) ?>
                            <div><h2>Manager Profile</h2><p>Your account information</p></div>
                        </div>
                        <div class="settings-fields manager-profile-fields">
                            <div><span class="manager-profile-label">Full Name</span><span class="manager-profile-value"><?= e((string) ($_SESSION['user']['full_name'] ?? 'Manager')) ?></span></div>
                            <div><span class="manager-profile-label">Username</span><span class="manager-profile-value"><?= e((string) ($_SESSION['user']['username'] ?? '')) ?></span></div>
                            <div><span class="manager-profile-label">Email</span><span class="manager-profile-value"><?= e((string) ($_SESSION['user']['email'] ?? 'Not provided')) ?></span></div>
                            <div><span class="manager-profile-label">Role</span><span class="manager-profile-value"><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'manager'))) ?></span></div>
                        </div>
                    </article>
                    <article class="module-panel settings-panel">
                        <div class="settings-heading"><span class="settings-symbol">⌂</span><div><h2>Spa Profile</h2><p>Manage your spa information</p></div></div>
                        <div class="settings-fields"><label>Spa Name<input value="Relax spa"></label><label>Phone Number<input value="+855 12 345 667"></label><label>Email<input value="relaxspa@gmail.com"></label><label>Currency<select><option>USD ($)</option></select></label><label class="wide-field">Address<input value="No, 123, Street 06, Siem Reap, Cambodia"></label></div>
                    </article>
                    <article class="module-panel settings-panel">
                        <div class="settings-heading"><span class="settings-symbol">◷</span><div><h2>Business Hours</h2><p>Set your spa opening hours.</p></div></div>
                        <div class="settings-fields"><label>Opening Time<input value="08:00"></label><label>Closing Time<input value="20:00"></label></div>
                    </article>
                    <article class="module-panel settings-panel settings-toggles">
                        <div class="settings-heading"><span class="settings-symbol">♧</span><div><h2>Notifications</h2><p>Manage system notifications.</p></div></div>
                        <label>Appointment Notifications<input type="checkbox" checked></label>
                        <label>Low Stock Alert<input type="checkbox" checked></label>
                    </article>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
