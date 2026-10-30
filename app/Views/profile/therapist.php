<?php
declare(strict_types=1);

$name = (string) ($therapistProfile['full_name'] ?? $profileUser['full_name'] ?? '');
$email = (string) ($therapistProfile['email'] ?? $profileUser['email'] ?? '');
$phone = (string) ($therapistProfile['profile_phone'] ?? $profileUser['phone'] ?? '');
$specialization = (string) ($therapistProfile['specialization'] ?? '');
$bio = (string) ($therapistProfile['bio'] ?? '');
$nameParts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$initials = $nameParts === []
    ? '?'
    : strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
$experienceYears = (float) ($therapistProfile['experience_years'] ?? 0);
$therapistTitle = $experienceYears >= 5 ? 'Senior Therapist' : 'Therapist';
$joinedTimestamp = !empty($therapistProfile['joined_date'])
    ? strtotime((string) $therapistProfile['joined_date'])
    : false;
$joinedDate = $joinedTimestamp !== false ? date('M Y', $joinedTimestamp) : 'Join date unavailable';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page therapist-dashboard-page" data-role="<?= e(strtolower((string) ($_SESSION['user']['role'] ?? 'staff'))) ?>">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>My Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>My Customer</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">▣</span><span>Treatment History</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>My Schedule</span></a>
                </div>
                <div class="nav-section" data-label="Work">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">♨</span><span>Service</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                </div>
                <?= therapist_account_nav('profile') ?>
            </nav>
            <?= sidebar_role_footer(false) ?>
            <p class="therapist-sidebar-note">Stay calm &amp; relax.<br>You're doing great today!</p>
        </aside>
        <main class="main-panel">
            <header class="topbar therapist-topbar">
                <div class="search-box search-input-wrap">
                    <span class="search-icon" aria-hidden="true">⌕</span>
                    <input class="search-input" type="search" placeholder="Search customer, appointments, services..." aria-label="Search">
                </div>
                <div class="topbar-actions">
                    <span class="therapist-notification" aria-hidden="true">♧</span>
                    <a class="therapist-calendar-shortcut" href="<?= e(APP_URL) ?>/?route=calendar" aria-label="Open my schedule">▦</a>
                    <div class="user-chip therapist-user-chip">
                        <?= profile_avatar($_SESSION['user'] ?? [], 'user-name', $initials) ?>
                        <span class="therapist-user-details"><strong><?= e($name) ?></strong><small>Therapist</small></span>
                    </div>
                </div>
            </header>
            <div class="content-wrap therapist-profile-content">
                <div class="therapist-profile-page-heading">
                    <h1>My Profile</h1>
                    <p>Your profile details provided by Admin</p>
                </div>

                <?php if ($therapistProfile === null): ?>
                    <div class="therapist-profile-notice" role="status">Your login is not linked to an Admin-managed therapist record. Contact Admin to link or update your profile.</div>
                <?php else: ?>
                    <div class="therapist-profile-notice" role="status">These details are managed by Admin. Contact Admin if they need to be changed.</div>
                <?php endif; ?>

                <section class="therapist-profile-card" aria-label="Personal and professional details">
                    <div class="therapist-profile-identity">
                        <?= profile_avatar($_SESSION['user'] ?? [], 'therapist-profile-avatar', $initials) ?>
                        <div><h2><?= e($name) ?></h2><p><?= e($therapistTitle) ?> <span>·</span> Joined <?= e($joinedDate) ?></p></div>
                    </div>
                    <div class="therapist-profile-fields">
                        <div class="therapist-profile-field">
                            <span>Full Name</span>
                            <div class="therapist-profile-value"><?= e($name !== '' ? $name : 'Not provided') ?></div>
                        </div>
                        <div class="therapist-profile-field">
                            <span>Email</span>
                            <div class="therapist-profile-value"><?= e($email !== '' ? $email : 'Not provided') ?></div>
                        </div>
                        <div class="therapist-profile-field">
                            <span>Phone</span>
                            <div class="therapist-profile-value"><?= e($phone !== '' ? $phone : 'Not provided') ?></div>
                        </div>
                        <div class="therapist-profile-field">
                            <span>Specialization</span>
                            <div class="therapist-profile-value"><?= e($specialization !== '' ? $specialization : 'Not provided') ?></div>
                        </div>
                        <div class="therapist-profile-field therapist-profile-bio">
                            <span>Bio</span>
                            <div class="therapist-profile-value therapist-profile-bio-value"><?= $bio !== '' ? nl2br(e($bio)) : 'Not provided' ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
