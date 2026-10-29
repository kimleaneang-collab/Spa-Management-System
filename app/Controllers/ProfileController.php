<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Therapist.php';
require_once __DIR__ . '/../../config/database.php';

final class ProfileController
{
    public function cashier(): void
    {
        require_roles(['cashier']);

        if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: GET, POST');
            echo 'Method not allowed.';
            return;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        $userModel = new User(Database::connection());
        $profileUser = $userModel->find($userId);
        if ($profileUser === null) {
            http_response_code(404);
            echo 'Profile not found.';
            return;
        }

        $formError = '';
        $formSuccess = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string) ($_POST['csrf_token'] ?? '');
            if (!hash_equals((string) $_SESSION['csrf_token'], $token)) {
                http_response_code(419);
                $formError = 'Your security token expired. Reload the page and try again.';
            } else {
                $name = trim((string) ($_POST['full_name'] ?? ''));
                $email = trim((string) ($_POST['email'] ?? ''));
                $phone = trim((string) ($_POST['phone'] ?? ''));

                if (!valid_text($name, 2, 150)) {
                    $formError = 'Enter a name between 2 and 150 characters.';
                } elseif (!valid_email($email)) {
                    $formError = 'Enter a valid email address.';
                } elseif (!valid_phone($phone)) {
                    $formError = 'Enter a valid phone number.';
                } else {
                    try {
                        if ($userModel->updateOwnProfile($userId, $name, $email, $phone)) {
                            $_SESSION['user']['full_name'] = $name;
                            $_SESSION['user']['email'] = $email;
                            $_SESSION['user']['phone'] = $phone;
                            $profileUser = $userModel->find($userId);
                            $formSuccess = 'Your profile has been updated.';
                        } else {
                            $formError = 'That email address is already in use.';
                        }
                    } catch (PDOException $exception) {
                        if ((string) $exception->getCode() !== '23000') {
                            throw $exception;
                        }
                        $formError = 'That email address is already in use.';
                    }
                }
            }

            if ($formError !== '') {
                $profileUser['full_name'] = trim((string) ($_POST['full_name'] ?? $profileUser['full_name']));
                $profileUser['email'] = trim((string) ($_POST['email'] ?? $profileUser['email']));
                $profileUser['phone'] = trim((string) ($_POST['phone'] ?? $profileUser['phone']));
            }
        }

        require __DIR__ . '/../Views/profile/cashier.php';
    }

    public function changePassword(): void
    {
        require_roles(['staff', 'therapist']);

        $user = $_SESSION['user'];
        $userId = (int) ($user['id'] ?? 0);
        $userName = (string) ($user['full_name'] ?? $user['username'] ?? 'Therapist');
        $formError = '';
        $formSuccess = (string) ($_SESSION['password_success'] ?? '');
        unset($_SESSION['password_success']);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string) ($_POST['csrf_token'] ?? '');
            if (empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $token)) {
                http_response_code(419);
                $formError = 'Your security token expired. Reload the page and try again.';
            } else {
                $currentPassword = (string) ($_POST['current_password'] ?? '');
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

                if (!valid_login_password($currentPassword)) {
                    $formError = 'Enter your current password.';
                } elseif (!valid_password($newPassword)) {
                    $formError = 'New password must be at least 16 characters and include a letter, a number, and a symbol.';
                } elseif (!hash_equals($newPassword, $confirmPassword)) {
                    $formError = 'The new password and confirmation do not match.';
                } elseif ((new User(Database::connection()))->changeOwnPassword($userId, $currentPassword, $newPassword)) {
                    $_SESSION['password_success'] = 'Your password has been changed.';
                    redirect('change-password');
                } else {
                    $formError = 'The current password is incorrect.';
                }
            }
        } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            header('Allow: GET, POST');
            echo 'Method not allowed.';
            return;
        }

        require __DIR__ . '/../Views/profile/change-password.php';
    }

    public function therapist(): void
    {
        require_roles(['staff', 'therapist']);

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            echo 'Therapist profile details are managed by Admin.';
            return;
        }

        $user = $_SESSION['user'];
        $userId = (int) ($user['id'] ?? 0);
        $db = Database::connection();
        $userModel = new User($db);
        $therapistModel = new Therapist($db);
        $profileUser = $userModel->find($userId);

        if ($profileUser === null) {
            http_response_code(404);
            echo 'Profile not found.';
            return;
        }

        $therapistProfile = $therapistModel->profileForUser(
            $userId,
            (string) ($profileUser['full_name'] ?? $profileUser['username'] ?? '')
        );

        require __DIR__ . '/../Views/profile/therapist.php';
    }
}
