<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

$appointments = [
    [
        'time' => '09:00 AM',
        'customer' => 'Nina Johnson',
        'therapist' => 'Maya Lim',
        'service' => 'Deep Tissue Massage',
        'room' => 'Room 02',
        'duration' => '60 min',
        'status' => 'Confirmed',
        'status_class' => 'confirmed'
    ],
    [
        'time' => '10:30 AM',
        'customer' => 'Daniel Lee',
        'therapist' => 'Sophie Tran',
        'service' => 'Facial Glow Ritual',
        'room' => 'Room 04',
        'duration' => '45 min',
        'status' => 'Pending',
        'status_class' => 'pending'
    ],
    [
        'time' => '12:00 PM',
        'customer' => 'Anna Smith',
        'therapist' => 'Hana Park',
        'service' => 'Body Sculpting',
        'room' => 'Room 01',
        'duration' => '90 min',
        'status' => 'In Progress',
        'status_class' => 'in-progress'
    ],
    [
        'time' => '02:15 PM',
        'customer' => 'Chris Brown',
        'therapist' => 'Maya Lim',
        'service' => 'Detox Body Wrap',
        'room' => 'Room 03',
        'duration' => '75 min',
        'status' => 'Completed',
        'status_class' => 'completed'
    ],
    [
        'time' => '04:00 PM',
        'customer' => 'Ella Gomez',
        'therapist' => 'Sophie Tran',
        'service' => 'Hot Stone Therapy',
        'room' => 'Room 05',
        'duration' => '60 min',
        'status' => 'Cancelled',
        'status_class' => 'cancelled'
    ]
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointments | Relax Spa</title>
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Treatments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">◍</span><span>Customer History</span></a>
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

            <div class="sidebar-role">
                <span>Current Role</span>
                <div class="role-select">
                    <span class="role-avatar">A</span>
                    <span><?= e(ucfirst($_SESSION['user']['role'] ?? 'admin')) ?></span>
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
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <button class="icon-button add-button" aria-label="Add" data-open-modal="appointment-modal">＋</button>
                    <div class="user-chip"><span class="user-name">SV</span><span class="user-role">Admin</span><span class="user-chevron">▾</span></div>
                </div>
            </header>

            <div class="content-wrap">
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">APPOINTMENTS</h1>
                        <div class="page-date">Today • 24 bookings</div>
                    </div>
                    <button class="primary-button" type="button" data-open-modal="appointment-modal">+ New appointment</button>
                </div>

                <section class="module-panel">
                    <div class="toolbar-row">
                        <div class="toolbar-pills">
                            <button class="pill active" type="button">All</button>
                            <button class="pill" type="button">Pending</button>
                            <button class="pill" type="button">Confirmed</button>
                            <button class="pill" type="button">Today</button>
                        </div>
                        <div class="toolbar-meta">5 appointments</div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Customer</th>
                                    <th>Therapist</th>
                                    <th>Service</th>
                                    <th>Room</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?= e($appointment['time']) ?></td>
                                        <td><?= e($appointment['customer']) ?></td>
                                        <td><?= e($appointment['therapist']) ?></td>
                                        <td><?= e($appointment['service']) ?></td>
                                        <td><?= e($appointment['room']) ?></td>
                                        <td><?= e($appointment['duration']) ?></td>
                                        <td><span class="status-pill status-<?= e($appointment['status_class']) ?>"><?= e($appointment['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-backdrop hidden" id="appointment-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="appointment-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">New booking</p>
                    <h2 id="appointment-title">Schedule Appointment</h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="appointment-modal">×</button>
            </div>

            <form class="appointment-form" method="post" action="#">
                <div class="form-grid two-col">
                    <label>
                        <span>Customer</span>
                        <input type="text" name="customer" placeholder="Customer name" required>
                    </label>
                    <label>
                        <span>Therapist</span>
                        <select name="therapist" required>
                            <option value="">Select therapist</option>
                            <option>Maya Lim</option>
                            <option>Sophie Tran</option>
                            <option>Hana Park</option>
                        </select>
                    </label>
                    <label>
                        <span>Service</span>
                        <select name="service" required>
                            <option value="">Select service</option>
                            <option>Deep Tissue Massage</option>
                            <option>Facial Glow Ritual</option>
                            <option>Hot Stone Therapy</option>
                        </select>
                    </label>
                    <label>
                        <span>Room</span>
                        <select name="room" required>
                            <option value="">Select room</option>
                            <option>Room 01</option>
                            <option>Room 02</option>
                            <option>Room 03</option>
                        </select>
                    </label>
                    <label>
                        <span>Date</span>
                        <input type="date" name="date" required>
                    </label>
                    <label>
                        <span>Time</span>
                        <input type="time" name="time" required>
                    </label>
                </div>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="appointment-modal">Cancel</button>
                    <button class="primary-button" type="submit">Save booking</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
