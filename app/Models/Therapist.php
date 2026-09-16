<?php
declare(strict_types=1);

final class Therapist
{
	public function __construct(private PDO $db)
	{
	}

	public function all(): array
	{
		$stmt = $this->db->query(
			"SELECT t.id, t.full_name AS name, t.phone, t.gender,
					COALESCE(t.specialization, 'General Wellness') AS specialty,
					t.employment_status,
					COUNT(a.id) AS today_bookings,
					0 AS rating
			 FROM therapists t
			 LEFT JOIN appointments a
				ON a.therapist_id = t.id AND a.appointment_date = CURDATE()
			 GROUP BY t.id, t.full_name, t.specialization, t.employment_status
			 ORDER BY t.id DESC"
		);

		return array_map(static function (array $therapist): array {
			$statusMap = [
				'active' => ['label' => 'Available', 'class' => 'available'],
				'inactive' => ['label' => 'Off Duty', 'class' => 'off-duty'],
				'on_leave' => ['label' => 'On Break', 'class' => 'break'],
			];
			$status = $statusMap[$therapist['employment_status']] ?? $statusMap['inactive'];

			return [
				'id' => (int) $therapist['id'],
				'name' => $therapist['name'],
				'specialty' => $therapist['specialty'],
				'status' => $status['label'],
				'status_class' => $status['class'],
				'today' => (int) $therapist['today_bookings'],
				'rating' => (float) $therapist['rating'],
			];
		}, $stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	public function find(int $id): ?array
	{
		$stmt = $this->db->prepare('SELECT id, full_name, phone, specialization, gender, employment_status FROM therapists WHERE id = :id');
		$stmt->execute([':id' => $id]);
		$therapist = $stmt->fetch(PDO::FETCH_ASSOC);

		return $therapist !== false ? $therapist : null;
	}

	public function create(
		string $name,
		string $phone,
		string $specialization,
		string $gender,
		string $status
	): void {
		$stmt = $this->db->prepare(
			'INSERT INTO therapists
				(therapist_code, full_name, gender, phone, specialization, employment_status)
			 VALUES (:code, :name, :gender, :phone, :specialization, :status)'
		);
		$stmt->execute([
			':code' => 'THER-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
			':name' => $name,
			':gender' => $gender !== '' ? $gender : null,
			':phone' => $phone,
			':specialization' => $specialization !== '' ? $specialization : null,
			':status' => $status,
		]);
	}

	public function update(int $id, string $name, string $phone, string $specialization, string $gender, string $status): void
	{
		$stmt = $this->db->prepare(
			'UPDATE therapists SET full_name = :name, phone = :phone, specialization = :specialization, gender = :gender, employment_status = :status WHERE id = :id'
		);
		$stmt->execute([
			':id' => $id,
			':name' => $name,
			':phone' => $phone,
			':specialization' => $specialization !== '' ? $specialization : null,
			':gender' => $gender !== '' ? $gender : null,
			':status' => $status,
		]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->db->prepare('DELETE FROM therapists WHERE id = :id');
		$stmt->execute([':id' => $id]);
	}
}
