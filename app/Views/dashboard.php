<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

if (empty($_SESSION['user'])) {
    redirect('login');
}

$user = $_SESSION['user'];
$db = Database::connection();

$totalRevenue = (float) $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE status IN ('paid', 'partial')"
)->fetchColumn();

$todayAppointments = (int) $db->query(
    "SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()"
)->fetchColumn();

$availableRooms = (int) $db->query(
    "SELECT COUNT(*) FROM treatment_rooms WHERE status = 'available'"
)->fetchColumn();

$lowStockItems = (int) $db->query(
    "SELECT COUNT(*) FROM products WHERE stock_quantity <= reorder_level AND status = 'active'"
)->fetchColumn();

$popularServices = $db->query(
    "SELECT s.name, COUNT(a.id) AS total
     FROM appointments a
     INNER JOIN services s ON s.id = a.service_id
     GROUP BY s.id, s.name
     ORDER BY total DESC
     LIMIT 4"
)->fetchAll(PDO::FETCH_ASSOC);

$serviceTotals = [];
$maxServiceCount = 1;
foreach ($popularServices as $row) {
    $serviceTotals[] = [
        'name' => $row['name'],
        'total' => (int) $row['total'],
    ];
    $maxServiceCount = max($maxServiceCount, (int) $row['total']);
}

$revenueData = $db->query(
    "SELECT DATE_FORMAT(appointment_date, '%Y-%m-%d') AS day,
            COUNT(*) AS count
     FROM appointments
     WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(appointment_date)
     ORDER BY day ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$chartValues = [];
$chartLabelMap = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chartLabelMap[$date] = date('D', strtotime($date));
    $chartValues[$date] = 0;
}

foreach ($revenueData as $day) {
    $chartValues[$day['day']] = (int) $day['count'];
}

$chartPoints = [];
$maxChartValue = max(1, max($chartValues));
$chartMaxY = 60;
foreach ($chartValues as $date => $value) {
    $index = array_search($date, array_keys($chartValues), true);
    $x = 18 + ($index * 75);
    $y = 140 - (($value / $maxChartValue) * 90);
    $chartPoints[] = [$x, $y, $chartLabelMap[$date], $value];
}

$pathPoints = [];
foreach ($chartPoints as $index => $point) {
    $pathPoints[] = ($index === 0 ? 'M ' : 'L ') . $point[0] . ' ' . $point[1];
}
$chartPath = implode(' ', $pathPoints);

$notifications = [];
$lowStockRows = $db->query(
    "SELECT name, stock_quantity, reorder_level
     FROM products
     WHERE stock_quantity <= reorder_level AND status = 'active'
     ORDER BY stock_quantity ASC
     LIMIT 3"
)->fetchAll(PDO::FETCH_ASSOC);
foreach ($lowStockRows as $row) {
    $notifications[] = [
        'type' => 'Low stock',
        'message' => $row['name'] . ' (Oil) is low (' . (int) $row['stock_quantity'] . ' left)',
        'time' => 'Now'
    ];
}

$membershipRows = $db->query(
    "SELECT c.full_name, m.expiry_date
     FROM memberships m
     INNER JOIN customers c ON c.id = m.customer_id
     WHERE m.status = 'active' AND m.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
     ORDER BY m.expiry_date ASC
     LIMIT 2"
)->fetchAll(PDO::FETCH_ASSOC);
foreach ($membershipRows as $row) {
    $notifications[] = [
        'type' => 'Membership',
        'message' => $row['full_name'] . " membership expires on " . date('M d', strtotime($row['expiry_date'])),
        'time' => '2m ago'
    ];
}

$appointmentRows = $db->query(
    "SELECT c.full_name, a.status, a.appointment_date, a.start_time
     FROM appointments a
     INNER JOIN customers c ON c.id = a.customer_id
     ORDER BY a.appointment_date DESC, a.start_time DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

$revenueTrend = max(0, min(100, round(($totalRevenue > 0 ? ($totalRevenue / 200000) * 100 : 0), 0)));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css">
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
                    <a class="nav-item active" href="#">
                        <span class="nav-icon">⌂</span>
                        <span>Dashboard</span>
                    </a>
                    <a class="nav-item" href="#">
                        <span class="nav-icon">☰</span>
                        <span>Appointments</span>
                    </a>
                    <a class="nav-item" href="#">
                        <span class="nav-icon">✦</span>
                        <span>Treatments</span>
                    </a>
                    <a class="nav-item" href="#">
                        <span class="nav-icon">◎</span>
                        <span>Therapists</span>
                    </a>
                    <a class="nav-item" href="#">
                        <span class="nav-icon">◫</span>
                        <span>Rooms</span>
                    </a>
                </div>

                <div class="nav-section">
                    <a class="nav-item" href="#">
                        <span class="nav-icon">◉</span>
                        <span>Customers</span>
                    </a>
                    <a class="nav-item" href="#">
                        <span class="nav-icon">◌</span>
                        <span>Memberships</span>
                    </a>
                    <a class="nav-item" href="#">
                        <span class="nav-icon">◍</span>
                        <span>Customer History</span>
                    </a>
                </div>

                <div class="nav-section">
                    <a class="nav-item" href="#">
                        <span class="nav-icon">▣</span>
                        <span>Products</span>
                    </a>
                    <a class="nav-item" href="#">
                        <span class="nav-icon">▤</span>
                        <span>Stock Management</span>
                    </a>
                    <a class="nav-item" href="#">
                        <span class="nav-icon">◧</span>
                        <span>Supplier</span>
                    </a>
                </div>

                <div class="nav-section">
                    <a class="nav-item" href="#">
                        <span class="nav-icon">▥</span>
                        <span>Reports &amp; Analytics</span>
                    </a>
                </div>

                <div class="nav-section">
                    <a class="nav-item" href="#">
                        <span class="nav-icon">⚙</span>
                        <span>Users &amp; Roles</span>
                    </a>
                    <a class="nav-item" href="#">
                        <span class="nav-icon">⚙</span>
                        <span>Settings</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-role">
                <span>Current Role</span>
                <div class="role-select">
                    <span class="role-avatar">A</span>
                    <span><?= e(ucfirst($user['role'])) ?></span>
                    <span class="chevron">▾</span>
                </div>
            </div>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left">
                    <div class="welcome-text">Welcome back, Admin!</div>
                </div>

                <div class="topbar-actions">
                    <div class="search-box">
                        <span class="search-icon">⌕</span>
                        <span>Search</span>
                    </div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <button class="icon-button add-button" aria-label="Add">＋</button>
                    <div class="user-chip">
                        <span class="user-name">SV</span>
                        <span class="user-role">Admin</span>
                        <span class="user-chevron">▾</span>
                    </div>
                </div>
            </header>

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
        </main>
    </div>
</body>
</html>
