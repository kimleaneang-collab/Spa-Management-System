<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Report.php';
require_once __DIR__ . '/../../config/database.php';

final class ReportController
{
    // Show analytics to managers and the inventory report to other users.
    public function index(): void
    {
        require_authentication();

        $role = strtolower((string) ($_SESSION['user']['role'] ?? ''));
        if (!in_array($role, ['admin', 'manager'], true)) {
            require __DIR__ . '/../Views/reports/inventory.php';
            return;
        }

        [$report, $currency] = $this->analyticsData();

        require __DIR__ . '/../Views/reports/analytics.php';
    }

    // Download the same live analytics displayed on the reports page.
    public function export(): void
    {
        require_roles(['admin', 'manager']);

        [$report, $currency] = $this->analyticsData();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="spa-report-' . $report['periodEnd'] . '.csv"');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            throw new RuntimeException('Unable to open the report export stream.');
        }

        $writeRow = function (array $row) use ($output): void {
            if (fputcsv($output, $row, ',', '"', '') === false) {
                throw new RuntimeException('Unable to write the report export.');
            }
        };

        $writeRow(['Relax Spa Analytics']);
        $writeRow(['Period start', $report['periodStart']]);
        $writeRow(['Period end', $report['periodEnd']]);
        $writeRow(['Total revenue', $report['totalRevenue'], $this->safeCsvText($currency)]);
        $writeRow(['Total appointments', $report['totalAppointments']]);
        $writeRow(['Average ticket', $report['averageTicket'], $this->safeCsvText($currency)]);
        $writeRow(['Rebooking rate', $report['rebookingRate'] . '%']);
        $writeRow([]);
        $writeRow(['Daily revenue']);
        $writeRow(['Date', 'Day', 'Revenue', 'Currency']);
        foreach ($report['dailyRevenue'] as $day) {
            $writeRow([
                $day['date'],
                $day['label'],
                $day['revenue'],
                $this->safeCsvText($currency),
            ]);
        }
        $writeRow([]);
        $writeRow(['Top services']);
        $writeRow(['Service', 'Revenue', 'Share']);
        foreach ($report['topServices'] as $service) {
            $writeRow([
                $this->safeCsvText((string) $service['name']),
                $service['revenue'],
                $service['share'] . '%',
            ]);
        }

        fclose($output);
        exit;
    }

    // Show the sales report page to signed-in users.
    public function sales(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/reports/sales.php';
    }

    // Show the inventory report page to signed-in users.
    public function inventory(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/reports/inventory.php';
    }

    // Return the analytics and currency used by both the page and CSV export.
    private function analyticsData(): array
    {
        $db = Database::connection();
        $report = (new Report($db))->analytics(new DateTimeImmutable('today'));
        $currencyQuery = $db->prepare(
            "SELECT setting_value FROM settings WHERE setting_key = 'currency' LIMIT 1"
        );
        $currencyQuery->execute();
        $currency = (string) ($currencyQuery->fetchColumn() ?: 'USD');

        return [$report, $currency];
    }

    // Prevent spreadsheet applications from evaluating database text as a formula.
    private function safeCsvText(string $value): string
    {
        $firstCharacter = substr(ltrim($value, " \t\r\n\0\x0B"), 0, 1);

        return in_array($firstCharacter, ['=', '+', '-', '@'], true)
            ? "'" . $value
            : $value;
    }
}
