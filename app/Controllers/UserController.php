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

        $editingUser = null;
        $editId = (int) ($_GET['edit'] ?? 0);
        if ($editId > 0) {
            $editingUser = $userModel->find($editId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'])) {
            $action = (string) ($_POST['action'] ?? 'create');
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['user_name'] ?? ''));
            $username = trim((string) ($_POST['username'] ?? ''));

            if ($name !== '' && $username !== '') {
                $email = trim((string) ($_POST['user_email'] ?? ''));
                $phone = trim((string) ($_POST['user_phone'] ?? ''));
                $role = trim((string) ($_POST['role_name'] ?? ''));
                    $password = (string) ($_POST['user_password'] ?? '');
                    $status = trim((string) ($_POST['user_status'] ?? 'active'));
                    $validAccount = valid_text($name, 2, 150)
                        && preg_match('/^[A-Za-z0-9_.-]{3,100}$/', $username) === 1
                        && valid_email($email, true)
                        && valid_phone($phone)
                        && valid_text($role, 1, 50)
                        && in_array($status, ['active', 'inactive', 'suspended'], true)
                        && (($action === 'create' && valid_password($password)) || ($action === 'update' && valid_password($password, false)));

                    if ($validAccount && $action === 'update' && $id > 0) {
                        $userModel->update($id, $name, $username, $email, $phone, $role, $status, $password);
                    } elseif ($validAccount && $action === 'create') {
                        $userModel->create($name, $username, $email, $phone, $role, $password);
                }
            }

            redirect('users-and-roles');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && $id !== (int) ($_SESSION['user']['id'] ?? 0)) {
                $userModel->delete($id);
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
