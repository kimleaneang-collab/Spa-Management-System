<?php
declare(strict_types=1);

final class RoomController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/rooms/index.php';
    }
}
