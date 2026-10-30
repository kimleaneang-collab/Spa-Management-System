<?php
declare(strict_types=1);

$user = $_SESSION['user'] ?? [];
$userName = (string) ($user['full_name'] ?? $user['username'] ?? 'Cashier');
$nameParts = preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$userInitials = $nameParts === []
    ? '?'
    : strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
$activeServices = array_values(array_filter(
    $services,
    static fn (array $service): bool => $service['status'] === 'Active'
));
$activeCategories = array_values(array_unique(array_column($activeServices, 'category')));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page cashier-dashboard-page cashier-services-page" data-role="cashier">
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
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Service</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                </div>
                <div class="nav-section" data-label="Settings">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=cashier-profile"><span class="nav-icon">◎</span><span>My Profile</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-actions cashier-services-topbar">
                    <label class="cashier-services-global-search">
                        <span aria-hidden="true">⌕</span>
                        <span class="visually-hidden">Search services</span>
                        <input type="search" placeholder="Search customer, appointments, services..." data-cashier-service-search>
                    </label>
                    <div class="cashier-services-tools" aria-hidden="true"><span>♧</span><span>▣</span></div>
                    <div class="user-chip">
                        <?= profile_avatar($user, 'user-name', $userInitials) ?>
                        <span class="cashier-services-user"><strong><?= e($userName) ?></strong><small>Cashier</small></span>
                    </div>
                </div>
            </header>

            <div class="content-wrap cashier-services-content">
                <div class="cashier-services-heading">
                    <div><h1>Services</h1><p>Manage the spa's service menu and pricing.</p></div>
                    <button class="cashier-services-add" type="button" disabled aria-disabled="true" title="Services are managed by an administrator">＋ Add service</button>
                </div>
                <nav class="cashier-service-categories" aria-label="Filter services">
                    <button class="active" type="button" data-cashier-service-category="all" aria-pressed="true">All</button>
                    <?php foreach ($activeCategories as $category): ?>
                        <button type="button" data-cashier-service-category="<?= e(strtolower(trim((string) $category))) ?>" aria-pressed="false"><?= e((string) $category) ?></button>
                    <?php endforeach; ?>
                </nav>

                <section class="cashier-service-grid" aria-label="Available spa services">
                    <?php foreach ($activeServices as $service): ?>
                        <?php $categoryKey = strtolower(trim((string) $service['category'])); ?>
                        <article class="cashier-service-card" data-cashier-service-card data-category="<?= e($categoryKey) ?>" data-search="<?= e(strtolower((string) $service['name'] . ' ' . (string) $service['category'] . ' ' . (string) $service['description'])) ?>">
                            <img src="<?= e((string) $service['image']) ?>" alt="<?= e((string) $service['name']) ?>" loading="lazy">
                            <div class="cashier-service-card-body">
                                <h2><?= e((string) $service['name']) ?></h2>
                                <p><?= e((string) $service['duration']) ?> - <?= e((string) $service['category']) ?></p>
                                <strong><?= e((string) $service['price']) ?></strong>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($activeServices === []): ?>
                        <p class="cashier-service-empty">No active services are available yet.</p>
                    <?php endif; ?>
                </section>
                <p class="cashier-service-no-results" data-cashier-service-no-results hidden>No services match your search.</p>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
