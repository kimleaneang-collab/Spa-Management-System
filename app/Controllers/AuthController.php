<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

final class AuthController
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    private User $users;

    public function __construct()
    {
        $this->users = new User(Database::connection());
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $this->verifyCsrf();

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if ($username === '' || $password === '') {
            $_SESSION['login_error'] = 'Please enter username and password.';
            redirect('login');
        }

        if ($this->users->failedAttemptsSince($username, self::LOCKOUT_MINUTES)
            >= self::MAX_FAILED_ATTEMPTS) {
            $_SESSION['login_error'] =
                'Too many failed attempts. Please try again in 15 minutes.';
            redirect('login');
        }

        $user = $this->users->findByUsername($username);

        $valid = $user
            && $user['status'] === 'active'
            && password_verify($password, $user['password_hash']);

        if (!$valid) {
            $this->users->logAttempt(
                $username,
                $user ? (int) $user['id'] : null,
                $ip,
                false
            );

            $_SESSION['login_error'] = 'Invalid username or password.';
            redirect('login');
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = Database::connection()->prepare(
                'UPDATE users SET password_hash = :password_hash WHERE id = :id'
            );
            $stmt->execute([
                'password_hash' => $newHash,
                'id' => $user['id'],
            ]);
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role_id' => (int) $user['role_id'],
            'role' => $user['role_name'],
        ];

        $this->users->updateLastLogin((int) $user['id']);
        $this->users->logAttempt(
            $username,
            (int) $user['id'],
            $ip,
            true
        );

        redirect('dashboard');
    }

    public function logout(): void
    {
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

    private function verifyCsrf(): void
    {
        $token = (string) ($_POST['csrf_token'] ?? '');

        if (
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }
    }
}
