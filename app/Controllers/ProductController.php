<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../../config/database.php';

final class ProductController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $productModel = new Product(Database::connection());

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
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
            $upload = $_FILES['product_image'] ?? null;

            if ($upload !== null && $upload['error'] === UPLOAD_ERR_OK) {
                if ((int) $upload['size'] > 5 * 1024 * 1024) {
                    redirect('products');
                }

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($upload['tmp_name']);
                $extensions = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                ];

                if (!isset($extensions[$mime])) {
                    redirect('products');
                }

                $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                $uploadDirectory = __DIR__ . '/../../public/uploads';
                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0755, true);
                }

                if (!move_uploaded_file($upload['tmp_name'], $uploadDirectory . DIRECTORY_SEPARATOR . $filename)) {
                    redirect('products');
                }
                $imagePath = 'public/uploads/' . $filename;
            }

            if (valid_text($code, 2, 40) && valid_text($name, 2, 150) && valid_text($category, 0, 100) && valid_text($unit, 1, 30) && valid_non_negative_number($costPriceInput) && valid_non_negative_number($sellingPriceInput) && valid_non_negative_number($stockQuantityInput) && valid_non_negative_number($reorderLevelInput) && valid_date_value($expiryDate)) {
                $productModel->create($code, $name, $category, $unit, $costPrice, $sellingPrice, $stockQuantity, $reorderLevel, $expiryDate, $imagePath);
            }

            redirect('products');
        }

        $products = [];
        try {
            $products = $productModel->all();
        } catch (Throwable $e) {
        }

        require __DIR__ . '/../Views/inventory/products.php';
    }
}
