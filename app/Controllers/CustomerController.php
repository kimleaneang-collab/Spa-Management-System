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

        $customerModel = new Customer(Database::connection());

        $editingCustomer = null;
        $editId = (int) ($_GET['edit'] ?? 0);
        if ($editId > 0) {
            $editingCustomer = $customerModel->find($editId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_name'])) {
            $action = (string) ($_POST['action'] ?? 'create');
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['customer_name'] ?? ''));
            $phone = trim((string) ($_POST['customer_phone'] ?? ''));

            if (valid_text($name, 2, 150) && valid_phone($phone, true)) {
                $email = trim((string) ($_POST['customer_email'] ?? ''));
                if (valid_email($email) && (($action === 'update' && $id > 0) || $action === 'create')) {
                    if ($action === 'update' && $id > 0) {
                    $customerModel->update($id, $name, $phone, $email);
                    } else {
                    $customerModel->create($name, $phone, $email);
                    }
                }
            }

            redirect('customers');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $customerModel->delete($id);
            }
            redirect('customers');
        }

        $customers = [];
        try {
            $customers = $customerModel->all();
        } catch (Throwable $e) {
            $customers = [];
        }

        require __DIR__ . '/../Views/customers/index.php';
    }

    public function history(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/customers/history.php';
    }
}