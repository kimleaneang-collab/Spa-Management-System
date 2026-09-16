<?php
declare(strict_types=1);

require_once DIR . '/../Models/Dashboard.php';
require_once DIR . '/../../config/database.php';

final class DashboardController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $user = $_SESSION['user'];
        $dashboard = new Dashboard(Database::connection());
        extract($dashboard->overview(), EXTR_SKIP);

        require DIR . '/../Views/dashboard.php';
    }
}