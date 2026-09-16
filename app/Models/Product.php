<?php
declare(strict_types=1);

final class Product
{
	public function __construct(private PDO $db)
	{
	}

	public function all(): array
	{
		$stmt = $this->db->query(
			'SELECT id, product_code, name, category, unit, selling_price, stock_quantity, reorder_level, status, image_path
			 FROM products
			 ORDER BY id DESC'
		);

		return array_map(static function (array $product): array {
			$stock = (float) $product['stock_quantity'];
			$reorder = (float) $product['reorder_level'];
			$status = $product['status'] === 'inactive'
				? ['label' => 'Inactive', 'class' => 'status-cancelled']
				: ($stock <= $reorder
					? ['label' => 'Low Stock', 'class' => 'status-cancelled']
					: ['label' => 'In Stock', 'class' => 'status-confirmed']);

			$defaultImages = [
				'massage oil' => 'massage oil.png',
				'essential oil' => 'Essentail-oil.png',
				'facial cream' => 'Facial Cream.png',
				'body lotion' => 'bodylotion.png',
				'shampoo' => 'shampoo.png',
				'towels' => 'Tower.png',
				'candles' => 'Candles Candles.png',
				'spa equipment' => 'Spa Equipment.jpg',
			];
			$productName = strtolower(trim((string) $product['name']));
			$storedImage = trim((string) ($product['image_path'] ?? ''));
			$imagePath = $storedImage !== ''
				? $storedImage
				: 'public/uploads/' . ($defaultImages[$productName] ?? 'login-bg.jpg');

			return [
				'id' => (int) $product['id'],
				'name' => $product['name'],
				'price' => '$' . number_format((float) $product['selling_price'], 2),
				'status' => $status['label'],
				'status_class' => $status['class'],
				'image' => APP_URL . '/' . ltrim($imagePath, '/'),
			];
		}, $stmt->fetchAll(PDO::FETCH_ASSOC));
	}

	public function create(
		string $code,
		string $name,
		string $category,
		string $unit,
		float $costPrice,
		float $sellingPrice,
		float $stockQuantity,
		float $reorderLevel,
		?string $expiryDate,
		string $imagePath
	): void {
		$stmt = $this->db->prepare(
			'INSERT INTO products
			 (product_code, name, category, unit, cost_price, selling_price, stock_quantity, reorder_level, expiry_date, status, image_path)
			 VALUES (:code, :name, :category, :unit, :cost_price, :selling_price, :stock_quantity, :reorder_level, :expiry_date, :status, :image_path)'
		);
		$stmt->execute([
			':code' => $code,
			':name' => $name,
			':category' => $category !== '' ? $category : null,
			':unit' => $unit !== '' ? $unit : 'pcs',
			':cost_price' => $costPrice,
			':selling_price' => $sellingPrice,
			':stock_quantity' => $stockQuantity,
			':reorder_level' => $reorderLevel,
			':expiry_date' => $expiryDate !== '' ? $expiryDate : null,
			':status' => 'active',
			':image_path' => $imagePath,
		]);
	}
}
