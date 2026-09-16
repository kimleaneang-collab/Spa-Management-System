<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../config/database.php';

final class UserController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $userModel = new User(Database::connection());

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'])) {
            $name = trim((string) ($_POST['user_name'] ?? ''));
            $username = trim((string) ($_POST['username'] ?? ''));

            if ($name !== '' && $username !== '') {
                $userModel->create(
                    $name,
                    $username,
                    trim((string) ($_POST['user_email'] ?? '')),
                    trim((string) ($_POST['user_phone'] ?? '')),
                    trim((string) ($_POST['role_name'] ?? ''))
                );
            }

            redirect('users-and-roles');
        }

        $users = [];
        $roles = [];
        try {
            $users = $userModel->all();
            $roles = $userModel->roles();
        } catch (Throwable $e) {
            $roles = ['Admin', 'Manager', 'Staff'];
        }

        require __DIR__ . '/../Views/settings/users.php';
    }
}
