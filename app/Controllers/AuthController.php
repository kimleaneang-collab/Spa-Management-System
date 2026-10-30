<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/User.php';

final class AuthController
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    private User $users;

    // Set up the user model used for sign-in.
    public function __construct()
    {
        $this->users = new User(
            Database::connection()
        );
    }

    // Check sign-in requests and start a session for a valid user.
    public function login(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Display Login Page
        |--------------------------------------------------------------------------
        */
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            require __DIR__ . '/../Views/auth/login.php';

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Only POST is allowed for login submission
        |--------------------------------------------------------------------------
        */
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            http_response_code(405);

            exit('Method Not Allowed');
        }

        /*
        |--------------------------------------------------------------------------
        | CSRF Protection
        |--------------------------------------------------------------------------
        |
        | SECURITY: Validate a session-bound CSRF token before accepting login/logout.
        | This helps prevent cross-site request forgery attacks.
        |--------------------------------------------------------------------------
        */
        // SECURITY: Verify the request token matches the current session token.
        $this->verifyCsrf();

        $username = trim(
            (string) ($_POST['username'] ?? '')
        );

        $password = (string) (
            $_POST['password'] ?? ''
        );

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $validUsername = valid_username($username);

        if (!$validUsername || !valid_login_password($password)) {

            $_SESSION['login_error'] =
                !$validUsername
                    ? 'Enter a valid username using at least 3 characters.'
                    : 'Enter your password.';
            $_SESSION['login_username'] = $username;

            redirect('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Brute Force Protection
        |--------------------------------------------------------------------------
        |
        | SECURITY: Block repeated login attempts from the same username to reduce
        | password guessing and brute-force attacks.
        |--------------------------------------------------------------------------
        */
        // SECURITY: Count failed logins in the last 15 minutes and enforce a lockout.
        $failedAttempts = $this->users->failedAttemptsSince(
            $username,
            self::LOCKOUT_MINUTES
        );

        if ($failedAttempts >= self::MAX_FAILED_ATTEMPTS) {

            $_SESSION['login_error'] =
                'Too many failed attempts. Please try again in 15 minutes.';

            redirect('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */
        $user = $this->users->findByUsername($username);

        /*
        |--------------------------------------------------------------------------
        | Verify Password
        |--------------------------------------------------------------------------
        |
        | SECURITY: Validate the submitted password against the stored hash.
        | Passwords are never compared or stored in plain text.
        |--------------------------------------------------------------------------
        */
        // SECURITY: Use password_verify() instead of comparing raw password strings.
        $valid =
            $user !== null
            && $user['status'] === 'active'
            && password_verify(
                $password,
                $user['password_hash']
            );

        /*
        |--------------------------------------------------------------------------
        | Invalid Login
        |--------------------------------------------------------------------------
        */
        if (!$valid) {

            $this->users->logAttempt(
                $username,
                $user !== null
                    ? (int) $user['id']
                    : null,
                $ip,
                false
            );

            $_SESSION['login_username'] = $username;
            $_SESSION['login_error'] = 'Invalid username or password.';

            redirect('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Rehash Password If Needed
        |--------------------------------------------------------------------------
        |
        | SECURITY: Upgrade stored hashes to the current default algorithm when needed.
        | This keeps password protection strong as algorithms evolve.
        |--------------------------------------------------------------------------
        */
        // SECURITY: Rehash legacy password values to keep them aligned with modern standards.
        if (
            password_needs_rehash(
                $user['password_hash'],
                PASSWORD_DEFAULT
            )
        ) {

            $newHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = Database::connection()->prepare(
                '
                UPDATE users
                SET password_hash = :password_hash
                WHERE id = :id
                '
            );

            $stmt->execute([
                ':password_hash' => $newHash,
                ':id' => $user['id']
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Session Fixation
        |--------------------------------------------------------------------------
        |
        | SECURITY: Regenerate the session ID after successful authentication to prevent
        | session fixation attacks where an attacker reuses a known session ID.
        |--------------------------------------------------------------------------
        */
        // SECURITY: Rotate the session ID once the user is authenticated.
        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['authenticated_at'] = time();
        $_SESSION['last_activity'] = time();

        /*
        |--------------------------------------------------------------------------
        | Store Logged-In User
        |--------------------------------------------------------------------------
        |
        | SECURITY: Keep only the minimum user identity in the session after login.
        | Do not store the raw password or any sensitive secret in session state.
        |--------------------------------------------------------------------------
        */
        // SECURITY: Store only authorized session data needed for access checks.
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'profile_image' => $user['profile_image'] ?? null,
            'role_id' => (int) $user['role_id'],
            'role' => $user['role_name']
        ];

        /*
        |--------------------------------------------------------------------------
        | Update Last Login
        |--------------------------------------------------------------------------
        */
        $this->users->updateLastLogin(
            (int) $user['id']
        );

        /*
        |--------------------------------------------------------------------------
        | Log Successful Login
        |--------------------------------------------------------------------------
        */
        $this->users->logAttempt(
            $username,
            (int) $user['id'],
            $ip,
            true
        );

        /*
        |--------------------------------------------------------------------------
        | Redirect To Dashboard
        |--------------------------------------------------------------------------
        */
        redirect('dashboard');
    }

    // End the signed-in user's session after checking the request token.
    public function logout(): void
    {
        // SECURITY: Only allow logout through a POST request to reduce CSRF abuse.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        // SECURITY: Validate the logout request with CSRF before destroying the session.
        $this->verifyCsrf();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();

        redirect('login');
    }

    // Check that the submitted token matches the session token.
    private function verifyCsrf(): void
    {
        $token = (string) (
            $_POST['csrf_token'] ?? ''
        );

        if (
            empty($_SESSION['csrf_token'])
            || !hash_equals(
                $_SESSION['csrf_token'],
                $token
            )
        ) {

            http_response_code(419);

            exit('Invalid CSRF token.');
        }
    }
}