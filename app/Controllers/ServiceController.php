<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Service.php';
require_once __DIR__ . '/../../config/database.php';

final class ServiceController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $serviceModel = new Service(Database::connection());

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $code = trim((string) ($_POST['service_code'] ?? ''));
            $name = trim((string) ($_POST['service_name'] ?? ''));
            $durationInput = trim((string) ($_POST['duration_minutes'] ?? ''));
            $priceInput = trim((string) ($_POST['price'] ?? ''));
            $duration = (int) $durationInput;
            $price = (float) $priceInput;
            $description = trim((string) ($_POST['description'] ?? ''));
            $imagePath = '';
            $upload = $_FILES['service_image'] ?? null;

            if ($upload !== null && $upload['error'] === UPLOAD_ERR_OK) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($upload['tmp_name']);
                $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                if ((int) $upload['size'] <= 5 * 1024 * 1024 && isset($extensions[$mime])) {
                    $directory = __DIR__ . '/../../public/uploads/service_images';
                    if (!is_dir($directory)) {
                        mkdir($directory, 0755, true);
                    }
                    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                    if (move_uploaded_file($upload['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
                        $imagePath = 'public/uploads/service_images/' . $filename;
                    }
                }
            }

            if ($categoryId > 0 && valid_text($code, 2, 30) && valid_text($name, 2, 150) && ctype_digit($durationInput) && $duration >= 5 && $duration <= 1440 && valid_non_negative_number($priceInput) && valid_text($description, 0, 2000)) {
                $serviceModel->create($categoryId, $code, $name, $duration, $price, $description, $imagePath);
            } else {
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

        require __DIR__ . '/../Views/services/index.php';
    }
}
