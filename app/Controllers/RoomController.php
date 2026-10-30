<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/TreatmentRoom.php';
require_once __DIR__ . '/../../config/database.php';

final class RoomController
{
    // Create rooms when allowed and show the room list.
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $role = strtolower((string) ($_SESSION['user']['role'] ?? ''));
        $isReceptionist = $role === 'receptionist';
        $canManageRooms = in_array($role, ['admin', 'manager'], true);
        $db = Database::connection();
        $roomModel = new TreatmentRoom($db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
            require_roles(['admin', 'manager']);

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
                write_audit_log($db, 'room.created', 'treatment_rooms', (int) $db->lastInsertId(), null, ['room_code' => $code]);
            }

            redirect('rooms');
        }

        $rooms = [];
        try {
            $rooms = $roomModel->all($isReceptionist);
        } catch (Throwable $e) {
        }

        require __DIR__ . '/../Views/rooms/index.php';
    }
}
