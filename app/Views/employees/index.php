<?php
declare(strict_types=1);

$currentUser = $_SESSION['user'] ?? [];
$userName = (string) ($currentUser['full_name'] ?? $currentUser['username'] ?? 'Administrator');
$initials = strtoupper(substr($userName, 0, 2));
$fieldValue = static fn (string $key, string $default = ''): string => (string) ($formValues[$key] ?? $default);
$weekdays = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday',
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Management | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page employee-management-page" data-role="admin">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Services</span></a>
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
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=employee-management"><span class="nav-icon">♙</span><span>Employee Management</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e($userName) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="user-chip">
                        <?= profile_avatar($currentUser, 'user-name', $initials) ?>
                        <span class="user-role">Administrator</span>
                    </div>
                </div>
            </header>

            <div class="content-wrap employee-management-content">
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">EMPLOYEE MANAGEMENT</h1>
                        <div class="page-date">Create employee records, track attendance, and set weekly schedules.</div>
                    </div>
                </div>

                <?php if ($successMessage !== ''): ?>
                    <div class="employee-management-message success" role="status"><?= e($successMessage) ?></div>
                <?php endif; ?>
                <?php if ($formError !== ''): ?>
                    <div class="alert alert-error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>

                <section class="employee-form-grid" aria-label="Employee tools">
                    <article class="module-panel employee-form-panel">
                        <h2>Add employee</h2>
                        <form method="post" class="employee-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="create_employee">
                            <div class="form-grid two-col">
                                <label>Employee code
                                    <input name="employee_code" maxlength="40" required value="<?= e($fieldValue('employee_code')) ?>">
                                </label>
                                <label>Full name
                                    <input name="full_name" maxlength="150" required value="<?= e($fieldValue('full_name')) ?>">
                                </label>
                                <label>Role
                                    <select name="role_name" required>
                                        <option value="">Select role</option>
                                        <?php foreach (['manager', 'receptionist', 'therapist', 'cashier', 'cleaner'] as $role): ?>
                                            <option value="<?= e($role) ?>" <?= $fieldValue('action') === 'create_employee' && $fieldValue('role_name') === $role ? 'selected' : '' ?>><?= e(role_label($role)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>Status
                                    <select name="status" required>
                                        <?php foreach (['active', 'inactive', 'on_leave'] as $status): ?>
                                            <option value="<?= e($status) ?>" <?= $fieldValue('action') === 'create_employee' && $fieldValue('status', 'active') === $status ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>Phone
                                    <input name="phone" type="tel" maxlength="30" value="<?= e($fieldValue('phone')) ?>">
                                </label>
                                <label>Email
                                    <input name="email" type="email" maxlength="150" value="<?= e($fieldValue('email')) ?>">
                                </label>
                                <label>Salary
                                    <input name="salary" type="number" min="0" step="0.01" value="<?= e($fieldValue('salary', '0')) ?>">
                                </label>
                                <label>Hire date
                                    <input name="hire_date" type="date" value="<?= e($fieldValue('hire_date')) ?>">
                                </label>
                            </div>
                            <button class="primary-button" type="submit">Add employee</button>
                        </form>
                    </article>

                    <article class="module-panel employee-form-panel">
                        <h2>Record attendance</h2>
                        <form method="post" class="employee-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="record_attendance">
                            <div class="form-grid two-col">
                                <label>Employee
                                    <select name="employee_id" required>
                                        <option value="">Select employee</option>
                                        <?php foreach ($employees as $employee): ?>
                                            <option value="<?= e((string) $employee['id']) ?>" <?= $fieldValue('action') === 'record_attendance' && $fieldValue('employee_id') === (string) $employee['id'] ? 'selected' : '' ?>><?= e($employee['full_name'] . ' (' . $employee['employee_code'] . ')') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>Date
                                    <input name="attendance_date" type="date" required value="<?= e($fieldValue('attendance_date', date('Y-m-d'))) ?>">
                                </label>
                                <label>Status
                                    <select name="attendance_status" required>
                                        <?php foreach (['present', 'late', 'absent', 'leave', 'half_day'] as $status): ?>
                                            <option value="<?= e($status) ?>" <?= $fieldValue('action') === 'record_attendance' && $fieldValue('attendance_status', 'present') === $status ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>Check in
                                    <input name="check_in" type="datetime-local" value="<?= e($fieldValue('check_in')) ?>">
                                </label>
                                <label>Check out
                                    <input name="check_out" type="datetime-local" value="<?= e($fieldValue('check_out')) ?>">
                                </label>
                                <label>Notes
                                    <input name="notes" maxlength="255" value="<?= e($fieldValue('action') === 'record_attendance' ? $fieldValue('notes') : '') ?>">
                                </label>
                            </div>
                            <button class="primary-button" type="submit" <?= $employees === [] ? 'disabled title="Add an employee first"' : '' ?>>Save attendance</button>
                        </form>
                    </article>

                    <article class="module-panel employee-form-panel">
                        <h2>Add weekly schedule</h2>
                        <form method="post" class="employee-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="create_schedule">
                            <div class="form-grid two-col">
                                <label>Employee
                                    <select name="employee_id" required>
                                        <option value="">Select employee</option>
                                        <?php foreach ($employees as $employee): ?>
                                            <option value="<?= e((string) $employee['id']) ?>" <?= $fieldValue('action') === 'create_schedule' && $fieldValue('employee_id') === (string) $employee['id'] ? 'selected' : '' ?>><?= e($employee['full_name'] . ' (' . $employee['employee_code'] . ')') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>Day of week
                                    <select name="day_of_week" required>
                                        <option value="">Select day</option>
                                        <?php foreach ($weekdays as $dayNumber => $dayName): ?>
                                            <option value="<?= e((string) $dayNumber) ?>" <?= $fieldValue('action') === 'create_schedule' && $fieldValue('day_of_week') === (string) $dayNumber ? 'selected' : '' ?>><?= e($dayName) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>Start time
                                    <input name="start_time" type="time" value="<?= e($fieldValue('action') === 'create_schedule' ? $fieldValue('start_time') : '09:00') ?>">
                                </label>
                                <label>End time
                                    <input name="end_time" type="time" value="<?= e($fieldValue('action') === 'create_schedule' ? $fieldValue('end_time') : '17:00') ?>">
                                </label>
                            </div>
                            <label class="employee-day-off">
                                <input name="is_day_off" type="checkbox" value="1" <?= $fieldValue('action') === 'create_schedule' && $fieldValue('is_day_off') === '1' ? 'checked' : '' ?>>
                                Day off
                            </label>
                            <button class="primary-button" type="submit" <?= $employees === [] ? 'disabled title="Add an employee first"' : '' ?>>Save schedule</button>
                        </form>
                    </article>
                </section>

                <section class="module-panel employee-table-panel">
                    <h2>Employees</h2>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead><tr><th>Code</th><th>Name</th><th>Role</th><th>Phone</th><th>Email</th><th>Salary</th><th>Hire date</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if ($employees === []): ?>
                                    <tr><td colspan="8">No employees added yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($employees as $employee): ?>
                                        <tr>
                                            <td><?= e($employee['employee_code']) ?></td>
                                            <td><?= e($employee['full_name']) ?></td>
                                            <td><?= e(role_label($employee['role_name'])) ?></td>
                                            <td><?= e($employee['phone'] ?? '') ?></td>
                                            <td><?= e($employee['email'] ?? '') ?></td>
                                            <td><?= e(number_format((float) $employee['salary'], 2)) ?></td>
                                            <td><?= e($employee['hire_date'] ?? '—') ?></td>
                                            <td><?= e(ucwords(str_replace('_', ' ', $employee['status']))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="employee-record-grid">
                    <article class="module-panel employee-table-panel">
                        <h2>Recent attendance</h2>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>Date</th><th>Employee</th><th>Status</th><th>Check in</th><th>Check out</th></tr></thead>
                                <tbody>
                                    <?php if ($attendanceRecords === []): ?>
                                        <tr><td colspan="5">No attendance records yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($attendanceRecords as $record): ?>
                                            <tr>
                                                <td><?= e($record['attendance_date']) ?></td>
                                                <td><?= e($record['full_name'] . ' (' . $record['employee_code'] . ')') ?></td>
                                                <td><?= e(ucwords(str_replace('_', ' ', $record['status']))) ?></td>
                                                <td><?= e($record['check_in'] !== null ? date('g:i A', strtotime($record['check_in'])) : '—') ?></td>
                                                <td><?= e($record['check_out'] !== null ? date('g:i A', strtotime($record['check_out'])) : '—') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="module-panel employee-table-panel">
                        <h2>Weekly schedules</h2>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>Employee</th><th>Day</th><th>Hours</th></tr></thead>
                                <tbody>
                                    <?php if ($schedules === []): ?>
                                        <tr><td colspan="3">No schedules added yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($schedules as $schedule): ?>
                                            <tr>
                                                <td><?= e($schedule['full_name'] . ' (' . $schedule['employee_code'] . ')') ?></td>
                                                <td><?= e($weekdays[(int) $schedule['day_of_week']] ?? 'Unknown') ?></td>
                                                <td><?= (int) $schedule['is_day_off'] === 1 ? 'Day off' : e(substr((string) $schedule['start_time'], 0, 5) . '–' . substr((string) $schedule['end_time'], 0, 5)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
