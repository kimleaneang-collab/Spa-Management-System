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
                    $this->removeProfileImage((string) ($targetUser['profile_image'] ?? ''));
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
                    [$newProfileImage, $uploadError] = $this->storeProfileImage($_FILES['profile_image'] ?? null);
                    if ($uploadError !== '') {
                        $formError = $uploadError;
                    } else {
                        $oldProfileImage = (string) ($targetUser['profile_image'] ?? '');
                        try {
                            if ($action === 'update') {
                                $userModel->update((int) $id, $name, $username, $email, $phone, $role, $status, $password, $newProfileImage);
                            } else {
                                $userModel->create($name, $username, $email, $phone, $role, $password, $newProfileImage);
                            }
                        } catch (Throwable $exception) {
                            if ($newProfileImage !== '') {
                                $this->removeProfileImage($newProfileImage);
                            }
                            if ($exception instanceof PDOException && $exception->getCode() === '23000') {
                                $formError = 'That username or email is already in use.';
                            } else {
                                throw $exception;
                            }
                        }

                        if ($formError === '') {
                            if ($action === 'update' && $newProfileImage !== '') {
                                $this->removeProfileImage($oldProfileImage);
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

    private function storeProfileImage(?array $upload): array
    {
        if ($upload === null) {
            return ['', ''];
        }

        $uploadError = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
        if (!is_scalar($uploadError)) {
            return ['', 'The selected profile photo is invalid. Please try again.'];
        }
        if ((int) $uploadError === UPLOAD_ERR_NO_FILE) {
            return ['', ''];
        }
        if ((int) $uploadError !== UPLOAD_ERR_OK) {
            return ['', 'The profile photo could not be uploaded. Please select it again.'];
        }

        $uploadSize = $upload['size'] ?? null;
        if (!is_scalar($uploadSize) || !is_numeric($uploadSize)) {
            return ['', 'The selected profile photo is invalid. Please try again.'];
        }
        if ((int) $uploadSize > 5 * 1024 * 1024) {
            return ['', 'The profile photo must be no larger than 5 MB.'];
        }

        $temporaryPath = $upload['tmp_name'] ?? '';
        if (!is_string($temporaryPath)) {
            return ['', 'The selected profile photo could not be verified. Please try again.'];
        }
        if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            return ['', 'The selected profile photo could not be verified. Please try again.'];
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        if (!is_string($mimeType) || !isset($extensions[$mimeType])) {
            return ['', 'Choose a JPG, PNG, GIF, or WEBP profile photo.'];
        }
        if (getimagesize($temporaryPath) === false) {
            return ['', 'The selected profile photo is not a valid image.'];
        }

        $directory = __DIR__ . '/../../public/uploads/user_profiles';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create the profile photo upload directory.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
        $destination = $directory . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($temporaryPath, $destination)) {
            return ['', 'The profile photo could not be saved. Please try again.'];
        }

        return ['public/uploads/user_profiles/' . $filename, ''];
    }

    private function removeProfileImage(string $imagePath): void
    {
        $prefix = 'public/uploads/user_profiles/';
        if (!str_starts_with($imagePath, $prefix)) {
            return;
        }

        $filename = substr($imagePath, strlen($prefix));
        if ($filename === '' || basename($filename) !== $filename) {
            return;
        }

        $absolutePath = __DIR__ . '/../../public/uploads/user_profiles/' . $filename;
        if (is_file($absolutePath) && !unlink($absolutePath)) {
            error_log('Unable to remove replaced user profile image: ' . $absolutePath);
        }
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
