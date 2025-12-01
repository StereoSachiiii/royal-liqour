<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/RecipeIngredientRepository.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/UnauthorizedException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../validators/RecipeIngredientValidator.php';

class RecipeIngredientController
{
    private RecipeIngredientRepository $repo;
    private Session $session;

    public function __construct()
    {
        $this->repo = new RecipeIngredientRepository();
        $this->session = Session::getInstance();
    }

    // ====================================================================
    // RESPONSE HELPERS
    // ====================================================================
    
    private function success(string $message, $data = [], int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
            'context' => []
        ];
    }

    private function logError(Throwable $e, array $context = []): void
    {
        error_log(sprintf(
            "[%s] RecipeIngredientController Error: %s | File: %s:%d | Context: %s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            json_encode($context)
        ));
    }

    private function error(Throwable $e): array
    {
        $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $context = method_exists($e, 'getContext') ? $e->getContext() : [];

        $this->logError($e, $context);

        return [
            'success' => false,
            'message' => $e->getMessage(),
            'code'    => $code,
            'context' => $context
        ];
    }

    private function handle(callable $callback): array
    {
        try {
            return $callback();
        } catch (ValidationException | NotFoundException | UnauthorizedException | DatabaseException $e) {
            return $this->error($e);
        } catch (Throwable $e) {
            return $this->error(new Exception('Unexpected error: ' . $e->getMessage(), 500));
        }
    }

    // ====================================================================
    // PUBLIC - READ OPERATIONS
    // ====================================================================

    /**
     * Get ingredient by ID
     */
    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $ingredient = $this->repo->findById($id);
            if (!$ingredient) {
                throw new NotFoundException('Recipe ingredient not found');
            }
            return $this->success('Ingredient retrieved', $ingredient->toArray());
        });
    }

    /**
     * Get all ingredients for a specific recipe
     */
    public function getByRecipeId(int $recipeId): array
    {
        return $this->handle(function () use ($recipeId) {
            $ingredients = $this->repo->getByRecipeId($recipeId);
            $data = array_map(fn($i) => $i->toArray(), $ingredients);
            return $this->success('Recipe ingredients retrieved', $data);
        });
    }

    /**
     * Get only required ingredients for a recipe
     */
    public function getRequiredByRecipeId(int $recipeId): array
    {
        return $this->handle(function () use ($recipeId) {
            $ingredients = $this->repo->getRequiredByRecipeId($recipeId);
            $data = array_map(fn($i) => $i->toArray(), $ingredients);
            return $this->success('Required ingredients retrieved', $data);
        });
    }

    /**
     * Get all recipes using a specific product
     */
    public function getByProductId(int $productId): array
    {
        return $this->handle(function () use ($productId) {
            $ingredients = $this->repo->getByProductId($productId);
            $data = array_map(fn($i) => $i->toArray(), $ingredients);
            return $this->success('Product usage in recipes retrieved', $data);
        });
    }

    /**
     * Calculate total cost for a recipe
     */
    public function getRecipeCost(int $recipeId, bool $includeOptional = false): array
    {
        return $this->handle(function () use ($recipeId, $includeOptional) {
            $costCents = $this->repo->calculateRecipeCost($recipeId, $includeOptional);
            $costFormatted = number_format($costCents / 100, 2);
            
            return $this->success('Recipe cost calculated', [
                'recipe_id' => $recipeId,
                'cost_cents' => $costCents,
                'cost_formatted' => $costFormatted,
                'include_optional' => $includeOptional
            ]);
        });
    }

    /**
     * Get ingredients with low stock
     */
    public function getLowStockIngredients(int $recipeId, int $threshold = 10): array
    {
        return $this->handle(function () use ($recipeId, $threshold) {
            $ingredients = $this->repo->getIngredientsWithLowStock($recipeId, $threshold);
            $data = array_map(fn($i) => $i->toArray(), $ingredients);
            
            return $this->success('Low stock ingredients retrieved', [
                'recipe_id' => $recipeId,
                'threshold' => $threshold,
                'low_stock_count' => count($data),
                'ingredients' => $data
            ]);
        });
    }

    /**
     * Search ingredients by product name
     */
    public function searchByProduct(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            if (empty(trim($query))) {
                $ingredients = $this->repo->getAll($limit, $offset);
            } else {
                $ingredients = $this->repo->searchByProductName(trim($query), $limit, $offset);
            }
            
            $data = array_map(fn($i) => $i->toArray(), $ingredients);
            return $this->success('Search results retrieved', $data);
        });
    }

    /**
     * Get all ingredients with pagination
     */
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $ingredients = $this->repo->getAll($limit, $offset);
            $data = array_map(fn($i) => $i->toArray(), $ingredients);
            return $this->success('Ingredients retrieved', $data);
        });
    }

    // ====================================================================
    // ADMIN ONLY - WRITE OPERATIONS
    // ====================================================================

    /**
     * Create a single ingredient
     */
    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {


            RecipeIngredientValidator::validateCreate($data);

            // Check if this combination already exists
            if ($this->repo->exists((int)$data['recipe_id'], (int)$data['product_id'])) {
                throw new ValidationException('This product is already an ingredient in this recipe', [
                    'errors' => ['product_id' => 'Already exists in recipe']
                ]);
            }

            $ingredient = $this->repo->create(
                (int)$data['recipe_id'],
                (int)$data['product_id'],
                (float)$data['quantity'],
                $data['unit'],
                (bool)($data['is_optional'] ?? false)
            );

            return $this->success('Ingredient added successfully', $ingredient->toArray(), 201);
        });
    }

    /**
     * Create multiple ingredients at once
     */
    public function createBulk(int $recipeId, array $ingredients): array
    {
        return $this->handle(function () use ($recipeId, $ingredients) {
            if (!$this->session->isAdmin()) {
                throw new UnauthorizedException('Admin access required');
            }

            RecipeIngredientValidator::validateBulkCreate($ingredients);

            // Add recipe_id to each ingredient
            $ingredientsWithRecipeId = array_map(function ($ing) use ($recipeId) {
                $ing['recipe_id'] = $recipeId;
                return $ing;
            }, $ingredients);

            $created = $this->repo->createBulk($recipeId, $ingredientsWithRecipeId);
            $data = array_map(fn($i) => $i->toArray(), $created);

            return $this->success('Ingredients added successfully', [
                'recipe_id' => $recipeId,
                'count' => count($data),
                'ingredients' => $data
            ], 201);
        });
    }

    /**
     * Update an ingredient
     */
    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {


          //  RecipeIngredientValidator::validateUpdate($data);

            $updates = [];
            if (isset($data['quantity'])) $updates['quantity'] = (float)$data['quantity'];
            if (isset($data['unit'])) $updates['unit'] = $data['unit'];
            if (isset($data['is_optional'])) $updates['is_optional'] = (bool)$data['is_optional'];

            if (empty($updates)) {
                throw new ValidationException('No valid fields to update');
            }

            $ingredient = $this->repo->update($id, $updates);
            return $this->success('Ingredient updated successfully', $ingredient->toArray());
        });
    }

    /**
     * Delete a single ingredient
     */
    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            if (!$this->session->isAdmin()) {
                throw new UnauthorizedException('Admin access required');
            }

            $affected = $this->repo->delete($id);
            if ($affected === 0) {
                throw new NotFoundException('Recipe ingredient not found');
            }

            return $this->success('Ingredient deleted successfully');
        });
    }

    /**
     * Delete all ingredients for a recipe
     */
    public function deleteByRecipeId(int $recipeId): array
    {
        return $this->handle(function () use ($recipeId) {
            if (!$this->session->isAdmin()) {
                throw new UnauthorizedException('Admin access required');
            }

            $affected = $this->repo->deleteByRecipeId($recipeId);
            
            return $this->success('Recipe ingredients deleted successfully', [
                'recipe_id' => $recipeId,
                'deleted_count' => $affected
            ]);
        });
    }

    /**
     * Replace all ingredients for a recipe
     */
    public function replaceRecipeIngredients(int $recipeId, array $newIngredients): array
    {
        return $this->handle(function () use ($recipeId, $newIngredients) {
            if (!$this->session->isAdmin()) {
                throw new UnauthorizedException('Admin access required');
            }

            RecipeIngredientValidator::validateBulkCreate($newIngredients);

            $created = $this->repo->replaceRecipeIngredients($recipeId, $newIngredients);
            $data = array_map(fn($i) => $i->toArray(), $created);

            return $this->success('Recipe ingredients replaced successfully', [
                'recipe_id' => $recipeId,
                'count' => count($data),
                'ingredients' => $data
            ]);
        });
    }

    // ====================================================================
    // UTILITY METHODS
    // ====================================================================

    /**
     * Check if a recipe-product combination exists
     */
    public function checkExists(int $recipeId, int $productId): array
    {
        return $this->handle(function () use ($recipeId, $productId) {
            $exists = $this->repo->exists($recipeId, $productId);
            return $this->success('Existence checked', [
                'recipe_id' => $recipeId,
                'product_id' => $productId,
                'exists' => $exists
            ]);
        });
    }

    /**
     * Get ingredient count for a recipe
     */
    public function getCount(int $recipeId): array
    {
        return $this->handle(function () use ($recipeId) {
            $count = $this->repo->countByRecipeId($recipeId);
            return $this->success('Ingredient count retrieved', [
                'recipe_id' => $recipeId,
                'count' => $count
            ]);
        });
    }
}