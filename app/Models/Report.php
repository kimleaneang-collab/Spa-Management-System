<?php
declare(strict_types=1);

final class Report
{
    // Store the database connection used by this model.
    public function __construct(private PDO $db)
    {
    }

    // Calculate recent revenue, appointment, customer, and service statistics.
    public function analytics(DateTimeImmutable $today): array
    {
        $periodStart = $today->modify('-6 days')->format('Y-m-d');
        $previousStart = $today->modify('-13 days')->format('Y-m-d');
        $tomorrow = $today->modify('+1 day')->format('Y-m-d');
        $todayString = $today->format('Y-m-d');

        $paymentStatement = $this->db->prepare(
            'SELECT CASE WHEN paid_at >= :period_start THEN \'current\' ELSE \'previous\' END AS period,
                    COALESCE(SUM(amount), 0) AS revenue,
                    COUNT(DISTINCT invoice_id) AS invoice_count
             FROM payments
             WHERE paid_at >= :previous_start AND paid_at < :tomorrow
             GROUP BY period'
        );
        $paymentStatement->execute([
            ':period_start' => $periodStart . ' 00:00:00',
            ':previous_start' => $previousStart . ' 00:00:00',
            ':tomorrow' => $tomorrow . ' 00:00:00',
        ]);
        $paymentPeriods = ['current' => ['revenue' => 0.0, 'invoice_count' => 0], 'previous' => ['revenue' => 0.0, 'invoice_count' => 0]];
        foreach ($paymentStatement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $paymentPeriods[$row['period']] = [
                'revenue' => (float) $row['revenue'],
                'invoice_count' => (int) $row['invoice_count'],
            ];
        }

        $appointmentStatement = $this->db->prepare(
            'SELECT CASE WHEN appointment_date >= :period_start THEN \'current\' ELSE \'previous\' END AS period,
                    COUNT(*) AS appointment_count,
                    COUNT(DISTINCT customer_id) AS customer_count
             FROM appointments
             WHERE appointment_date >= :previous_start
               AND appointment_date < :tomorrow
               AND status <> \'cancelled\'
             GROUP BY period'
        );
        $appointmentStatement->execute([
            ':period_start' => $periodStart,
            ':previous_start' => $previousStart,
            ':tomorrow' => $tomorrow,
        ]);
        $appointmentPeriods = [
            'current' => ['appointment_count' => 0, 'customer_count' => 0],
            'previous' => ['appointment_count' => 0, 'customer_count' => 0],
        ];
        foreach ($appointmentStatement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $appointmentPeriods[$row['period']] = [
                'appointment_count' => (int) $row['appointment_count'],
                'customer_count' => (int) $row['customer_count'],
            ];
        }

        $repeatStatement = $this->db->prepare(
            'SELECT COUNT(*) FROM (
                SELECT customer_id
                FROM appointments
                WHERE appointment_date >= :period_start
                  AND appointment_date < :tomorrow
                  AND status <> \'cancelled\'
                GROUP BY customer_id
                HAVING COUNT(*) > 1
             ) AS repeat_customers'
        );
        $repeatStatement->execute([
            ':period_start' => $periodStart,
            ':tomorrow' => $tomorrow,
        ]);
        $repeatCustomers = (int) $repeatStatement->fetchColumn();

        $dailyStatement = $this->db->prepare(
            'SELECT DATE(paid_at) AS report_date, SUM(amount) AS revenue
             FROM payments
             WHERE paid_at >= :period_start AND paid_at < :tomorrow
             GROUP BY DATE(paid_at)
             ORDER BY report_date'
        );
        $dailyStatement->execute([
            ':period_start' => $periodStart . ' 00:00:00',
            ':tomorrow' => $tomorrow . ' 00:00:00',
        ]);
        $dailyRevenue = [];
        for ($offset = 6; $offset >= 0; $offset--) {
            $date = $today->modify('-' . $offset . ' days');
            $key = $date->format('Y-m-d');
            $dailyRevenue[$key] = [
                'date' => $key,
                'label' => $date->format('D'),
                'revenue' => 0.0,
            ];
        }
        foreach ($dailyStatement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (isset($dailyRevenue[$row['report_date']])) {
                $dailyRevenue[$row['report_date']]['revenue'] = (float) $row['revenue'];
            }
        }
        $maxRevenue = max(1.0, ...array_column($dailyRevenue, 'revenue'));
        foreach ($dailyRevenue as &$day) {
            $day['bar_height'] = $day['revenue'] > 0
                ? max(5, (int) round($day['revenue'] / $maxRevenue * 100))
                : 0;
        }
        unset($day);

