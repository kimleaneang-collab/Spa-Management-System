<?php
declare(strict_types=1);

$user = $_SESSION['user'] ?? [];
$userName = (string) ($user['full_name'] ?? $user['username'] ?? 'Receptionist');
$nameParts = preg_split('/\s+/', trim($userName)) ?: [];
$initials = strtoupper(substr($nameParts[0] ?? 'R', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
$selectedCustomerId = (int) ($_GET['customer_id'] ?? 0);
$selectedCustomerName = '';
foreach ($options['customers'] as $customerOption) {
    if ((int) $customerOption['id'] === $selectedCustomerId) {
        $selectedCustomerName = (string) $customerOption['full_name'];
        break;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Appointment | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page receptionist-role receptionist-booking-page" data-role="receptionist">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block"><div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div></div>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>Calendar</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section" data-label="Quick Actions"><a class="nav-item active" href="<?= e(APP_URL) ?>/?route=new-appointment"><span class="nav-icon">＋</span><span>New Appointment</span></a><a class="nav-item" href="<?= e(APP_URL) ?>/?route=register-customer"><span class="nav-icon">♙</span><span>Register Customer</span></a></div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>
        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-actions receptionist-topbar-actions">
                    <div class="search-box"><span class="search-icon" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><circle cx="8.5" cy="8.5" r="5.75"></circle><path d="m13 13 4 4"></path></svg></span><span>Search</span></div>
                    <div class="receptionist-topbar-tools">
                        <button class="icon-button" type="button" aria-label="Notifications"><svg viewBox="0 0 24 24" fill="none"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"></path></svg></button>
                        <a class="icon-button" href="<?= e(APP_URL) ?>/?route=calendar" aria-label="Calendar"><svg viewBox="0 0 24 24" fill="none"><rect x="4" y="5" width="16" height="16" rx="2"></rect><path d="M8 3v4m8-4v4M4 10h16m-8 3v6m-3-3h6"></path></svg></a>
                    </div>
                    <div class="user-chip receptionist-user-chip">
                        <?= profile_avatar($user, 'user-name', $initials) ?>
                        <span class="receptionist-user-details"><span class="receptionist-user-full-name"><?= e($userName) ?></span><span class="user-role">Receptionist</span></span>
                        <span class="user-chevron" aria-hidden="true">⌄</span>
                    </div>
                </div>
            </header>
            <div class="content-wrap receptionist-workspace receptionist-new-appointment-page">
                <div class="page-heading receptionist-form-heading">
                    <div><h1 class="page-title">NEW APPOINTMENT</h1><div class="page-date">Book a treatment for a customer or walk-in guest.</div></div>
                </div>
                <?php if ($formError !== ''): ?><div class="alert alert-error" role="alert"><?= e($formError) ?></div><?php endif; ?>
                <form class="receptionist-booking-layout receptionist-new-appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=appointments" data-booking-form>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="status" value="pending">
                        <div class="receptionist-form-card">
                            <section class="receptionist-form-section">
                                <h2>Customer</h2>
                                <div class="receptionist-form-grid receptionist-customer-fields">
                                    <label><span>Customer Type</span><input type="text" value="Existing Customer" readonly aria-label="Customer type"></label>
                                    <label><span>Search Customer</span><input type="search" name="customer_search" placeholder="eg. May Berry" autocomplete="off" list="appointment-customer-options" value="<?= e($selectedCustomerName) ?>" required data-customer-picker-search><datalist id="appointment-customer-options"><?php foreach ($options['customers'] as $customer): ?><option value="<?= e((string) $customer['full_name']) ?>" data-customer-id="<?= e((string) $customer['id']) ?>"></option><?php endforeach; ?></datalist></label>
                                </div>
                                <input type="hidden" name="customer_id" value="<?= e((string) $selectedCustomerId) ?>" data-booking-customer>
                            </section>
                            <section class="receptionist-form-section">
                                <h2>Personal Information</h2>
                                <div class="receptionist-form-grid">
                                    <label><span>Service</span><select name="service_id" required data-booking-service><option value="">Select a service</option><?php foreach ($options['services'] as $service): ?><option value="<?= e((string) $service['id']) ?>" data-duration="<?= e((string) $service['duration_minutes']) ?>" data-price="<?= e((string) $service['price']) ?>"><?= e((string) $service['name']) ?></option><?php endforeach; ?></select></label>
                                    <label><span>Duration</span><input type="text" value="Select a service" readonly data-booking-duration></label>
                                    <label><span>Therapist</span><select name="therapist_id" data-summary-field="therapist"><option value="">Select a therapist</option><?php foreach ($options['therapists'] as $therapist): ?><option value="<?= e((string) $therapist['id']) ?>"><?= e((string) $therapist['full_name']) ?></option><?php endforeach; ?></select></label>
                                    <label><span>Room</span><select name="room_id" data-summary-field="room"><option value="">Select a room</option><?php foreach ($options['rooms'] as $room): ?><option value="<?= e((string) $room['id']) ?>"><?= e((string) ($room['room_name'] ?: $room['room_code'])) ?></option><?php endforeach; ?></select></label>
                                </div>
                            </section>
                            <section class="receptionist-form-section">
                                <h2>Date &amp; Time</h2>
                                <div class="receptionist-form-grid">
                                    <label><span>Date</span><input type="date" name="appointment_date" value="<?= e(date('Y-m-d')) ?>" min="<?= e(date('Y-m-d')) ?>" required data-summary-field="date"></label>
                                    <label><span>Time</span><input type="time" name="start_time" required data-summary-field="time"></label>
                                </div>
                            </section>
                            <section class="receptionist-form-section"><h2>Notes</h2><label><span class="visually-hidden">Special requests or allergies</span><textarea name="notes" rows="3" maxlength="2000" placeholder="Any special requests or allergies..."></textarea></label></section>
                        </div>
                    <aside class="receptionist-booking-summary receptionist-booking-summary-card">
                        <h2>Booking Summary</h2>
                        <dl>
                            <div><dt>Customer</dt><dd data-summary-customer>Select customer</dd></div>
                            <div><dt>Service</dt><dd data-summary-service>Select service</dd></div>
                            <div><dt>Therapist</dt><dd data-summary-therapist>Not selected</dd></div>
                            <div><dt>Room</dt><dd data-summary-room>Not selected</dd></div>
                            <div><dt>Date</dt><dd><span data-summary-date><?= e(date('M j')) ?></span>, <span data-summary-time>Not selected</span></dd></div>
                            <div class="summary-total"><dt>Total</dt><dd data-summary-price>—</dd></div>
                        </dl>
                        <div class="receptionist-form-actions"><a class="secondary-button" href="<?= e(APP_URL) ?>/?route=appointments">Cancel</a><button class="primary-button" type="submit">Book Appointment</button></div>
                    </aside>
                </form>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>