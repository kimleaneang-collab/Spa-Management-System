<?php
declare(strict_types=1);

$user = $_SESSION['user'] ?? [];
$isReceptionist = strtolower((string) ($user['role'] ?? '')) === 'receptionist';
$isTherapist = in_array(strtolower((string) ($user['role'] ?? '')), ['staff', 'therapist'], true);
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
    <title><?= $isTherapist ? 'My Schedule' : 'Calendar' ?> | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page <?= $isReceptionist ? 'receptionist-role' : '' ?> <?= $isTherapist ? 'therapist-dashboard-page' : '' ?>" data-role="<?= e(strtolower((string) ($user['role'] ?? ''))) ?>">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark">
                    <span class="brand-leaf">⌁</span>
                    <div class="brand-text"><div class="brand-name">Relax</div><div class="brand-subtitle">SPA</div></div>
                </div>
            </div>
            <?php if ($isTherapist): ?>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>My Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>My Customer</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">▣</span><span>Treatment History</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>My Schedule</span></a>
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
            <?php endif; ?>
            <?= $isTherapist ? sidebar_role_footer(false) : sidebar_role_footer() ?>
            <?php if ($isTherapist): ?><p class="therapist-sidebar-note">Stay calm &amp; relax.<br>You're doing great today!</p><?php endif; ?>
        </aside>
        <main class="main-panel">
            <header class="topbar <?= $isTherapist ? 'therapist-topbar' : '' ?>">
                <?php if ($isTherapist): ?>
                <div class="search-box search-input-wrap">
                    <span class="search-icon" aria-hidden="true">⌕</span>
                    <input type="search" class="search-input" placeholder="Search customer, appointments, services..." aria-label="Search schedule" data-therapist-schedule-search>
                </div>
                <div class="topbar-actions">
                    <span class="therapist-notification" aria-hidden="true">♧</span>
                    <a class="therapist-calendar-shortcut" href="<?= e(APP_URL) ?>/?route=calendar" aria-label="Open my schedule">▦</a>
                    <div class="user-chip therapist-user-chip">
                        <?= profile_avatar($user, 'user-name', $userInitials) ?>
                        <span class="therapist-user-details"><strong><?= e($userName) ?></strong><small>Therapist</small></span>
                    </div>
                </div>
                <?php else: ?>
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e($userName) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <?php if ($isReceptionist): ?>
                        <a class="icon-button" href="<?= e(APP_URL) ?>/?route=calendar" aria-label="Calendar">▦</a>
                    <?php else: ?>
                        <a class="primary-button calendar-new-appointment" href="<?= e(APP_URL) ?>/?route=appointments">＋ New Appointment</a>
                    <?php endif; ?>
                    <div class="user-chip"><?= profile_avatar($user, 'user-name', $userInitials) ?><span class="user-role"><?= e(role_label((string) ($user['role'] ?? 'staff'))) ?></span></div>
                </div>
                <?php endif; ?>
            </header>
            <div class="content-wrap manager-calendar <?= $isReceptionist ? 'receptionist-calendar' : '' ?> <?= $isTherapist ? 'therapist-calendar-content' : '' ?>">
                <?php if ($isTherapist): ?>
                <div class="therapist-calendar-heading">
                    <div><h1>My Schedule</h1><p>Your working hours and booked slots</p></div>
                    <span class="therapist-calendar-period">This Week <span aria-hidden="true">⌄</span></span>
                </div>
                <div class="therapist-calendar-range">
                    Week of <?= e($scheduleStart->format('M j')) ?> - <?= e($scheduleStart->modify('+6 days')->format('M j, Y')) ?>
                </div>
                <?php if ($therapistId === null): ?>
                    <div class="therapist-profile-notice" role="status">Your login is not linked to a therapist profile yet. Please ask your manager to connect your account.</div>
                <?php endif; ?>
                <section class="therapist-week-grid" aria-label="My weekly schedule">
                    <?php for ($dayOffset = 0; $dayOffset < $scheduleDays; $dayOffset++): ?>
                        <?php
                            $day = $scheduleStart->modify('+' . $dayOffset . ' days');
                            $dateKey = $day->format('Y-m-d');
                            $dayAppointments = $appointmentsByDate[$dateKey] ?? [];
                        ?>
                        <article class="therapist-week-day" data-schedule-date="<?= e($dateKey) ?>">
                            <h2><?= e(strtoupper($day->format('D'))) ?> <span><?= e($day->format('j')) ?></span></h2>
                            <?php if ($dayAppointments === []): ?>
                                <span class="therapist-schedule-off">Off</span>
                            <?php else: ?>
                                <?php foreach ($dayAppointments as $appointment): ?>
                                    <div class="therapist-week-booking" data-schedule-booking data-schedule-search="<?= e(strtolower((string) $appointment['customer'] . ' ' . (string) $appointment['service'])) ?>" title="<?= e((string) $appointment['customer'] . ' · ' . (string) $appointment['service']) ?>">
                                        <time><?= e(date('g:i', strtotime((string) $appointment['start_time']))) ?></time> <?= e((string) $appointment['customer']) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </article>
                    <?php endfor; ?>
                    <div class="therapist-calendar-empty" data-therapist-calendar-empty hidden>No bookings match your search.</div>
                </section>
                <?php else: ?>
                <div class="page-heading">
                    <div><h1 class="page-title">CALENDAR</h1><div class="page-date">Full salon schedule across all rooms.</div></div>
                    <?php if ($isReceptionist): ?>
                        <a class="primary-button receptionist-calendar-new" href="<?= e(APP_URL) ?>/?route=new-appointment">＋ New Appointment</a>
                    <?php endif; ?>
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
                                <?php foreach (array_slice($appointmentsByDate[$dateKey] ?? [], 0, $isReceptionist ? 4 : 3) as $appointment): ?>
                                    <?php $status = strtolower((string) $appointment['status']); ?>
                                    <?php if ($isReceptionist): ?>
                                        <span class="calendar-event event-<?= e(preg_replace('/[^a-z0-9-]/', '-', $status) ?? 'pending') ?>" title="<?= e((string) $appointment['customer'] . ' — ' . (string) $appointment['service']) ?>">
                                            <time><?= e(date('g:i A', strtotime((string) $appointment['start_time']))) ?></time>
                                            <?= e((string) ($appointment['therapist'] ?: $appointment['customer'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <a class="calendar-event event-<?= e(preg_replace('/[^a-z0-9-]/', '-', $status) ?? 'pending') ?>" href="<?= e(APP_URL) ?>/?route=appointments&amp;edit=<?= e((string) $appointment['id']) ?>" title="<?= e((string) $appointment['customer'] . ' — ' . $appointment['service']) ?>">
                                            <time><?= e(date('g:i', strtotime((string) $appointment['start_time']))) ?></time>
                                            <?= e((string) $appointment['customer']) ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (count($appointmentsByDate[$dateKey] ?? []) > ($isReceptionist ? 4 : 3)): ?>
                                    <span class="calendar-more">+<?= count($appointmentsByDate[$dateKey]) - ($isReceptionist ? 4 : 3) ?> more</span>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </section>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
