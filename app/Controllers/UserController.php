<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../config/database.php';

final class UserController
{
    public function index(): void
    {
        require_roles(['admin', 'manager']);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $userModel = new User(Database::connection());
        $currentRole = strtolower((string) ($_SESSION['user']['role'] ?? ''));
        $isAdministrator = $currentRole === 'admin';
        $roles = $userModel->roles();
        if (!$isAdministrator) {
            $roles = array_values(array_filter($roles, static fn (string $role): bool => $role !== 'admin'));
        }
        $editingUser = null;
        $formValues = [];
        $formError = '';
        $showUserModal = false;
        $currentUserId = (int) ($_SESSION['user']['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $action = (string) ($_POST['action'] ?? '');

            if ($action === 'delete') {
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                $targetUser = $id !== false && $id !== null && $id > 0 ? $userModel->find((int) $id) : null;
                if ($targetUser === null) {
                    http_response_code(400);
                    $formError = 'The selected account could not be found.';
                } elseif (!$isAdministrator && strtolower((string) $targetUser['role_name']) === 'admin') {
                    $formError = 'Only an Administrator can delete an Administrator account.';
                } elseif ($id === $currentUserId && !$userModel->hasOtherActiveAdmin($currentUserId)) {
                    $formError = 'Invite another active Administrator before deleting your own account.';
                } else {
                    $userModel->delete($id);
                    if ($id === $currentUserId) {
                        $_SESSION['user']['id'] = null;
                    }
                    write_audit_log(Database::connection(), 'user.deleted', 'users', (int) $id, null, [
                        'role' => (string) $targetUser['role_name'],
                    ]);
                    remove_profile_image((string) ($targetUser['profile_image'] ?? ''));
                    if ($id === $currentUserId) {
                        $this->endSession();
                    }
                    redirect('users-and-roles');
                }
            } elseif ($action === 'create' || $action === 'update') {
                $formValues = $_POST;
                $showUserModal = true;
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                $name = trim((string) ($_POST['user_name'] ?? ''));
                $username = trim((string) ($_POST['username'] ?? ''));
                $email = trim((string) ($_POST['user_email'] ?? ''));
                $phone = trim((string) ($_POST['user_phone'] ?? ''));
                $role = strtolower(trim((string) ($_POST['role_name'] ?? '')));
                $password = (string) ($_POST['user_password'] ?? '');
                $status = strtolower(trim((string) ($_POST['user_status'] ?? 'active')));
                $validationErrors = [];
                if (!valid_text($name, 2, 150)) {
                    $validationErrors[] = 'Enter a name between 2 and 150 characters.';
                }
                if (!valid_username($username)) {
                    $validationErrors[] = 'Username must be 3–100 characters and may contain letters, numbers, @, dots, underscores, or hyphens.';
                }
                if (!valid_email($email, true)) {
                    $validationErrors[] = 'Enter a valid email address.';
                }
                if (!valid_phone($phone)) {
                    $validationErrors[] = 'Enter a valid phone number or leave the phone field blank.';
                }
                if (!in_array($role, $roles, true)) {
                    $validationErrors[] = 'Select a valid role.';
                }
                if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
                    $validationErrors[] = 'Select a valid account status.';
                }
                if (($action === 'create' && !valid_password($password))
                    || ($action === 'update' && !valid_password($password, false))) {
                    $validationErrors[] = 'Password must be at least 16 characters and include a letter, a number, and a symbol.';
                }
                $validAccount = $validationErrors === [];
                $targetUser = null;

                if ($action === 'update' && ($id === false || $id === null || $id < 1)) {
                    $validAccount = false;
                    $formError = 'The selected account could not be found.';
                } elseif ($action === 'update') {
                    $targetUser = $userModel->find((int) $id);
                    if ($targetUser === null) {
                        $validAccount = false;
                        $formError = 'The selected account could not be found.';
                    }
                }

                if ($action === 'update' && $id === $currentUserId) {
                    if ($targetUser !== null && ($role !== strtolower((string) $targetUser['role_name']) || $status !== $targetUser['status'])) {
                        $validAccount = false;
                        $formError = 'You cannot change your own role or account status.';
                    }
                }
                if (!$isAdministrator && $action === 'update' && $id !== false && $id !== null) {
                    if ($targetUser !== null && strtolower((string) $targetUser['role_name']) === 'admin') {
                        $validAccount = false;
                        $formError = 'Only an Administrator can edit an Administrator account.';
                    }
                }

                if (!$validAccount) {
                    if ($formError === '') {
                        $formError = implode(' ', $validationErrors);
                    }
                } else {
                    [$newProfileImage, $uploadError] = store_profile_image($_FILES['profile_image'] ?? null);
                    if ($uploadError !== '') {
                        $formError = $uploadError;
                    } else {
                        $oldProfileImage = (string) ($targetUser['profile_image'] ?? '');
                        try {
                            if ($action === 'update') {
                                $userModel->update((int) $id, $name, $username, $email, $phone, $role, $status, $password, $newProfileImage);
                                $recordId = (int) $id;
                            } else {
                                $userModel->create($name, $username, $email, $phone, $role, $password, $newProfileImage);
                                $recordId = (int) Database::connection()->lastInsertId();
                            }
                            write_audit_log(Database::connection(), 'user.' . $action, 'users', $recordId, null, [
                                'role' => $role,
                                'status' => $status,
                            ]);
                        } catch (Throwable $exception) {
                            if ($newProfileImage !== '') {
                                remove_profile_image($newProfileImage);
                            }
                            if ($exception instanceof PDOException && $exception->getCode() === '23000') {
                                $formError = 'That username or email is already in use.';
                            } else {
                                throw $exception;
                            }
                        }

                        if ($formError === '') {
                            if ($action === 'update' && $newProfileImage !== '') {
                                remove_profile_image($oldProfileImage);
                            }
                            if ($action === 'update' && (int) $id === $currentUserId && $newProfileImage !== '') {
                                $_SESSION['user']['profile_image'] = $newProfileImage;
                            }
                            redirect('users-and-roles');
                        }
                    }
                }

                if ($action === 'update' && $id !== false && $id !== null) {
                    $editingUser = $userModel->find((int) $id);
                }
            } else {
                http_response_code(400);
                $formError = 'Unsupported account action.';
            }
        }

        if ($editingUser === null && isset($_GET['edit'])) {
            $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
            if ($editId !== false && $editId !== null && $editId > 0) {
                $editingUser = $userModel->find($editId);
                if ($editingUser !== null) {
                    $showUserModal = true;
                }
            }
        }

        $users = $userModel->all();
        require __DIR__ . '/../Views/settings/users.php';
    }

    private function verifyCsrf(): void
    {
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (
            empty($_SESSION['csrf_token'])
            || !hash_equals((string) $_SESSION['csrf_token'], $token)
        ) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }
    }

    private function endSession(): void
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
}
