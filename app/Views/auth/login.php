<?php
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Relax Spa - Login</title>

    <link rel="stylesheet"
          href="public/css/style.css">
</head>

<body class="login-page">

    <div class="login-overlay"></div>

    <div class="login-container">

        <!-- Logo -->
        <div class="spa-logo">
            <div class="logo-script">Relax</div>
            <div class="logo-spa">♨ spa</div>
        </div>

        <!-- Login Form -->
        <div class="login-box">

            <h1>Welcome</h1>

            <?php if ($error): ?>
                <div class="login-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="index.php?action=login"
                  method="POST">

                <!-- Username -->
                <div class="input-group">
                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        autocomplete="username"
                    >
                </div>

                <!-- Password -->
                <div class="input-group">
                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                    >
                </div>

                <!-- Login Button -->
                <button type="submit" class="login-button">
                    Log in
                </button>

            </form>

        </div>

    </div>

</body>
</html>