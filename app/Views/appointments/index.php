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
$isReceptionist = strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'receptionist';
$isTherapist = in_array(strtolower((string) ($_SESSION['user']['role'] ?? '')), ['staff', 'therapist'], true);
$appointmentStatusCounts = [
    'all' => count($appointments),
    'pending' => 0,
    'confirmed' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'cancelled' => 0,
];
foreach ($appointments as $appointment) {
    $status = (string) ($appointment['status'] ?? '');
    if (array_key_exists($status, $appointmentStatusCounts)) {
        $appointmentStatusCounts[$status]++;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointments | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page <?= $isReceptionist ? 'receptionist-role' : '' ?> <?= $isTherapist ? 'therapist-dashboard-page' : '' ?>" data-role="<?= e(strtolower((string) ($_SESSION['user']['role'] ?? ''))) ?>">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>

            <?php if ($isTherapist): ?>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>My Appointments</span></a>
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
            <?php else: ?>
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
            <?php endif; ?>

            <?= $isTherapist ? sidebar_role_footer(false) : sidebar_role_footer() ?>
            <?php if ($isTherapist): ?><p class="therapist-sidebar-note">Stay calm &amp; relax.<br>You're doing great today!</p><?php endif; ?>
        </aside>

        <main class="main-panel">
            <header class="topbar <?= $isTherapist ? 'therapist-topbar' : '' ?>">
                <?php if ($isTherapist): ?>
                    <div class="search-box search-input-wrap">
                        <span class="search-icon" aria-hidden="true">⌕</span>
                        <input class="search-input" type="search" placeholder="Search customer, appointments, services..." aria-label="Search appointments" data-appointment-search>
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
                <div class="topbar-left">
                    <div class="welcome-text">Welcome back, <?= e((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Admin')) ?>!</div>
                </div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <?php if ($isReceptionist): ?>
                        <a class="icon-button add-button" aria-label="New appointment" href="<?= e(APP_URL) ?>/?route=new-appointment">＋</a>
                    <?php else: ?>
                        <a class="icon-button add-button" aria-label="New appointment" href="<?= e(APP_URL) ?>/?route=new-appointment">＋</a>
                    <?php endif; ?>
                    <div class="user-chip"><?= profile_avatar($_SESSION['user'] ?? [], 'user-name', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'SV'), 0, 2))) ?><span class="user-role"><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'admin'))) ?></span><span class="user-chevron">▾</span></div>
                </div>
                <?php endif; ?>
            </header>

            <div class="content-wrap <?= $isReceptionist ? 'receptionist-appointments-page' : '' ?> <?= $isTherapist ? 'therapist-appointments-content' : '' ?>">
                <div class="page-heading <?= $isReceptionist ? 'receptionist-customers-heading' : '' ?>">
                    <div>
                        <h1 class="page-title"><?= $isTherapist ? 'My Appointment' : ($isReceptionist ? 'APPOINTMENT' : 'APPOINTMENTS') ?></h1>
                        <div class="page-date"><?= $isTherapist ? 'All your upcoming and past bookings.' : ($isReceptionist ? 'Manage booking across every room and therapist.' : e((string) count($appointments)) . ' bookings') ?></div>
                    </div>
                    <?php if ($isTherapist): ?>
                        <span class="therapist-appointment-count"><?= e((string) count($appointments)) ?> appointments</span>
                    <?php elseif ($isReceptionist): ?>
                        <a class="primary-button" href="<?= e(APP_URL) ?>/?route=register-customer">＋ Register Customer</a>
                    <?php else: ?>
                        <a class="primary-button" href="<?= e(APP_URL) ?>/?route=new-appointment">+ New appointment</a>
                    <?php endif; ?>
                </div>
                <?php if ($formError !== ''): ?>
                    <div class="alert alert-error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>

                <?php if ($isReceptionist): ?>
                    <div class="receptionist-appointment-toolbar">
                        <label class="receptionist-appointment-search">
                            <span class="visually-hidden">Search by customer, service, or therapist</span>
                            <span aria-hidden="true">⌕</span>
                            <input type="search" placeholder="Search by Customer, Service or therapist" data-appointment-search>
                        </label>
                        <label class="receptionist-appointment-date">
                            <span class="visually-hidden">Filter appointments by date</span>
                            <span aria-hidden="true">▣</span>
                            <input type="date" value="<?= e(date('Y-m-d')) ?>" data-appointment-date-filter>
                        </label>
                        <label class="receptionist-appointment-status-select">
                            <span class="visually-hidden">Filter appointments by status</span>
                            <span aria-hidden="true">☷</span>
                            <select data-appointment-status-filter>
                                <option value="all">Status: All</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </label>
                    </div>
                <?php endif; ?>

                <section class="module-panel <?= $isReceptionist ? 'receptionist-appointment-panel' : '' ?> <?= $isTherapist ? 'therapist-appointments-panel' : '' ?>">
                    <div class="toolbar-row">
                        <div class="toolbar-pills">
                            <?php if ($isTherapist): ?>
                                <button class="pill active" type="button" data-appointment-filter="today">Today</button>
                                <button class="pill" type="button" data-appointment-filter="upcoming">Upcoming</button>
                                <button class="pill" type="button" data-appointment-filter="past">Past</button>
                                <button class="pill" type="button" data-appointment-filter="cancelled">Cancelled</button>
                            <?php else: ?>
                            <button class="pill active" type="button" data-appointment-filter="all"><?= $isReceptionist ? 'All (' . e((string) $appointmentStatusCounts['all']) . ')' : 'All' ?></button>
                            <button class="pill" type="button" data-appointment-filter="pending"><?= $isReceptionist ? 'Pending (' . e((string) $appointmentStatusCounts['pending']) . ')' : 'Pending' ?></button>
                            <button class="pill" type="button" data-appointment-filter="confirmed"><?= $isReceptionist ? 'Confirmed (' . e((string) $appointmentStatusCounts['confirmed']) . ')' : 'Confirmed' ?></button>
                            <?php if ($isReceptionist): ?>
                                <button class="pill" type="button" data-appointment-filter="in_progress">In Progress (<?= e((string) $appointmentStatusCounts['in_progress']) ?>)</button>
                                <button class="pill" type="button" data-appointment-filter="completed">Completed (<?= e((string) $appointmentStatusCounts['completed']) ?>)</button>
                            <?php else: ?>
                                <button class="pill" type="button" data-appointment-filter="today">Today</button>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!$isReceptionist && !$isTherapist): ?><div class="toolbar-meta"><?= e((string) count($appointments)) ?> appointments</div><?php endif; ?>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table <?= $isReceptionist ? 'receptionist-appointment-table' : '' ?> <?= $isTherapist ? 'therapist-appointment-table' : '' ?>">
                            <thead>
                                <tr>
                                    <?php if ($isTherapist): ?>
                                        <th>Time</th><th>Customer</th><th>Service</th><th>Duration</th><th>Status</th><th>View</th>
                                    <?php else: ?>
                                    <th>Time</th><th>Customer</th>
                                    <?php if ($isReceptionist): ?>
                                        <th>Service</th>
                                        <th>Therapist</th>
                                    <?php else: ?>
                                        <th>Therapist</th>
                                        <th>Service</th>
                                    <?php endif; ?>
                                    <th>Room</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <?php if ($isReceptionist): ?><th><span class="visually-hidden">Actions</span></th><?php else: ?><th>Actions</th><?php endif; ?>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($appointments === []): ?>
                                    <tr class="appointment-empty-row"><td colspan="<?= $isTherapist ? '6' : '8' ?>"><?= $isTherapist && $therapistId === null ? 'Your account is not linked to a therapist profile yet. Please ask your manager to connect it.' : 'No appointments available.' ?></td></tr>
                                <?php else: ?>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr data-appointment-status="<?= e((string) $appointment['status']) ?>" data-appointment-date="<?= e((string) $appointment['appointment_date']) ?>" data-appointment-search="<?= e(strtolower((string) $appointment['customer'] . ' ' . (string) $appointment['service'] . ' ' . (string) $appointment['therapist'])) ?>">
                                        <td><?= e(date('g:i A', strtotime((string) $appointment['start_time']))) ?></td>
                                        <td>
                                            <?php if ($isReceptionist): ?>
                                                <span class="receptionist-appointment-customer"><span class="receptionist-appointment-avatar"><?= e(strtoupper(substr((string) $appointment['customer'], 0, 2))) ?></span><?= e($appointment['customer']) ?></span>
                                            <?php else: ?>
                                                <?= e($appointment['customer']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($isTherapist): ?>
                                            <td><?= e($appointment['service']) ?></td>
                                            <td><?= e((string) $appointment['duration_minutes']) ?> min</td>
                                            <td><span class="status-pill status-<?= e($statusClasses[$appointment['status']] ?? 'pending') ?>"><?= e(ucwords(str_replace('_', ' ', $appointment['status']))) ?></span></td>
                                            <td><button class="therapist-appointment-view" type="button" data-appointment-view data-open-modal="therapist-appointment-detail" data-customer="<?= e((string) $appointment['customer']) ?>" data-service="<?= e((string) $appointment['service']) ?>" data-date="<?= e(date('M j, Y', strtotime((string) $appointment['appointment_date']))) ?>" data-time="<?= e(date('g:i A', strtotime((string) $appointment['start_time']))) ?>" data-duration="<?= e((string) $appointment['duration_minutes']) ?> min" data-room="<?= e((string) $appointment['room']) ?>" data-status="<?= e(ucwords(str_replace('_', ' ', (string) $appointment['status']))) ?>">View</button></td>
                                        <?php else: ?>
                                        <?php if ($isReceptionist): ?>
                                            <td><?= e($appointment['service']) ?></td>
                                            <td><?= e($appointment['therapist']) ?></td>
                                        <?php else: ?>
                                            <td><?= e($appointment['therapist']) ?></td>
                                            <td><?= e($appointment['service']) ?></td>
                                        <?php endif; ?>
                                        <td><?= e($appointment['room']) ?></td>
                                        <td><?= e((string) $appointment['duration_minutes']) ?> min</td>
                                        <td><span class="status-pill status-<?= e($statusClasses[$appointment['status']] ?? 'pending') ?>"><?= e(ucwords(str_replace('_', ' ', $appointment['status']))) ?></span></td>
                                        <td class="<?= $isReceptionist ? 'receptionist-appointment-actions' : '' ?>">
                                            <?php if ($isReceptionist): ?>
                                                <a href="<?= e(APP_URL) ?>/?route=new-appointment&amp;customer_id=<?= e((string) $appointment['customer_id']) ?>" aria-label="Book another appointment for <?= e((string) $appointment['customer']) ?>" title="Book another appointment">⋮</a>
                                            <?php else: ?>
                                            <a class="secondary-button" href="<?= e(APP_URL) ?>/?route=appointments&amp;edit=<?= e((string) $appointment['id']) ?>">Edit</a>
                                            <form method="post" action="<?= e(APP_URL) ?>/?route=appointments" style="display:inline" onsubmit="return confirm('Delete this appointment?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= e((string) $appointment['id']) ?>">
                                                <button class="secondary-button" type="submit">Delete</button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                                <tr class="appointment-no-results" hidden><td colspan="<?= $isTherapist ? '6' : '8' ?>">No appointments match these filters.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <?php if ($isTherapist): ?>
    <div class="modal-backdrop hidden" id="therapist-appointment-detail" aria-hidden="true">
        <div class="modal-card therapist-appointment-detail-card" role="dialog" aria-modal="true" aria-labelledby="therapist-appointment-title">
            <div class="modal-header">
                <div><p class="eyebrow">Appointment details</p><h2 id="therapist-appointment-title">Booking</h2></div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="therapist-appointment-detail">×</button>
            </div>
            <dl class="therapist-appointment-details">
                <div><dt>Customer</dt><dd data-appointment-detail="customer"></dd></div>
                <div><dt>Service</dt><dd data-appointment-detail="service"></dd></div>
                <div><dt>Date</dt><dd data-appointment-detail="date"></dd></div>
                <div><dt>Time</dt><dd data-appointment-detail="time"></dd></div>
                <div><dt>Duration</dt><dd data-appointment-detail="duration"></dd></div>
                <div><dt>Room</dt><dd data-appointment-detail="room"></dd></div>
                <div><dt>Status</dt><dd data-appointment-detail="status"></dd></div>
            </dl>
        </div>
    </div>
    <?php elseif (!$isReceptionist): ?>
    <div class="modal-backdrop <?= $editingAppointment !== null || $showCreateModal ? '' : 'hidden' ?>" id="appointment-modal" aria-hidden="<?= $editingAppointment !== null || $showCreateModal ? 'false' : 'true' ?>">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="appointment-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow"><?= $editingAppointment !== null ? 'Edit booking' : 'New booking' ?></p>
                    <h2 id="appointment-title"><?= $editingAppointment !== null ? 'Edit Appointment' : 'Schedule Appointment' ?></h2>
                </div>
                <a class="modal-close" href="<?= e(APP_URL) ?>/?route=appointments" aria-label="Close">×</a>
            </div>

            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=appointments">
                <?= csrf_field() ?>
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
                    <a class="secondary-button" href="<?= e(APP_URL) ?>/?route=appointments">Cancel</a>
                    <button class="primary-button" type="submit"><?= $editingAppointment !== null ? 'Update booking' : 'Save booking' ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
