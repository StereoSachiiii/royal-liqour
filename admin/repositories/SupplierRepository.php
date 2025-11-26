<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/SupplierModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class SupplierRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM suppliers 
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
            "SELECT * FROM suppliers 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?SupplierModel
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM suppliers WHERE id = :id AND is_active = TRUE AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByIdAdmin(int $id): ?SupplierModel
    {
        $stmt = $this->pdo->prepare("SELECT * FROM suppliers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByName(string $name): ?SupplierModel
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM suppliers WHERE name = :name AND deleted_at IS NULL"
        );
        $stmt->execute([':name' => $name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM suppliers 
             WHERE (name ILIKE :query OR email ILIKE :query OR phone ILIKE :query OR address ILIKE :query) 
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
            "SELECT COUNT(*) FROM suppliers WHERE is_active = TRUE AND deleted_at IS NULL"
        );
        return (int)$stmt->fetchColumn();
    }

    public function countAll(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM suppliers");
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): SupplierModel
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO suppliers (name, email, phone, address) 
             VALUES (:name, :email, :phone, :address) 
             RETURNING *"
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':address' => $data['address'] ?? null
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create supplier');
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?SupplierModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['name', 'email', 'phone', 'address'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($sets)) return null;

        $sql = "UPDATE suppliers SET " . implode(', ', $sets) . ", updated_at = NOW() 
                WHERE id = :id AND deleted_at IS NULL RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE suppliers SET deleted_at = NOW(), is_active = FALSE, updated_at = NOW() 
             WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function restore(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE suppliers SET deleted_at = NULL, is_active = TRUE, updated_at = NOW() 
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function hardDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM suppliers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapToModel(array $row): SupplierModel
    {
        return new SupplierModel(
            id: (int)$row['id'],
            name: $row['name'],
            email: $row['email'],
            phone: $row['phone'],
            address: $row['address'],
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