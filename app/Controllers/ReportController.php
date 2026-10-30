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

        if (strtolower((string) ($_SESSION['user']['role'] ?? '')) !== 'manager') {
            require __DIR__ . '/../Views/reports/inventory.php';
            return;
        }

        $today = new DateTimeImmutable('today');
        $report = (new Report(Database::connection()))->analytics($today);
        $currencyQuery = Database::connection()->prepare(
            "SELECT setting_value FROM settings WHERE setting_key = 'currency' LIMIT 1"
        );
        $currencyQuery->execute();
        $currency = (string) ($currencyQuery->fetchColumn() ?: 'USD');

        require __DIR__ . '/../Views/reports/analytics.php';
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
}
