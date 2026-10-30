<?php
declare(strict_types=1);

final class Service
{
	// Store the database connection used by this model.
	public function __construct(private PDO $db)
	{
	}

	// Return the names and IDs of active service categories.
	public function categories(): array
	{
		$stmt = $this->db->query("SELECT id, name FROM service_categories WHERE status = 'active' ORDER BY name");

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Return service records with formatted values and a fallback image when needed.
	public function all(): array
	{
		$stmt = $this->db->query(
			"SELECT s.id, s.name, s.description, s.duration_minutes, s.price, s.status, s.image_path, c.name AS category
			 FROM services s
			 INNER JOIN service_categories c ON c.id = s.category_id
			 ORDER BY s.id DESC"
		);

		$fallbackImages = [
			'massage' => 'fullbody-massage.png',
			'facial' => 'facial-treatment.png',
			'body' => 'body-scrub.png',
			'wellness' => 'aromatherapy.png',
			'hair' => 'Essentail-oil.png',
			'nail' => 'foot-message.png',
		];

		// Format each service and choose an image from its category if none is saved.
		return array_map(static function (array $service) use ($fallbackImages): array {
			$category = strtolower((string) $service['category']);
			$imagePath = trim((string) ($service['image_path'] ?? ''));
			if ($imagePath === '') {
				$imagePath = 'public/uploads/' . ($fallbackImages[match (true) {
					str_contains($category, 'massage') => 'massage',
					str_contains($category, 'facial') => 'facial',
					str_contains($category, 'body') => 'body',
					str_contains($category, 'wellness') => 'wellness',
					str_contains($category, 'hair') => 'hair',
					str_contains($category, 'nail') => 'nail',
					default => 'wellness',
				}] ?? 'login-bg.jpg');
			}

			return [
				'id' => (int) $service['id'],
				'name' => $service['name'],
				'description' => (string) ($service['description'] ?? ''),
				'duration' => $service['duration_minutes'] . ' mins',
				'price' => '$' . number_format((float) $service['price'], 2),
				'category' => $service['category'],
				'status' => $service['status'] === 'active' ? 'Active' : 'Inactive',
				'thumb' => strtolower(strtok($service['category'], ' ')),
				'image' => APP_URL . '/' . ltrim($imagePath, '/'),
			];
		}, $stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	// Add a service and store optional description and image values as null when blank.
	public function create(int $categoryId, string $code, string $name, int $duration, float $price, string $description, string $imagePath): void
	{
		$stmt = $this->db->prepare(
			"INSERT INTO services (category_id, service_code, name, duration_minutes, price, description, image_path, status)
			 VALUES (:category_id, :code, :name, :duration, :price, :description, :image_path, 'active')"
		);
		$stmt->execute([
			':category_id' => $categoryId,
			':code' => $code,
			':name' => $name,
			':duration' => $duration,
			':price' => $price,
			':description' => $description !== '' ? $description : null,
			':image_path' => $imagePath !== '' ? $imagePath : null,
		]);
	}
}
