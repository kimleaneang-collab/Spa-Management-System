<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

$statusClasses = [
    'pending' => 'pending',
    'confirmed' => 'confirmed',
    'in_progress' => 'in-progress',
    'completed' => 'completed',
    'cancelled' => 'cancelled',
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
                        <div class="page-date"><?= e((string) count($appointments)) ?> bookings</div>
                    </div>
                    <button class="primary-button" type="button" data-open-modal="appointment-modal">+ New appointment</button>
                </div>
                <?php if ($formError !== ''): ?>
                    <div class="alert alert-error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>

                <section class="module-panel">
                    <div class="toolbar-row">
                        <div class="toolbar-pills">
                            <button class="pill active" type="button">All</button>
                            <button class="pill" type="button">Pending</button>
                            <button class="pill" type="button">Confirmed</button>
                            <button class="pill" type="button">Today</button>
                        </div>
                        <div class="toolbar-meta"><?= e((string) count($appointments)) ?> appointments</div>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($appointments === []): ?>
                                    <tr><td colspan="8">No appointments available.</td></tr>
                                <?php else: ?>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?= e(date('g:i A', strtotime((string) $appointment['start_time']))) ?></td>
                                        <td><?= e($appointment['customer']) ?></td>
                                        <td><?= e($appointment['therapist']) ?></td>
                                        <td><?= e($appointment['service']) ?></td>
                                        <td><?= e($appointment['room']) ?></td>
                                        <td><?= e((string) $appointment['duration_minutes']) ?> min</td>
                                        <td><span class="status-pill status-<?= e($statusClasses[$appointment['status']] ?? 'pending') ?>"><?= e(ucwords(str_replace('_', ' ', $appointment['status']))) ?></span></td>
                                        <td>
                                            <a class="secondary-button" href="<?= e(APP_URL) ?>/?route=appointments&amp;edit=<?= e((string) $appointment['id']) ?>">Edit</a>
                                            <form method="post" action="<?= e(APP_URL) ?>/?route=appointments" style="display:inline" onsubmit="return confirm('Delete this appointment?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= e((string) $appointment['id']) ?>">
                                                <button class="secondary-button" type="submit">Delete</button>
                                            </form>
                                        </td>
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

    <div class="modal-backdrop <?= $editingAppointment !== null ? '' : 'hidden' ?>" id="appointment-modal" aria-hidden="<?= $editingAppointment !== null ? 'false' : 'true' ?>">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="appointment-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow"><?= $editingAppointment !== null ? 'Edit booking' : 'New booking' ?></p>
                    <h2 id="appointment-title"><?= $editingAppointment !== null ? 'Edit Appointment' : 'Schedule Appointment' ?></h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="appointment-modal">×</button>
            </div>

            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=appointments">
                <input type="hidden" name="action" value="<?= $editingAppointment !== null ? 'update' : 'create' ?>">
                <input type="hidden" name="id" value="<?= e((string) ($editingAppointment['id'] ?? '')) ?>">
                <div class="form-grid two-col">
                    <label>
                        <span>Customer</span>
                        <select name="customer_id" required>
                            <option value="">Select customer</option>
                            <?php foreach ($options['customers'] as $customer): ?>
                                <option value="<?= e((string) $customer['id']) ?>" <?= (int) ($editingAppointment['customer_id'] ?? 0) === (int) $customer['id'] ? 'selected' : '' ?>><?= e($customer['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Therapist</span>
                        <select name="therapist_id">
                            <option value="">Select therapist</option>
                            <?php foreach ($options['therapists'] as $therapist): ?>
                                <option value="<?= e((string) $therapist['id']) ?>" <?= (int) ($editingAppointment['therapist_id'] ?? 0) === (int) $therapist['id'] ? 'selected' : '' ?>><?= e($therapist['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Service</span>
                        <select name="service_id" required>
                            <option value="">Select service</option>
                            <?php foreach ($options['services'] as $service): ?>
                                <option value="<?= e((string) $service['id']) ?>" <?= (int) ($editingAppointment['service_id'] ?? 0) === (int) $service['id'] ? 'selected' : '' ?>><?= e($service['name']) ?> (<?= e((string) $service['duration_minutes']) ?> min)</option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Room</span>
                        <select name="room_id">
                            <option value="">Select room</option>
                            <?php foreach ($options['rooms'] as $room): ?>
                                <option value="<?= e((string) $room['id']) ?>" <?= (int) ($editingAppointment['room_id'] ?? 0) === (int) $room['id'] ? 'selected' : '' ?>><?= e($room['room_name'] ?: $room['room_code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Date</span>
                        <input type="date" name="appointment_date" value="<?= e($editingAppointment['appointment_date'] ?? '') ?>" required>
                    </label>
                    <label>
                        <span>Time</span>
                        <input type="time" name="start_time" value="<?= e(substr((string) ($editingAppointment['start_time'] ?? ''), 0, 5)) ?>" required>
                    </label>
                    <label>
                        <span>End time</span>
                        <input type="time" name="end_time" value="<?= e(substr((string) ($editingAppointment['end_time'] ?? ''), 0, 5)) ?>">
                    </label>
                    <label>
                        <span>Status</span>
                        <select name="status">
                            <?php foreach (['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'] as $status): ?>
                                <option value="<?= e($status) ?>" <?= ($editingAppointment['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="appointment-modal">Cancel</button>
                    <button class="primary-button" type="submit"><?= $editingAppointment !== null ? 'Update booking' : 'Save booking' ?></button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
