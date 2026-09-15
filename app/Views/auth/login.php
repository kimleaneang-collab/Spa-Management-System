<?php
declare(strict_types=1);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Relax Spa Management System login">
    <title>Welcome | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-brand" aria-label="Relax Spa">
            <div class="brand-mark">
                <span class="brand-leaf">⌁</span>
                <div>
                    <div class="brand-name">Relax</div>
                    <div class="brand-subtitle">SPA</div>
                </div>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-card">
                <h1>Welcome</h1>
                <p class="login-description">Sign in to manage your spa</p>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-error" role="alert">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post"
                      action="<?= e(APP_URL) ?>/?route=login"
                      autocomplete="on"
                      novalidate>
                    <input type="hidden"
                           name="csrf_token"
                           value="<?= e($_SESSION['csrf_token']) ?>">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            inputmode="text"
                            maxlength="100"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrap">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                maxlength="255"
                                autocomplete="current-password"
                                required
                            >
                            <button
                                class="password-toggle"
                                type="button"
                                aria-label="Show password"
                                data-password-toggle
                            >Show</button>
                        </div>
                    </div>

                    <button class="login-button" type="submit">Log in</button>
                </form>

                <p class="secure-note">Secure staff access</p>
            </div>
        </section>
    </main>

    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
