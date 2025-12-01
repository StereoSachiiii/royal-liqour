<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/StockModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class StockRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }
    // Add to StockRepository class:

public function reserveStock(int $orderId): void
{
    try {
        $this->pdo->beginTransaction();
        
        // Get order items
        $itemsStmt = $this->pdo->prepare(
            "SELECT product_id, quantity FROM order_items WHERE order_id = :order_id"
        );
        $itemsStmt->execute([':order_id' => $orderId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($items as $item) {
            // Find warehouse with available stock
            $stockStmt = $this->pdo->prepare(
                "SELECT id, warehouse_id FROM stock 
                 WHERE product_id = :product_id 
                 AND (quantity - reserved) >= :quantity
                 ORDER BY (quantity - reserved) DESC
                 LIMIT 1
                 FOR UPDATE"
            );
            $stockStmt->execute([
                ':product_id' => $item['product_id'],
                ':quantity' => $item['quantity']
            ]);
            $stock = $stockStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stock) {
                throw new DatabaseException("Insufficient stock for product {$item['product_id']}");
            }
            
            // Reserve the stock
            $reserveStmt = $this->pdo->prepare(
                "UPDATE stock SET reserved = reserved + :quantity, updated_at = NOW()
                 WHERE id = :stock_id"
            );
            $reserveStmt->execute([
                ':quantity' => $item['quantity'],
                ':stock_id' => $stock['id']
            ]);
            
            // Update order_item with warehouse_id
            $updateItemStmt = $this->pdo->prepare(
                "UPDATE order_items SET warehouse_id = :warehouse_id
                 WHERE order_id = :order_id AND product_id = :product_id"
            );
            $updateItemStmt->execute([
                ':warehouse_id' => $stock['warehouse_id'],
                ':order_id' => $orderId,
                ':product_id' => $item['product_id']
            ]);
        }
        
        $this->pdo->commit();
        
    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}

public function confirmPayment(int $orderId): void
{
    try {
        $this->pdo->beginTransaction();
        
        $stmt = $this->pdo->prepare(
            "UPDATE stock s
             SET quantity = quantity - oi.quantity,
                 reserved = reserved - oi.quantity,
                 updated_at = NOW()
             FROM order_items oi
             WHERE oi.order_id = :order_id
             AND s.product_id = oi.product_id
             AND s.warehouse_id = oi.warehouse_id"
        );
        $stmt->execute([':order_id' => $orderId]);
        
        $this->pdo->commit();
        
    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}

public function cancelOrder(int $orderId): void
{
    try {
        $this->pdo->beginTransaction();
        
        $stmt = $this->pdo->prepare(
            "UPDATE stock s
             SET reserved = reserved - oi.quantity,
                 updated_at = NOW()
             FROM order_items oi
             WHERE oi.order_id = :order_id
             AND s.product_id = oi.product_id
             AND s.warehouse_id = oi.warehouse_id"
        );
        $stmt->execute([':order_id' => $orderId]);
        
        $this->pdo->commit();
        
    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}

public function refundOrder(int $orderId): void
{
    try {
        $this->pdo->beginTransaction();
        
        $stmt = $this->pdo->prepare(
            "UPDATE stock s
             SET quantity = quantity + oi.quantity,
                 updated_at = NOW()
             FROM order_items oi
             WHERE oi.order_id = :order_id
             AND s.product_id = oi.product_id
             AND s.warehouse_id = oi.warehouse_id"
        );
        $stmt->execute([':order_id' => $orderId]);
        
        $this->pdo->commit();
        
    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM stock ORDER BY updated_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?StockModel
    {
        $stmt = $this->pdo->prepare("SELECT * FROM stock WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByProductAndWarehouse(int $productId, int $warehouseId): ?StockModel
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM stock WHERE product_id = :product_id AND warehouse_id = :warehouse_id"
        );
        $stmt->execute([
            ':product_id' => $productId,
            ':warehouse_id' => $warehouseId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByProduct(int $productId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM stock WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $productId]);
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getByWarehouse(int $warehouseId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM stock WHERE warehouse_id = :warehouse_id");
        $stmt->execute([':warehouse_id' => $warehouseId]);
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function count(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM stock");
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): StockModel
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO stock (product_id, warehouse_id, quantity, reserved) 
             VALUES (:product_id, :warehouse_id, :quantity, :reserved) 
             RETURNING *"
        );
        $stmt->execute([
            ':product_id' => $data['product_id'],
            ':warehouse_id' => $data['warehouse_id'],
            ':quantity' => $data['quantity'] ?? 0,
            ':reserved' => $data['reserved'] ?? 0
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create stock');
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?StockModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['quantity', 'reserved'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($sets)) return null;

        $sql = "UPDATE stock SET " . implode(', ', $sets) . ", updated_at = NOW() 
                WHERE id = :id RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function updateByProductWarehouse(int $productId, int $warehouseId, array $data): ?StockModel
    {
        $sets = [];
        $params = [':product_id' => $productId, ':warehouse_id' => $warehouseId];

        foreach (['quantity', 'reserved'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($sets)) return null;

        $sql = "UPDATE stock SET " . implode(', ', $sets) . ", updated_at = NOW() 
                WHERE product_id = :product_id AND warehouse_id = :warehouse_id RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM stock WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function deleteByProductWarehouse(int $productId, int $warehouseId): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM stock WHERE product_id = :product_id AND warehouse_id = :warehouse_id"
        );
        $stmt->execute([
            ':product_id' => $productId,
            ':warehouse_id' => $warehouseId
        ]);
        return $stmt->rowCount() > 0;
    }

    public function getAvailableStockByProduct(int $productId): int
{
    $stmt = $this->pdo->prepare(
        "SELECT COALESCE(SUM(quantity - reserved), 0) as available 
         FROM stock 
         WHERE product_id = :product_id"
    );
    $stmt->execute([':product_id' => $productId]);
    return (int)$stmt->fetchColumn();
}
    private function mapToModel(array $row): StockModel
    {
        return new StockModel(
            id: (int)$row['id'],
            product_id: (int)$row['product_id'],
            warehouse_id: (int)$row['warehouse_id'],
            quantity: (int)$row['quantity'],
            reserved: (int)$row['reserved'],
            created_at: $row['created_at'],
            updated_at: $row['updated_at']
        );
    }

    private function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}