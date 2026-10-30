<?php

declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}
$managerDashboard = strtolower((string) ($user['role'] ?? '')) === 'manager';
$receptionistDashboard = strtolower((string) ($user['role'] ?? '')) === 'receptionist';
$currentRole = strtolower((string) ($user['role'] ?? 'admin'));
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
<body class="dashboard-page" data-role="<?= e($currentRole) ?>">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
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

            <?= sidebar_role_footer() ?>
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

            <?php if ($managerDashboard || $receptionistDashboard): ?>
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
                        <?php if ($appointmentRows === [] && !$receptionistDashboard): ?>
                            <p class="manager-empty">No appointments are scheduled for today.</p>
                        <?php elseif ($receptionistDashboard): ?>
                        <div class="content-wrap receptionist-dashboard">
                            <div class="receptionist-dashboard-heading">
                                <div>
                                    <h1>Good <?= e((int) date('G') < 12 ? 'Morning' : ((int) date('G') < 17 ? 'Afternoon' : 'Evening')) ?>, <?= e((string) ($user['full_name'] ?? $user['username'] ?? '')) ?>!</h1>
                                    <p>Here’s your front desk overview for today.</p>
                                </div>
                                <span class="receptionist-date"><?= e(date('M j, Y')) ?></span>
                            </div>

                            <section class="receptionist-stat-grid" aria-label="Today's front desk overview">
                                <article class="receptionist-stat-card">
                                    <span class="receptionist-stat-icon green">▣</span>
                                    <span class="receptionist-stat-label">Today's Appointments</span>
                                    <strong><?= number_format($todayAppointments) ?></strong>
                                    <small>Scheduled today</small>
                                </article>
                                <article class="receptionist-stat-card">
                                    <span class="receptionist-stat-icon blue">✓</span>
                                    <span class="receptionist-stat-label">Confirmed</span>
                                    <strong><?= number_format($todayConfirmedAppointments) ?></strong>
                                    <small>Ready for today</small>
                                </article>
                                <article class="receptionist-stat-card">
                                    <span class="receptionist-stat-icon amber">◷</span>
                                    <span class="receptionist-stat-label">Pending</span>
                                    <strong><?= number_format($pendingAppointments) ?></strong>
                                    <small>Awaiting confirmation</small>
                                </article>
                                <article class="receptionist-stat-card">
                                    <span class="receptionist-stat-icon violet">◉</span>
                                    <span class="receptionist-stat-label">New Customers</span>
                                    <strong><?= number_format($newCustomersToday) ?></strong>
                                    <small>Added today</small>
                                </article>
                                <article class="receptionist-stat-card">
                                    <span class="receptionist-stat-icon violet">⌂</span>
                                    <span class="receptionist-stat-label">Available Rooms</span>
                                    <strong><?= number_format($availableRooms) ?>/<?= number_format($totalRooms) ?></strong>
                                    <small>Ready for service</small>
                                </article>
                                <article class="receptionist-stat-card">
                                    <span class="receptionist-stat-icon green">◎</span>
                                    <span class="receptionist-stat-label">Active Therapists</span>
                                    <strong><?= number_format($activeTherapists) ?></strong>
                                    <small>On the team</small>
                                </article>
                            </section>

                            <section class="receptionist-panel-grid">
                                <article class="receptionist-panel">
                                    <div class="receptionist-panel-heading">
                                        <h2>Today's Appointments</h2>
                                        <a href="<?= e(APP_URL) ?>/?route=appointments">View all</a>
                                    </div>
                                    <?php if ($appointmentRows === []): ?>
                                        <p class="manager-empty">No appointments are scheduled for today.</p>
                                    <?php else: ?>
                                        <div class="receptionist-appointment-list">
                                            <?php foreach (array_slice($appointmentRows, 0, 6) as $appointment): ?>
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
                                                <div class="receptionist-appointment">
                                                    <time><?= e(date('g:i A', strtotime((string) $appointment['start_time']))) ?></time>
                                                    <span class="receptionist-customer-avatar"><?= e($dashboardInitials((string) $appointment['full_name'])) ?></span>
                                                    <span class="receptionist-appointment-info">
                                                        <strong><?= e((string) $appointment['full_name']) ?></strong>
                                                        <small><?= e((string) $appointment['service']) ?> · <?= e((string) $appointment['therapist']) ?></small>
                                                    </span>
                                                    <span class="manager-appointment-status <?= e($statusClass) ?>"><?= e(ucwords(str_replace('_', ' ', $status))) ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </article>

                                <article class="receptionist-panel">
                                    <div class="receptionist-panel-heading">
                                        <h2>Today's Schedule</h2>
                                        <a href="<?= e(APP_URL) ?>/?route=calendar">Calendar</a>
                                    </div>
                                    <?php if ($appointmentRows === []): ?>
                                        <p class="manager-empty">Your schedule is clear.</p>
                                    <?php else: ?>
                                        <div class="receptionist-schedule-list">
                                            <?php foreach (array_slice($appointmentRows, 0, 5) as $appointment): ?>
                                                <div class="receptionist-schedule-item">
                                                    <time><?= e(date('g:i A', strtotime((string) $appointment['start_time']))) ?></time>
                                                    <span>
                                                        <strong><?= e((string) $appointment['service']) ?></strong>
                                                        <small><?= e((string) $appointment['full_name']) ?> · <?= e((string) $appointment['room']) ?></small>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </article>

                                <article class="receptionist-panel">
                                    <div class="receptionist-panel-heading">
                                        <h2>Needs Confirmation</h2>
                                        <span class="receptionist-alert-count"><?= number_format($pendingAppointments) ?></span>
                                    </div>
                                    <?php if ($receptionistAlerts === []): ?>
                                        <p class="manager-empty">No pending appointments.</p>
                                    <?php else: ?>
                                        <div class="receptionist-alert-list">
                                            <?php foreach ($receptionistAlerts as $alert): ?>
                                                <a class="receptionist-alert" href="<?= e(APP_URL) ?>/?route=appointments">
                                                    <span class="receptionist-alert-icon">!</span>
                                                    <span>
                                                        <strong><?= e((string) $alert['full_name']) ?></strong>
                                                        <small>Pending · <?= e(date('g:i A', strtotime((string) $alert['start_time']))) ?></small>
                                                    </span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </article>
                            </section>

                            <section class="receptionist-bottom-grid">
                                <article class="receptionist-panel receptionist-customers-panel">
                                    <div class="receptionist-panel-heading">
                                        <h2>Recent Customers</h2>
                                        <a href="<?= e(APP_URL) ?>/?route=customers">View all</a>
                                    </div>
                                    <?php if ($recentCustomers === []): ?>
                                        <p class="manager-empty">No customers have been added yet.</p>
                                    <?php else: ?>
                                        <div class="receptionist-customer-list">
                                            <?php foreach ($recentCustomers as $customer): ?>
                                                <?php $createdAt = strtotime((string) $customer['created_at']); ?>
                                                <?php $customerTime = $createdAt === false ? '—' : (date('Y-m-d', $createdAt) === date('Y-m-d') ? date('g:i A', $createdAt) : date('M j', $createdAt)); ?>
                                                <div class="receptionist-customer">
                                                    <span class="receptionist-customer-avatar"><?= e($dashboardInitials((string) $customer['full_name'])) ?></span>
                                                    <strong><?= e((string) $customer['full_name']) ?></strong>
                                                    <time><?= e($customerTime) ?></time>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </article>

                                <article class="receptionist-panel receptionist-quick-actions">
                                    <div class="receptionist-panel-heading"><h2>Quick Actions</h2></div>
                                    <div class="receptionist-action-grid">
                                        <a href="<?= e(APP_URL) ?>/?route=appointments">
                                            <span>＋</span><strong>New Appointment</strong>
                                        </a>
                                        <a href="<?= e(APP_URL) ?>/?route=customers">
                                            <span>◉</span><strong>Customers</strong>
                                        </a>
                                        <a href="<?= e(APP_URL) ?>/?route=calendar">
                                            <span>▦</span><strong>Check Schedule</strong>
                                        </a>
                                        <a href="<?= e(APP_URL) ?>/?route=rooms">
                                            <span>⌂</span><strong>Room Status</strong>
                                        </a>
                                        <a href="<?= e(APP_URL) ?>/?route=pos">
                                            <span>$</span><strong>POS &amp; Billing</strong>
                                        </a>
                                        <a href="<?= e(APP_URL) ?>/?route=customer-history">
                                            <span>◷</span><strong>Customer History</strong>
                                        </a>
                                    </div>
                                </article>
                            </section>
                        </div>
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
                <div class="page-date"><?= e(date('F j, Y')) ?></div>

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
                        <svg class="chart-svg revenue-chart" viewBox="0 0 470 190" preserveAspectRatio="none" role="img" aria-label="Revenue trend for the last seven days">
                            <defs>
                                <linearGradient id="revenueChartArea" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#39b8bc" stop-opacity="0.28"/>
                                    <stop offset="100%" stop-color="#39b8bc" stop-opacity="0.02"/>
                                </linearGradient>
                            </defs>
                            <?php foreach ($chartTicks as $tick): ?>
                                <line class="revenue-chart-gridline" x1="52" x2="458" y1="<?= e((string) $tick['y']) ?>" y2="<?= e((string) $tick['y']) ?>" />
                                <text class="revenue-chart-axis-label" x="44" y="<?= e((string) ($tick['y'] + 4)) ?>" text-anchor="end"><?= e($tick['label']) ?></text>
                            <?php endforeach; ?>
                            <path class="revenue-chart-area" d="<?= e($chartAreaPath) ?>" fill="url(#revenueChartArea)" />
                            <path class="revenue-chart-line" d="<?= e($chartPath) ?>" pathLength="1" />
                            <?php foreach ($chartPoints as $index => $point): ?>
                                <circle class="revenue-chart-point" cx="<?= e((string) $point[0]) ?>" cy="<?= e((string) $point[1]) ?>" r="3.5" style="--point-index: <?= (int) $index ?>">
                                    <title><?= e($point[2]) ?>: $<?= e(number_format((float) $point[3], 2)) ?></title>
                                </circle>
                            <?php endforeach; ?>
                            <?php foreach ($chartPoints as $point): ?>
                                <text class="revenue-chart-date-label" x="<?= e((string) $point[0]) ?>" y="179" text-anchor="middle"><?= e($point[2]) ?></text>
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
