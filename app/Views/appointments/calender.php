<?php
declare(strict_types=1);

$user = $_SESSION['user'] ?? [];
$userName = (string) ($user['full_name'] ?? $user['username'] ?? 'User');
$userInitials = strtoupper(substr($userName, 0, 1));
$calendarQuery = static fn (DateTimeImmutable $date): string => http_build_query([
    'route' => 'calendar',
    'year' => $date->format('Y'),
    'month' => $date->format('n'),
]);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendar | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark">
                    <span class="brand-leaf">⌁</span>
                    <div class="brand-text"><div class="brand-name">Relax</div><div class="brand-subtitle">SPA</div></div>
                </div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>Calendar</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Services</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS &amp; Billing</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Inventory</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
            <div class="sidebar-role">
                <span>Current Role</span>
                <div class="role-select"><?= profile_avatar($user, 'role-avatar', $userInitials) ?><span><?= e(role_label((string) ($user['role'] ?? 'staff'))) ?></span></div>
            </div>
        </aside>
        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e($userName) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <a class="primary-button calendar-new-appointment" href="<?= e(APP_URL) ?>/?route=appointments">＋ New Appointment</a>
                    <div class="user-chip"><?= profile_avatar($user, 'user-name', $userInitials) ?><span class="user-role"><?= e(role_label((string) ($user['role'] ?? 'staff'))) ?></span></div>
                </div>
            </header>
            <div class="content-wrap manager-calendar">
                <div class="page-heading">
                    <div><h1 class="page-title">CALENDAR</h1><div class="page-date">Full salon schedule across all rooms.</div></div>
                </div>
                <section class="calendar-panel">
                    <div class="calendar-toolbar">
                        <h2><?= e($monthStart->format('F Y')) ?></h2>
                        <div class="calendar-navigation">
                            <a href="<?= e(APP_URL) ?>/?<?= e($calendarQuery($previousMonth)) ?>" aria-label="Previous month">‹</a>
                            <a class="calendar-today" href="<?= e(APP_URL) ?>/?route=calendar">Today</a>
                            <a href="<?= e(APP_URL) ?>/?<?= e($calendarQuery($nextMonth)) ?>" aria-label="Next month">›</a>
                        </div>
                    </div>
                    <div class="calendar-grid calendar-weekdays" aria-hidden="true">
                        <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday): ?>
                            <div><?= e($weekday) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="calendar-grid calendar-days">
                        <?php for ($dayOffset = 0; $dayOffset < 42; $dayOffset++): ?>
                            <?php
                                $day = $gridStart->modify('+' . $dayOffset . ' days');
                                $dateKey = $day->format('Y-m-d');
                                $isOutsideMonth = $day->format('n') !== $monthStart->format('n');
                            ?>
                            <div class="calendar-day <?= $isOutsideMonth ? 'outside-month' : '' ?> <?= $dateKey === $today ? 'is-today' : '' ?>">
                                <span class="calendar-day-number"><?= e($day->format('j')) ?></span>
                                <?php foreach (array_slice($appointmentsByDate[$dateKey] ?? [], 0, 3) as $appointment): ?>
                                    <?php $status = strtolower((string) $appointment['status']); ?>
                                    <a class="calendar-event event-<?= e(preg_replace('/[^a-z0-9-]/', '-', $status) ?? 'pending') ?>" href="<?= e(APP_URL) ?>/?route=appointments&amp;edit=<?= e((string) $appointment['id']) ?>" title="<?= e((string) $appointment['customer'] . ' — ' . $appointment['service']) ?>">
                                        <time><?= e(date('g:i', strtotime((string) $appointment['start_time']))) ?></time>
                                        <?= e((string) $appointment['customer']) ?>
                                    </a>
                                <?php endforeach; ?>
                                <?php if (count($appointmentsByDate[$dateKey] ?? []) > 3): ?>
                                    <span class="calendar-more">+<?= count($appointmentsByDate[$dateKey]) - 3 ?> more</span>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
