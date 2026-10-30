<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

$activeServices = array_values(array_filter(
    $services,
    static fn (array $service): bool => $service['status'] === 'Active'
));
$activeCategories = array_unique(array_column($activeServices, 'category'));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page service-page <?= strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'manager' ? 'manager-role' : '' ?>" data-role="<?= e(strtolower((string) ($_SESSION['user']['role'] ?? ''))) ?>">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>

            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>Calendar</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section" data-label="Quick Actions">
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Services</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS &amp; Billing</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                </div>
                <div class="nav-section" data-label="Reports">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
                </div>
                <div class="nav-section" data-label="Settings">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left">
                    <div class="search-box">
                        <span class="search-icon">⌕</span>
                        <span>Search</span>
                    </div>
                </div>
                <div class="topbar-actions">
                    <button class="icon-button service-topbar-icon" type="button" aria-label="Notifications">♧</button>
                    <a class="icon-button service-topbar-icon" href="<?= e(APP_URL) ?>/?route=appointments" aria-label="Create appointment">▣</a>
                    <div class="user-chip">
                        <?= profile_avatar($_SESSION['user'] ?? [], 'user-name', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'M'), 0, 2))) ?>
                        <span class="user-identity">
                            <strong><?= e((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'User')) ?></strong>
                            <small><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'admin'))) ?></small>
                        </span>
                        <span class="user-chevron">⌄</span>
                    </div>
                </div>
            </header>

            <div class="content-wrap service-content">
                <div class="service-page-heading">
                    <div>
                        <h1 class="page-title">SERVICES</h1>
                        <p class="service-summary"><strong><?= count($activeServices) ?></strong> active services across <strong><?= count($activeCategories) ?></strong> categories.</p>
                    </div>
                    <button class="primary-button service-add-button" type="button" data-open-modal="service-modal"><span>＋</span> Add Service</button>
                </div>
                <?php if ($formError !== ''): ?>
                    <div class="alert alert-error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>

                <div class="service-filters">
                    <label class="service-inline-search" for="service-search">
                        <span class="search-icon">⌕</span>
                        <input id="service-search" type="search" placeholder="Search services by name or category" autocomplete="off">
                    </label>
                    <label class="service-category-select" for="service-category">
                        <span>☷</span>
                        <select id="service-category">
                            <option value="all">Category : All</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e(strtolower((string) $category['name'])) ?>"><?= e((string) $category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <section class="service-grid" id="service-grid" aria-label="Spa services">
                    <?php foreach ($services as $service): ?>
                        <?php $categoryKey = strtolower(trim((string) $service['category'])); ?>
                        <article class="service-card <?= $service['status'] !== 'Active' ? 'is-inactive' : '' ?>" data-category="<?= e($categoryKey) ?>" data-search="<?= e(strtolower((string) $service['name'] . ' ' . (string) $service['category'] . ' ' . (string) $service['description'])) ?>">
                            <div class="service-body">
                                <div class="service-label"><?= e(strtoupper((string) $service['category'])) ?></div>
                                <h2><?= e((string) $service['name']) ?></h2>
                                <p class="service-description"><?= e((string) $service['description']) ?></p>
                                <div class="service-meta">
                                    <span><?= e((string) $service['duration']) ?></span>
                                    <strong><?= e((string) $service['price']) ?></strong>
                                </div>
                            </div>
                            <?php if ($service['status'] !== 'Active'): ?>
                                <span class="service-inactive-label">Inactive</span>
                            <?php endif; ?>
                        </article>
                        <?php endforeach; ?>
                </section>
                <p class="service-no-results" id="service-no-results" hidden>No services match your search.</p>
            </div>
        </main>
    </div>

    <div class="modal-backdrop hidden" id="service-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="service-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Service</p>
                    <h2 id="service-title">Add service</h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="service-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=services" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                <div class="form-grid two-col">
                    <label>
                        <span>Category</span>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e((string) $category['id']) ?>"><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Service code</span>
                        <input type="text" name="service_code" minlength="2" maxlength="30" placeholder="SVC011" required>
                    </label>
                    <label>
                        <span>Service name</span>
                        <input type="text" name="service_name" minlength="2" maxlength="150" placeholder="Swedish Massage" required>
                    </label>
                    <label>
                        <span>Duration (minutes)</span>
                        <input type="number" name="duration_minutes" min="5" max="1440" step="1" required>
                    </label>
                    <label>
                        <span>Price</span>
                        <input type="number" name="price" min="0" step="0.01" required>
                    </label>
                    <label>
                        <span>Service picture (optional)</span>
                        <input type="file" name="service_image" accept="image/jpeg,image/png,image/gif,image/webp">
                    </label>
                    <label>
                        <span>Description</span>
                        <textarea name="description" maxlength="2000" rows="3"></textarea>
                    </label>
                </div>
                <p class="page-date">Images must be JPG, PNG, GIF, or WEBP and no larger than 5 MB.</p>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="service-modal">Cancel</button>
                    <button class="primary-button" type="submit">Save service</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
