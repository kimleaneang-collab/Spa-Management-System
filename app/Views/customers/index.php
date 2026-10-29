<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}
$isReceptionist = strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'receptionist';
$isTherapist = in_array(strtolower((string) ($_SESSION['user']['role'] ?? '')), ['staff', 'therapist'], true);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customers | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page <?= $isReceptionist ? 'receptionist-role' : '' ?> <?= $isTherapist ? 'therapist-dashboard-page' : '' ?>" data-role="<?= e(strtolower((string) ($_SESSION['user']['role'] ?? ''))) ?>">
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
            <?php if ($isTherapist): ?>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☷</span><span>My Appointments</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>My Customer</span></a>
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Treatments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
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
                    <input type="search" class="search-input" placeholder="Search customer, appointments, services..." aria-label="Search customers" data-therapist-customer-search>
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
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Admin')) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="search-box search-input-wrap">
                        <span class="search-icon">⌕</span>
                        <input type="search" class="search-input" placeholder="Search" aria-label="Search">
                    </div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <a class="icon-button add-button" href="<?= e(APP_URL) ?>/?route=register-customer" aria-label="Register customer" title="Register customer">＋</a>
                    <div class="user-chip">
                        <?= profile_avatar($_SESSION['user'] ?? [], 'user-name', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'SV'), 0, 2))) ?>
                        <span class="user-role"><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'admin'))) ?></span>
                        <form method="post" action="<?= e(APP_URL) ?>/?route=logout" class="logout-form">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <button class="logout-link" type="submit">Logout</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </header>
            <div class="content-wrap <?= $isReceptionist ? 'receptionist-customers-page' : '' ?> <?= $isTherapist ? 'therapist-customers-content' : '' ?>">
                <?php if ($isTherapist): ?>
                <div class="therapist-dashboard-heading therapist-customers-heading">
                    <div><h1>My Customer</h1><p>Everyone you've treated, at a glance.</p></div>
                </div>
                <div class="therapist-customers-toolbar">
                    <span><?= number_format($customerCount) ?> customers</span>
                    <button class="primary-button" type="button" data-open-modal="therapist-customer-modal">＋ Add customer</button>
                </div>
                <?php if ($therapistId === null): ?>
                    <div class="therapist-profile-notice" role="status">Your login is not linked to a therapist profile yet. Please ask your manager to connect your account.</div>
                <?php endif; ?>
                <section class="therapist-customer-grid" aria-label="Customers treated by you">
                    <?php if ($customers === []): ?>
                        <div class="therapist-customer-empty"><?= $therapistId === null ? 'Customers will appear here after your account is linked to a therapist profile.' : 'No customers have been assigned to you yet.' ?></div>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <?php
                                $customerNameParts = preg_split('/\s+/', trim((string) $customer['full_name']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                                $customerInitials = strtoupper(substr($customerNameParts[0] ?? '?', 0, 1) . substr($customerNameParts[count($customerNameParts) - 1] ?? '', 0, 1));
                            ?>
                            <article class="therapist-customer-card" data-therapist-customer-card>
                                <span class="therapist-customer-avatar"><?= e($customerInitials) ?></span>
                                <span class="therapist-customer-identity"><strong><?= e((string) $customer['full_name']) ?></strong><small><?= e((string) ($customer['phone'] ?? '')) ?> · <?= number_format((int) $customer['visits']) ?> visits</small></span>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="therapist-customer-empty" data-therapist-customer-empty hidden>No customers match your search.</div>
                </section>
                <?php elseif ($isReceptionist): ?>
                <div class="page-heading receptionist-customers-heading">
                    <div>
                        <h1 class="page-title">CUSTOMERS</h1>
                        <div class="page-date"><?= number_format($customerCount) ?> registered guests &amp; <?= number_format($checkedInToday) ?> Checked in Today.</div>
                    </div>
                    <a class="primary-button" href="<?= e(APP_URL) ?>/?route=register-customer">＋ Register Customer</a>
                </div>
                <div class="receptionist-customer-toolbar">
                    <label class="receptionist-customer-search"><span class="visually-hidden">Search customers by name or phone</span><input type="search" placeholder="Search customers by name or phone" data-customer-search></label>
                    <label class="receptionist-membership-filter"><span class="visually-hidden">Filter by membership</span><select data-membership-filter><option value="">Membership: All</option><?php foreach (array_unique(array_column($customers, 'membership')) as $membership): ?><option value="<?= e(strtolower((string) $membership)) ?>"><?= e((string) $membership) ?></option><?php endforeach; ?></select></label>
                </div>
                <section class="receptionist-customer-grid" aria-label="Customer directory">
                    <article class="receptionist-customer-empty" data-customer-no-results hidden>No customers match your search.</article>
                    <?php if ($customers === []): ?>
                        <article class="receptionist-customer-empty">No customers are registered yet.</article>
                    <?php else: ?>
                        <?php $customerIndex = 0; ?>
                        <?php foreach ($customers as $customer): ?>
                            <?php
                                $lastVisit = 'No visits';
                                if (!empty($customer['last_visit'])) {
                                    $visitTimestamp = strtotime((string) $customer['last_visit']);
                                    $lastVisit = date('Y-m-d', $visitTimestamp) === date('Y-m-d')
                                        ? 'Today'
                                        : (date('Y-m-d', $visitTimestamp) === date('Y-m-d', strtotime('yesterday'))
                                            ? 'Yesterday'
                                            : date('M j', $visitTimestamp));
                                }
                                $customerNameParts = preg_split('/\s+/', trim((string) $customer['full_name']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                                $customerInitials = strtoupper(substr($customerNameParts[0] ?? '?', 0, 1) . substr($customerNameParts[1] ?? '', 0, 1));
                                $membershipName = (string) $customer['membership'];
                                $membershipLabel = $membershipName === '' || strtolower($membershipName) === 'none'
                                    ? 'Regular'
                                    : $membershipName . ' Member';
                                $avatarTone = ($customerIndex++ % 5) + 1;
                            ?>
                            <article class="receptionist-customer-card" data-membership="<?= e(strtolower((string) $customer['membership'])) ?>">
                                <div class="receptionist-customer-card-header"><span class="receptionist-customer-avatar tone-<?= e((string) $avatarTone) ?>"><?= e($customerInitials) ?></span><span class="receptionist-customer-identity"><strong><?= e((string) $customer['full_name']) ?></strong><small><?= e((string) $customer['phone']) ?>&nbsp; <?= e($membershipLabel) ?></small></span></div>
                                <div class="receptionist-customer-metrics"><span><strong><?= number_format((int) $customer['visits']) ?></strong><small>Visits</small></span><span><strong>$<?= e(number_format((float) $customer['total_spent'], 2)) ?></strong><small>Spent</small></span><span><strong><?= e($lastVisit) ?></strong><small>Last visit</small></span></div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
                <?php else: ?>
                <div class="page-heading">
                    <div><h1 class="page-title">CUSTOMERS</h1><div class="page-date">Customer directory</div></div>
                    <button class="primary-button" type="button" data-open-modal="customer-modal">+ Add customer</button>
                </div>
                <section class="module-panel">
                    <div class="table-wrap">
                        <table class="data-table searchable-table">
                            <thead>
                                <tr><th>Customer</th><th>Phone</th><th>Visits</th><th>Total spent</th><th>Membership</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($customers === []): ?>
                                    <tr><td colspan="6">No customers available.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td><?= e($customer['full_name'] ?? '') ?></td>
                                            <td><?= e($customer['phone'] ?? '') ?></td>
                                            <td><?= e((string) ($customer['visits'] ?? 0)) ?></td>
                                            <td>$<?= e(number_format((float) ($customer['total_spent'] ?? 0), 2)) ?></td>
                                            <td><span class="status-pill status-confirmed"><?= e($customer['membership'] ?? 'None') ?></span></td>
                                            <td>
                                                <a class="secondary-button" href="<?= e(APP_URL) ?>/?route=customers&amp;edit=<?= e((string) ($customer['id'] ?? 0)) ?>">Edit</a>
                                                <form method="post" action="<?= e(APP_URL) ?>/?route=customers" style="display:inline" onsubmit="return confirm('Delete this customer?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= e((string) ($customer['id'] ?? 0)) ?>">
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
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php if ($isTherapist): ?>
    <div class="modal-backdrop hidden" id="therapist-customer-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="therapist-customer-title">
            <div class="modal-header">
                <div><p class="eyebrow">Customer</p><h2 id="therapist-customer-title">Add customer</h2></div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="therapist-customer-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=customers">
                <input type="hidden" name="action" value="create">
                <div class="form-grid two-col">
                    <label><span>Name</span><input type="text" name="customer_name" minlength="2" maxlength="150" required></label>
                    <label><span>Phone</span><input type="tel" name="customer_phone" pattern="[0-9+()\-\s]{7,30}" maxlength="30" required></label>
                    <label><span>Email</span><input type="email" name="customer_email" maxlength="150"></label>
                </div>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="therapist-customer-modal">Cancel</button>
                    <button class="primary-button" type="submit">Save customer</button>
                </div>
            </form>
        </div>
    </div>
    <?php elseif (!$isReceptionist): ?>
    <div class="modal-backdrop <?= $editingCustomer !== null ? '' : 'hidden' ?>" id="customer-modal" aria-hidden="<?= $editingCustomer !== null ? 'false' : 'true' ?>">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Customer</p>
                    <h2><?= $editingCustomer !== null ? 'Edit customer' : 'Add customer' ?></h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="customer-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=customers">
                <input type="hidden" name="action" value="<?= $editingCustomer !== null ? 'update' : 'create' ?>">
                <input type="hidden" name="id" value="<?= e((string) ($editingCustomer['id'] ?? '')) ?>">
                <div class="form-grid two-col">
                    <label>
                        <span>Name</span>
                        <input type="text" name="customer_name" value="<?= e($editingCustomer['full_name'] ?? '') ?>" minlength="2" maxlength="150" required>
                    </label>
                    <label>
                        <span>Phone</span>
                        <input type="tel" name="customer_phone" value="<?= e($editingCustomer['phone'] ?? '') ?>" pattern="[0-9+()\-\s]{7,30}" maxlength="30" required>
                    </label>
                    <label>
                        <span>Email</span>
                        <input type="email" name="customer_email" value="<?= e($editingCustomer['email'] ?? '') ?>" maxlength="150">
                    </label>
                    <label>
                        <span>Membership</span>
                        <select name="customer_membership">
                            <option value="">Select</option>
                            <option>Silver</option>
                            <option>Gold</option>
                            <option>Platinum</option>
                        </select>
                    </label>
                </div>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="customer-modal">Cancel</button>
                    <button class="primary-button" type="submit"><?= $editingCustomer !== null ? 'Update customer' : 'Save customer' ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
