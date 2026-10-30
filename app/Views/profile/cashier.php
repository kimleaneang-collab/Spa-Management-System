<?php
declare(strict_types=1);

$userName = (string) ($profileUser['full_name'] ?? $profileUser['username'] ?? 'Cashier');
$nameParts = preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$userInitials = $nameParts === []
    ? '?'
    : strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
$email = (string) ($profileUser['email'] ?? '');
$phone = (string) ($profileUser['phone'] ?? '');
$roleName = role_label((string) ($profileUser['role_name'] ?? 'cashier'));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page cashier-dashboard-page cashier-profile-page" data-role="cashier">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS / New Sale</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard#recent-transactions"><span class="nav-icon">▧</span><span>Transactions</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=receipts"><span class="nav-icon">▤</span><span>Receipt</span></a>
                </div>
                <div class="nav-section" data-label="Quick Access">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Service</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                </div>
                <div class="nav-section" data-label="Settings">
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=cashier-profile"><span class="nav-icon">◎</span><span>My Profile</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-actions cashier-profile-topbar">
                    <label class="cashier-profile-search">
                        <span aria-hidden="true">⌕</span>
                        <span class="visually-hidden">Search</span>
                        <input type="search" placeholder="Search customer, appointments, services...">
                    </label>
                    <div class="cashier-profile-tools" aria-hidden="true"><span>♧</span><span>▣</span></div>
                    <div class="user-chip">
                        <?= profile_avatar($profileUser, 'user-name', $userInitials) ?>
                        <span class="cashier-profile-user"><strong><?= e($userName) ?></strong><small>Cashier</small></span>
                    </div>
                </div>
            </header>

            <div class="content-wrap cashier-profile-content">
                <div class="cashier-profile-heading">
                    <h1>My profile</h1>
                    <p>Your account details and preferences.</p>
                </div>

                <?php if ($formError !== ''): ?>
                    <div class="cashier-profile-message error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>
                <?php if ($formSuccess !== ''): ?>
                    <div class="cashier-profile-message success" role="status"><?= e($formSuccess) ?></div>
                <?php endif; ?>

                <section class="cashier-profile-identity" aria-label="Profile identity">
                    <span class="cashier-profile-avatar"><?= e($userInitials) ?></span>
                    <div><h2><?= e($userName) ?></h2><p><?= e($roleName) ?> Emerald Spa, Phnom Penh</p></div>
                </section>

                <section class="cashier-profile-form-panel">
                    <h2>Personal Information</h2>
                    <form method="post" action="<?= e(APP_URL) ?>/?route=cashier-profile">
                        <input type="hidden" name="csrf_token" value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                        <div class="cashier-profile-fields">
                            <label><span>Full name</span><input type="text" name="full_name" value="<?= e($userName) ?>" minlength="2" maxlength="150" autocomplete="name" required></label>
                            <label><span>Email</span><input type="email" name="email" value="<?= e($email) ?>" maxlength="150" autocomplete="email"></label>
                            <label><span>Phone</span><input type="tel" name="phone" value="<?= e($phone) ?>" pattern="[0-9+()\-\s]{7,30}" maxlength="30" autocomplete="tel"></label>
                            <label><span>Role</span><input type="text" value="<?= e($roleName) ?>" readonly aria-label="Role"></label>
                        </div>
                        <button type="submit" class="cashier-profile-save">Save Changes</button>
                    </form>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
