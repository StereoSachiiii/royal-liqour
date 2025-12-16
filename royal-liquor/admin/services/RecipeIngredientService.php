<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/RecipeIngredientRepository.php';
require_once __DIR__ . '/../validators/RecipeIngredientValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/UnauthorizedException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class RecipeIngredientService
{
    public function __construct(
        private RecipeIngredientRepository $repo,
    ) {}

    public function create(array $data): array
    {
        RecipeIngredientValidator::validateCreate($data);
        $ingredient = $this->repo->create($data);
        return $ingredient->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $ingredients = $this->repo->getAll($limit, $offset);
        return array_map(fn($i) => $i->toArray(), $ingredients);
    }

    public function searchByProduct(string $query, int $limit = 50, int $offset = 0): array
    {
        $ingredients = $this->repo->searchByProduct($query, $limit, $offset);
        return array_map(fn($i) => $i->toArray(), $ingredients);
    }

    public function getById(int $id): array
    {
        $ingredient = $this->repo->getById($id);
        if (!$ingredient) {
            throw new NotFoundException('Recipe ingredient not found');
        }
        return $ingredient->toArray();
    }

    public function getByRecipe(int $recipeId): array
    {
        $ingredients = $this->repo->getByRecipe($recipeId);
        return array_map(fn($i) => $i->toArray(), $ingredients);
    }

    public function update(int $id, array $data): array
    {
        RecipeIngredientValidator::validateUpdate($data);
        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new NotFoundException('Recipe ingredient not found');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Recipe ingredient not found');
        }
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function bulkCreate(array $ingredients): array
    {
        RecipeIngredientValidator::validateBulkCreate($ingredients);
        $results = $this->repo->bulkCreate($ingredients);
        return array_map(fn($r) => $r->toArray(), $results);
    }

    public function deleteByRecipe(int $recipeId): void
    {
        $this->repo->deleteByRecipe($recipeId);
    }
}
