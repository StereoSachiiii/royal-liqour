<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/WarehouseModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class WarehouseRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM warehouses 
             WHERE is_active = TRUE AND deleted_at IS NULL 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM warehouses 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?WarehouseModel
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM warehouses 
             WHERE id = :id AND is_active = TRUE AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByIdAdmin(int $id): ?WarehouseModel
    {
        $stmt = $this->pdo->prepare("SELECT * FROM warehouses WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByName(string $name): ?WarehouseModel
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM warehouses 
             WHERE name = :name AND is_active = TRUE AND deleted_at IS NULL"
        );
        $stmt->execute([':name' => $name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM warehouses 
             WHERE (name ILIKE :query OR address ILIKE :query OR phone ILIKE :query) 
             AND is_active = TRUE AND deleted_at IS NULL 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->execute([
            ':query' => "%$query%",
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function count(): int
    {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*) FROM warehouses 
             WHERE is_active = TRUE AND deleted_at IS NULL"
        );
        return (int)$stmt->fetchColumn();
    }

    public function countAll(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM warehouses");
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): WarehouseModel
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO warehouses (name, address, phone, image_url) 
             VALUES (:name, :address, :phone, :image_url) 
             RETURNING *"
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':address' => $data['address'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':image_url' => $data['image_url'] ?? null
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create warehouse');
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?WarehouseModel
    {
        $stmt = $this->pdo->prepare(
            "UPDATE warehouses SET 
                name = :name, address = :address, phone = :phone, 
                image_url = :image_url, updated_at = NOW() 
             WHERE id = :id RETURNING *"
        );
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':address' => $data['address'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':image_url' => $data['image_url'] ?? null
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function partialUpdate(int $id, array $data): ?WarehouseModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['name', 'address', 'phone', 'image_url'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($sets)) return null;

        $sql = "UPDATE warehouses SET " . implode(', ', $sets) . ", updated_at = NOW() 
                WHERE id = :id RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE warehouses SET deleted_at = NOW(), is_active = FALSE, updated_at = NOW() 
             WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function restore(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE warehouses SET deleted_at = NULL, is_active = TRUE, updated_at = NOW() 
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function hardDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM warehouses WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapToModel(array $row): WarehouseModel
    {
        return new WarehouseModel(
            id: (int)$row['id'],
            name: $row['name'],
            address: $row['address'],
            phone: $row['phone'],
            image_url: $row['image_url'],
            is_active: (bool)$row['is_active'],
            created_at: $row['created_at'],
            updated_at: $row['updated_at'],
            deleted_at: $row['deleted_at']
        );
    }

    private function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}