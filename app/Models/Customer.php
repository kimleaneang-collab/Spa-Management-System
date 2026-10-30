<?php
declare(strict_types=1);

final class Customer
{
	// Store the database connection used by this model.
	public function __construct(private PDO $db)
	{
	}

	// Return customer records with visit, spending, and membership details.
	public function all(?int $limit = 100): array
	{
		$query = 'SELECT c.id, c.full_name,
					    c.phone,
				    (SELECT COUNT(*) FROM appointments a WHERE a.customer_id = c.id) AS visits,
				    (SELECT MAX(a.appointment_date) FROM appointments a WHERE a.customer_id = c.id) AS last_visit,
				    (SELECT COALESCE(SUM(i.total_amount), 0)
				     FROM invoices i
				     WHERE i.customer_id = c.id AND i.status IN (\'paid\', \'partial\')) AS total_spent,
				    COALESCE(ml.name, \'None\') AS membership
			 FROM customers c
			 LEFT JOIN memberships m
				 ON m.customer_id = c.id
					AND m.status = \'active\'
					AND m.start_date <= CURDATE()
					AND m.expiry_date >= CURDATE()
			 LEFT JOIN membership_levels ml ON ml.id = m.level_id
			 ORDER BY c.id DESC';
		if ($limit !== null) {
			$query .= ' LIMIT :limit';
		}

		$stmt = $this->db->prepare($query);
		if ($limit !== null) {
			$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
		}
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Find the therapist for this user and return that therapist's customers.
	public function forTherapist(int $userId, string $userName): array
	{
		$therapistId = $this->therapistIdForUser($userId, $userName);

		if ($therapistId === false) {
			return ['therapist_id' => null, 'customers' => []];
		}

		$customersQuery = $this->db->prepare(
			'SELECT c.id, c.full_name, c.phone,
					COUNT(a.id) AS visits,
					MAX(a.appointment_date) AS last_visit
			 FROM appointments a
			 INNER JOIN customers c ON c.id = a.customer_id
			 WHERE a.therapist_id = :therapist_id
			 GROUP BY c.id, c.full_name, c.phone
			 ORDER BY last_visit DESC, c.full_name ASC'
		);
		$customersQuery->execute([':therapist_id' => (int) $therapistId]);

		return [
			'therapist_id' => (int) $therapistId,
			'customers' => $customersQuery->fetchAll(PDO::FETCH_ASSOC),
		];
	}

	// Return the appointment history for the therapist linked to this user.
	public function treatmentHistoryForTherapist(int $userId, string $userName): array
	{
		$therapistId = $this->therapistIdForUser($userId, $userName);

		if ($therapistId === false) {
			return ['therapist_id' => null, 'history' => []];
		}

		$historyQuery = $this->db->prepare(
			'SELECT a.id, a.appointment_date, a.start_time, a.status,
					c.full_name AS customer, s.name AS service
			 FROM appointments a
			 INNER JOIN customers c ON c.id = a.customer_id
			 INNER JOIN services s ON s.id = a.service_id
			 WHERE a.therapist_id = :therapist_id
			 ORDER BY a.appointment_date DESC, a.start_time DESC'
		);
		$historyQuery->execute([':therapist_id' => (int) $therapistId]);

		return [
			'therapist_id' => (int) $therapistId,
			'history' => $historyQuery->fetchAll(PDO::FETCH_ASSOC),
		];
	}

	// Match a user to a therapist by linked account, employee record, or name.
	private function therapistIdForUser(int $userId, string $userName): int|false
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

		return $therapistId === false ? false : (int) $therapistId;
	}

	// Find one customer by ID, or return null if the customer does not exist.
	public function find(int $id): ?array
	{
		$stmt = $this->db->prepare('SELECT id, full_name, phone, email, gender, date_of_birth, address, notes FROM customers WHERE id = :id');
		$stmt->execute([':id' => $id]);
		$customer = $stmt->fetch(PDO::FETCH_ASSOC);

		return $customer !== false ? $customer : null;
	}

	// Count all appointments scheduled for today.
	public function appointmentsToday(): int
	{
		$stmt = $this->db->query('SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()');

		return (int) $stmt->fetchColumn();
	}

	// Count today's appointments that are currently in progress.
	public function checkedInToday(): int
	{
		$stmt = $this->db->query(
			'SELECT COUNT(*) FROM appointments
			 WHERE appointment_date = CURDATE() AND status = \'in_progress\''
		);

		return (int) $stmt->fetchColumn();
	}

	// Return the total number of customers.
	public function count(): int
	{
		$stmt = $this->db->query('SELECT COUNT(*) FROM customers');

		return (int) $stmt->fetchColumn();
	}

	// Add a customer and use null for optional details that were left blank.
	public function create(
		string $name,
		string $phone,
		string $email,
		string $gender = '',
		string $dateOfBirth = '',
		string $notes = ''
	): void {
		$stmt = $this->db->prepare(
			'INSERT INTO customers (customer_code, full_name, phone, email, gender, date_of_birth, notes, status, created_at)
			 VALUES (:customer_code, :full_name, :phone, :email, :gender, :date_of_birth, :notes, :status, NOW())'
		);

		$stmt->execute([
			':customer_code' => 'CUST-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
			':full_name' => $name,
			':phone' => $phone,
			':email' => $email,
			':gender' => $gender !== '' ? $gender : null,
			':date_of_birth' => $dateOfBirth !== '' ? $dateOfBirth : null,
			':notes' => $notes !== '' ? $notes : null,
			':status' => 'active',
		]);
	}

	// Update the supplied customer details and leave omitted optional fields unchanged.
	public function update(int $id, string $name, string $phone, string $email, ?string $gender = null, ?string $dateOfBirth = null, ?string $notes = null): void
	{
		$fields = 'full_name = :full_name, phone = :phone, email = :email';
		$parameters = [
			':id' => $id,
			':full_name' => $name,
			':phone' => $phone,
			':email' => $email,
		];
		foreach ([
			'gender' => $gender,
			'date_of_birth' => $dateOfBirth,
			'notes' => $notes,
		] as $column => $value) {
			if ($value !== null) {
				$fields .= ', ' . $column . ' = :' . $column;
				$parameters[':' . $column] = $value !== '' ? $value : null;
			}
		}

		$stmt = $this->db->prepare('UPDATE customers SET ' . $fields . ' WHERE id = :id');
		$stmt->execute($parameters);
	}

	// Remove a customer by ID.
	public function delete(int $id): void
	{
		$stmt = $this->db->prepare('DELETE FROM customers WHERE id = :id');
		$stmt->execute([':id' => $id]);
	}
}
