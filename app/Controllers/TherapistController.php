<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Therapist.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../config/database.php';

final class TherapistController
{
    // Manage therapist records and show the therapist list.
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $db = Database::connection();
        $therapistModel = new Therapist($db);
        $userModel = new User($db);

        $editingTherapist = null;
        $therapistFormError = (string) ($_SESSION['therapist_form_error'] ?? '');
        unset($_SESSION['therapist_form_error']);
        $editId = (int) ($_GET['edit'] ?? 0);
        if ($editId > 0) {
            $editingTherapist = $therapistModel->find($editId);
        }
        $therapistAccounts = $therapistModel->accountsForAssignment($editId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $action = (string) ($_POST['action'] ?? '');

            if ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    $therapistModel->delete($id);
                }
                redirect('therapists');
            }

            if ($action !== 'create' && $action !== 'update') {
                http_response_code(400);
                exit('Invalid therapist action.');
            }

            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['therapist_name'] ?? ''));
            $email = trim((string) ($_POST['therapist_email'] ?? ''));
            $phone = trim((string) ($_POST['therapist_phone'] ?? ''));
            $accountInput = trim((string) ($_POST['therapist_user_id'] ?? ''));
            $createLogin = $accountInput === 'new';
            $userId = null;

            if ($accountInput !== '' && !$createLogin) {
                $validatedUserId = filter_var($accountInput, FILTER_VALIDATE_INT);
                if ($validatedUserId === false || !$therapistModel->accountCanBeAssigned((int) $validatedUserId, $id)) {
                    $this->redirectWithError('Choose an active therapist login account that is not already assigned.', $id);
                }
                $userId = (int) $validatedUserId;
            }

            $specialization = trim((string) ($_POST['therapist_specialization'] ?? ''));
            $bio = trim((string) ($_POST['therapist_bio'] ?? ''));
            $gender = trim((string) ($_POST['therapist_gender'] ?? ''));
            $status = trim((string) ($_POST['therapist_status'] ?? 'active'));
            $loginUsername = trim((string) ($_POST['therapist_username'] ?? ''));
            $loginPassword = (string) ($_POST['therapist_password'] ?? '');
            $validAction = ($action === 'create' && $id === 0) || ($action === 'update' && $id > 0);

            if (!$validAction
                || !valid_text($name, 2, 150)
                || !valid_email($email)
                || strlen($email) > 150
                || !valid_phone($phone, true)
                || !valid_text($specialization, 0, 150)
                || !valid_text($bio, 0, 2000)
                || !in_array($gender, ['', 'male', 'female', 'other'], true)
                || !in_array($status, ['active', 'inactive', 'on_leave'], true)) {
                $this->redirectWithError('Check the therapist details and try again.', $id);
            }
            if ($action === 'update' && $therapistModel->find($id) === null) {
                $this->redirectWithError('The selected therapist could not be found.', $id);
            }

            if ($createLogin && (!valid_username($loginUsername) || !valid_password($loginPassword))) {
                $this->redirectWithError(
                    'For a new therapist login, enter a valid username and a password of at least 16 characters with a letter, number, and symbol.',
                    $id
                );
            }

            [$newProfileImage, $uploadError] = store_profile_image($_FILES['profile_image'] ?? null);
            if ($uploadError !== '') {
                $this->redirectWithError($uploadError, $id);
            }
            if ($newProfileImage !== '' && $accountInput === '') {
                remove_profile_image($newProfileImage);
                $this->redirectWithError('Choose or create a login account before adding a profile photo.', $id);
            }

            $oldProfileImage = '';
            try {
                $db->beginTransaction();
                if ($createLogin) {
                    $userModel->create($name, $loginUsername, $email, $phone, 'staff', $loginPassword, $newProfileImage);
                    $userId = (int) $db->lastInsertId();
                    if ($userId < 1) {
                        throw new RuntimeException('Unable to create the therapist login account.');
                    }
                } elseif ($userId !== null) {
                    $userModel->updateEmail($userId, $email);
                    if ($newProfileImage !== '') {
                        $oldProfileImage = $userModel->updateProfileImage($userId, $newProfileImage);
                    }
                }

                if ($action === 'update') {
                    $therapistModel->update($id, $name, $email, $phone, $specialization, $gender, $status, $bio, $userId);
                } else {
                    $therapistModel->create($name, $email, $phone, $specialization, $gender, $status, $bio, $userId);
                }
                $therapistRecordId = $action === 'update' ? $id : (int) $db->lastInsertId();
                write_audit_log($db, 'therapist.' . $action, 'therapists', $therapistRecordId);
                $db->commit();
            } catch (PDOException $exception) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                if ($newProfileImage !== '') {
                    remove_profile_image($newProfileImage);
                }
                if ($exception->getCode() === '23000' && $email !== '') {
                    $message = $createLogin
                        ? 'That username or email is already in use. Choose another username or email.'
                        : 'That email address is already associated with another login account.';
                    $this->redirectWithError($message, $id);
                }
                if ($createLogin && $exception->getCode() === '23000') {
                    $this->redirectWithError('That username is already in use. Choose another username.', $id);
                }
                throw $exception;
            } catch (Throwable $exception) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                if ($newProfileImage !== '') {
                    remove_profile_image($newProfileImage);
                }
                throw $exception;
            }

            if ($oldProfileImage !== '') {
                remove_profile_image($oldProfileImage);
            }
            redirect('therapists');
        }

        $therapists = [];
        try {
            $therapists = $therapistModel->all();
        } catch (Throwable $e) {
            $therapists = [
                ['name' => 'Maya Lim', 'specialty' => 'Massage Therapy', 'status' => 'Available', 'status_class' => 'available', 'today' => 5, 'rating' => 4.9],
                ['name' => 'Sophie Tran', 'specialty' => 'Facial Care', 'status' => 'On Break', 'status_class' => 'break', 'today' => 3, 'rating' => 4.8],
                ['name' => 'Hana Park', 'specialty' => 'Body Treatment', 'status' => 'Available', 'status_class' => 'available', 'today' => 6, 'rating' => 4.7],
                ['name' => 'Daniel Cruz', 'specialty' => 'Thermal Therapy', 'status' => 'Off Duty', 'status_class' => 'off-duty', 'today' => 0, 'rating' => 4.6],
            ];
        }

        require __DIR__ . '/../Views/therapists/index.php';
    }

    // Check that the submitted token matches the session token.
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

    // Save a form error and return to the therapist form.
    private function redirectWithError(string $message, int $id): never
    {
        $_SESSION['therapist_form_error'] = $message;
        $query = $id > 0 ? '&edit=' . $id : '&new=1';
        header('Location: ' . APP_URL . '/?route=therapists' . $query);
        exit;
    }
}
