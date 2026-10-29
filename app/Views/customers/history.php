<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}
$isTherapist = in_array(strtolower((string) ($_SESSION['user']['role'] ?? '')), ['staff', 'therapist'], true);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isTherapist ? 'Treatment History' : 'Customer History' ?> | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page <?= $isTherapist ? 'therapist-dashboard-page' : '' ?>" data-role="<?= e(strtolower((string) ($_SESSION['user']['role'] ?? ''))) ?>">
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
            <?php if ($isTherapist): ?>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>My Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>My Customer</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">▣</span><span>Treatment History</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>My Schedule</span></a>
                </div>
                <div class="nav-section" data-label="Work">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">♨</span><span>Service</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                </div>
                <?= therapist_account_nav() ?>
            </nav>
            <?php else: ?>
            <nav class="sidebar-menu">
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Treatments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">◍</span><span>Customer History</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=stock-management"><span class="nav-icon">▤</span><span>Stock Management</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=suppliers"><span class="nav-icon">◧</span><span>Supplier</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
            <?php endif; ?>
            <?= $isTherapist ? sidebar_role_footer(false) : sidebar_role_footer() ?>
            <?php if ($isTherapist): ?><p class="therapist-sidebar-note">Stay calm &amp; relax.<br>You're doing great today!</p><?php endif; ?>
        </aside>
        <main class="main-panel">
            <header class="topbar <?= $isTherapist ? 'therapist-topbar' : '' ?>">
                <?php if ($isTherapist): ?>
                <div class="search-box search-input-wrap">
                    <span class="search-icon" aria-hidden="true">⌕</span>
                    <input type="search" class="search-input" placeholder="Search customer, appointments, services..." aria-label="Search treatment history" data-therapist-history-search>
                </div>
                <div class="topbar-actions">
                    <span class="therapist-notification" aria-hidden="true">♧</span>
                    <a class="therapist-calendar-shortcut" href="<?= e(APP_URL) ?>/?route=calendar" aria-label="Open my schedule">▦</a>
                    <div class="user-chip therapist-user-chip">
                        <?= profile_avatar($_SESSION['user'] ?? [], 'user-name', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'TH'), 0, 2))) ?>
                        <span class="therapist-user-details"><strong><?= e((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Therapist')) ?></strong><small>Therapist</small></span>
                    </div>
                </div>
                <?php else: ?>
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Admin')) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <div class="user-chip"><?= profile_avatar($_SESSION['user'] ?? [], 'user-name', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'SV'), 0, 2))) ?><span class="user-role"><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'admin'))) ?></span><span class="user-chevron">▾</span></div>
                </div>
                <?php endif; ?>
            </header>
            <div class="content-wrap <?= $isTherapist ? 'therapist-history-content' : '' ?>">
                <?php if ($isTherapist): ?>
                <div class="therapist-dashboard-heading therapist-history-heading">
                    <div><h1>Treatment History</h1><p>A log of completed and past treatments.</p></div>
                </div>
                <?php if ($therapistId === null): ?>
                    <div class="therapist-profile-notice" role="status">Your login is not linked to a therapist profile yet. Please ask your manager to connect your account.</div>
                <?php endif; ?>
                <section class="therapist-history-panel" aria-label="Treatment history">
                    <div class="therapist-history-table-wrap">
                        <table class="therapist-history-table">
                            <thead><tr><th scope="col">Date</th><th scope="col">Customer</th><th scope="col">Service</th><th scope="col">Status</th></tr></thead>
                            <tbody>
                                <?php if ($treatmentHistory === []): ?>
                                    <tr><td class="therapist-history-empty" colspan="4"><?= $therapistId === null ? 'History will appear after your account is linked to a therapist profile.' : 'No treatment history is available yet.' ?></td></tr>
                                <?php else: ?>
                                    <?php foreach ($treatmentHistory as $historyItem): ?>
                                        <?php
                                            $status = strtolower((string) $historyItem['status']);
                                            $statusClass = match ($status) {
                                                'confirmed' => 'confirmed',
                                                'completed' => 'completed',
                                                'in_progress' => 'in-progress',
                                                'cancelled' => 'cancelled',
                                                default => 'pending',
                                            };
                                        ?>
                                        <tr data-therapist-history-row>
                                            <td><span><?= e(date('g:i A', strtotime((string) $historyItem['start_time']))) ?></span><small><?= e(date('M j, Y', strtotime((string) $historyItem['appointment_date']))) ?></small></td>
                                            <td><?= e((string) $historyItem['customer']) ?></td>
                                            <td><?= e((string) $historyItem['service']) ?></td>
                                            <td><span class="therapist-history-status <?= e($statusClass) ?>"><?= e(ucwords(str_replace('_', ' ', $status))) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr class="therapist-history-no-results" hidden><td colspan="4">No treatment history matches your search.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php else: ?>
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">CUSTOMER HISTORY</h1>
                        <div class="page-date">Purchase and service history</div>
                    </div>
                </div>
                <section class="module-panel">
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr><th>Date</th><th>Service</th><th>Therapist</th><th>Amount</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>May 10, 2026</td><td>Foot Spa</td><td>Meng Lily</td><td>$40.00</td><td><span class="status-pill status-completed">Completed</span></td></tr>
                                <tr><td>Apr 22, 2026</td><td>Aromatherapy suite .1 bed</td><td>Vanna Sok</td><td>$45.00</td><td><span class="status-pill status-completed">Completed</span></td></tr>
                                <tr><td>Apr 02, 2026</td><td>Massage suite .1 bed</td><td>Song Ha</td><td>$35.00</td><td><span class="status-pill status-completed">Completed</span></td></tr>
                                <tr><td>May 10, 2026</td><td>Massage Body</td><td>Vanna Davith</td><td>$30.00</td><td><span class="status-pill status-cancelled">Cancelled</span></td></tr>
                                <tr><td>May 15, 2026</td><td>Hair Spa</td><td>Rotha Kim</td><td>$60.00</td><td><span class="status-pill status-cancelled">Cancelled</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
