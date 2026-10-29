<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Dashboard.php';
require_once __DIR__ . '/../../config/database.php';

final class DashboardController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $user = $_SESSION['user'];
        $dashboard = new Dashboard(Database::connection());
        $role = strtolower((string) ($user['role'] ?? ''));

        if (in_array($role, ['staff', 'therapist'], true)) {
            extract($dashboard->therapistOverview(
                (int) ($user['id'] ?? 0),
                (string) ($user['full_name'] ?? $user['username'] ?? '')
            ), EXTR_SKIP);
            require __DIR__ . '/../Views/therapist-dashboard.php';
            return;
        }

        if ($role === 'cashier') {
            extract($dashboard->cashierOverview((int) ($user['id'] ?? 0)), EXTR_SKIP);
            $currencyQuery = Database::connection()->prepare(
                "SELECT setting_value FROM settings WHERE setting_key = 'currency' LIMIT 1"
            );
            $currencyQuery->execute();
            $currency = (string) ($currencyQuery->fetchColumn() ?: 'USD');
            require __DIR__ . '/../Views/cashier-dashboard.php';
            return;
        }

        extract($dashboard->overview(), EXTR_SKIP);

        if ($role === 'receptionist') {
            require __DIR__ . '/../Views/receptionist-dashboard.php';
            return;
        }

        require __DIR__ . '/../Views/dashboard.php';
    }
}