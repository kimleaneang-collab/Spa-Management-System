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
    <title>Products | Relax Spa</title>
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=stock-management"><span class="nav-icon">▤</span><span>Stock Management</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=suppliers"><span class="nav-icon">◧</span><span>Supplier</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">◍</span><span>Customer History</span></a>
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
                        <h1 class="page-title">PRODUCTS</h1>
                        <div class="page-date">Inventory catalog</div>
                    </div>
                    <button class="primary-button" type="button" data-open-modal="product-modal">+ Add product</button>
                </div>
                <section class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="product-card">
                            <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
                            <div class="product-card-body">
                                <h3><?= e($product['name']) ?></h3>
                                <p><?= e($product['price']) ?></p>
                                <span class="status-pill <?= e($product['status_class']) ?>"><?= e($product['status']) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-backdrop hidden" id="product-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="product-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Inventory</p>
                    <h2 id="product-title">Add product</h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="product-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=products" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="form-grid two-col">
                    <label>
                        <span>Product code</span>
                        <input type="text" name="product_code" placeholder="PROD-001" minlength="2" maxlength="40" required>
                    </label>
                    <label>
                        <span>Product name</span>
                        <input type="text" name="product_name" placeholder="Massage Oil 100ml" minlength="2" maxlength="150" required>
                    </label>
                    <label>
                        <span>Category</span>
                        <input type="text" name="category" placeholder="Spa supplies">
                    </label>
                    <label>
                        <span>Unit</span>
                        <input type="text" name="unit" value="pcs" required>
                    </label>
                    <label>
                        <span>Cost price</span>
                        <input type="number" name="cost_price" min="0" step="0.01" value="0">
                    </label>
                    <label>
                        <span>Selling price</span>
                        <input type="number" name="selling_price" min="0" step="0.01" value="0" required>
                    </label>
                    <label>
                        <span>Stock quantity</span>
                        <input type="number" name="stock_quantity" min="0" step="0.001" value="0" required>
                    </label>
                    <label>
                        <span>Reorder level</span>
                        <input type="number" name="reorder_level" min="0" step="0.001" value="0" required>
                    </label>
                    <label>
                        <span>Expiry date</span>
                        <input type="date" name="expiry_date">
                    </label>
                    <label>
                        <span>Product picture</span>
                        <input type="file" name="product_image" accept="image/jpeg,image/png,image/gif,image/webp">
                    </label>
                </div>
                <p class="page-date">Accepted: JPG, PNG, GIF, or WEBP. Maximum 5 MB.</p>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="product-modal">Cancel</button>
                    <button class="primary-button" type="submit">Save product</button>
                </div>
            </form>
        </div>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
