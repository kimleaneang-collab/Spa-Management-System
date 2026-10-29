<?php
declare(strict_types=1);

$userName = (string) ($user['full_name'] ?? $user['username'] ?? 'Therapist');
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
    <title>Therapist Dashboard | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page therapist-dashboard-page" data-role="<?= e((string) ($user['role'] ?? 'staff')) ?>">
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
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>My Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>My Customer</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">▣</span><span>Treatment History</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>My Schedule</span></a>
                </div>
                <div class="nav-section" data-label="Work">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">♨</span><span>Service</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                </div>
                <?= therapist_account_nav() ?>
            </nav>
            <?= sidebar_role_footer(false) ?>
            <p class="therapist-sidebar-note">Stay calm &amp; relax.<br>You're doing great today!</p>
        </aside>

        <main class="main-panel">
            <header class="topbar therapist-topbar">
                <div class="search-box search-input-wrap">
                    <span class="search-icon" aria-hidden="true">⌕</span>
                    <input class="search-input" type="search" placeholder="Search customer, appointments, services..." aria-label="Search today's appointments">
                </div>
                <div class="topbar-actions">
                    <span class="therapist-notification" aria-hidden="true">♧</span>
                    <a class="therapist-calendar-shortcut" href="<?= e(APP_URL) ?>/?route=calendar" aria-label="Open my schedule">▦</a>
                    <div class="user-chip therapist-user-chip">
                        <?= profile_avatar($user, 'user-name', $initials) ?>
                        <span class="therapist-user-details"><strong><?= e($userName) ?></strong><small>Therapist</small></span>
                    </div>
                </div>
            </header>

            <div class="content-wrap therapist-dashboard-content">
                <div class="therapist-dashboard-heading">
                    <div>
                        <h1>Dashboard</h1>
                        <p>Here’s what’s happening with your schedule today.</p>
                    </div>
                </div>

                <?php if ($therapistId === null): ?>
                    <div class="therapist-profile-notice" role="status">Your login is not linked to a therapist profile yet. Please ask your manager to connect your account.</div>
                <?php endif; ?>

                <section class="therapist-stat-grid" aria-label="Today's therapist overview">
                    <article class="therapist-stat-card">
                        <span class="therapist-stat-icon calendar">▦</span>
                        <strong><?= number_format($todayAppointments) ?></strong>
                        <span>Today’s Appointments</span>
                    </article>
                    <article class="therapist-stat-card">
                        <span class="therapist-stat-icon customers">♙</span>
                        <strong><?= number_format($totalCustomers) ?></strong>
                        <span>Total Customers</span>
                    </article>
                    <article class="therapist-stat-card">
                        <span class="therapist-stat-icon pending">⌛</span>
                        <strong><?= number_format($pendingTreatments) ?></strong>
                        <span>Pending Treatment</span>
                    </article>
                    <article class="therapist-stat-card">
                        <span class="therapist-stat-icon revenue">◆</span>
                        <strong>$<?= number_format($todayRevenue, 2) ?></strong>
                        <span>Revenue Today</span>
                    </article>
                </section>

                <section class="therapist-schedule-panel" aria-labelledby="therapist-schedule-title">
                    <div class="therapist-schedule-heading">
                        <h2 id="therapist-schedule-title">Today’s Schedule</h2>
                        <span><?= number_format($todayAppointments) ?> Booked</span>
                    </div>
                    <div class="therapist-schedule-table-wrap">
                        <table class="therapist-schedule-table searchable-table">
                            <thead><tr><th scope="col">Time</th><th scope="col">Customer</th><th scope="col">Service</th><th scope="col">Status</th></tr></thead>
                            <tbody>
                                <?php if ($therapistSchedule === []): ?>
                                    <tr><td class="therapist-schedule-empty" colspan="4">No appointments are scheduled for you today.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($therapistSchedule as $appointment): ?>
                                        <?php
                                            $status = strtolower((string) $appointment['status']);
                                            $statusClass = match ($status) {
                                                'confirmed' => 'confirmed',
                                                'completed' => 'completed',
                                                'in_progress' => 'in-progress',
                                                'cancelled' => 'cancelled',
                                                default => 'pending',
                                            };
                                        ?>
                                        <tr>
                                            <td><?= e(date('g:i A', strtotime((string) $appointment['start_time']))) ?></td>
                                            <td><?= e((string) $appointment['customer']) ?></td>
                                            <td><?= e((string) $appointment['service']) ?></td>
                                            <td><span class="therapist-status-pill <?= e($statusClass) ?>"><?= e(ucwords(str_replace('_', ' ', $status))) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
