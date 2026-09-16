<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Therapists | Relax Spa</title>
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
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, Admin!</div></div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <div class="user-chip"><span class="user-name">SV</span><span class="user-role">Admin</span><span class="user-chevron">▾</span></div>
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

    <div class="modal-backdrop <?= $editingTherapist !== null ? '' : 'hidden' ?>" id="therapist-modal" aria-hidden="<?= $editingTherapist !== null ? 'false' : 'true' ?>">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="therapist-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Therapist</p>
                    <h2 id="therapist-title"><?= $editingTherapist !== null ? 'Edit therapist' : 'Add therapist' ?></h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="therapist-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=therapists">
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
                        <span>Specialization</span>
                        <input type="text" name="therapist_specialization" value="<?= e($editingTherapist['specialization'] ?? '') ?>" placeholder="Massage Therapy">
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

    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
