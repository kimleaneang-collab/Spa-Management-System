<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/TreatmentRoom.php';
require_once __DIR__ . '/../../config/database.php';

final class RoomController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $roomModel = new TreatmentRoom(Database::connection());

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
            $code = trim((string) ($_POST['room_code'] ?? ''));
            $name = trim((string) ($_POST['room_name'] ?? ''));
            $type = trim((string) ($_POST['room_type'] ?? ''));
            $capacityInput = trim((string) ($_POST['capacity'] ?? ''));
            $capacity = (int) $capacityInput;
            $status = trim((string) ($_POST['status'] ?? 'available'));
            $notes = trim((string) ($_POST['notes'] ?? ''));
            $allowedStatuses = ['available', 'occupied', 'cleaning', 'under_maintenance'];

            if (valid_text($code, 2, 30) && valid_text($name, 2, 100) && valid_text($type, 0, 100) && ctype_digit($capacityInput) && $capacity >= 1 && $capacity <= 999 && in_array($status, $allowedStatuses, true) && valid_text($notes, 0, 1000)) {
                $roomModel->create($code, $name, $type, $capacity, $status, $notes);
            }

            redirect('rooms');
        }

        $rooms = [];
        try {
            $rooms = $roomModel->all();
        } catch (Throwable $e) {
        }

        require __DIR__ . '/../Views/rooms/index.php';
    }
}
