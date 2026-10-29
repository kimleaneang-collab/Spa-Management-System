<?php
declare(strict_types=1);

$user = $_SESSION['user'] ?? [];
$userName = (string) ($user['full_name'] ?? $user['username'] ?? 'Receptionist');
$nameParts = preg_split('/\s+/', trim($userName)) ?: [];
$initials = strtoupper(substr($nameParts[0] ?? 'R', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register Customer | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page receptionist-role receptionist-booking-page receptionist-register-customer-page" data-role="receptionist">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block"><div class="brand-mark"><span class="brand-leaf">⌁</span><div class="brand-text"><div class="brand-name">Relax</div><div class="brand-subtitle">SPA</div></div></div></div>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>Calendar</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section" data-label="Quick Actions"><a class="nav-item" href="<?= e(APP_URL) ?>/?route=new-appointment"><span class="nav-icon">＋</span><span>New Appointment</span></a><a class="nav-item active" href="<?= e(APP_URL) ?>/?route=register-customer"><span class="nav-icon">♙</span><span>Register Customer</span></a></div>
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
            <div class="content-wrap receptionist-workspace receptionist-new-appointment-page receptionist-register-customer-page-content">
                <div class="page-heading receptionist-form-heading"><div><h1 class="page-title">REGISTER CUSTOMER</h1><div class="page-date">Add a new guest to the Relax Spa system.</div></div></div>
                <form class="receptionist-booking-layout receptionist-new-appointment-form receptionist-register-form" method="post" action="<?= e(APP_URL) ?>/?route=customers">
                        <input type="hidden" name="action" value="create">
                        <div class="receptionist-form-card receptionist-register-card">
                        <section class="receptionist-form-section receptionist-profile-photo-section">
                            <h2>Profile Photo</h2>
                            <label class="receptionist-photo-upload" for="customer-photo-input">
                                <input id="customer-photo-input" type="file" accept="image/png,image/jpeg" data-customer-photo>
                                <img alt="Customer photo preview" data-customer-photo-preview hidden>
                                <span class="receptionist-photo-upload-prompt" data-customer-photo-prompt>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 15v4h14v-4"></path></svg>
                                    <strong>Click to upload a photo</strong>
                                    <small>PNG or JPG, up to 5MB</small>
                                </span>
                            </label>
                            <span class="receptionist-photo-upload-error" role="alert" data-customer-photo-error hidden></span>
                        </section>
                        <section class="receptionist-form-section receptionist-personal-information"><h2>Personal Information</h2>
                            <div class="receptionist-form-grid">
                                <label><span>Full Name</span><input type="text" name="customer_name" placeholder="e.g May Berry" minlength="2" maxlength="150" required></label>
                                <label><span>Phone Number</span><input type="tel" name="customer_phone" placeholder="e.g 012345678" pattern="[0-9+()\-\s]{7,30}" maxlength="30" required></label>
                                <label><span>Email Address</span><input type="email" name="customer_email" placeholder="e.g mayberry@email.com" maxlength="150"></label>
                                <label><span>Date Of Birth</span><input type="date" name="customer_date_of_birth" max="<?= e(date('Y-m-d')) ?>"></label>
                                <label><span>Gender</span><select name="customer_gender"><option value="">Select gender</option><option value="female">Female</option><option value="male">Male</option><option value="other">Other</option><option value="prefer_not_to_say">Prefer not to say</option></select></label>
                                <label><span>Membership Tier</span><input type="text" value="Regular" readonly aria-label="Membership tier"></label>
                            </div>
                        </section>
                        <section class="receptionist-form-section receptionist-additional-notes"><h2>Additional Notes</h2><label><span class="visually-hidden">Allergies and preferences</span><textarea name="customer_notes" rows="3" maxlength="2000" placeholder="Allergies, preferences, or how they heard about us..."></textarea></label></section>
                        <div class="receptionist-form-actions"><a class="secondary-button" href="<?= e(APP_URL) ?>/?route=customers">Cancel</a><button class="primary-button" type="submit">Register Customer</button></div>
                        </div>
                    <aside class="receptionist-booking-summary receptionist-booking-summary-card receptionist-registration-summary"><h2>New Guest</h2><dl><div><dt>Status</dt><dd>Draft</dd></div><div><dt>Membership</dt><dd>Regular</dd></div><div><dt>Visits</dt><dd>0</dd></div><div><dt>Registered by</dt><dd><?= e($userName) ?></dd></div></dl></aside>
                </form>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
