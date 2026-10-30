<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

$userName = (string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Therapist');
$userRoleLabel = role_label((string) ($_SESSION['user']['role'] ?? 'staff'));
$isCashier = strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'cashier';
$nameParts = preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$userInitials = $nameParts === []
    ? '?'
    : strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
$activeServices = array_values(array_filter(
    $services,
    // Keep only services that are currently active.
    static fn (array $service): bool => $service['status'] === 'Active'
));
$activeCategories = array_unique(array_column($activeServices, 'category'));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page therapist-dashboard-page" data-role="<?= e(strtolower((string) ($_SESSION['user']['role'] ?? 'staff'))) ?>">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <?php if ($isCashier): ?><a class="nav-item" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS / New Sale</span></a><?php endif; ?>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>My Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>My Customer</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">▣</span><span>Treatment History</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>My Schedule</span></a>
                </div>
                <div class="nav-section" data-label="Work">
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">♨</span><span>Service</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                </div>
                <?= therapist_account_nav() ?>
            </nav>
            <?= sidebar_role_footer($isCashier) ?>
            <?php if (!$isCashier): ?><p class="therapist-sidebar-note">Stay calm &amp; relax.<br>You're doing great today!</p><?php endif; ?>
        </aside>
        <main class="main-panel">
            <header class="topbar therapist-topbar">
                <div class="search-box search-input-wrap">
                    <span class="search-icon" aria-hidden="true">⌕</span>
                    <input class="search-input" type="search" placeholder="Search customer, appointments, services..." aria-label="Search services" data-therapist-service-search>
                </div>
                <div class="topbar-actions">
                    <span class="therapist-notification" aria-hidden="true">♧</span>
                    <a class="therapist-calendar-shortcut" href="<?= e(APP_URL) ?>/?route=calendar" aria-label="Open my schedule">▦</a>
                    <div class="user-chip therapist-user-chip">
                        <?= profile_avatar($_SESSION['user'] ?? [], 'user-name', $userInitials) ?>
                        <span class="therapist-user-details"><strong><?= e($userName) ?></strong><small><?= e($userRoleLabel) ?></small></span>
                    </div>
                </div>
            </header>
            <div class="content-wrap therapist-services-content">
                <div class="therapist-services-heading">
                    <div><h1>Service</h1><p>Manage the treatments you offer.</p></div>
                    <button class="therapist-service-add" type="button" disabled aria-disabled="true">＋ Add service</button>
                </div>
                <div class="therapist-service-count"><?= count($activeServices) ?> Services</div>
                <section class="therapist-service-grid" aria-label="Available spa services">
                    <?php foreach ($services as $service): ?>
                        <article class="therapist-service-card <?= $service['status'] !== 'Active' ? 'is-inactive' : '' ?>" data-service-card data-search="<?= e(strtolower((string) $service['name'] . ' ' . (string) $service['category'] . ' ' . (string) $service['description'])) ?>">
                            <div class="therapist-service-card-heading">
                                <h2><?= e((string) $service['name']) ?></h2>
                                <strong><?= e((string) $service['price']) ?></strong>
                            </div>
                            <div class="therapist-service-duration"><?= e((string) $service['duration']) ?></div>
                            <p><?= e((string) $service['description']) ?></p>
                            <?php if ($service['status'] !== 'Active'): ?>
                                <span class="therapist-service-inactive">Inactive</span>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($services === []): ?>
                        <div class="therapist-service-empty">No services are available yet.</div>
                    <?php endif; ?>
                </section>
                <p class="therapist-service-no-results" data-therapist-service-empty hidden>No services match your search.</p>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
