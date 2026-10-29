<?php
declare(strict_types=1);

final class Dashboard
{
    public function __construct(private PDO $db)
    {
    }

    public function therapistOverview(int $userId, string $userName): array
    {
        $therapistLookup = $this->db->prepare(
            "SELECT t.id
             FROM therapists t
             LEFT JOIN employees e
                ON e.user_id = :user_id
               AND e.role_name = 'therapist'
               AND LOWER(TRIM(e.full_name)) = LOWER(TRIM(t.full_name))
             WHERE e.id IS NOT NULL
                OR LOWER(TRIM(t.full_name)) = LOWER(TRIM(:full_name))
             ORDER BY (e.id IS NOT NULL) DESC, t.id ASC
             LIMIT 1"
        );
        $therapistLookup->execute([
            ':user_id' => $userId,
            ':full_name' => $userName,
        ]);
        $therapistId = $therapistLookup->fetchColumn();

        if ($therapistId === false) {
            return [
                'therapistId' => null,
                'todayAppointments' => 0,
                'totalCustomers' => 0,
                'pendingTreatments' => 0,
                'todayRevenue' => 0.0,
                'therapistSchedule' => [],
            ];
        }

        $therapistId = (int) $therapistId;
        $statsQuery = $this->db->prepare(
            "SELECT COUNT(CASE WHEN a.appointment_date = CURDATE() THEN 1 END) AS today_appointments,
                    COUNT(DISTINCT a.customer_id) AS total_customers,
                    SUM(CASE WHEN a.appointment_date = CURDATE() AND a.status = 'pending' THEN 1 ELSE 0 END) AS pending_treatments
             FROM appointments a
             WHERE a.therapist_id = :therapist_id"
        );
        $statsQuery->execute([':therapist_id' => $therapistId]);
        $stats = $statsQuery->fetch(PDO::FETCH_ASSOC) ?: [];

        $revenueQuery = $this->db->prepare(
            "SELECT COALESCE(SUM(i.total_amount), 0)
             FROM invoices i
             INNER JOIN appointments a ON a.id = i.appointment_id
             WHERE a.therapist_id = :therapist_id
               AND a.appointment_date = CURDATE()
               AND i.status IN ('paid', 'partial')"
        );
        $revenueQuery->execute([':therapist_id' => $therapistId]);

        $scheduleQuery = $this->db->prepare(
            "SELECT c.full_name AS customer,
                    s.name AS service,
                    a.start_time,
                    a.status
             FROM appointments a
             INNER JOIN customers c ON c.id = a.customer_id
             INNER JOIN services s ON s.id = a.service_id
             WHERE a.therapist_id = :therapist_id
               AND a.appointment_date = CURDATE()
             ORDER BY a.start_time ASC"
        );
        $scheduleQuery->execute([':therapist_id' => $therapistId]);

        return [
            'therapistId' => $therapistId,
            'todayAppointments' => (int) ($stats['today_appointments'] ?? 0),
            'totalCustomers' => (int) ($stats['total_customers'] ?? 0),
            'pendingTreatments' => (int) ($stats['pending_treatments'] ?? 0),
            'todayRevenue' => (float) $revenueQuery->fetchColumn(),
            'therapistSchedule' => $scheduleQuery->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function cashierOverview(int $userId): array
    {
        $statsQuery = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS today_sales,
                    COUNT(*) AS transaction_count,
                    COUNT(DISTINCT CASE WHEN i.membership_id IS NOT NULL THEN p.invoice_id END) AS memberships_used
             FROM payments p
             INNER JOIN invoices i ON i.id = p.invoice_id
             WHERE p.received_by = :user_id
               AND p.paid_at >= CURDATE()
               AND p.paid_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
        );
        $statsQuery->execute([':user_id' => $userId]);
        $stats = $statsQuery->fetch(PDO::FETCH_ASSOC) ?: [];

        $pendingPayments = (int) $this->db->query(
            "SELECT COUNT(*) FROM invoices WHERE status IN ('unpaid', 'partial')"
        )->fetchColumn();

        $transactionCount = (int) ($stats['transaction_count'] ?? 0);
        $todaySales = (float) ($stats['today_sales'] ?? 0);
        $recentQuery = $this->db->prepare(
            "SELECT i.id AS invoice_id,
                    i.invoice_no,
                    c.full_name AS customer_name,
                    GROUP_CONCAT(DISTINCT ii.description ORDER BY ii.description SEPARATOR ', ') AS service_name,
                    SUM(p.amount) AS amount,
                    MAX(p.payment_method) AS payment_method,
                    i.status
             FROM payments p
             INNER JOIN invoices i ON i.id = p.invoice_id
             INNER JOIN customers c ON c.id = i.customer_id
             LEFT JOIN invoice_items ii ON ii.invoice_id = i.id
             WHERE p.received_by = :user_id
             GROUP BY i.id, i.invoice_no, c.full_name, i.status
             ORDER BY MAX(p.paid_at) DESC, i.id DESC
             LIMIT 8"
        );
        $recentQuery->execute([':user_id' => $userId]);

        return [
            'todaySales' => $todaySales,
            'transactionCount' => $transactionCount,
            'averageOrderValue' => $transactionCount > 0 ? $todaySales / $transactionCount : 0.0,
            'pendingPayments' => $pendingPayments,
            'membershipsUsed' => (int) ($stats['memberships_used'] ?? 0),
            'recentTransactions' => $recentQuery->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function overview(): array
    {
        $totalRevenue = (float) $this->db->query(
            "SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE status IN ('paid', 'partial')"
        )->fetchColumn();

        $todayAppointments = (int) $this->db->query(
            "SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()"
        )->fetchColumn();

        $pendingAppointments = (int) $this->db->query(
            "SELECT COUNT(*) FROM appointments
             WHERE appointment_date = CURDATE() AND status = 'pending'"
        )->fetchColumn();

        $todayConfirmedAppointments = (int) $this->db->query(
            "SELECT COUNT(*) FROM appointments
             WHERE appointment_date = CURDATE() AND status = 'confirmed'"
        )->fetchColumn();

        $newCustomersToday = (int) $this->db->query(
            "SELECT COUNT(*) FROM customers
             WHERE created_at >= CURDATE() AND created_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
        )->fetchColumn();

        $activeTherapists = (int) $this->db->query(
            "SELECT COUNT(*) FROM therapists WHERE employment_status = 'active'"
        )->fetchColumn();

        $recentCustomers = $this->db->query(
            "SELECT full_name, created_at
             FROM customers
             WHERE status = 'active'
             ORDER BY created_at DESC
             LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        $receptionistAlerts = $this->db->query(
            "SELECT c.full_name, a.start_time, a.status
             FROM appointments a
             INNER JOIN customers c ON c.id = a.customer_id
             WHERE a.appointment_date = CURDATE()
               AND a.status = 'pending'
             ORDER BY a.start_time ASC
             LIMIT 4"
        )->fetchAll(PDO::FETCH_ASSOC);

        $todayRevenue = (float) $this->db->query(
            "SELECT COALESCE(SUM(total_amount), 0)
             FROM invoices
             WHERE DATE(invoice_date) = CURDATE()
               AND status IN ('paid', 'partial')"
        )->fetchColumn();

        $availableRooms = (int) $this->db->query(
            "SELECT COUNT(*) FROM treatment_rooms WHERE status = 'available'"
        )->fetchColumn();

        $totalRooms = (int) $this->db->query(
            "SELECT COUNT(*) FROM treatment_rooms WHERE status <> 'under_maintenance'"
        )->fetchColumn();

        $lowStockItems = (int) $this->db->query(
            "SELECT COUNT(*) FROM products WHERE stock_quantity <= reorder_level AND status = 'active'"
        )->fetchColumn();

        $popularServices = $this->db->query(
            "SELECT s.name, COUNT(a.id) AS total
             FROM appointments a
             INNER JOIN services s ON s.id = a.service_id
             GROUP BY s.id, s.name
             ORDER BY total DESC
             LIMIT 4"
        )->fetchAll(PDO::FETCH_ASSOC);

        $serviceTotals = [];
        $maxServiceCount = 1;
        foreach ($popularServices as $row) {
            $serviceTotals[] = [
                'name' => $row['name'],
                'total' => (int) $row['total'],
            ];
            $maxServiceCount = max($maxServiceCount, (int) $row['total']);
        }

        $revenueData = $this->db->query(
            "SELECT DATE_FORMAT(invoice_date, '%Y-%m-%d') AS day,
                    COALESCE(SUM(total_amount), 0) AS revenue
             FROM invoices
             WHERE invoice_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               AND invoice_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
               AND status IN ('paid', 'partial')
             GROUP BY DATE(invoice_date)
             ORDER BY day ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $chartValues = [];
        $chartLabelMap = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartLabelMap[$date] = date('M j', strtotime($date));
            $chartValues[$date] = 0;
        }

        foreach ($revenueData as $day) {
            $chartValues[$day['day']] = (float) $day['revenue'];
        }

        $highestRevenue = max($chartValues);
        $rawStep = max($highestRevenue, 1) / 3;
        $stepMagnitude = 10 ** floor(log10($rawStep));
        $normalizedStep = $rawStep / $stepMagnitude;
        $stepFactor = $normalizedStep <= 1 ? 1 : ($normalizedStep <= 2 ? 2 : ($normalizedStep <= 5 ? 5 : 10));
        $chartStep = max(10000, $stepFactor * $stepMagnitude);
        $chartMaximum = max($chartStep * 3, ceil($highestRevenue / $chartStep) * $chartStep);
        $chartTicks = [];
        for ($tick = 0; $tick <= 3; $tick++) {
            $value = $tick * $chartStep;
            $label = $value >= 1000 && $value % 1000 === 0
                ? '$' . number_format($value / 1000) . 'K'
                : '$' . number_format($value, 0);
            $chartTicks[] = [
                'y' => 150 - ($tick * (130 / 3)),
                'label' => $label,
            ];
        }

        $chartPoints = [];
        foreach (array_values($chartValues) as $index => $value) {
            $x = 52 + ($index * (406 / 6));
            $y = 150 - (($value / $chartMaximum) * 130);
            $date = array_keys($chartValues)[$index];
            $chartPoints[] = [round($x, 2), round($y, 2), $chartLabelMap[$date], $value];
        }

        $chartPath = '';
        foreach ($chartPoints as $index => $point) {
            if ($index === 0) {
                $chartPath = 'M ' . $point[0] . ' ' . $point[1];
                continue;
            }

            $previousPoint = $chartPoints[$index - 1];
            $controlX = round(($previousPoint[0] + $point[0]) / 2, 2);
            $chartPath .= ' C ' . $controlX . ' ' . $previousPoint[1]
                . ', ' . $controlX . ' ' . $point[1]
                . ', ' . $point[0] . ' ' . $point[1];
        }
        $firstPoint = $chartPoints[0];
        $lastPoint = $chartPoints[count($chartPoints) - 1];
        $chartAreaPath = $chartPath . ' L ' . $lastPoint[0] . ' 150 L ' . $firstPoint[0] . ' 150 Z';

        $notifications = [];
        $lowStockRows = $this->db->query(
            "SELECT name, stock_quantity, reorder_level
             FROM products
             WHERE stock_quantity <= reorder_level AND status = 'active'
             ORDER BY stock_quantity ASC
             LIMIT 3"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($lowStockRows as $row) {
            $notifications[] = [
                'type' => 'Low stock',
                'message' => $row['name'] . ' (Oil) is low (' . (int) $row['stock_quantity'] . ' left)',
                'time' => 'Now'
            ];
        }

        $membershipRows = $this->db->query(
            "SELECT c.full_name, m.expiry_date
             FROM memberships m
             INNER JOIN customers c ON c.id = m.customer_id
             WHERE m.status = 'active' AND m.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             ORDER BY m.expiry_date ASC
             LIMIT 2"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($membershipRows as $row) {
            $notifications[] = [
                'type' => 'Membership',
                'message' => $row['full_name'] . " membership expires on " . date('M d', strtotime($row['expiry_date'])),
                'time' => '2m ago'
            ];
        }

        $appointmentRows = $this->db->query(
            "SELECT c.full_name, a.status, a.appointment_date, a.start_time,
                    s.name AS service,
                    COALESCE(t.full_name, 'Unassigned') AS therapist,
                    COALESCE(r.room_name, r.room_code, 'Unassigned') AS room
             FROM appointments a
             INNER JOIN customers c ON c.id = a.customer_id
             INNER JOIN services s ON s.id = a.service_id
             LEFT JOIN therapists t ON t.id = a.therapist_id
             LEFT JOIN treatment_rooms r ON r.id = a.room_id
             WHERE a.appointment_date = CURDATE()
             ORDER BY a.start_time ASC
             LIMIT 8"
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'totalRevenue' => $totalRevenue,
            'todayAppointments' => $todayAppointments,
            'pendingAppointments' => $pendingAppointments,
            'todayConfirmedAppointments' => $todayConfirmedAppointments,
            'newCustomersToday' => $newCustomersToday,
            'activeTherapists' => $activeTherapists,
            'recentCustomers' => $recentCustomers,
            'receptionistAlerts' => $receptionistAlerts,
            'todayRevenue' => $todayRevenue,
            'availableRooms' => $availableRooms,
            'totalRooms' => $totalRooms,
            'lowStockItems' => $lowStockItems,
            'serviceTotals' => $serviceTotals,
            'maxServiceCount' => $maxServiceCount,
            'chartPoints' => $chartPoints,
            'chartPath' => $chartPath,
            'chartAreaPath' => $chartAreaPath,
            'chartTicks' => $chartTicks,
            'notifications' => $notifications,
            'appointmentRows' => $appointmentRows,
            'revenueTrend' => max(0, min(100, round(($totalRevenue > 0 ? ($totalRevenue / 200000) * 100 : 0), 0))),
        ];
    }
}
