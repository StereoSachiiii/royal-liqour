<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/RecipeIngredientsModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class RecipeIngredientRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    /**
     * Create a new recipe ingredient
     */
    public function create(int $recipeId, int $productId, float $quantity, string $unit, bool $isOptional = false): RecipeIngredientModel
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO recipe_ingredients (recipe_id, product_id, quantity, unit, is_optional)
            VALUES (:recipe_id, :product_id, :quantity, :unit, :is_optional)
            RETURNING *
        ");
        
        $stmt->execute([
            ':recipe_id' => $recipeId,
            ':product_id' => $productId,
            ':quantity' => $quantity,
            ':unit' => $unit,
            ':is_optional' => $isOptional
        ]);

        $row = $stmt->fetch();
        if (!$row) throw new DatabaseException('Failed to create recipe ingredient');
        return $this->mapToModel($row);
    }

    /**
     * Bulk create multiple ingredients for a recipe
     */
    public function createBulk(int $recipeId, array $ingredients): array
    {
        $this->pdo->beginTransaction();
        try {
            $created = [];
            foreach ($ingredients as $ingredient) {
                $created[] = $this->create(
                    $recipeId,
                    $ingredient['product_id'],
                    (float)$ingredient['quantity'],
                    $ingredient['unit'],
                    $ingredient['is_optional'] ?? false
                );
            }
            $this->pdo->commit();
            return $created;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new DatabaseException('Failed to create bulk ingredients: ' . $e->getMessage());
        }
    }

    /**
     * Find ingredient by ID
     */
    public function findById(int $id): ?RecipeIngredientModel
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM recipe_ingredients 
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapToModel($row) : null;
    }

    /**
     * Get all ingredients for a specific recipe
     */
    public function getByRecipeId(int $recipeId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ri.*, 
                   p.name as product_name,
                   p.price_cents as product_price_cents,
                   p.image_url as product_image_url,
                   p.is_active as product_is_active
            FROM recipe_ingredients ri
            JOIN products p ON ri.product_id = p.id
            WHERE ri.recipe_id = :recipe_id
            ORDER BY ri.is_optional ASC, ri.created_at ASC
        ");
        $stmt->execute([':recipe_id' => $recipeId]);

        $ingredients = [];
        while ($row = $stmt->fetch()) {
            $ingredients[] = $this->mapToModel($row);
        }
        return $ingredients;
    }

    /**
     * Get only required (non-optional) ingredients for a recipe
     */
    public function getRequiredByRecipeId(int $recipeId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ri.*, 
                   p.name as product_name,
                   p.price_cents as product_price_cents,
                   p.image_url as product_image_url,
                   p.is_active as product_is_active
            FROM recipe_ingredients ri
            JOIN products p ON ri.product_id = p.id
            WHERE ri.recipe_id = :recipe_id AND ri.is_optional = FALSE
            ORDER BY ri.created_at ASC
        ");
        $stmt->execute([':recipe_id' => $recipeId]);

        $ingredients = [];
        while ($row = $stmt->fetch()) {
            $ingredients[] = $this->mapToModel($row);
        }
        return $ingredients;
    }

    /**
     * Get all recipes that use a specific product
     */
    public function getByProductId(int $productId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ri.*,
                   cr.name as recipe_name,
                   cr.difficulty as recipe_difficulty,
                   cr.is_active as recipe_is_active
            FROM recipe_ingredients ri
            JOIN cocktail_recipes cr ON ri.recipe_id = cr.id
            WHERE ri.product_id = :product_id AND cr.deleted_at IS NULL
            ORDER BY cr.name ASC
        ");
        $stmt->execute([':product_id' => $productId]);

        $ingredients = [];
        while ($row = $stmt->fetch()) {
            $ingredients[] = $this->mapToModel($row);
        }
        return $ingredients;
    }

    /**
     * Update an ingredient
     */
    public function update(int $id, array $data): RecipeIngredientModel
{
    $sets   = [];
    $params = [':id' => $id];
    $types  = []; // This is the key!

    $allowedFields = ['quantity', 'unit', 'is_optional'];

    foreach ($allowedFields as $field) {
        if (array_key_exists($field, $data)) {
            $value = $data[$field];

            if ($field === 'is_optional') {
                // Force boolean – handles "", null, "null", 0, "0", false, etc.
                $boolValue = $value === '' || $value === null || $value === 'null' 
                    ? false 
                    : filter_var($value, FILTER_VALIDATE_BOOLEAN);

                $sets[] = "$field = :$field";
                $params[":$field"] = $boolValue;
                $types[":$field"] = PDO::PARAM_BOOL; // CRITICAL: Tell PDO it's a boolean
                continue;
            }

            // Optional: allow quantity to be null
            if ($field === 'quantity' && ($value === '' || $value === null)) {
                $value = null;
            }

            $sets[] = "$field = :$field";
            $params[":$field"] = $value;
            // quantity and unit use default binding (string/float)
        }
    }

    if (empty($sets)) {
        throw new DatabaseException('No valid fields to update');
    }

    $sql = "UPDATE recipe_ingredients SET " . implode(', ', $sets) . "
            WHERE id = :id
            RETURNING *";

    $stmt = $this->pdo->prepare($sql);

    // Bind parameters with explicit types
    foreach ($params as $param => $value) {
        $type = $types[$param] ?? PDO::PARAM_STR;
        $stmt->bindValue($param, $value, $type);
    }

    $stmt->execute();

    $row = $stmt->fetch();
    if (!$row) {
        throw new NotFoundException('Recipe ingredient not found after update');
    }

    return $this->mapToModel($row);
}

    /**
     * Check if a recipe-product combination exists
     */
    public function exists(int $recipeId, int $productId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1 FROM recipe_ingredients 
            WHERE recipe_id = :recipe_id AND product_id = :product_id
            LIMIT 1
        ");
        $stmt->execute([
            ':recipe_id' => $recipeId,
            ':product_id' => $productId
        ]);
        return (bool)$stmt->fetch();
    }

    /**
     * Get ingredient count for a recipe
     */
    public function countByRecipeId(int $recipeId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM recipe_ingredients 
            WHERE recipe_id = :recipe_id
        ");
        $stmt->execute([':recipe_id' => $recipeId]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Calculate total cost of ingredients for a recipe
     */
    public function calculateRecipeCost(int $recipeId, bool $includeOptional = false): int
    {
        $sql = "
            SELECT COALESCE(SUM(p.price_cents * ri.quantity), 0) as total_cost
            FROM recipe_ingredients ri
            JOIN products p ON ri.product_id = p.id
            WHERE ri.recipe_id = :recipe_id
        ";
        
        if (!$includeOptional) {
            $sql .= " AND ri.is_optional = FALSE";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':recipe_id' => $recipeId]);
        $result = $stmt->fetch();
        return (int)$result['total_cost'];
    }

    /**
     * Get ingredients with low or out-of-stock products
     */
    public function getIngredientsWithLowStock(int $recipeId, int $threshold = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ri.*,
                   p.name as product_name,
                   p.price_cents as product_price_cents,
                   COALESCE(SUM(s.quantity - s.reserved), 0) as available_stock
            FROM recipe_ingredients ri
            JOIN products p ON ri.product_id = p.id
            LEFT JOIN stock s ON p.id = s.product_id
            WHERE ri.recipe_id = :recipe_id
            GROUP BY ri.id, p.id, p.name, p.price_cents
            HAVING COALESCE(SUM(s.quantity - s.reserved), 0) < :threshold
            ORDER BY available_stock ASC
        ");
        
        $stmt->execute([
            ':recipe_id' => $recipeId,
            ':threshold' => $threshold
        ]);

        $ingredients = [];
        while ($row = $stmt->fetch()) {
            $ingredients[] = $this->mapToModel($row);
        }
        return $ingredients;
    }

    /**
     * Search ingredients across all recipes
     */
    public function searchByProductName(string $query, int $limit = 50, int $offset = 0): array
    {
        $searchTerm = "%{$query}%";
        $stmt = $this->pdo->prepare("
            SELECT ri.*,
                   p.name as product_name,
                   p.price_cents as product_price_cents,
                   cr.name as recipe_name
            FROM recipe_ingredients ri
            JOIN products p ON ri.product_id = p.id
            JOIN cocktail_recipes cr ON ri.recipe_id = cr.id
            WHERE p.name ILIKE :query AND cr.deleted_at IS NULL
            ORDER BY cr.name ASC, ri.created_at ASC
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':query', $searchTerm);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $ingredients = [];
        while ($row = $stmt->fetch()) {
            $ingredients[] = $this->mapToModel($row);
        }
        return $ingredients;
    }

    /**
     * Get all ingredients with pagination
     */
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ri.*,
                   p.name as product_name,
                   p.price_cents as product_price_cents,
                   cr.name as recipe_name
            FROM recipe_ingredients ri
            JOIN products p ON ri.product_id = p.id
            JOIN cocktail_recipes cr ON ri.recipe_id = cr.id
            WHERE cr.deleted_at IS NULL
            ORDER BY ri.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $ingredients = [];
        while ($row = $stmt->fetch()) {
            $ingredients[] = $this->mapToModel($row);
        }
        return $ingredients;
    }

    /**
     * Replace all ingredients for a recipe (transaction-safe)
     */
    public function replaceRecipeIngredients(int $recipeId, array $newIngredients): array
    {
        $this->pdo->beginTransaction();
        try {
            // Delete existing ingredients
            $this->deleteByRecipeId($recipeId);
            
            // Create new ingredients
            $created = $this->createBulk($recipeId, $newIngredients);
            
            $this->pdo->commit();
            return $created;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new DatabaseException('Failed to replace recipe ingredients: ' . $e->getMessage());
        }
    }

    /**
     * Map database row to model
     */
    private function mapToModel(array $row): RecipeIngredientModel
    {
        return new RecipeIngredientModel(
            id: (int)$row['id'],
            recipeId: (int)$row['recipe_id'],
            productId: (int)$row['product_id'],
            quantity: (float)$row['quantity'],
            unit: $row['unit'],
            isOptional: (bool)$row['is_optional'],
            createdAt: $row['created_at'],
            // Include additional fields if present from JOIN queries
            productName: $row['product_name'] ?? null,
            productPriceCents: isset($row['product_price_cents']) ? (int)$row['product_price_cents'] : null,
            productImageUrl: $row['product_image_url'] ?? null,
            productIsActive: isset($row['product_is_active']) ? (bool)$row['product_is_active'] : null,
            recipeName: $row['recipe_name'] ?? null,
            recipeDifficulty: $row['recipe_difficulty'] ?? null,
            recipeIsActive: isset($row['recipe_is_active']) ? (bool)$row['recipe_is_active'] : null,
            availableStock: isset($row['available_stock']) ? (int)$row['available_stock'] : null
        );
    }
}