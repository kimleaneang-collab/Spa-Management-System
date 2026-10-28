<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

$currentUser = $_SESSION['user'];
$isManager = strtolower((string) ($currentUser['role'] ?? '')) === 'manager';
$currentUserName = (string) ($currentUser['full_name'] ?? $currentUser['username'] ?? 'User');
$currentUserRole = role_label((string) ($currentUser['role'] ?? 'staff'));
$formValue = static function (string $key, mixed $default = '') use ($formValues): string {
    return (string) ($formValues[$key] ?? $default);
};
$editingSelf = $editingUser !== null && (int) $editingUser['id'] === (int) ($currentUser['id'] ?? 0);
$initials = static function (string $name): string {
    $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if ($parts === []) {
        return '?';
    }

    return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
};
$lastActive = static function (?string $value): string {
    if ($value === null || $value === '') {
        return 'Never';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return 'Unknown';
    }

    $elapsed = max(0, time() - $timestamp);
    if ($elapsed < 60) {
        return 'Just now';
    }
    if ($elapsed < 3600) {
        return (string) floor($elapsed / 60) . ' min ago';
    }
    if ($elapsed < 86400) {
        return (string) floor($elapsed / 3600) . ' hr ago';
    }

    return date('M j, Y', $timestamp);
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users &amp; Roles | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page <?= $isManager ? 'manager-role' : '' ?>" data-role="<?= e(strtolower((string) ($currentUser['role'] ?? ''))) ?>">
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span><?= $isManager ? 'Services' : 'Treatments' ?></span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">◍</span><span>Customer History</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span><?= $isManager ? 'Inventory' : 'Products' ?></span></a>
                    <?php if (!$isManager): ?>
                        <a class="nav-item" href="<?= e(APP_URL) ?>/?route=stock-management"><span class="nav-icon">▤</span><span>Stock Management</span></a>
                        <a class="nav-item" href="<?= e(APP_URL) ?>/?route=suppliers"><span class="nav-icon">◧</span><span>Supplier</span></a>
                    <?php endif; ?>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
                </div>
                <div class="nav-section">
                    <?php if ($isManager): ?>
                        <a class="nav-item" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS &amp; Billing</span></a>
                    <?php endif; ?>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
            <div class="sidebar-role">
                <span>Current Role</span>
                <div class="role-select">
                    <?= profile_avatar($currentUser, 'role-avatar', $initials($currentUserName)) ?>
                    <span><?= e($currentUserRole) ?></span>
                </div>
            </div>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e($currentUserName) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="search-box search-input-wrap">
                        <span class="search-icon">⌕</span>
                        <input type="search" class="search-input" placeholder="Search users" aria-label="Search users">
                    </div>
                    <div class="user-chip">
                        <?= profile_avatar($currentUser, 'user-name', $initials($currentUserName)) ?>
                        <span class="user-role"><?= e($currentUserRole) ?></span>
                        <form method="post" action="<?= e(APP_URL) ?>/?route=logout" class="logout-form">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <button class="logout-link" type="submit">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <div class="content-wrap users-content">
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">USERS &amp; ROLES</h1>
                        <div class="page-date">Manage staff accounts and access levels.</div>
                    </div>
                    <button class="primary-button" type="button" data-open-modal="user-modal">＋ Invite User</button>
                </div>

                <?php if ($formError !== ''): ?>
                    <div class="alert alert-error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>

                <section class="module-panel users-panel" aria-label="Staff accounts">
                    <div class="users-table-wrap table-wrap">
                        <table class="data-table searchable-table users-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last active</th>
                                    <th><span class="visually-hidden">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users === []): ?>
                                    <tr><td class="users-empty" colspan="6">No staff accounts yet. Invite a user to get started.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <?php
                                            $name = (string) ($user['full_name'] ?? '');
                                            $role = strtolower((string) ($user['role_name'] ?? ''));
                                            $status = strtolower((string) ($user['status'] ?? 'inactive'));
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <?= profile_avatar($user, 'user-avatar', $initials($name)) ?>
                                                    <span>
                                                        <span class="user-cell-name"><?= e($name) ?></span>
                                                        <span class="user-cell-username"><?= e((string) ($user['username'] ?? '')) ?></span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td><?= e((string) ($user['email'] ?? '')) ?></td>
                                            <td><span class="role-badge role-<?= e(preg_replace('/[^a-z0-9-]/', '', $role) ?? '') ?>"><?= e(role_label($role)) ?></span></td>
                                            <td><span class="account-status status-<?= e($status) ?>"><?= e(ucfirst($status)) ?></span></td>
                                            <td><?= e($lastActive(isset($user['last_login_at']) ? (string) $user['last_login_at'] : null)) ?></td>
                                            <td class="user-actions">
                                                <a class="user-action-button" href="<?= e(APP_URL) ?>/?route=users-and-roles&amp;edit=<?= e((string) $user['id']) ?>">Edit</a>
                                                <form method="post" action="<?= e(APP_URL) ?>/?route=users-and-roles" onsubmit="return confirm('Delete this user account? You will be logged out if this is your own account.');">
                                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= e((string) $user['id']) ?>">
                                                    <button class="user-action-button delete-user" type="submit">Delete</button>
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

    <div class="modal-backdrop <?= $showUserModal ? '' : 'hidden' ?>" id="user-modal" aria-hidden="<?= $showUserModal ? 'false' : 'true' ?>">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Team access</p>
                    <h2 id="user-modal-title"><?= $editingUser !== null ? 'Edit user' : 'Invite user' ?></h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="user-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=users-and-roles" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="<?= $editingUser !== null ? 'update' : 'create' ?>">
                <input type="hidden" name="id" value="<?= e((string) ($editingUser['id'] ?? '')) ?>">
                <div class="form-grid two-col">
                    <label>
                        <span>Full name</span>
                        <input type="text" name="user_name" value="<?= e($formValue('user_name', $editingUser['full_name'] ?? '')) ?>" minlength="2" maxlength="150" required autocomplete="name">
                    </label>
                    <label>
                        <span>Username</span>
                        <input type="text" name="username" value="<?= e($formValue('username', $editingUser['username'] ?? '')) ?>" pattern="[A-Za-z0-9@_.-]{3,100}" minlength="3" maxlength="100" title="Use 3–100 characters: letters, numbers, @, dots, underscores, or hyphens." required autocomplete="username">
                    </label>
                    <label>
                        <span>Email</span>
                        <input type="email" name="user_email" value="<?= e($formValue('user_email', $editingUser['email'] ?? '')) ?>" maxlength="150" required autocomplete="email">
                    </label>
                    <label>
                        <span>Phone</span>
                        <input type="tel" name="user_phone" value="<?= e($formValue('user_phone', $editingUser['phone'] ?? '')) ?>" maxlength="30" autocomplete="tel">
                    </label>
                    <div class="user-profile-photo-field">
                        <div class="user-profile-photo-preview">
                            <img
                                id="user-profile-photo-image"
                                src="<?= !empty($editingUser['profile_image']) ? e(APP_URL . '/' . ltrim((string) $editingUser['profile_image'], '/')) : '' ?>"
                                alt="Selected profile photo preview"
                                <?= empty($editingUser['profile_image']) ? 'hidden' : '' ?>
                            >
                            <span id="user-profile-photo-initials" <?= !empty($editingUser['profile_image']) ? 'hidden' : '' ?>><?= e($initials((string) ($editingUser['full_name'] ?? $formValue('user_name', 'New user')))) ?></span>
                        </div>
                        <div class="user-profile-photo-controls">
                            <strong>Profile photo</strong>
                            <label class="user-profile-photo-button" for="user-profile-photo-input">Choose photo</label>
                            <input
                                class="user-profile-photo-input"
                                id="user-profile-photo-input"
                                type="file"
                                name="profile_image"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                            >
                            <small id="user-profile-photo-filename">JPG, PNG, GIF, or WEBP. Maximum 5 MB. Leave empty to keep the current photo.</small>
                        </div>
                    </div>
                    <label>
                        <span>Role</span>
                        <select name="role_name" required <?= $editingSelf ? 'disabled' : '' ?>>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= e((string) $role) ?>" <?= strtolower($formValue('role_name', $editingUser['role_name'] ?? 'manager')) === strtolower((string) $role) ? 'selected' : '' ?>><?= e(role_label((string) $role)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($editingSelf): ?>
                            <input type="hidden" name="role_name" value="<?= e((string) $editingUser['role_name']) ?>">
                        <?php endif; ?>
                    </label>
                    <label>
                        <span>Status</span>
                        <select name="user_status" required <?= $editingSelf ? 'disabled' : '' ?>>
                            <?php foreach (['active', 'inactive', 'suspended'] as $status): ?>
                                <option value="<?= e($status) ?>" <?= $formValue('user_status', $editingUser['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($editingSelf): ?>
                            <input type="hidden" name="user_status" value="<?= e((string) $editingUser['status']) ?>">
                        <?php endif; ?>
                    </label>
                    <label class="user-password-field">
                        <span>Password <?= $editingUser !== null ? '(leave blank to keep current)' : '' ?></span>
                        <div class="password-wrap user-password-wrap">
                            <input type="password" name="user_password" minlength="16" maxlength="255" pattern="(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s]).{16,255}" title="Use at least 16 characters, including a letter, a number, and a symbol." <?= $editingUser === null ? 'required' : '' ?> autocomplete="new-password">
                            <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false" data-password-toggle>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" data-password-visible>
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" data-password-hidden hidden>
                                    <path d="m3 3 18 18"></path>
                                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                    <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.8"></path>
                                    <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10.7 10.7 0 0 0 4-.8"></path>
                                </svg>
                            </button>
                        </div>
                        <small>Use at least 16 characters, including a letter, a number, and a symbol (for example: @, #, or $). Leave blank when editing to keep the current password.</small>
                    </label>
                </div>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="user-modal">Cancel</button>
                    <button class="primary-button" type="submit"><?= $editingUser !== null ? 'Save changes' : 'Create account' ?></button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
