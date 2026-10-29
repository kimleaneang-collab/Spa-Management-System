<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Invoice.php';
require_once __DIR__ . '/../../config/database.php';

final class BillingController
{
    public function receipts(): void
    {
        require_roles(['cashier']);

        $user = $_SESSION['user'];
        $receipts = (new Invoice(Database::connection()))->receiptsForCashier((int) $user['id']);
        $currencyQuery = Database::connection()->prepare(
            "SELECT setting_value FROM settings WHERE setting_key = 'currency' LIMIT 1"
        );
        $currencyQuery->execute();
        $currency = (string) ($currencyQuery->fetchColumn() ?: 'USD');

        require __DIR__ . '/../Views/billing/cashier-receipts.php';
    }

    public function index(): void
    {
        require_roles(['admin', 'manager', 'staff', 'receptionist', 'cashier']);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $invoiceModel = new Invoice(Database::connection());
        $formError = '';
        $formSuccess = (string) ($_SESSION['pos_success'] ?? '');
        unset($_SESSION['pos_success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string) ($_POST['csrf_token'] ?? '');
            if (empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $token)) {
                http_response_code(419);
                exit('Invalid CSRF token.');
            }

            $customerId = filter_var($_POST['customer_id'] ?? null, FILTER_VALIDATE_INT);
            $paymentMethod = (string) ($_POST['payment_method'] ?? '');
            $discountInput = trim((string) ($_POST['discount_amount'] ?? '0'));
            $itemsInput = (string) ($_POST['items_json'] ?? '');
            $items = json_decode($itemsInput, true);
            if ($customerId === false || $customerId < 1) {
                $formError = 'Select a customer to continue.';
            } elseif (!in_array($paymentMethod, ['cash', 'card', 'bank_transfer', 'qr_payment'], true)) {
                $formError = 'Select a valid payment method.';
            } elseif (!is_numeric($discountInput) || (float) $discountInput < 0) {
                $formError = 'Enter a valid discount amount.';
            } elseif (!is_array($items)) {
                $formError = 'The cart data could not be read. Please add your items again.';
            } else {
                try {
                    $invoiceNumber = $invoiceModel->checkout(
                        (int) $customerId,
                        $items,
                        round((float) $discountInput, 2),
                        $paymentMethod,
                        (int) ($_SESSION['user']['id'] ?? 0) ?: null
                    );
                    $_SESSION['pos_success'] = 'Payment completed. Invoice ' . $invoiceNumber . ' was saved.';
                    redirect('pos');
                } catch (DomainException $exception) {
                    $formError = $exception->getMessage();
                } catch (Throwable $exception) {
                    error_log('POS checkout failed: ' . $exception->getMessage());
                    $formError = 'The sale could not be saved. No payment was recorded; please try again.';
                }
            }
        }

        $catalog = $invoiceModel->catalog();
        $taxPercent = $invoiceModel->taxPercent();
        $currencyQuery = Database::connection()->prepare(
            "SELECT setting_value FROM settings WHERE setting_key = 'currency' LIMIT 1"
        );
        $currencyQuery->execute();
        $currency = (string) ($currencyQuery->fetchColumn() ?: 'USD');
        $csrfToken = (string) $_SESSION['csrf_token'];

        if (strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'cashier') {
            require __DIR__ . '/../Views/billing/cashier-pos.php';
            return;
        }

        require __DIR__ . '/../Views/billing/inoice.php';
    }
}
