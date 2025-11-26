<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class ProductRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM products 
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
            "SELECT * FROM products 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?ProductModel
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM products WHERE id = :id AND is_active = TRUE AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByIdAdmin(int $id): ?ProductModel
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getBySlug(string $slug): ?ProductModel
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM products WHERE slug = :slug AND is_active = TRUE AND deleted_at IS NULL"
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM products 
             WHERE (name ILIKE :query OR description ILIKE :query) 
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
            "SELECT COUNT(*) FROM products WHERE is_active = TRUE AND deleted_at IS NULL"
        );
        return (int)$stmt->fetchColumn();
    }

    public function countAll(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products");
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): ProductModel
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO products (name, slug, description, price_cents, image_url, category_id, supplier_id) 
             VALUES (:name, :slug, :description, :price_cents, :image_url, :category_id, :supplier_id) 
             RETURNING *"
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':slug' => $data['slug'] ?? null,
            ':description' => $data['description'] ?? null,
            ':price_cents' => $data['price_cents'],
            ':image_url' => $data['image_url'] ?? null,
            ':category_id' => $data['category_id'],
            ':supplier_id' => $data['supplier_id'] ?? null
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create product');
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?ProductModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['name', 'slug', 'description', 'price_cents', 'image_url', 'category_id', 'supplier_id'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($sets)) return null;

        $sql = "UPDATE products SET " . implode(', ', $sets) . ", updated_at = NOW() 
                WHERE id = :id AND deleted_at IS NULL RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET deleted_at = NOW(), is_active = FALSE, updated_at = NOW() 
             WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function restore(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET deleted_at = NULL, is_active = TRUE, updated_at = NOW() 
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function hardDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapToModel(array $row): ProductModel
    {
        return new ProductModel(
            id: (int)$row['id'],
            name: $row['name'],
            slug: $row['slug'],
            description: $row['description'],
            price_cents: (int)$row['price_cents'],
            image_url: $row['image_url'],
            category_id: (int)$row['category_id'],
            supplier_id: $row['supplier_id'] ? (int)$row['supplier_id'] : null,
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