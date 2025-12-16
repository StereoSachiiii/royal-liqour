<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/RecipeIngredientService.php';

class RecipeIngredientController extends BaseController
{
    public function __construct(
        private RecipeIngredientService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->success('Recipe ingredient created', $this->service->create($data), 201);
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->success('Fetched recipe ingredients', $this->service->getAll($limit, $offset));
    }

    public function searchByProduct(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->success('Fetched recipe ingredients', $this->service->searchByProduct($query, $limit, $offset));
    }

    public function getById(int $id): array
    {
        return $this->success('Fetched recipe ingredient', $this->service->getById($id));
    }

    public function getByRecipe(int $recipeId): array
    {
        return $this->success('Fetched recipe ingredients', $this->service->getByRecipe($recipeId));
    }

    public function update(int $id, array $data): array
    {
        return $this->success('Recipe ingredient updated', $this->service->update($id, $data));
    }

    public function delete(int $id): array
    {
        $this->service->delete($id);
        return $this->success('Recipe ingredient deleted');
    }

    public function count(): array
    {
        return $this->success('Count retrieved', ['count' => $this->service->count()]);
    }

    public function bulkCreate(array $ingredients): array
    {
        return $this->success('Recipe ingredients created', $this->service->bulkCreate($ingredients), 201);
    }

    public function deleteByRecipe(int $recipeId): array
    {
        $this->service->deleteByRecipe($recipeId);
        return $this->success('Recipe ingredients deleted');
    }
}
