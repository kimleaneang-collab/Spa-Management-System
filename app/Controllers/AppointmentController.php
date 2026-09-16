<?php
declare(strict_types=1);

final class AppointmentController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/appointments/index.php';
    }
}
