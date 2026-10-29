<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Customer.php';
require_once __DIR__ . '/../../config/database.php';

final class CustomerController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $user = $_SESSION['user'];
        $role = strtolower((string) ($user['role'] ?? ''));
        $isReceptionist = $role === 'receptionist';
        $isTherapist = in_array($role, ['staff', 'therapist'], true);
        $isCashier = $role === 'cashier';
        $customerModel = new Customer(Database::connection());
        $checkedInToday = $isReceptionist ? $customerModel->checkedInToday() : 0;
        $customerCount = $isTherapist ? 0 : $customerModel->count();
        $therapistId = null;

        if ($isCashier && empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $editingCustomer = null;
        $editId = $isTherapist ? 0 : (int) ($_GET['edit'] ?? 0);
        if ($editId > 0) {
            $editingCustomer = $customerModel->find($editId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_name'])) {
            $action = (string) ($_POST['action'] ?? 'create');
            if ($isCashier) {
                $token = (string) ($_POST['csrf_token'] ?? '');
                if (empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $token)) {
                    http_response_code(419);
                    echo 'Invalid CSRF token.';
                    return;
                }
            }
            if (($isTherapist || $isCashier) && $action !== 'create') {
                http_response_code(403);
                echo $isCashier
                    ? 'Cashiers cannot edit customer records.'
                    : 'Therapists cannot edit or delete customer records.';
                return;
            }
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['customer_name'] ?? ''));
            $phone = trim((string) ($_POST['customer_phone'] ?? ''));
            $email = trim((string) ($_POST['customer_email'] ?? ''));
            $gender = trim((string) ($_POST['customer_gender'] ?? ''));
            $dateOfBirth = trim((string) ($_POST['customer_date_of_birth'] ?? ''));
            $notes = trim((string) ($_POST['customer_notes'] ?? ''));
            $validGender = $gender === '' || in_array($gender, ['male', 'female', 'other', 'prefer_not_to_say'], true);

            if (valid_text($name, 2, 150)
                && valid_phone($phone, true)
                && valid_email($email)
                && $validGender
                && valid_date_value($dateOfBirth)
                && valid_text($notes, 0, 2000)
                && (($action === 'update' && $id > 0) || $action === 'create')) {
                if ($action === 'update') {
                    $customerModel->update(
                        $id,
                        $name,
                        $phone,
                        $email,
                        array_key_exists('customer_gender', $_POST) ? $gender : null,
                        array_key_exists('customer_date_of_birth', $_POST) ? $dateOfBirth : null,
                        array_key_exists('customer_notes', $_POST) ? $notes : null
                    );
                } else {
                    $customerModel->create($name, $phone, $email, $gender, $dateOfBirth, $notes);
                }
            }

            redirect('customers');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            if ($isTherapist || $isCashier) {
                http_response_code(403);
                echo $isCashier
                    ? 'Cashiers cannot delete customer records.'
                    : 'Therapists cannot edit or delete customer records.';
                return;
            }
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $customerModel->delete($id);
            }
            redirect('customers');
        }

        $customers = [];
        if ($isTherapist) {
            $therapistCustomers = $customerModel->forTherapist(
                (int) ($user['id'] ?? 0),
                (string) ($user['full_name'] ?? $user['username'] ?? '')
            );
            $therapistId = $therapistCustomers['therapist_id'];
            $customers = $therapistCustomers['customers'];
            $customerCount = count($customers);
        } elseif ($isCashier) {
            $customers = $customerModel->all(null);
            $customerCount = count($customers);
            $currencyQuery = Database::connection()->prepare(
                "SELECT setting_value FROM settings WHERE setting_key = 'currency' LIMIT 1"
            );
            $currencyQuery->execute();
            $currency = (string) ($currencyQuery->fetchColumn() ?: 'USD');
        } else {
            try {
                $customers = $customerModel->all($isReceptionist ? null : 100);
            } catch (Throwable $e) {
                $customers = [];
            }
        }

        if ($isReceptionist && (string) ($_GET['route'] ?? '') === 'register-customer') {
            require __DIR__ . '/../Views/customers/create.php';
            return;
        }

        if ($isCashier) {
            require __DIR__ . '/../Views/customers/cashier.php';
            return;
        }

        require __DIR__ . '/../Views/customers/index.php';
    }

    public function history(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $user = $_SESSION['user'];
        $role = strtolower((string) ($user['role'] ?? ''));
        $isTherapist = in_array($role, ['staff', 'therapist'], true);
        $therapistId = null;
        $treatmentHistory = [];

        if ($isTherapist) {
            $history = (new Customer(Database::connection()))->treatmentHistoryForTherapist(
                (int) ($user['id'] ?? 0),
                (string) ($user['full_name'] ?? $user['username'] ?? '')
            );
            $therapistId = $history['therapist_id'];
            $treatmentHistory = $history['history'];
        }

        require __DIR__ . '/../Views/customers/history.php';
    }
}