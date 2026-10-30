<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Service.php';
require_once __DIR__ . '/../../config/database.php';

final class ServiceController
{
    // Create services when allowed and show the service list.
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $db = Database::connection();
        $serviceModel = new Service($db);

        $role = strtolower((string) ($_SESSION['user']['role'] ?? ''));
        $isTherapist = in_array($role, ['staff', 'therapist'], true);
        $isReadOnly = $isTherapist || in_array($role, ['cashier', 'receptionist'], true);

        if ($role === 'cashier' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            http_response_code(403);
            echo 'Cashiers cannot create or modify services.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
            require_roles(['admin', 'manager']);

            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $code = trim((string) ($_POST['service_code'] ?? ''));
            $name = trim((string) ($_POST['service_name'] ?? ''));
            $durationInput = trim((string) ($_POST['duration_minutes'] ?? ''));
            $priceInput = trim((string) ($_POST['price'] ?? ''));
            $duration = (int) $durationInput;
            $price = (float) $priceInput;
            $description = trim((string) ($_POST['description'] ?? ''));
            [$imagePath, $uploadError] = store_image_upload($_FILES['service_image'] ?? null, 'service_images');
            if ($uploadError !== '') {
                $_SESSION['form_error'] = $uploadError;
                redirect('services');
            }

            if ($categoryId > 0 && valid_text($code, 2, 30) && valid_text($name, 2, 150) && ctype_digit($durationInput) && $duration >= 5 && $duration <= 1440 && valid_non_negative_number($priceInput) && valid_text($description, 0, 2000)) {
                $serviceModel->create($categoryId, $code, $name, $duration, $price, $description, $imagePath);
                write_audit_log($db, 'service.created', 'services', (int) $db->lastInsertId(), null, ['service_code' => $code]);
            } else {
                if ($imagePath !== '') {
                    remove_uploaded_image($imagePath);
                }
                $_SESSION['form_error'] = 'Please enter a valid category, code, name, duration, and price.';
            }

            redirect('services');
        }

        $services = [];
        $categories = [];
        $formError = (string) ($_SESSION['form_error'] ?? '');
        unset($_SESSION['form_error']);
        try {
            $services = $serviceModel->all();
            $categories = $serviceModel->categories();
        } catch (Throwable $e) {
        }

        $view = $role === 'cashier'
            ? 'cashier.php'
            : ($role === 'manager' ? 'index.php' : ($isReadOnly ? 'therapist.php' : 'admin.php'));
        require __DIR__ . '/../Views/services/' . $view;
    }
}
