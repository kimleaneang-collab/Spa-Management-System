<?php
declare(strict_types=1);

final class Customer
{
	public function __construct(private PDO $db)
	{
	}

	public function all(int $limit = 20): array
	{
		$stmt = $this->db->prepare(
				'SELECT c.id, c.full_name,
					    c.phone,
				    (SELECT COUNT(*) FROM appointments a WHERE a.customer_id = c.id) AS visits,
				    (SELECT COALESCE(SUM(i.total_amount), 0)
				     FROM invoices i
				     WHERE i.customer_id = c.id AND i.status IN (\'paid\', \'partial\')) AS total_spent,
				    COALESCE(ml.name, \'None\') AS membership
			 FROM customers c
			 LEFT JOIN memberships m ON m.customer_id = c.id AND m.status = \'active\'
			 LEFT JOIN membership_levels ml ON ml.id = m.level_id
			 ORDER BY c.id DESC
			 LIMIT :limit'
		);
		$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function find(int $id): ?array
	{
		$stmt = $this->db->prepare('SELECT id, full_name, phone, email FROM customers WHERE id = :id');
		$stmt->execute([':id' => $id]);
		$customer = $stmt->fetch(PDO::FETCH_ASSOC);

		return $customer !== false ? $customer : null;
	}

	public function create(
		string $name,
		string $phone,
		string $email
	): void {
		$stmt = $this->db->prepare(
			'INSERT INTO customers (customer_code, full_name, phone, email, status, created_at)
			 VALUES (:customer_code, :full_name, :phone, :email, :status, NOW())'
		);

		$stmt->execute([
			':customer_code' => 'CUST-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
			':full_name' => $name,
			':phone' => $phone,
			':email' => $email,
			':status' => 'active',
		]);
	}

	public function update(int $id, string $name, string $phone, string $email): void
	{
		$stmt = $this->db->prepare(
			'UPDATE customers SET full_name = :full_name, phone = :phone, email = :email WHERE id = :id'
		);
		$stmt->execute([
			':id' => $id,
			':full_name' => $name,
			':phone' => $phone,
			':email' => $email,
		]);
	}

	public function delete(int $id): void
	{
		$stmt = $this->db->prepare('DELETE FROM customers WHERE id = :id');
		$stmt->execute([':id' => $id]);
	}
}
