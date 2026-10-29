<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

$formatChange = static function (?float $change): string {
    if ($change === null) {
        return 'No prior-period data';
    }

    $arrow = $change > 0 ? '↑' : ($change < 0 ? '↓' : '•');
    return $arrow . ' ' . number_format(abs($change), 1) . '% vs last week';
};
$changeClass = static fn (?float $change): string => $change !== null && $change < 0 ? 'negative' : 'positive';
$formatMoney = static fn (float $amount): string => e($currency) . ' ' . number_format($amount, 2);
$userName = (string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'User');
$userInitials = strtoupper(substr($userName, 0, 2));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reports &amp; Analytics | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page analytics-page <?= strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'manager' ? 'manager-role' : '' ?>" data-role="<?= e(strtolower((string) ($_SESSION['user']['role'] ?? ''))) ?>">
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>Calendar</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section" data-label="Quick Actions">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Services</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS &amp; Billing</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Inventory</span></a>
                </div>
                <div class="nav-section" data-label="Reports">
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
                </div>
                <div class="nav-section" data-label="Settings">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div></div>
                <div class="topbar-actions">
                    <button class="icon-button analytics-topbar-icon" type="button" aria-label="Notifications">♧</button>
                    <a class="icon-button analytics-topbar-icon" href="<?= e(APP_URL) ?>/?route=appointments" aria-label="Create appointment">▣</a>
                    <div class="user-chip">
                        <?= profile_avatar($_SESSION['user'] ?? [], 'user-name', $userInitials) ?>
                        <span class="analytics-user-identity"><strong><?= e($userName) ?></strong><small><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'staff'))) ?></small></span>
                        <span class="user-chevron">⌄</span>
                    </div>
                </div>
            </header>

            <div class="content-wrap analytics-content">
                <div class="analytics-heading">
                    <div>
                        <h1 class="page-title">REPORTS &amp; ANALYTICS</h1>
                        <p>Performance across bookings, revenue and staff.</p>
                    </div>
                    <span class="analytics-period">▤ &nbsp;Last 7 Days</span>
                </div>

                <section class="analytics-stat-grid" aria-label="Performance summary for the last seven days">
                    <article class="analytics-stat-card">
                        <span>Total Revenue</span>
                        <strong><?= e($formatMoney((float) $report['totalRevenue'])) ?></strong>
                        <b class="<?= e($changeClass($report['revenueChange'])) ?>"><?= e($formatChange($report['revenueChange'])) ?></b>
                    </article>
                    <article class="analytics-stat-card">
                        <span>Total Appointments</span>
                        <strong><?= number_format((int) $report['totalAppointments']) ?></strong>
                        <b class="<?= e($changeClass($report['appointmentChange'])) ?>"><?= e($formatChange($report['appointmentChange'])) ?></b>
                    </article>
                    <article class="analytics-stat-card">
                        <span>Avg. Ticket Size</span>
                        <strong><?= e($formatMoney((float) $report['averageTicket'])) ?></strong>
                        <b class="steady">Average paid invoice</b>
                    </article>
                    <article class="analytics-stat-card">
                        <span>Rebooking Rate</span>
                        <strong><?= number_format((int) $report['rebookingRate']) ?>%</strong>
                        <b class="<?= e($changeClass($report['rebookingChange'])) ?>"><?= e($formatChange($report['rebookingChange'])) ?></b>
                    </article>
                </section>

                <section class="analytics-panel analytics-revenue-panel">
                    <h2>REVENUE — LAST 7 DAYS</h2>
                    <div class="analytics-chart" role="img" aria-label="Daily revenue amounts for the last seven days">
                        <?php foreach ($report['dailyRevenue'] as $day): ?>
                            <div class="analytics-chart-day" title="<?= e($day['label'] . ': ' . $formatMoney((float) $day['revenue'])) ?>">
                                <div class="analytics-chart-column">
                                    <span style="height: <?= e((string) $day['bar_height']) ?>%"></span>
                                </div>
                                <small><?= e((string) $day['label']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="analytics-panel analytics-services-panel">
                    <h2>TOP SERVICES</h2>
                    <?php if ($report['topServices'] === []): ?>
                        <p class="analytics-empty">There are no paid service sales in this period yet.</p>
                    <?php else: ?>
                        <div class="analytics-service-list">
                            <?php foreach ($report['topServices'] as $index => $service): ?>
                                <div class="analytics-service-row">
                                    <span class="analytics-service-name"><i class="service-color service-color-<?= e((string) $index) ?>"></i><?= e((string) $service['name']) ?></span>
                                    <span class="analytics-service-share"><?= number_format((int) $service['share']) ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
