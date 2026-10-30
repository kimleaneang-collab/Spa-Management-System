<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class MembershipController
{
    // Enroll members and show current membership details.
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $role = strtolower((string) ($_SESSION['user']['role'] ?? ''));
        $db = Database::connection();
        $formError = '';
        $formValues = [];
        $showMembershipModal = false;
        $formSuccess = (string) ($_SESSION['membership_success'] ?? '');
        unset($_SESSION['membership_success']);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_roles(['admin']);
            $showMembershipModal = true;
            $formValues = [
                'customer_id' => trim((string) ($_POST['customer_id'] ?? '')),
                'level_id' => trim((string) ($_POST['level_id'] ?? '')),
                'start_date' => trim((string) ($_POST['start_date'] ?? '')),
                'expiry_date' => trim((string) ($_POST['expiry_date'] ?? '')),
            ];

            $customerId = filter_var($formValues['customer_id'], FILTER_VALIDATE_INT);
            $levelId = filter_var($formValues['level_id'], FILTER_VALIDATE_INT);
            $startDate = $formValues['start_date'];
            $expiryDate = $formValues['expiry_date'];

            if ($customerId === false || $customerId < 1 || $levelId === false || $levelId < 1) {
                $formError = 'Select a customer and membership tier.';
            } elseif (!valid_date_value($startDate, true) || !valid_date_value($expiryDate, true) || $expiryDate < $startDate) {
                $formError = 'Enter valid membership dates. The expiry date must be on or after the start date.';
            } elseif ($startDate < date('Y-m-d')) {
                $formError = 'A new membership cannot start in the past.';
            } else {
                try {
                    $db->beginTransaction();

                    $customerStatement = $db->prepare(
                        "SELECT id FROM customers WHERE id = :id AND status = 'active' FOR UPDATE"
                    );
                    $customerStatement->execute([':id' => $customerId]);
                    if ($customerStatement->fetchColumn() === false) {
                        throw new DomainException('Select an active customer.');
                    }

                    $levelStatement = $db->prepare(
                        'SELECT id FROM membership_levels WHERE id = :id'
                    );
                    $levelStatement->execute([':id' => $levelId]);
                    if ($levelStatement->fetchColumn() === false) {
                        throw new DomainException('Select a valid membership tier.');
                    }

                    $overlappingMembershipStatement = $db->prepare(
                        "SELECT id
                         FROM memberships
                         WHERE customer_id = :customer_id
                           AND status = 'active'
                           AND start_date <= :expiry_date
                           AND expiry_date >= :start_date
                         LIMIT 1
                         FOR UPDATE"
                    );
                    $overlappingMembershipStatement->execute([
                        ':customer_id' => $customerId,
                        ':start_date' => $startDate,
                        ':expiry_date' => $expiryDate,
                    ]);
                    if ($overlappingMembershipStatement->fetchColumn() !== false) {
                        throw new DomainException('This customer already has an active membership during those dates.');
                    }

                    $membershipNumber = 'MEM-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(8)));
                    $membershipStatement = $db->prepare(
                        "INSERT INTO memberships
                            (customer_id, level_id, membership_no, start_date, expiry_date, status)
                         VALUES
                            (:customer_id, :level_id, :membership_no, :start_date, :expiry_date, 'active')"
                    );
                    $membershipStatement->execute([
                        ':customer_id' => $customerId,
                        ':level_id' => $levelId,
                        ':membership_no' => $membershipNumber,
                        ':start_date' => $startDate,
                        ':expiry_date' => $expiryDate,
                    ]);
                    $membershipId = (int) $db->lastInsertId();
                    write_audit_log($db, 'membership.created', 'memberships', $membershipId, null, [
                        'membership_no' => $membershipNumber,
                        'customer_id' => $customerId,
                        'level_id' => $levelId,
                        'start_date' => $startDate,
                        'expiry_date' => $expiryDate,
                    ]);
                    $db->commit();

                    $_SESSION['membership_success'] = 'Membership ' . $membershipNumber . ' was added successfully.';
                    redirect('memberships');
                } catch (DomainException $exception) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    $formError = $exception->getMessage();
                } catch (Throwable $exception) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    error_log('Membership enrollment failed: ' . $exception->getMessage());
                    $formError = 'The membership could not be saved. Please try again.';
                }
            }
        }

        $levelStatement = $db->query(
            "SELECT ml.id, ml.name, ml.discount_percent,
                    ml.loyalty_points_multiplier, ml.annual_fee,
                    COUNT(CASE
                        WHEN m.status = 'active'
                         AND m.start_date <= CURDATE()
                         AND m.expiry_date >= CURDATE() THEN m.id
                    END) AS active_members
             FROM membership_levels ml
             LEFT JOIN memberships m ON m.level_id = ml.id
             GROUP BY ml.id, ml.name, ml.discount_percent,
                      ml.loyalty_points_multiplier, ml.annual_fee
             ORDER BY ml.id"
        );
        $membershipLevels = $levelStatement->fetchAll(PDO::FETCH_ASSOC);

        $memberStatement = $db->query(
            "SELECT m.membership_no, c.full_name, ml.name AS tier, m.start_date, m.expiry_date
             FROM memberships m
             INNER JOIN customers c ON c.id = m.customer_id
             INNER JOIN membership_levels ml ON ml.id = m.level_id
             WHERE m.status = 'active'
               AND m.start_date <= CURDATE()
               AND m.expiry_date >= CURDATE()
             ORDER BY m.expiry_date, c.full_name
             LIMIT 100"
        );
        $members = $memberStatement->fetchAll(PDO::FETCH_ASSOC);

        if ($role === 'cashier') {
            require __DIR__ . '/../Views/memberships/cashier.php';
            return;
        }

        $isAdministrator = $role === 'admin';
        $customers = [];
        if ($isAdministrator) {
            $customers = $db->query(
                "SELECT id, full_name, phone
                 FROM customers
                 WHERE status = 'active'
                 ORDER BY full_name"
            )->fetchAll(PDO::FETCH_ASSOC);
        }

        require __DIR__ . '/../Views/memberships/index.php';
    }
}
