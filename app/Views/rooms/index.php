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
    <title>Rooms | Relax Spa</title>
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
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
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
                        <h1 class="page-title">ROOMS</h1>
                        <div class="page-date">Availability and service status</div>
                    </div>
                    <button class="primary-button" type="button" data-open-modal="room-modal">+ Add room</button>
                </div>

                <section class="module-panel">
                    <div class="room-grid">
                        <?php if ($rooms === []): ?>
                            <p>No rooms available.</p>
                        <?php endif; ?>
                        <?php foreach ($rooms as $room): ?>
                            <article class="room-card">
                                <div class="room-icon">◫</div>
                                <div class="room-line">
                                    <h3><?= e($room['number']) ?></h3>
                                    <span class="status-pill status-<?= e($room['status_class']) ?>"><?= e($room['status']) ?></span>
                                </div>
                                <p><?= e($room['type']) ?></p>
                                <small>Capacity: <?= e((string) $room['capacity']) ?></small>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-backdrop hidden" id="room-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="room-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Treatment room</p>
                    <h2 id="room-title">Add room</h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="room-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=rooms">
                <input type="hidden" name="action" value="create">
                <div class="form-grid two-col">
                    <label>
                        <span>Room code</span>
                        <input type="text" name="room_code" placeholder="ROOM-07" minlength="2" maxlength="30" required>
                    </label>
                    <label>
                        <span>Room name</span>
                        <input type="text" name="room_name" placeholder="Room 07" minlength="2" maxlength="100" required>
                    </label>
                    <label>
                        <span>Room type</span>
                        <input type="text" name="room_type" placeholder="Massage Suite">
                    </label>
                    <label>
                        <span>Capacity</span>
                        <input type="number" name="capacity" min="1" max="999" step="1" value="1" required>
                    </label>
                    <label>
                        <span>Status</span>
                        <select name="status">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="cleaning">Cleaning</option>
                            <option value="under_maintenance">Maintenance</option>
                        </select>
                    </label>
                    <label>
                        <span>Notes</span>
                        <input type="text" name="notes" placeholder="Optional notes">
                    </label>
                </div>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="room-modal">Cancel</button>
                    <button class="primary-button" type="submit">Save room</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
