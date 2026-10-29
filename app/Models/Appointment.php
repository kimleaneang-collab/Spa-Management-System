<?php
declare(strict_types=1);

final class Appointment
{
	public function __construct(private PDO $db)
	{
	}

	public function all(): array
	{
		$stmt = $this->db->query(
			'SELECT a.id, a.customer_id, a.therapist_id, a.service_id, a.room_id,
					a.appointment_date, a.start_time, a.end_time, a.status,
					c.full_name AS customer, t.full_name AS therapist,
					s.name AS service, s.duration_minutes,
					COALESCE(r.room_name, r.room_code, "Unassigned") AS room
			 FROM appointments a
			 INNER JOIN customers c ON c.id = a.customer_id
			 INNER JOIN services s ON s.id = a.service_id
			 LEFT JOIN therapists t ON t.id = a.therapist_id
			 LEFT JOIN treatment_rooms r ON r.id = a.room_id
			 ORDER BY a.appointment_date DESC, a.start_time DESC'
		);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function forTherapistUser(int $userId, string $userName): array
	{
		$therapistLookup = $this->db->prepare(
			"SELECT t.id
			 FROM therapists t
			 LEFT JOIN employees e
				ON e.user_id = :user_id
			   AND e.role_name = 'therapist'
			   AND LOWER(TRIM(e.full_name)) = LOWER(TRIM(t.full_name))
			 WHERE t.user_id = :linked_user_id
				OR e.id IS NOT NULL
				OR LOWER(TRIM(t.full_name)) = LOWER(TRIM(:full_name))
			 ORDER BY (t.user_id = :priority_user_id) DESC, (e.id IS NOT NULL) DESC, t.id ASC
			 LIMIT 1"
		);
		$therapistLookup->execute([
			':user_id' => $userId,
			':linked_user_id' => $userId,
			':priority_user_id' => $userId,
			':full_name' => $userName,
		]);
		$therapistId = $therapistLookup->fetchColumn();

		if ($therapistId === false) {
			return ['therapist_id' => null, 'appointments' => []];
		}

		$stmt = $this->db->prepare(
			'SELECT a.id, a.customer_id, a.therapist_id, a.service_id, a.room_id,
					a.appointment_date, a.start_time, a.end_time, a.status,
					c.full_name AS customer, t.full_name AS therapist,
					s.name AS service, s.duration_minutes,
					COALESCE(r.room_name, r.room_code, "Unassigned") AS room
			 FROM appointments a
			 INNER JOIN customers c ON c.id = a.customer_id
			 INNER JOIN services s ON s.id = a.service_id
			 LEFT JOIN therapists t ON t.id = a.therapist_id
			 LEFT JOIN treatment_rooms r ON r.id = a.room_id
			 WHERE a.therapist_id = :therapist_id
			 ORDER BY a.appointment_date DESC, a.start_time DESC'
		);
		$stmt->execute([':therapist_id' => (int) $therapistId]);

		return [
			'therapist_id' => (int) $therapistId,
			'appointments' => $stmt->fetchAll(PDO::FETCH_ASSOC),
		];
	}

	public function betweenDates(string $startDate, string $endDate): array
	{
		$stmt = $this->db->prepare(
			'SELECT a.id, a.appointment_date, a.start_time, a.status,
					c.full_name AS customer, t.full_name AS therapist,
					s.name AS service
			 FROM appointments a
			 INNER JOIN customers c ON c.id = a.customer_id
			 INNER JOIN services s ON s.id = a.service_id
			 LEFT JOIN therapists t ON t.id = a.therapist_id
			 WHERE a.appointment_date >= :start_date
			   AND a.appointment_date < :end_date
			 ORDER BY a.appointment_date, a.start_time'
		);
		$stmt->execute([
			':start_date' => $startDate,
			':end_date' => $endDate,
		]);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function betweenDatesForTherapist(int $therapistId, string $startDate, string $endDate): array
	{
		$stmt = $this->db->prepare(
			'SELECT a.id, a.appointment_date, a.start_time, a.status,
					c.full_name AS customer, t.full_name AS therapist,
					s.name AS service
			 FROM appointments a
			 INNER JOIN customers c ON c.id = a.customer_id
			 INNER JOIN services s ON s.id = a.service_id
			 LEFT JOIN therapists t ON t.id = a.therapist_id
			 WHERE a.therapist_id = :therapist_id
			   AND a.appointment_date >= :start_date
			   AND a.appointment_date < :end_date
			 ORDER BY a.appointment_date, a.start_time'
		);
		$stmt->execute([
			':therapist_id' => $therapistId,
			':start_date' => $startDate,
			':end_date' => $endDate,
		]);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function find(int $id): ?array
	{
		$stmt = $this->db->prepare('SELECT id, customer_id, service_id, therapist_id, room_id, appointment_date, start_time, end_time, status, notes FROM appointments WHERE id = :id');
		$stmt->execute([':id' => $id]);
		$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

		return $appointment !== false ? $appointment : null;
	}

	public function options(): array
	{
		return [
			'customers' => $this->db->query('SELECT id, full_name FROM customers WHERE status = "active" ORDER BY full_name')->fetchAll(PDO::FETCH_ASSOC),
			'therapists' => $this->db->query('SELECT id, full_name FROM therapists WHERE employment_status = "active" ORDER BY full_name')->fetchAll(PDO::FETCH_ASSOC),
			'services' => $this->db->query('SELECT id, name, duration_minutes, price FROM services WHERE status = "active" ORDER BY name')->fetchAll(PDO::FETCH_ASSOC),
			'rooms' => $this->db->query('SELECT id, room_name, room_code FROM treatment_rooms WHERE status <> "under_maintenance" ORDER BY room_name')->fetchAll(PDO::FETCH_ASSOC),
		];
	}

	public function create(int $customerId, int $serviceId, ?int $therapistId, ?int $roomId, string $date, string $start, string $end, string $status, string $notes, ?int $createdBy): void
	{
		$stmt = $this->db->prepare(
			'INSERT INTO appointments (appointment_no, customer_id, service_id, therapist_id, room_id, appointment_date, start_time, end_time, status, notes, created_by)
			 VALUES (:number, :customer, :service, :therapist, :room, :date, :start, :end, :status, :notes, :created_by)'
		);
		$stmt->execute([
			':number' => 'APT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
			':customer' => $customerId, ':service' => $serviceId, ':therapist' => $therapistId,
			':room' => $roomId, ':date' => $date, ':start' => $start, ':end' => $end,
			':status' => $status, ':notes' => $notes !== '' ? $notes : null, ':created_by' => $createdBy,
		]);
	}

	public function update(int $id, int $customerId, int $serviceId, ?int $therapistId, ?int $roomId, string $date, string $start, string $end, string $status, string $notes): void
	{
		$stmt = $this->db->prepare(
			'UPDATE appointments SET customer_id = :customer, service_id = :service, therapist_id = :therapist, room_id = :room,
			 appointment_date = :date, start_time = :start, end_time = :end, status = :status, notes = :notes WHERE id = :id'
		);
		$stmt->execute([
			':id' => $id, ':customer' => $customerId, ':service' => $serviceId, ':therapist' => $therapistId,
			':room' => $roomId, ':date' => $date, ':start' => $start, ':end' => $end, ':status' => $status,
			':notes' => $notes !== '' ? $notes : null,
		]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->db->prepare('DELETE FROM appointments WHERE id = :id');
		$stmt->execute([':id' => $id]);
	}
}
