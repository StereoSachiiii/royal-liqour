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