<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

$categoryNames = ['All'];
foreach ($categories as $category):
    $categoryNames[] = $category['name'];
endforeach;
$defaultServiceImage = APP_URL . '/public/uploads/login-bg.jpg';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Treatments | Relax Spa</title>
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
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Treatments</span></a>
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
                        <h1 class="page-title">TREATMENTS</h1>
                        <div class="page-date">Curated spa offerings</div>
                    </div>
                    <button class="primary-button" type="button" data-open-modal="service-modal">+ Add treatment</button>
                </div>
                <?php if ($formError !== ''): ?>
                    <div class="alert alert-error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>

                <section class="module-panel">
                    <div class="toolbar-row">
                        <div class="toolbar-pills categories">
                            <?php foreach ($categoryNames as $category): ?>
                                <button class="pill <?= $category === 'All' ? 'active' : '' ?>" type="button"><?= e($category) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="service-grid">
                        <?php foreach ($services as $service): ?>
                            <article class="service-card">
                                <div class="service-thumb thumb-<?= e($service['thumb']) ?>">
                                    <img class="service-image" src="<?= e($service['image'] ?? $defaultServiceImage) ?>" alt="<?= e($service['name']) ?>">
                                    <span class="service-badge service-<?= strtolower(str_replace(' ', '-', $service['status'])) ?>"><?= e($service['status']) ?></span>
                                </div>
                                <div class="service-body">
                                    <div class="service-label"><?= e($service['category']) ?></div>
                                    <h3><?= e($service['name']) ?></h3>
                                    <div class="service-meta">
                                        <span><?= e($service['duration']) ?></span>
                                        <span><?= e($service['price']) ?></span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-backdrop hidden" id="service-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="service-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Treatment</p>
                    <h2 id="service-title">Add treatment</h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="service-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=services" enctype="multipart/form-data">
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
                        <span>Treatment name</span>
                        <input type="text" name="service_name" minlength="2" maxlength="150" placeholder="Relaxing Massage" required>
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
                        <span>Treatment picture</span>
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
                    <button class="primary-button" type="submit">Save treatment</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
