<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../config/database.php';

final class ProfileController
{
    // Show and update the signed-in user's profile and password.
    public function index(): void
    {
        require_roles(['admin', 'manager', 'staff', 'therapist', 'receptionist', 'cashier']);

        if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: GET, POST');
            exit('Method not allowed.');
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        $db = Database::connection();
        $userModel = new User($db);
        $profileUser = $userModel->find($userId);
        if ($profileUser === null) {
            http_response_code(404);
            exit('Profile not found.');
        }

        $formError = '';
        $formSuccess = '';
        $passwordError = '';
        $passwordSuccess = (string) ($_SESSION['profile_password_success'] ?? '');
        unset($_SESSION['profile_password_success']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isPasswordChange = (string) ($_POST['action'] ?? '') === 'change_password';
            $token = (string) ($_POST['csrf_token'] ?? '');
            if (!hash_equals((string) $_SESSION['csrf_token'], $token)) {
                http_response_code(419);
                if ($isPasswordChange) {
                    $passwordError = 'Your security token expired. Reload the page and try again.';
                } else {
                    $formError = 'Your security token expired. Reload the page and try again.';
                }
            } elseif ($isPasswordChange) {
                $currentPassword = (string) ($_POST['current_password'] ?? '');
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

                if (!valid_login_password($currentPassword)) {
                    $passwordError = 'Enter your current password.';
                } elseif (!valid_password($newPassword)) {
                    $passwordError = 'New password must be at least 16 characters and include a letter, a number, and a symbol.';
                } elseif (!hash_equals($newPassword, $confirmPassword)) {
                    $passwordError = 'The new password and confirmation do not match.';
                } else {
                    try {
                        $db->beginTransaction();
                        if (!$userModel->changeOwnPassword($userId, $currentPassword, $newPassword)) {
                            $db->rollBack();
                            $passwordError = 'The current password is incorrect.';
                        } else {
                            write_audit_log($db, 'profile.password_changed', 'users', $userId);
                            $db->commit();
                            $_SESSION['profile_password_success'] = 'Your password has been changed.';
                            redirect('profile');
                        }
                    } catch (Throwable $exception) {
                        if ($db->inTransaction()) {
                            $db->rollBack();
                        }
                        throw $exception;
                    }
                }
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
                    [$newProfileImage, $uploadError] = store_profile_image($_FILES['profile_image'] ?? null);
                    if ($uploadError !== '') {
                        $formError = $uploadError;
                    } else {
                        $oldProfileImage = '';
                        try {
                            $db->beginTransaction();
                            if (!$userModel->updateOwnProfile($userId, $name, $email, $phone)) {
                                $db->rollBack();
                                $formError = 'That email address is already in use.';
                            } else {
                                if ($newProfileImage !== '') {
                                    $oldProfileImage = $userModel->updateProfileImage($userId, $newProfileImage);
                                }
                                write_audit_log($db, 'profile.updated', 'users', $userId);
                                $profileUser = $userModel->find($userId) ?? $profileUser;
                                $db->commit();

                                $_SESSION['user']['full_name'] = $name;
                                $_SESSION['user']['email'] = $email;
                                $_SESSION['user']['phone'] = $phone;
                                if ($newProfileImage !== '') {
                                    $_SESSION['user']['profile_image'] = $newProfileImage;
                                    remove_profile_image($oldProfileImage);
                                }
                                $formSuccess = 'Your profile has been updated.';
                            }
                        } catch (PDOException $exception) {
                            if ($db->inTransaction()) {
                                $db->rollBack();
                            }
                            remove_profile_image($newProfileImage);
                            $newProfileImage = '';
                            if ((string) $exception->getCode() !== '23000') {
                                throw $exception;
                            }
                            $formError = 'That email address is already in use.';
                        } catch (Throwable $exception) {
                            if ($db->inTransaction()) {
                                $db->rollBack();
                            }
                            remove_profile_image($newProfileImage);
                            $newProfileImage = '';
                            throw $exception;
                        }

                        if ($formError !== '' && $newProfileImage !== '') {
                            remove_profile_image($newProfileImage);
                        }
                        if ($formError !== '') {
                            $profileUser = $userModel->find($userId) ?? $profileUser;
                        }
                    }
                }
            }

            if ($formError !== '' && !$isPasswordChange) {
                $profileUser['full_name'] = trim((string) ($_POST['full_name'] ?? $profileUser['full_name']));
                $profileUser['email'] = trim((string) ($_POST['email'] ?? $profileUser['email']));
                $profileUser['phone'] = trim((string) ($_POST['phone'] ?? $profileUser['phone']));
            }
        }

        require __DIR__ . '/../Views/profile/index.php';
    }

    // Use the profile page for cashier account settings.
    public function cashier(): void
    {
        $this->index();
    }

    // Let therapists update their password and show the password form.
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

    // Use the profile page for therapist account settings.
    public function therapist(): void
    {
        $this->index();
    }
}