        $serviceStatement = $this->db->prepare(
            'SELECT COALESCE(s.name, ii.description) AS service_name,
                    SUM(ii.total_amount) AS revenue
             FROM invoice_items ii
             INNER JOIN invoices i ON i.id = ii.invoice_id
             LEFT JOIN services s ON s.id = ii.service_id
             WHERE ii.service_id IS NOT NULL
               AND i.invoice_date >= :period_start
               AND i.invoice_date < :tomorrow
               AND i.status IN (\'paid\', \'partial\')
             GROUP BY service_name
             ORDER BY revenue DESC, service_name'
        );
        $serviceStatement->execute([
            ':period_start' => $periodStart . ' 00:00:00',
            ':tomorrow' => $tomorrow . ' 00:00:00',
        ]);
        $services = $serviceStatement->fetchAll(PDO::FETCH_ASSOC);
        $serviceRevenueTotal = array_sum(array_map(
            // Read each service's revenue as a number for the total.
            static fn (array $row): float => (float) $row['revenue'],
            $services
        ));
        $topRows = array_slice($services, 0, 4);
        $otherRevenue = array_sum(array_map(
            // Read each remaining service's revenue to group it as Other.
            static fn (array $row): float => (float) $row['revenue'],
            array_slice($services, 4)
        ));
        if ($otherRevenue > 0) {
            $topRows[] = ['service_name' => 'Other', 'revenue' => $otherRevenue];
        }
        $topServices = array_map(
            // Add a revenue share to each service row for the report display.
            static function (array $row) use ($serviceRevenueTotal): array {
                $revenue = (float) $row['revenue'];

                return [
                    'name' => (string) $row['service_name'],
                    'revenue' => $revenue,
                    'share' => $serviceRevenueTotal > 0
                        ? (int) round($revenue / $serviceRevenueTotal * 100)
                        : 0,
                ];
            },
            $topRows
        );

        $currentRevenue = $paymentPeriods['current']['revenue'];
        $previousRevenue = $paymentPeriods['previous']['revenue'];
        $currentAppointments = $appointmentPeriods['current']['appointment_count'];
        $previousAppointments = $appointmentPeriods['previous']['appointment_count'];
        $currentCustomers = $appointmentPeriods['current']['customer_count'];
        $currentRebookingRate = $currentCustomers > 0
            ? (int) round($repeatCustomers / $currentCustomers * 100)
            : 0;

        return [
            'periodStart' => $periodStart,
            'periodEnd' => $todayString,
            'totalRevenue' => $currentRevenue,
            'revenueChange' => $this->percentageChange($currentRevenue, $previousRevenue),
            'totalAppointments' => $currentAppointments,
            'appointmentChange' => $this->percentageChange($currentAppointments, $previousAppointments),
            'averageTicket' => $paymentPeriods['current']['invoice_count'] > 0
                ? $currentRevenue / $paymentPeriods['current']['invoice_count']
                : 0.0,
            'rebookingRate' => $currentRebookingRate,
            'rebookingChange' => $this->percentageChange($currentRebookingRate, $this->rebookingRate($previousStart, $periodStart)),
            'dailyRevenue' => array_values($dailyRevenue),
            'topServices' => $topServices,
        ];
    }

    // Compare two values and return the percentage change, or null if undefined.
    private function percentageChange(float|int $current, float|int $previous): ?float
    {
        if ($previous == 0) {
            return $current == 0 ? 0.0 : null;
        }

        return (($current - $previous) / $previous) * 100;
    }

    // Calculate the share of customers with more than one appointment in a date range.
    private function rebookingRate(string $startDate, string $endDate): int
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) AS appointment_count, COUNT(DISTINCT customer_id) AS customer_count
             FROM appointments
             WHERE appointment_date >= :start_date
               AND appointment_date < :end_date
               AND status <> \'cancelled\''
        );
        $statement->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $customerCount = (int) $row['customer_count'];
        if ($customerCount === 0) {
            return 0;
        }

        $repeatStatement = $this->db->prepare(
            'SELECT COUNT(*) FROM (
                SELECT customer_id
                FROM appointments
                WHERE appointment_date >= :start_date
                  AND appointment_date < :end_date
                  AND status <> \'cancelled\'
                GROUP BY customer_id
                HAVING COUNT(*) > 1
             ) AS repeat_customers'
        );
        $repeatStatement->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ]);

        return (int) round((int) $repeatStatement->fetchColumn() / $customerCount * 100);
    }
}
