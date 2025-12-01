<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/CocktailRecipeModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class CocktailRecipeRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function getAll(int $limit = 50, int $offset = 0, bool $includeDeleted = false): array
    {
        $sql = "SELECT * FROM cocktail_recipes";
        if (!$includeDeleted) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id, bool $includeDeleted = false): ?CocktailRecipeModel
    {
        $sql = "SELECT * FROM cocktail_recipes WHERE id = :id";
        if (!$includeDeleted) {
            $sql .= " AND deleted_at IS NULL";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToModel($row) : null;
    }

    public function create(array $data): CocktailRecipeModel
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO cocktail_recipes (
                name, description, instructions, image_url, difficulty,
                preparation_time, serves, is_active
            ) VALUES (
                :name, :description, :instructions, :image_url, :difficulty,
                :preparation_time, :serves, :is_active
            ) RETURNING *
        ");

        $stmt->execute([
            ':name'             => $data['name'],
            ':description'      => $data['description'] ?? null,
            ':instructions'     => $data['instructions'],
            ':image_url'        => $data['image_url'] ?? null,
            ':difficulty'       => $data['difficulty'] ?? 'easy',
            ':preparation_time' => $data['preparation_time'] ?? null,
            ':serves'           => $data['serves'] ?? 1,
            ':is_active'        => $data['is_active'] ?? true
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create cocktail recipe');

        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?CocktailRecipeModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach ([
            'name', 'description', 'instructions', 'image_url', 'difficulty',
            'preparation_time', 'serves', 'is_active'
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($sets)) return $this->getById($id);

        $sql = "UPDATE cocktail_recipes SET " . implode(', ', $sets) . ", updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL RETURNING *";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToModel($row) : null;
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE cocktail_recipes 
            SET deleted_at = NOW(), is_active = FALSE, updated_at = NOW()
            WHERE id = :id AND deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function hardDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM cocktail_recipes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(bool $includeDeleted = false): int
    {
        $sql = "SELECT COUNT(*) FROM cocktail_recipes";
        if (!$includeDeleted) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        $stmt = $this->pdo->query($sql);
        return (int)$stmt->fetchColumn();
    }

    private function mapToModel(array $row): CocktailRecipeModel
    {
        return new CocktailRecipeModel(
            id: (int)$row['id'],
            name: $row['name'],
            description: $row['description'],
            instructions: $row['instructions'],
            image_url: $row['image_url'],
            difficulty: $row['difficulty'],
            preparation_time: $row['preparation_time'] !== null ? (int)$row['preparation_time'] : null,
            serves: (int)$row['serves'],
            is_active: (bool)$row['is_active'],
            created_at: $row['created_at'],
            updated_at: $row['updated_at'],
            deleted_at: $row['deleted_at']
        );
    }

    private function mapToModels(array $rows): array
    {
        return array_map($this->mapToModel(...), $rows);
    }
}