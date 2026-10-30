<?php
declare(strict_types=1);

$userName = (string) ($user['full_name'] ?? $user['username'] ?? 'Receptionist');
$initials = static function (string $name): string {
    $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    return $parts === [] ? '?' : strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
};
$greeting = (int) date('G') < 12 ? 'Morning' : ((int) date('G') < 17 ? 'Afternoon' : 'Evening');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receptionist Dashboard | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page receptionist-role" data-role="receptionist">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section">
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>Calendar</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=new-appointment"><span class="nav-icon">＋</span><span>New Appointment</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=register-customer"><span class="nav-icon">♙</span><span>Register Customer</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>
        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e($userName) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <a class="icon-button" href="<?= e(APP_URL) ?>/?route=calendar" aria-label="Open calendar">▦</a>
                    <a class="icon-button add-button" href="<?= e(APP_URL) ?>/?route=new-appointment" aria-label="New appointment">＋</a>
                    <div class="user-chip">
                        <?= profile_avatar($user, 'user-name', $initials($userName)) ?>
                        <span class="user-role">Receptionist</span>
                        <span class="user-chevron">▾</span>
                    </div>
                </div>
            </header>
            <div class="content-wrap receptionist-dashboard">
                <div class="receptionist-dashboard-heading">
                    <div>
                        <h1>Good <?= e($greeting) ?>, <?= e($userName) ?>!</h1>
                        <p>Here’s your front desk overview for today.</p>
                    </div>
                    <span class="receptionist-date"><?= e(date('M j, Y')) ?></span>
                </div>
                <section class="receptionist-stat-grid" aria-label="Today's front desk overview">
                    <article class="receptionist-stat-card"><span class="receptionist-stat-icon green">▣</span><span class="receptionist-stat-label">Today's Appointments</span><strong><?= number_format($todayAppointments) ?></strong><small>Scheduled today</small></article>
                    <article class="receptionist-stat-card"><span class="receptionist-stat-icon blue">✓</span><span class="receptionist-stat-label">Confirmed</span><strong><?= number_format($todayConfirmedAppointments) ?></strong><small>Ready for today</small></article>
                    <article class="receptionist-stat-card"><span class="receptionist-stat-icon amber">◷</span><span class="receptionist-stat-label">Pending</span><strong><?= number_format($pendingAppointments) ?></strong><small>Awaiting confirmation</small></article>
                    <article class="receptionist-stat-card"><span class="receptionist-stat-icon violet">◉</span><span class="receptionist-stat-label">New Customers</span><strong><?= number_format($newCustomersToday) ?></strong><small>Added today</small></article>
                    <article class="receptionist-stat-card"><span class="receptionist-stat-icon violet">⌂</span><span class="receptionist-stat-label">Available Rooms</span><strong><?= number_format($availableRooms) ?>/<?= number_format($totalRooms) ?></strong><small>Ready for service</small></article>
                    <article class="receptionist-stat-card"><span class="receptionist-stat-icon green">◎</span><span class="receptionist-stat-label">Active Therapists</span><strong><?= number_format($activeTherapists) ?></strong><small>On the team</small></article>
                </section>
                <section class="receptionist-panel-grid">
                    <article class="receptionist-panel">
                        <div class="receptionist-panel-heading"><h2>Today's Appointments</h2><a href="<?= e(APP_URL) ?>/?route=appointments">View all</a></div>
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
                                        <span class="receptionist-customer-avatar"><?= e($initials((string) $appointment['full_name'])) ?></span>
                                        <span class="receptionist-appointment-info"><strong><?= e((string) $appointment['full_name']) ?></strong><small><?= e((string) $appointment['service']) ?> · <?= e((string) $appointment['therapist']) ?></small></span>
                                        <span class="manager-appointment-status <?= e($statusClass) ?>"><?= e(ucwords(str_replace('_', ' ', $status))) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                    <article class="receptionist-panel">
                        <div class="receptionist-panel-heading"><h2>Today's Schedule</h2><a href="<?= e(APP_URL) ?>/?route=calendar">Calendar</a></div>
                        <?php if ($appointmentRows === []): ?>
                            <p class="manager-empty">Your schedule is clear.</p>
                        <?php else: ?>
                            <div class="receptionist-schedule-list">
                                <?php foreach (array_slice($appointmentRows, 0, 5) as $appointment): ?>
                                    <div class="receptionist-schedule-item"><time><?= e(date('g:i A', strtotime((string) $appointment['start_time']))) ?></time><span><strong><?= e((string) $appointment['service']) ?></strong><small><?= e((string) $appointment['full_name']) ?> · <?= e((string) $appointment['room']) ?></small></span></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                    <article class="receptionist-panel">
                        <div class="receptionist-panel-heading"><h2>Needs Confirmation</h2><span class="receptionist-alert-count"><?= number_format($pendingAppointments) ?></span></div>
                        <?php if ($receptionistAlerts === []): ?>
                            <p class="manager-empty">No pending appointments.</p>
                        <?php else: ?>
                            <div class="receptionist-alert-list">
                                <?php foreach ($receptionistAlerts as $alert): ?>
                                    <a class="receptionist-alert" href="<?= e(APP_URL) ?>/?route=appointments"><span class="receptionist-alert-icon">!</span><span><strong><?= e((string) $alert['full_name']) ?></strong><small>Pending · <?= e(date('g:i A', strtotime((string) $alert['start_time']))) ?></small></span></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                </section>
                <section class="receptionist-bottom-grid">
                    <article class="receptionist-panel receptionist-customers-panel">
                        <div class="receptionist-panel-heading"><h2>Recent Customers</h2><a href="<?= e(APP_URL) ?>/?route=customers">View all</a></div>
                        <?php if ($recentCustomers === []): ?>
                            <p class="manager-empty">No customers have been added yet.</p>
                        <?php else: ?>
                            <div class="receptionist-customer-list">
                                <?php foreach ($recentCustomers as $customer): ?>
                                    <?php $createdAt = strtotime((string) $customer['created_at']); ?>
                                    <?php $customerTime = $createdAt === false ? '—' : (date('Y-m-d', $createdAt) === date('Y-m-d') ? date('g:i A', $createdAt) : date('M j', $createdAt)); ?>
                                    <div class="receptionist-customer"><span class="receptionist-customer-avatar"><?= e($initials((string) $customer['full_name'])) ?></span><strong><?= e((string) $customer['full_name']) ?></strong><time><?= e($customerTime) ?></time></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                    <article class="receptionist-panel receptionist-quick-actions">
                        <div class="receptionist-panel-heading"><h2>Quick Actions</h2></div>
                        <div class="receptionist-action-grid">
                            <a href="<?= e(APP_URL) ?>/?route=new-appointment"><span>＋</span><strong>New Appointment</strong></a>
                            <a href="<?= e(APP_URL) ?>/?route=customers"><span>◉</span><strong>Customers</strong></a>
                            <a href="<?= e(APP_URL) ?>/?route=calendar"><span>▦</span><strong>Check Schedule</strong></a>
                            <a href="<?= e(APP_URL) ?>/?route=rooms"><span>⌂</span><strong>Room Status</strong></a>
                            <a href="<?= e(APP_URL) ?>/?route=pos"><span>$</span><strong>POS &amp; Billing</strong></a>
                            <a href="<?= e(APP_URL) ?>/?route=register-customer"><span>♙</span><strong>Register Customer</strong></a>
                        </div>
                    </article>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
