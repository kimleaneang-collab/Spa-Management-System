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
			'SELECT id, product_code, name, category, unit, cost_price, selling_price,
					stock_quantity, reorder_level, status, image_path
			 FROM products
			 ORDER BY id DESC'
		);

		return array_map(static function (array $product): array {
			$stock = (float) $product['stock_quantity'];
			$reorder = (float) $product['reorder_level'];
			$status = match (true) {
				$product['status'] !== 'active' => ['label' => 'Inactive', 'class' => 'inactive'],
				$stock <= $reorder * 0.5 => ['label' => 'Low Stock', 'class' => 'low-stock'],
				$stock <= $reorder => ['label' => 'Reorder Soon', 'class' => 'reorder-soon'],
				default => ['label' => 'In Stock', 'class' => 'in-stock'],
			};

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
				'code' => (string) $product['product_code'],
				'name' => $product['name'],
				'category' => trim((string) ($product['category'] ?? '')) !== ''
					? (string) $product['category']
					: 'Uncategorized',
				'unit' => (string) $product['unit'],
				'price' => '$' . number_format((float) $product['selling_price'], 2),
				'unit_cost' => '$' . number_format((float) $product['cost_price'], 2),
				'stock_quantity' => $stock,
				'stock_display' => rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.') . ' ' . (string) $product['unit'],
				'reorder_level' => $reorder,
				'reorder_display' => rtrim(rtrim(number_format($reorder, 3, '.', ''), '0'), '.') . ' ' . (string) $product['unit'],
				'stock_percent' => $reorder > 0 ? min(100, max(0, ($stock / $reorder) * 100)) : ($stock > 0 ? 100 : 0),
				'active' => $product['status'] === 'active',
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
