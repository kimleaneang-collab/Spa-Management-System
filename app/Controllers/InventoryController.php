<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class InventoryController
{
    // Show stock movements and handle inventory adjustments.
    public function stock(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $db = Database::connection();
        $formError = '';
        $formValues = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_roles(['admin', 'manager']);
            require_valid_csrf_token();
            $formValues = $_POST;
            $formError = $this->recordStockMovement($db);
            if ($formError === '') {
                $_SESSION['inventory_success'] = 'Stock movement recorded successfully.';
                redirect('stock-management');
            }
        } elseif (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $products = $db->query(
            "SELECT id, name, product_code, unit, stock_quantity, cost_price
             FROM products
             WHERE status = 'active'
             ORDER BY name"
        )->fetchAll(PDO::FETCH_ASSOC);
        $transactions = $db->query(
            'SELECT st.transaction_type, st.quantity, st.unit_cost, st.reason, st.created_at,
                    p.name AS product_name, p.unit, u.full_name AS created_by_name
             FROM stock_transactions st
             INNER JOIN products p ON p.id = st.product_id
             LEFT JOIN users u ON u.id = st.created_by
             ORDER BY st.created_at DESC, st.id DESC
             LIMIT 100'
        )->fetchAll(PDO::FETCH_ASSOC);
        $successMessage = (string) ($_SESSION['inventory_success'] ?? '');
        unset($_SESSION['inventory_success']);
        $canManageInventory = in_array(
            strtolower((string) ($_SESSION['user']['role'] ?? '')),
            ['admin', 'manager'],
            true
        );

        require __DIR__ . '/../Views/inventory/stock-in-out.php';
    }

    // Show supplier records and handle new supplier creation.
    public function suppliers(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $db = Database::connection();
        $formError = '';
        $formValues = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_roles(['admin', 'manager']);
            require_valid_csrf_token();
            $formValues = $_POST;
            $formError = $this->createSupplier($db);
            if ($formError === '') {
                $_SESSION['supplier_success'] = 'Supplier added successfully.';
                redirect('suppliers');
            }
        } elseif (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $suppliers = $db->query(
            'SELECT s.id, s.supplier_code, s.name, s.contact_person, s.phone, s.email, s.status,
                    GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR \', \') AS products
             FROM suppliers s
             LEFT JOIN supplier_products sp ON sp.supplier_id = s.id
             LEFT JOIN products p ON p.id = sp.product_id
             GROUP BY s.id, s.supplier_code, s.name, s.contact_person, s.phone, s.email, s.status
             ORDER BY s.name'
        )->fetchAll(PDO::FETCH_ASSOC);
        $successMessage = (string) ($_SESSION['supplier_success'] ?? '');
        unset($_SESSION['supplier_success']);
        $canManageInventory = in_array(
            strtolower((string) ($_SESSION['user']['role'] ?? '')),
            ['admin', 'manager'],
            true
        );

        require __DIR__ . '/../Views/inventory/suppliers.php';
    }

    private function recordStockMovement(PDO $db): string
    {
        $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
        $type = (string) ($_POST['transaction_type'] ?? '');
        $quantityInput = trim((string) ($_POST['quantity'] ?? ''));
        $unitCostInput = trim((string) ($_POST['unit_cost'] ?? ''));
        $reason = trim((string) ($_POST['reason'] ?? ''));

        if ($productId === false || $productId < 1) {
            return 'Select a product.';
        }
        if (!in_array($type, ['stock_in', 'stock_out'], true)) {
            return 'Select a valid stock movement type.';
        }
        if (
            !is_numeric($quantityInput)
            || !is_finite((float) $quantityInput)
            || (float) $quantityInput <= 0
            || (float) $quantityInput > 99999999999.999
            || abs((float) $quantityInput - round((float) $quantityInput, 3)) > 0.000001
        ) {
            return 'Enter a quantity greater than zero with no more than 3 decimal places.';
        }
        if (
            ($unitCostInput !== '' && !valid_non_negative_number($unitCostInput))
            || ($unitCostInput !== '' && (float) $unitCostInput > 9999999999.99)
        ) {
            return 'Enter a valid non-negative unit cost.';
        }
        if (!valid_text($reason, 0, 255)) {
            return 'The note must be 255 characters or fewer.';
        }

        try {
            $db->beginTransaction();
            $productStatement = $db->prepare(
                "SELECT name, stock_quantity FROM products
                 WHERE id = :id AND status = 'active'
                 FOR UPDATE"
            );
            $productStatement->execute([':id' => $productId]);
            $product = $productStatement->fetch(PDO::FETCH_ASSOC);
            if ($product === false) {
                $db->rollBack();
                return 'The selected product is no longer available.';
            }

            $quantity = (float) $quantityInput;
            $currentStock = (float) $product['stock_quantity'];
            if ($type === 'stock_out' && $quantity > $currentStock) {
                $db->rollBack();
                return 'There is not enough stock available for this stock-out.';
            }

            $newStock = $type === 'stock_in'
                ? $currentStock + $quantity
                : $currentStock - $quantity;
            if ($newStock > 99999999999.999) {
                $db->rollBack();
                return 'The resulting stock quantity is too large to save.';
            }
            $updateStatement = $db->prepare(
                'UPDATE products SET stock_quantity = :stock_quantity WHERE id = :id'
            );
            $updateStatement->execute([
                ':stock_quantity' => $newStock,
                ':id' => $productId,
            ]);

            $movementStatement = $db->prepare(
                'INSERT INTO stock_transactions
                    (product_id, transaction_type, quantity, unit_cost, reason, created_by)
                 VALUES
                    (:product_id, :transaction_type, :quantity, :unit_cost, :reason, :created_by)'
            );
            $movementStatement->execute([
                ':product_id' => $productId,
                ':transaction_type' => $type,
                ':quantity' => $quantity,
                ':unit_cost' => $unitCostInput !== '' ? $unitCostInput : null,
                ':reason' => $reason !== '' ? $reason : null,
                ':created_by' => (int) ($_SESSION['user']['id'] ?? 0) ?: null,
            ]);

            $transactionId = (int) $db->lastInsertId();
            write_audit_log($db, 'inventory.stock_movement', 'stock_transactions', $transactionId, null, [
                'product_id' => (int) $productId,
                'product_name' => (string) $product['name'],
                'transaction_type' => $type,
                'quantity' => $quantity,
                'stock_before' => $currentStock,
                'stock_after' => $newStock,
            ]);
            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }

        return '';
    }

    private function createSupplier(PDO $db): string
    {
        $code = trim((string) ($_POST['supplier_code'] ?? ''));
        $name = trim((string) ($_POST['supplier_name'] ?? ''));
        $contact = trim((string) ($_POST['contact_person'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));

        if (!valid_text($code, 1, 40)) {
            return 'Enter a supplier code between 1 and 40 characters.';
        }
        if (!valid_text($name, 2, 150)) {
            return 'Enter a supplier name between 2 and 150 characters.';
        }
        if (!valid_text($contact, 0, 150)) {
            return 'The contact name must be 150 characters or fewer.';
        }
        if (!valid_phone($phone)) {
            return 'Enter a valid phone number or leave it blank.';
        }
        if (!valid_email($email)) {
            return 'Enter a valid email address or leave it blank.';
        }
        if (!valid_text($address, 0, 65535)) {
            return 'Enter a valid address.';
        }

        try {
            $statement = $db->prepare(
                "INSERT INTO suppliers
                    (supplier_code, name, contact_person, phone, email, address, status)
                 VALUES
                    (:supplier_code, :name, :contact_person, :phone, :email, :address, 'active')"
            );
            $statement->execute([
                ':supplier_code' => $code,
                ':name' => $name,
                ':contact_person' => $contact !== '' ? $contact : null,
                ':phone' => $phone !== '' ? $phone : null,
                ':email' => $email !== '' ? $email : null,
                ':address' => $address !== '' ? $address : null,
            ]);
            $supplierId = (int) $db->lastInsertId();
            write_audit_log($db, 'supplier.created', 'suppliers', $supplierId, null, [
                'supplier_code' => $code,
                'name' => $name,
            ]);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return 'That supplier code is already in use.';
            }
            throw $exception;
        }

        return '';
    }
}
