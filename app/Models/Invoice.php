<?php
declare(strict_types=1);

final class Invoice
{
    public function __construct(private PDO $db)
    {
    }

    public function catalog(): array
    {
        return [
            'services' => $this->db->query(
                "SELECT id, name, price AS unit_price, 'service' AS item_type
                 FROM services
                 WHERE status = 'active'
                 ORDER BY name"
            )->fetchAll(PDO::FETCH_ASSOC),
            'products' => $this->db->query(
                "SELECT id, name, selling_price AS unit_price, stock_quantity, unit, 'product' AS item_type
                 FROM products
                 WHERE status = 'active' AND stock_quantity > 0
                 ORDER BY name"
            )->fetchAll(PDO::FETCH_ASSOC),
            'customers' => $this->db->query(
                "SELECT id, full_name, phone
                 FROM customers
                 WHERE status = 'active'
                 ORDER BY full_name"
            )->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function taxPercent(): float
    {
        $statement = $this->db->prepare(
            "SELECT setting_value FROM settings WHERE setting_key = 'tax_percent' LIMIT 1"
        );
        $statement->execute();
        $taxPercent = $statement->fetchColumn();

        return $taxPercent !== false && is_numeric($taxPercent)
            ? max(0, min(100, (float) $taxPercent))
            : 0.0;
    }

    public function checkout(
        int $customerId,
        array $items,
        float $discountAmount,
        string $paymentMethod,
        ?int $userId
    ): string {
        if (!in_array($paymentMethod, ['cash', 'card', 'bank_transfer', 'qr_payment'], true)) {
            throw new DomainException('Select a valid payment method.');
        }

        $this->db->beginTransaction();

        try {
            $customerStatement = $this->db->prepare(
                "SELECT id FROM customers WHERE id = :id AND status = 'active' FOR UPDATE"
            );
            $customerStatement->execute([':id' => $customerId]);
            if ($customerStatement->fetchColumn() === false) {
                throw new DomainException('Select an active customer before completing checkout.');
            }

            $normalizedItems = [];
            foreach ($items as $item) {
                $type = (string) ($item['type'] ?? '');
                $id = (int) ($item['id'] ?? 0);
                $quantity = (float) ($item['quantity'] ?? 0);
                if (
                    !in_array($type, ['service', 'product'], true)
                    || $id < 1
                    || $quantity <= 0
                    || $quantity > 10000
                    || abs($quantity - round($quantity, 3)) > 0.000001
                ) {
                    throw new DomainException('The cart contains an invalid item or quantity.');
                }

                $key = $type . ':' . $id;
                $normalizedItems[$key] ??= ['type' => $type, 'id' => $id, 'quantity' => 0.0];
                $normalizedItems[$key]['quantity'] += $quantity;
            }

            if ($normalizedItems === []) {
                throw new DomainException('Add at least one service or product to the cart.');
            }
            ksort($normalizedItems, SORT_STRING);

            $invoiceItems = [];
            $subtotal = 0.0;
            foreach ($normalizedItems as $item) {
                if ($item['type'] === 'service') {
                    $statement = $this->db->prepare(
                        "SELECT id, name, price AS unit_price
                         FROM services
                         WHERE id = :id AND status = 'active'
                         FOR UPDATE"
                    );
                } else {
                    $statement = $this->db->prepare(
                        "SELECT id, name, selling_price AS unit_price, stock_quantity
                         FROM products
                         WHERE id = :id AND status = 'active'
                         FOR UPDATE"
                    );
                }
                $statement->execute([':id' => $item['id']]);
                $catalogItem = $statement->fetch(PDO::FETCH_ASSOC);
                if ($catalogItem === false) {
                    throw new DomainException('A selected item is no longer available.');
                }
                if ($item['type'] === 'product' && (float) $catalogItem['stock_quantity'] < $item['quantity']) {
                    throw new DomainException($catalogItem['name'] . ' does not have enough stock.');
                }

                $lineTotal = round((float) $catalogItem['unit_price'] * $item['quantity'], 2);
                $subtotal += $lineTotal;
                $invoiceItems[] = [
                    'type' => $item['type'],
                    'id' => $item['id'],
                    'name' => (string) $catalogItem['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => (float) $catalogItem['unit_price'],
                    'total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            if ($discountAmount < 0 || $discountAmount > $subtotal) {
                throw new DomainException('Discount must be between zero and the cart subtotal.');
            }

            $taxPercent = $this->taxPercent();
            $taxAmount = round(($subtotal - $discountAmount) * $taxPercent / 100, 2);
            $total = round($subtotal - $discountAmount + $taxAmount, 2);
            if ($total <= 0) {
                throw new DomainException('The total must be greater than zero to complete payment.');
            }
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

            $invoiceStatement = $this->db->prepare(
                "INSERT INTO invoices
                    (invoice_no, customer_id, invoice_date, subtotal, discount_amount, tax_amount,
                     total_amount, paid_amount, status, created_by)
                 VALUES
                    (:invoice_no, :customer_id, NOW(), :subtotal, :discount, :tax, :total, :paid, 'paid', :created_by)"
            );
            $invoiceStatement->execute([
                ':invoice_no' => $invoiceNumber,
                ':customer_id' => $customerId,
                ':subtotal' => $subtotal,
                ':discount' => $discountAmount,
                ':tax' => $taxAmount,
                ':total' => $total,
                ':paid' => $total,
                ':created_by' => $userId,
            ]);
            $invoiceId = (int) $this->db->lastInsertId();

            $itemStatement = $this->db->prepare(
                'INSERT INTO invoice_items
                    (invoice_id, service_id, product_id, description, quantity, unit_price, total_amount)
                 VALUES (:invoice_id, :service_id, :product_id, :description, :quantity, :unit_price, :total)'
            );
            $stockStatement = $this->db->prepare(
                'UPDATE products SET stock_quantity = stock_quantity - :quantity
                 WHERE id = :id AND stock_quantity >= :minimum'
            );
            $stockTransactionStatement = $this->db->prepare(
                "INSERT INTO stock_transactions
                    (product_id, transaction_type, quantity, reference_type, reference_id, reason, created_by)
                 VALUES (:product_id, 'stock_out', :quantity, 'invoice', :invoice_id, 'POS sale', :created_by)"
            );

            foreach ($invoiceItems as $item) {
                $isService = $item['type'] === 'service';
                $itemStatement->execute([
                    ':invoice_id' => $invoiceId,
                    ':service_id' => $isService ? $item['id'] : null,
                    ':product_id' => $isService ? null : $item['id'],
                    ':description' => $item['name'],
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['unit_price'],
                    ':total' => $item['total'],
                ]);

                if (!$isService) {
                    $stockStatement->execute([
                        ':quantity' => $item['quantity'],
                        ':id' => $item['id'],
                        ':minimum' => $item['quantity'],
                    ]);
                    if ($stockStatement->rowCount() !== 1) {
                        throw new DomainException($item['name'] . ' stock changed. Please review the cart.');
                    }
                    $stockTransactionStatement->execute([
                        ':product_id' => $item['id'],
                        ':quantity' => $item['quantity'],
                        ':invoice_id' => $invoiceId,
                        ':created_by' => $userId,
                    ]);
                }
            }

            $paymentStatement = $this->db->prepare(
                'INSERT INTO payments (invoice_id, payment_no, amount, payment_method, received_by)
                 VALUES (:invoice_id, :payment_no, :amount, :method, :received_by)'
            );
            $paymentStatement->execute([
                ':invoice_id' => $invoiceId,
                ':payment_no' => 'PAY-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
                ':amount' => $total,
                ':method' => $paymentMethod,
                ':received_by' => $userId,
            ]);

            $this->db->commit();

            return $invoiceNumber;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }
}
