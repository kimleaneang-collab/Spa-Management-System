<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class MembershipController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        if (strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'cashier') {
            $db = Database::connection();
            $levelStatement = $db->prepare(
                "SELECT ml.id, ml.name, ml.discount_percent,
                        ml.loyalty_points_multiplier, ml.annual_fee,
                        COUNT(CASE
                            WHEN m.status = 'active' AND m.expiry_date >= CURDATE() THEN m.id
                        END) AS active_members
                 FROM membership_levels ml
                 LEFT JOIN memberships m ON m.level_id = ml.id
                 GROUP BY ml.id, ml.name, ml.discount_percent,
                          ml.loyalty_points_multiplier, ml.annual_fee
                 ORDER BY ml.id"
            );
            $levelStatement->execute();
            $membershipLevels = $levelStatement->fetchAll(PDO::FETCH_ASSOC);

            $memberStatement = $db->query(
                "SELECT c.full_name, ml.name AS tier, m.start_date, m.expiry_date
                 FROM memberships m
                 INNER JOIN customers c ON c.id = m.customer_id
                 INNER JOIN membership_levels ml ON ml.id = m.level_id
                 WHERE m.status = 'active' AND m.expiry_date >= CURDATE()
                 ORDER BY m.start_date DESC, c.full_name
                 LIMIT 100"
            );
            $members = $memberStatement->fetchAll(PDO::FETCH_ASSOC);

            require __DIR__ . '/../Views/memberships/cashier.php';
            return;
        }

        require __DIR__ . '/../Views/memberships/index.php';
    }
}
