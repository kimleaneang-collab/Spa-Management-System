<?php
declare(strict_types=1);

final class Dashboard
{
    public function __construct(private PDO $db)
    {
    }

    public function overview(): array
    {
        $totalRevenue = (float) $this->db->query(
            "SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE status IN ('paid', 'partial')"
        )->fetchColumn();

        $todayAppointments = (int) $this->db->query(
            "SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()"
        )->fetchColumn();

        $availableRooms = (int) $this->db->query(
            "SELECT COUNT(*) FROM treatment_rooms WHERE status = 'available'"
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
            "SELECT DATE_FORMAT(appointment_date, '%Y-%m-%d') AS day,
                    COUNT(*) AS count
             FROM appointments
             WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY DATE(appointment_date)
             ORDER BY day ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $chartValues = [];
        $chartLabelMap = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartLabelMap[$date] = date('D', strtotime($date));
            $chartValues[$date] = 0;
        }

        foreach ($revenueData as $day) {
            $chartValues[$day['day']] = (int) $day['count'];
        }

        $chartPoints = [];
        $maxChartValue = max(1, max($chartValues));
        foreach ($chartValues as $date => $value) {
            $index = array_search($date, array_keys($chartValues), true);
            $x = 18 + ($index * 75);
            $y = 140 - (($value / $maxChartValue) * 90);
            $chartPoints[] = [$x, $y, $chartLabelMap[$date], $value];
        }

        $pathPoints = [];
        foreach ($chartPoints as $index => $point) {
            $pathPoints[] = ($index === 0 ? 'M ' : 'L ') . $point[0] . ' ' . $point[1];
        }

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
            "SELECT c.full_name, a.status, a.appointment_date, a.start_time
             FROM appointments a
             INNER JOIN customers c ON c.id = a.customer_id
             ORDER BY a.appointment_date DESC, a.start_time DESC
             LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'totalRevenue' => $totalRevenue,
            'todayAppointments' => $todayAppointments,
            'availableRooms' => $availableRooms,
            'lowStockItems' => $lowStockItems,
            'serviceTotals' => $serviceTotals,
            'maxServiceCount' => $maxServiceCount,
            'chartPoints' => $chartPoints,
            'chartPath' => implode(' ', $pathPoints),
            'notifications' => $notifications,
            'appointmentRows' => $appointmentRows,
            'revenueTrend' => max(0, min(100, round(($totalRevenue > 0 ? ($totalRevenue / 200000) * 100 : 0), 0))),
        ];
    }
}
