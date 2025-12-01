<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/OrderItemModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class OrderItemRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM order_items ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?OrderItemModel
    {
        $stmt = $this->pdo->prepare("SELECT * FROM order_items WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByOrder(int $orderId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $orderId]);
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

public function update(int $id, array $data): OrderItemModel
{
    // Only allow updating quantity and warehouse_id
    $allowedFields = [];
    $params = [':id' => $id];
    
    if (isset($data['quantity'])) {
        $allowedFields[] = 'quantity = :quantity';
        $params[':quantity'] = $data['quantity'];
    }
    
    if (isset($data['warehouse_id'])) {
        $allowedFields[] = 'warehouse_id = :warehouse_id';
        $params[':warehouse_id'] = $data['warehouse_id'];
    }
    
    if (empty($allowedFields)) {
        throw new \InvalidArgumentException('No valid fields to update');
    }
    
    $sql = "UPDATE order_items SET " . implode(', ', $allowedFields) . " WHERE id = :id RETURNING *";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new NotFoundException("Order item with ID $id not found");
    }
    
    return $this->mapToModel($row);
}
    public function count(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM order_items");
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): OrderItemModel
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO order_items (order_id, product_id, product_name, product_image_url, 
              price_cents, quantity) 
             VALUES (:order_id, :product_id, :product_name, :product_image_url, 
              :price_cents, :quantity) 
             RETURNING *"
        );
        $stmt->execute([
            ':order_id' => $data['order_id'],
            ':product_id' => $data['product_id'],
            ':product_name' => $data['product_name'],
            ':product_image_url' => $data['product_image_url'] ?? null,
            ':price_cents' => $data['price_cents'],
            ':quantity' => $data['quantity']
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create order item');
        return $this->mapToModel($row);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM order_items WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

private function mapToModel(array $row): OrderItemModel
{
    return new OrderItemModel(
        id: (int)$row['id'],
        order_id: (int)$row['order_id'],
        product_id: (int)$row['product_id'],
        product_name: $row['product_name'],
        product_image_url: $row['product_image_url'],
        price_cents: (int)$row['price_cents'],
        quantity: (int)$row['quantity'],
        warehouse_id: isset($row['warehouse_id']) ? (int)$row['warehouse_id'] : null, // ADD THIS
        created_at: $row['created_at']
    );
}

    private function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}