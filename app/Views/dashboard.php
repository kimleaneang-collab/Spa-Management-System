<?php

declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}
$managerDashboard = strtolower((string) ($user['role'] ?? '')) === 'manager';
$dashboardInitials = static function (string $name): string {
    $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if ($parts === []) {
        return '?';
    }

    return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark">
                    <span class="brand-leaf">⌁</span>
                    <div class="brand-text">
                        <div class="brand-name">Relax</div>
                        <div class="brand-subtitle">SPA</div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-menu">
                <div class="nav-section">
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=dashboard">
                        <span class="nav-icon">⌂</span>
                        <span>Dashboard</span>
                    </a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments">
                        <span class="nav-icon">☰</span>
                        <span>Appointments</span>
                    </a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services">
                        <span class="nav-icon">✦</span>
                        <span>Treatments</span>
                    </a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists">
                        <span class="nav-icon">◎</span>
                        <span>Therapists</span>
                    </a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms">
                        <span class="nav-icon">◫</span>
                        <span>Rooms</span>
                    </a>
                </div>

                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers">
                        <span class="nav-icon">◉</span>
                        <span>Customers</span>
                    </a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships">
                        <span class="nav-icon">◌</span>
                        <span>Memberships</span>
                    </a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history">
                        <span class="nav-icon">◍</span>
                        <span>Customer History</span>
                    </a>
                </div>

                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products">
                        <span class="nav-icon">▣</span>
                        <span>Products</span>
                    </a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=stock-management">
                        <span class="nav-icon">▤</span>
                        <span>Stock Management</span>
                    </a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=suppliers">
                        <span class="nav-icon">◧</span>
                        <span>Supplier</span>
                    </a>
                </div>

                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=reports">
                        <span class="nav-icon">▥</span>
                        <span>Reports &amp; Analytics</span>
                    </a>
                </div>

                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles">
                        <span class="nav-icon">⚙</span>
                        <span>Users &amp; Roles</span>
                    </a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings">
                        <span class="nav-icon">⚙</span>
                        <span>Settings</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-role">
                <span>Current Role</span>
                <div class="role-select">
                    <?= profile_avatar($user, 'role-avatar', $dashboardInitials((string) ($user['full_name'] ?? $user['username'] ?? 'A'))) ?>
                    <span><?= e(role_label((string) $user['role'])) ?></span>
                    <span class="chevron">▾</span>
                </div>
            </div>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left">
                    <div class="welcome-text">Welcome back, <?= e((string) ($user['full_name'] ?? $user['username'] ?? 'Admin')) ?>!</div>
                </div>

                <div class="topbar-actions">
                    <div class="search-box">
                        <span class="search-icon">⌕</span>
                        <span>Search</span>
                    </div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <button class="icon-button add-button" aria-label="Add">＋</button>
                    <div class="user-chip">
                        <?= profile_avatar($user, 'user-name', $dashboardInitials((string) ($user['full_name'] ?? $user['username'] ?? 'SV'))) ?>
                        <span class="user-role"><?= e(role_label((string) ($user['role'] ?? 'admin'))) ?></span>
                        <span class="user-chevron">▾</span>
                    </div>
                </div>
            </header>

            <?php if ($managerDashboard): ?>
            <div class="content-wrap manager-dashboard">
                <div class="manager-dashboard-heading">
                    <div>
                        <h1 class="page-title">DASHBOARD</h1>
                        <div class="page-date">Welcome back — here’s how Relax Spa is doing today.</div>
                    </div>
                    <span class="manager-date"><?= e(date('M j, Y')) ?></span>
                </div>

                <section class="manager-stat-grid" aria-label="Today's spa overview">
                    <article class="manager-stat-card">
                        <span class="manager-stat-icon green">▣</span>
                        <span class="manager-stat-label">Today's Appointments</span>
                        <strong><?= number_format($todayAppointments) ?></strong>
                        <small>Scheduled for today</small>
                    </article>
                    <article class="manager-stat-card">
                        <span class="manager-stat-icon blue">$</span>
                        <span class="manager-stat-label">Revenue Today</span>
                        <strong>$<?= number_format($todayRevenue, 2) ?></strong>
                        <small>Paid and partial invoices</small>
                    </article>
                    <article class="manager-stat-card">
                        <span class="manager-stat-icon amber">◷</span>
                        <span class="manager-stat-label">Pending Bookings</span>
                        <strong><?= number_format($pendingAppointments) ?></strong>
                        <small>Awaiting confirmation</small>
                    </article>
                    <article class="manager-stat-card">
                        <span class="manager-stat-icon violet">⌂</span>
                        <span class="manager-stat-label">Rooms Available</span>
                        <strong><?= number_format($availableRooms) ?> / <?= number_format($totalRooms) ?></strong>
                        <small><?= number_format(max(0, $totalRooms - $availableRooms)) ?> currently in use</small>
                    </article>
                </section>

                <section class="manager-dashboard-panels">
                    <article class="manager-dashboard-panel">
                        <div class="manager-panel-heading">
                            <h2>Today's Appointments</h2>
                            <a href="<?= e(APP_URL) ?>/?route=appointments">View appointments</a>
                        </div>
                        <?php if ($appointmentRows === []): ?>
                            <p class="manager-empty">No appointments are scheduled for today.</p>
                        <?php else: ?>
                            <div class="manager-appointment-list">
                                <?php foreach ($appointmentRows as $appointment): ?>
                                    <?php
                                        $status = strtolower((string) $appointment['status']);
                                        $statusClass = match ($status) {
                                            'confirmed' => 'confirmed',
                                            'in_progress' => 'in-progress',
                                            'completed' => 'completed',
                                            'cancelled' => 'cancelled',
                                            default => 'pending',
                                        };
                                    ?>
                                    <div class="manager-appointment-row">
                                        <time><?= e(date('g:i A', strtotime((string) $appointment['start_time']))) ?></time>
                                        <span class="manager-appointment-avatar"><?= e($dashboardInitials((string) $appointment['full_name'])) ?></span>
                                        <span class="manager-appointment-info">
                                            <strong><?= e((string) $appointment['full_name']) ?></strong>
                                            <small><?= e((string) $appointment['service']) ?> · <?= e((string) $appointment['room']) ?></small>
                                        </span>
                                        <span class="manager-appointment-status <?= e($statusClass) ?>"><?= e(ucwords(str_replace('_', ' ', $status))) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>

                    <article class="manager-dashboard-panel">
                        <div class="manager-panel-heading">
                            <h2>Notifications</h2>
                            <a href="<?= e(APP_URL) ?>/?route=inventory-report">View details</a>
                        </div>
                        <?php if ($notifications === []): ?>
                            <p class="manager-empty">You’re all caught up. No new alerts.</p>
                        <?php else: ?>
                            <div class="manager-notifications">
                                <?php foreach (array_slice($notifications, 0, 4) as $notification): ?>
                                    <?php $notificationClass = strtolower((string) $notification['type']) === 'low stock' ? 'warning' : 'success'; ?>
                                    <div class="manager-notification">
                                        <span class="manager-notification-icon <?= e($notificationClass) ?>"><?= $notificationClass === 'warning' ? '!' : '✓' ?></span>
                                        <span>
                                            <strong><?= e((string) $notification['type']) ?></strong>
                                            <small><?= e((string) $notification['message']) ?></small>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                </section>
            </div>
            <?php else: ?>
            <div class="content-wrap">
                <h1 class="page-title">DASHBOARD OVERVIEW</h1>
                <div class="page-date">May 16, 2026</div>

                <section class="stats-grid">
                    <article class="stat-card revenue">
                        <div class="stat-icon">$</div>
                        <div class="stat-copy">
                            <div class="stat-label">Total revenue</div>
                            <div class="stat-value">$<?= number_format($totalRevenue, 2) ?></div>
                            <div class="stat-trend up">▲ 18.6% vs yesterday</div>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon">☑</div>
                        <div class="stat-copy">
                            <div class="stat-label">Today's appointments</div>
                            <div class="stat-value"><?= number_format($todayAppointments) ?></div>
                            <div class="stat-trend up">▲ 12.5% vs yesterday</div>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon">◫</div>
                        <div class="stat-copy">
                            <div class="stat-label">Available rooms</div>
                            <div class="stat-value"><?= $availableRooms ?> / 8</div>
                            <div class="stat-subtext">5 rooms available</div>
                        </div>
                    </article>

                    <article class="stat-card low-stock">
                        <div class="stat-icon">△</div>
                        <div class="stat-copy">
                            <div class="stat-label">Low stock alerts</div>
                            <div class="stat-value"><?= number_format($lowStockItems) ?></div>
                            <div class="stat-link">View details</div>
                        </div>
                    </article>
                </section>

                <section class="panel-grid">
                    <article class="panel chart-panel">
                        <div class="panel-header">
                            <span>Revenue trend</span>
                            <span class="panel-meta">Last 7 days</span>
                        </div>
                        <svg class="chart-svg" viewBox="0 0 430 180" preserveAspectRatio="none" aria-label="Revenue trend chart">
                            <defs>
                                <linearGradient id="chartArea" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#2d7a58" stop-opacity="0.18"/>
                                    <stop offset="100%" stop-color="#2d7a58" stop-opacity="0.02"/>
                                </linearGradient>
                            </defs>
                            <path d="M 18 140 L 93 112 L 168 95 L 243 70 L 318 82 L 393 58 L 393 150 L 18 150 Z" fill="url(#chartArea)" opacity="0.8"></path>
                            <path d="M 18 140 L 93 112 L 168 95 L 243 70 L 318 82 L 393 58" fill="none" stroke="#2c7755" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                            <?php foreach ($chartPoints as $point): ?>
                                <circle cx="<?= e((string) $point[0]) ?>" cy="<?= e((string) $point[1]) ?>" r="3.5" fill="#2c7755" />
                            <?php endforeach; ?>
                            <?php foreach ($chartPoints as $point): ?>
                                <text x="<?= e((string) $point[0]) ?>" y="170" text-anchor="middle" font-size="10" fill="#74807a"><?= e($point[2]) ?></text>
                            <?php endforeach; ?>
                        </svg>
                    </article>

                    <article class="panel service-panel">
                        <div class="panel-header">
                            <span>Popular services</span>
                            <span class="panel-meta">This Month</span>
                        </div>
                        <div class="service-list">
                            <?php foreach ($serviceTotals as $service): ?>
                                <?php $percent = $maxServiceCount > 0 ? round(($service['total'] / $maxServiceCount) * 100) : 0; ?>
                                <div class="service-row">
                                    <div class="service-name-row">
                                        <span><?= e($service['name']) ?></span>
                                        <span class="service-percent"><?= $percent ?>%</span>
                                    </div>
                                    <div class="progress">
                                        <span style="width: <?= $percent ?>%;"></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                </section>

                <section class="notifications-panel panel">
                    <div class="panel-header">
                        <span>Recent notifications</span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Message</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $notification): ?>
                                <tr>
                                    <td>
                                        <span class="tag <?= strtolower(str_replace(' ', '-', $notification['type'])) ?>"><?= e($notification['type']) ?></span>
                                    </td>
                                    <td><?= e($notification['message']) ?></td>
                                    <td><?= e($notification['time']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            </div>
            <?php endif; ?>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
