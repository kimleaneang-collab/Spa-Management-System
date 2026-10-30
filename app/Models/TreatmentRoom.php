<?php
declare(strict_types=1);

final class TreatmentRoom
{
	// Store the database connection used by this model.
	public function __construct(private PDO $db)
	{
	}

	// Return rooms with their current occupancy and display-ready status.
	public function all(bool $ascending = false): array
	{
		$stmt = $this->db->query(
			'SELECT r.id, r.room_code, r.room_name, r.room_type, r.capacity, r.status, r.notes,
					(SELECT a.id
					 FROM appointments a
					 WHERE a.room_id = r.id
					   AND a.appointment_date = CURDATE()
					   AND a.start_time <= CURTIME()
					   AND a.end_time > CURTIME()
					   AND a.status IN (\'confirmed\', \'in_progress\')
					 ORDER BY a.start_time DESC
					 LIMIT 1) AS current_appointment_id,
					(SELECT t.full_name
					 FROM appointments a
					 LEFT JOIN therapists t ON t.id = a.therapist_id
					 WHERE a.room_id = r.id
					   AND a.appointment_date = CURDATE()
					   AND a.start_time <= CURTIME()
					   AND a.end_time > CURTIME()
					   AND a.status IN (\'confirmed\', \'in_progress\')
					 ORDER BY a.start_time DESC
					 LIMIT 1) AS current_therapist,
					(SELECT TIME_FORMAT(a.end_time, \'%l:%i%p\')
					 FROM appointments a
					 WHERE a.room_id = r.id
					   AND a.appointment_date = CURDATE()
					   AND a.start_time <= CURTIME()
					   AND a.end_time > CURTIME()
					   AND a.status IN (\'confirmed\', \'in_progress\')
					 ORDER BY a.start_time DESC
					 LIMIT 1) AS occupied_until
			 FROM treatment_rooms r
			 ORDER BY r.id ' . ($ascending ? 'ASC' : 'DESC')
		);

		// Format each room's status and current appointment details.
		return array_map(static function (array $room): array {
			$statusMap = [
				'available' => ['label' => 'Available', 'class' => 'available'],
				'occupied' => ['label' => 'Occupied', 'class' => 'occupied'],
				'cleaning' => ['label' => 'Cleaning', 'class' => 'cleaning'],
				'under_maintenance' => ['label' => 'Maintenance', 'class' => 'maintenance'],
			];
			$status = $statusMap[$room['status']] ?? $statusMap['available'];
			$isOccupied = !empty($room['current_appointment_id']) && !in_array($room['status'], ['cleaning', 'under_maintenance'], true);
			if ($isOccupied) {
				$status = $statusMap['occupied'];
			}

			return [
				'id' => (int) $room['id'],
				'number' => $room['room_name'] ?: $room['room_code'],
				'type' => $room['room_type'] ?: 'Treatment room',
				'capacity' => (int) $room['capacity'],
				'status' => $status['label'],
				'status_class' => $status['class'],
				'therapist' => $isOccupied ? ((string) ($room['current_therapist'] ?? '') ?: 'Guest appointment') : '',
				'occupied_until' => $isOccupied ? (string) $room['occupied_until'] : '',
			];
		}, $stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	// Add a treatment room and save its optional type and notes.
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
