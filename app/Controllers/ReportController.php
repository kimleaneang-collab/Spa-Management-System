<?php
declare(strict_types=1);

final class ReportController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/reports/inventory.php';
    }

    public function sales(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/reports/sales.php';
    }

    public function inventory(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/reports/inventory.php';
    }
}
