<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

$therapistName = (string) ($editingTherapist['full_name'] ?? 'New therapist');
$therapistNameParts = preg_split('/\s+/', trim($therapistName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$therapistInitials = $therapistNameParts === []
    ? '?'
    : strtoupper(substr($therapistNameParts[0], 0, 1) . substr($therapistNameParts[count($therapistNameParts) - 1], 0, 1));
$accountPhotoUrl = profile_image_url((string) ($editingTherapist['account_profile_image'] ?? ''));

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Therapists | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>

            <nav class="sidebar-menu">
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Treatments</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
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
            <?= sidebar_role_footer() ?>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Admin')) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <div class="user-chip"><?= profile_avatar($_SESSION['user'] ?? [], 'user-name', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'SV'), 0, 2))) ?><span class="user-role"><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'admin'))) ?></span><span class="user-chevron">▾</span></div>
                </div>
            </header>

            <div class="content-wrap">
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">THERAPISTS</h1>
                        <div class="page-date">Daily availability overview</div>
                    </div>
                    <button class="primary-button" type="button" data-open-modal="therapist-modal">+ Add therapist</button>
                </div>

                <section class="module-panel">
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Therapist</th>
                                    <th>Specialty</th>
                                    <th>Status</th>
                                    <th>Today bookings</th>
                                    <th>Rating</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($therapists as $therapist): ?>
                                    <tr>
                                        <td><?= e($therapist['name']) ?></td>
                                        <td><?= e($therapist['specialty']) ?></td>
                                        <td><span class="status-pill status-<?= e($therapist['status_class']) ?>"><?= e($therapist['status']) ?></span></td>
                                        <td><?= e((string) $therapist['today']) ?></td>
                                        <td>⭐ <?= e(number_format((float) $therapist['rating'], 1)) ?></td>
                                        <td>
                                            <a class="secondary-button" href="<?= e(APP_URL) ?>/?route=therapists&amp;edit=<?= e((string) ($therapist['id'] ?? 0)) ?>">Edit</a>
                                            <form method="post" action="<?= e(APP_URL) ?>/?route=therapists" style="display:inline" onsubmit="return confirm('Delete this therapist?');">
                                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= e((string) ($therapist['id'] ?? 0)) ?>">
                                                <button class="secondary-button" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-backdrop <?= ($editingTherapist !== null || !empty($_GET['new']) || $therapistFormError !== '') ? '' : 'hidden' ?>" id="therapist-modal" aria-hidden="<?= ($editingTherapist !== null || !empty($_GET['new']) || $therapistFormError !== '') ? 'false' : 'true' ?>">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="therapist-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Therapist</p>
                    <h2 id="therapist-title"><?= $editingTherapist !== null ? 'Edit therapist' : 'Add therapist' ?></h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="therapist-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=therapists" enctype="multipart/form-data">
                <?php if ($therapistFormError !== ''): ?>
                    <div class="therapist-profile-notice" role="alert"><?= e($therapistFormError) ?></div>
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="<?= $editingTherapist !== null ? 'update' : 'create' ?>">
                <input type="hidden" name="id" value="<?= e((string) ($editingTherapist['id'] ?? '')) ?>">
                <div class="form-grid two-col">
                    <label>
                        <span>Full name</span>
                        <input type="text" name="therapist_name" value="<?= e($editingTherapist['full_name'] ?? '') ?>" minlength="2" maxlength="150" required>
                    </label>
                    <label>
                        <span>Phone</span>
                        <input type="tel" name="therapist_phone" value="<?= e($editingTherapist['phone'] ?? '') ?>" pattern="[0-9+()\-\s]{7,30}" maxlength="30" required>
                    </label>
                    <label>
                        <span>Email</span>
                        <input type="email" name="therapist_email" value="<?= e($editingTherapist['email'] ?? $editingTherapist['account_email'] ?? '') ?>" maxlength="150" autocomplete="email">
                    </label>
                    <label>
                        <span>Login account</span>
                        <select name="therapist_user_id" id="therapist-login-account">
                            <option value="">Not linked</option>
                            <?php foreach ($therapistAccounts as $account): ?>
                                <option value="<?= e((string) $account['id']) ?>" data-profile-image="<?= e(profile_image_url((string) ($account['profile_image'] ?? ''))) ?>" <?= (int) ($editingTherapist['user_id'] ?? 0) === (int) $account['id'] ? 'selected' : '' ?>>
                                    <?= e($account['full_name'] . ' (' . $account['username'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="new">Create new therapist login</option>
                        </select>
                    </label>
                    <div class="therapist-login-credentials" id="therapist-login-credentials" hidden>
                        <label>
                            <span>Therapist username</span>
                            <input type="text" name="therapist_username" minlength="3" maxlength="100" pattern="[A-Za-z0-9@_.-]{3,100}" title="Use 3–100 characters: letters, numbers, @, dots, underscores, or hyphens." autocomplete="username">
                        </label>
                        <label>
                            <span>Login password</span>
                            <input type="password" name="therapist_password" minlength="16" maxlength="255" pattern="(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s]).{16,255}" title="Use at least 16 characters, including a letter, a number, and a symbol." autocomplete="new-password">
                        </label>
                        <p>Share this username and password with the therapist. Use at least 16 characters with a letter, number, and symbol. Passwords are stored as hashes.</p>
                    </div>
                    <label>
                        <span>Specialization</span>
                        <input type="text" name="therapist_specialization" value="<?= e($editingTherapist['specialization'] ?? '') ?>" placeholder="Massage Therapy">
                    </label>
                    <div class="user-profile-photo-field">
                        <div class="user-profile-photo-preview">
                            <img id="therapist-profile-photo-image" alt="Selected therapist profile photo preview" <?= $accountPhotoUrl !== '' ? 'src="' . e($accountPhotoUrl) . '"' : 'hidden' ?>>
                            <span id="therapist-profile-photo-initials" <?= $accountPhotoUrl !== '' ? 'hidden' : '' ?>><?= e($therapistInitials) ?></span>
                        </div>
                        <div class="user-profile-photo-controls">
                            <strong>Therapist profile photo</strong>
                            <label class="user-profile-photo-button" for="therapist-profile-photo-input">Choose photo</label>
                            <input
                                class="user-profile-photo-input"
                                id="therapist-profile-photo-input"
                                type="file"
                                name="profile_image"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                            >
                            <small id="therapist-profile-photo-filename">JPG, PNG, GIF, or WEBP. Maximum 5 MB. Choose or create a login account to save the photo.</small>
                        </div>
                    </div>
                    <label class="therapist-admin-bio">
                        <span>Bio</span>
                        <textarea name="therapist_bio" maxlength="2000" rows="3"><?= e($editingTherapist['bio'] ?? '') ?></textarea>
                    </label>
                    <label>
                        <span>Gender</span>
                        <select name="therapist_gender">
                            <option value="">Select gender</option>
                            <option value="female" <?= ($editingTherapist['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="male" <?= ($editingTherapist['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="other" <?= ($editingTherapist['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </label>
                    <label>
                        <span>Status</span>
                        <select name="therapist_status">
                            <option value="active" <?= ($editingTherapist['employment_status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Available</option>
                            <option value="on_leave" <?= ($editingTherapist['employment_status'] ?? '') === 'on_leave' ? 'selected' : '' ?>>On Break</option>
                            <option value="inactive" <?= ($editingTherapist['employment_status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Off Duty</option>
                        </select>
                    </label>
                </div>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="therapist-modal">Cancel</button>
                    <button class="primary-button" type="submit"><?= $editingTherapist !== null ? 'Update therapist' : 'Save therapist' ?></button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
