<?php
declare(strict_types=1);

require_once DIR . '/../Models/Customer.php';
require_once DIR . '/../../config/database.php';

final class CustomerController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $customerModel = new Customer(Database::connection());

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_name'])) {
            $name = trim((string) ($_POST['customer_name'] ?? ''));
            $phone = trim((string) ($_POST['customer_phone'] ?? ''));

            if ($name !== '' && $phone !== '') {
                $customerModel->create(
                    $name,
                    $phone,
                    trim((string) ($_POST['customer_email'] ?? ''))
                );
            }

            redirect('customers');
        }

        $customers = [];
        try {
            $customers = $customerModel->all();
        } catch (Throwable $e) {
            $customers = [];
        }

        require DIR . '/../Views/customers/index.php';
    }

    public function history(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require DIR . '/../Views/customers/history.php';
    }
}