<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../../config/database.php';

final class ProductController
{
    // Handle product creation and show the product list.
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $db = Database::connection();
        $productModel = new Product($db);
        $role = strtolower((string) ($_SESSION['user']['role'] ?? ''));
        $formError = (string) ($_SESSION['form_error'] ?? '');
        unset($_SESSION['form_error']);

        if ($role === 'cashier' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            http_response_code(403);
            echo 'Cashiers cannot create or modify products.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
            require_roles(['admin', 'manager']);

            $code = trim((string) ($_POST['product_code'] ?? ''));
            $name = trim((string) ($_POST['product_name'] ?? ''));
            $category = trim((string) ($_POST['category'] ?? ''));
            $unit = trim((string) ($_POST['unit'] ?? 'pcs'));
            $costPriceInput = trim((string) ($_POST['cost_price'] ?? '0'));
            $sellingPriceInput = trim((string) ($_POST['selling_price'] ?? '0'));
            $stockQuantityInput = trim((string) ($_POST['stock_quantity'] ?? '0'));
            $reorderLevelInput = trim((string) ($_POST['reorder_level'] ?? '0'));
            $costPrice = (float) $costPriceInput;
            $sellingPrice = (float) $sellingPriceInput;
            $stockQuantity = (float) $stockQuantityInput;
            $reorderLevel = (float) $reorderLevelInput;
            $expiryDate = trim((string) ($_POST['expiry_date'] ?? ''));
            $imagePath = '';
            [$imagePath, $uploadError] = store_image_upload($_FILES['product_image'] ?? null);
            if ($uploadError !== '') {
                $_SESSION['form_error'] = $uploadError;
                redirect('products');
            }

            if (valid_text($code, 2, 40) && valid_text($name, 2, 150) && valid_text($category, 0, 100) && valid_text($unit, 1, 30) && valid_non_negative_number($costPriceInput) && valid_non_negative_number($sellingPriceInput) && valid_non_negative_number($stockQuantityInput) && valid_non_negative_number($reorderLevelInput) && valid_date_value($expiryDate)) {
                $productModel->create($code, $name, $category, $unit, $costPrice, $sellingPrice, $stockQuantity, $reorderLevel, $expiryDate, $imagePath);
                write_audit_log($db, 'product.created', 'products', (int) $db->lastInsertId(), null, ['product_code' => $code]);
            } elseif ($imagePath !== '') {
                remove_uploaded_image($imagePath);
                $_SESSION['form_error'] = 'Please enter valid product details.';
            } else {
                $_SESSION['form_error'] = 'Please enter valid product details.';
            }

            redirect('products');
        }

        $formError = (string) ($_SESSION['form_error'] ?? $formError);
        unset($_SESSION['form_error']);
        $products = $productModel->all();

        $view = match ($role) {
            'manager' => 'products.php',
            'cashier' => 'cashier.php',
            'staff', 'therapist' => 'therapist.php',
            default => 'admin.php',
        };
        require __DIR__ . '/../Views/inventory/' . $view;
    }
}
