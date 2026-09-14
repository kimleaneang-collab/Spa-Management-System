<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Relax Spa - Login</title>

    <link rel="stylesheet" href="/Relax%20%26%20Spa/public/css/login.css">
</head>

<body>

    <div class="login-page">

        <!-- Background image -->
        <div class="background"></div>

        <!-- Login content -->
        <div class="login-container">

            <!-- Logo -->
            <div class="logo">
                <img src=""
                     alt="Relax Spa Logo">
            </div>

            <!-- Welcome -->
            <h1>Welcome</h1>

            <form action="/Relax%20%26%20Spa/public/index.php?action=login"
                  method="POST">

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                </div>

                <!-- Error -->
                <?php if (!empty($error)): ?>
                    <div class="error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Login button -->
                <button type="submit">
                    Log in
                </button>

            </form>

        </div>

    </div>

</body>
</html>