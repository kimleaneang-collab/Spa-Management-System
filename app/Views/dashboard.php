<?php
declare(strict_types=1);
$user = $_SESSION['user'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css">
</head>
<body class="dashboard-page">
    <header class="topbar">
        <div class="brand-inline">Relax <span>SPA</span></div>
        <div class="user-area">
            <span><?= e($user['full_name']) ?></span>
            <span class="role-pill"><?= e(ucfirst($user['role'])) ?></span>
            <a href="<?= e(APP_URL) ?>/?route=logout">Log out</a>
        </div>
    </header>

    <main class="dashboard-content">
        <h1>Welcome, <?= e($user['full_name']) ?></h1>
        <p>You are logged in successfully as <strong><?= e($user['role']) ?></strong>.</p>
        <div class="coming-soon">
            <strong>Login module is ready.</strong>
            <span>The remaining 16 management modules can be implemented on top of the database schema included with this project.</span>
        </div>
    </main>
</body>
</html>
