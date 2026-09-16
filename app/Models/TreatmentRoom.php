<?php
declare(strict_types=1);

final class TreatmentRoom
{
	public function __construct(private PDO $db)
	{
	}

	public function all(): array
	{
		$stmt = $this->db->query(
			'SELECT id, room_code, room_name, room_type, capacity, status, notes
			 FROM treatment_rooms
			 ORDER BY id DESC'
		);

		return array_map(static function (array $room): array {
			$statusMap = [
				'available' => ['label' => 'Available', 'class' => 'available'],
				'occupied' => ['label' => 'Occupied', 'class' => 'occupied'],
				'cleaning' => ['label' => 'Cleaning', 'class' => 'cleaning'],
				'under_maintenance' => ['label' => 'Maintenance', 'class' => 'maintenance'],
			];
			$status = $statusMap[$room['status']] ?? $statusMap['available'];

			return [
				'id' => (int) $room['id'],
				'number' => $room['room_name'] ?: $room['room_code'],
				'type' => $room['room_type'] ?: 'Treatment room',
				'capacity' => (int) $room['capacity'],
				'status' => $status['label'],
				'status_class' => $status['class'],
			];
		}, $stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	public function create(
		string $code,
		string $name,
		string $type,
		int $capacity,
		string $status,
		string $notes
	): void {
		$stmt = $this->db->prepare(
			'INSERT INTO treatment_rooms (room_code, room_name, room_type, capacity, status, notes)
			 VALUES (:code, :name, :type, :capacity, :status, :notes)'
		);
		$stmt->execute([
			':code' => $code,
			':name' => $name,
			':type' => $type !== '' ? $type : null,
			':capacity' => $capacity,
			':status' => $status,
			':notes' => $notes !== '' ? $notes : null,
		]);
	}
}
