<?php
declare(strict_types=1);

final class Therapist
{
	// Store the database connection used by this model.
	public function __construct(private PDO $db)
	{
	}

	// Return therapist records with booking counts and display-ready status values.
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

		// Convert each therapist row into the fields used by the display.
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

	// Find one therapist and include details from the linked user account.
	public function find(int $id): ?array
	{
		$stmt = $this->db->prepare(
			'SELECT t.id, t.user_id, t.full_name, t.email, t.phone, t.specialization, t.bio, t.gender, t.employment_status,
					u.email AS account_email, u.profile_image AS account_profile_image
			 FROM therapists t
			 LEFT JOIN users u ON u.id = t.user_id
			 WHERE t.id = :id'
		);
		$stmt->execute([':id' => $id]);
		$therapist = $stmt->fetch(PDO::FETCH_ASSOC);

		return $therapist !== false ? $therapist : null;
	}

	// List eligible user accounts that can be linked to this therapist.
	public function accountsForAssignment(int $therapistId = 0): array
	{
		$stmt = $this->db->prepare(
			"SELECT u.id, u.full_name, u.username, u.profile_image
			 FROM users u
			 INNER JOIN roles r ON r.id = u.role_id
			 LEFT JOIN therapists assigned
				ON assigned.user_id = u.id
			   AND assigned.id <> :therapist_id
			 LEFT JOIN therapists current_assignment
				ON current_assignment.user_id = u.id
			   AND current_assignment.id = :current_therapist_id
			 WHERE r.name IN ('staff', 'therapist')
			   AND (u.status = 'active' OR current_assignment.id IS NOT NULL)
			   AND assigned.id IS NULL
			 ORDER BY u.full_name, u.id"
		);
		$stmt->execute([
			':therapist_id' => $therapistId,
			':current_therapist_id' => $therapistId,
		]);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Check whether a user account can be linked to this therapist.
	public function accountCanBeAssigned(int $userId, int $therapistId = 0): bool
	{
		$stmt = $this->db->prepare(
			"SELECT COUNT(*)
			 FROM users u
			 INNER JOIN roles r ON r.id = u.role_id
			 WHERE u.id = :user_id
			   AND r.name IN ('staff', 'therapist')
			   AND (
					u.status = 'active'
					OR EXISTS (
						SELECT 1
						FROM therapists current_assignment
						WHERE current_assignment.user_id = u.id
						  AND current_assignment.id = :current_therapist_id
					)
			   )
			   AND NOT EXISTS (
					SELECT 1
					FROM therapists assigned
					WHERE assigned.user_id = u.id
					  AND assigned.id <> :therapist_id
			   )"
		);
		$stmt->execute([
			':user_id' => $userId,
			':current_therapist_id' => $therapistId,
			':therapist_id' => $therapistId,
		]);

		return (int) $stmt->fetchColumn() > 0;
	}

	// Find a therapist profile using the user's account link or name.
	public function profileForUser(int $userId, string $userName): ?array
	{
		$stmt = $this->db->prepare(
			"SELECT t.id, t.full_name, t.email, t.phone, t.specialization, t.bio, t.experience_years,
					COALESCE(NULLIF(t.email, ''), NULLIF(e.email, ''), u.email) AS email,
					COALESCE(NULLIF(e.phone, ''), NULLIF(t.phone, ''), u.phone) AS profile_phone,
					COALESCE(e.hire_date, DATE(t.created_at)) AS joined_date
			 FROM therapists t
			 LEFT JOIN employees e
				ON e.user_id = :user_id
			   AND e.role_name = 'therapist'
			   AND LOWER(TRIM(e.full_name)) = LOWER(TRIM(t.full_name))
			 LEFT JOIN users u ON u.id = :account_id
			 WHERE e.id IS NOT NULL
				OR t.user_id = :linked_user_id
				OR LOWER(TRIM(t.full_name)) = LOWER(TRIM(:user_name))
			 ORDER BY (t.user_id = :priority_user_id) DESC, (e.id IS NOT NULL) DESC, t.id ASC
			 LIMIT 1"
		);
		$stmt->execute([
			':user_id' => $userId,
			':account_id' => $userId,
			':linked_user_id' => $userId,
			':priority_user_id' => $userId,
			':user_name' => $userName,
		]);
		$profile = $stmt->fetch(PDO::FETCH_ASSOC);

		return $profile !== false ? $profile : null;
	}

	// Add a therapist and connect an account when one is provided.
	public function create(
		string $name,
		string $email,
		string $phone,
		string $specialization,
		string $gender,
		string $status,
		string $bio,
		?int $userId
	): void {
		$stmt = $this->db->prepare(
			'INSERT INTO therapists
				(user_id, therapist_code, full_name, email, gender, phone, specialization, bio, employment_status)
			 VALUES (:user_id, :code, :name, :email, :gender, :phone, :specialization, :bio, :status)'
		);
		$stmt->execute([
			':user_id' => $userId,
			':code' => 'THER-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
			':name' => $name,
			':email' => $email !== '' ? $email : null,
			':gender' => $gender !== '' ? $gender : null,
			':phone' => $phone,
			':specialization' => $specialization !== '' ? $specialization : null,
			':bio' => $bio !== '' ? $bio : null,
			':status' => $status,
		]);
	}

	// Update a therapist and synchronize matching employee and account names.
	public function update(int $id, string $name, string $email, string $phone, string $specialization, string $gender, string $status, string $bio, ?int $userId): void
	{
		$current = $this->db->prepare('SELECT full_name FROM therapists WHERE id = :id');
		$current->execute([':id' => $id]);
		$previousName = $current->fetchColumn();

		$stmt = $this->db->prepare(
			'UPDATE therapists SET user_id = :user_id, full_name = :name, email = :email, phone = :phone, specialization = :specialization, bio = :bio, gender = :gender, employment_status = :status WHERE id = :id'
		);
		$stmt->execute([
			':id' => $id,
			':user_id' => $userId,
			':name' => $name,
			':email' => $email !== '' ? $email : null,
			':phone' => $phone,
			':specialization' => $specialization !== '' ? $specialization : null,
			':bio' => $bio !== '' ? $bio : null,
			':gender' => $gender !== '' ? $gender : null,
			':status' => $status,
		]);

		if ($previousName !== false) {
			$employees = $this->db->prepare(
				"UPDATE employees
				 SET full_name = :name, phone = :phone
				 WHERE role_name = 'therapist'
				   AND LOWER(TRIM(full_name)) = LOWER(TRIM(:previous_name))"
			);
			$employees->execute([
				':name' => $name,
				':phone' => $phone,
				':previous_name' => (string) $previousName,
			]);

			$accounts = $this->db->prepare(
				"UPDATE users u
				 INNER JOIN roles r ON r.id = u.role_id
				 LEFT JOIN employees e ON e.user_id = u.id AND e.role_name = 'therapist'
				 SET u.full_name = :name, u.phone = :phone
				 WHERE r.name IN ('staff', 'therapist')
				   AND e.id IS NULL
				   AND LOWER(TRIM(u.full_name)) = LOWER(TRIM(:previous_name))"
			);
			$accounts->execute([
				':name' => $name,
				':phone' => $phone,
				':previous_name' => (string) $previousName,
			]);
		}
	}

	// Remove a therapist by ID.
	public function delete(int $id): void
	{
		$stmt = $this->db->prepare('DELETE FROM therapists WHERE id = :id');
		$stmt->execute([':id' => $id]);
	}
}
