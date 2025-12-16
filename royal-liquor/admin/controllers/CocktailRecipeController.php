<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/CocktailRecipeService.php';

class CocktailRecipeController extends BaseController
{
    public function __construct(
        private CocktailRecipeService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->success('Cocktail recipe created', $this->service->create($data), 201);
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->success('Fetched cocktail recipes', $this->service->getAll($limit, $offset));
    }

    public function getById(int $id): array
    {
        return $this->success('Fetched cocktail recipe', $this->service->getById($id));
    }

    public function update(int $id, array $data): array
    {
        return $this->success('Cocktail recipe updated', $this->service->update($id, $data));
    }

    public function delete(int $id): array
    {
        $this->service->delete($id);
        return $this->success('Cocktail recipe deleted');
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->success('Search results', $this->service->search($query, $limit, $offset));
    }

    public function count(): array
    {
        return $this->success('Count retrieved', ['count' => $this->service->count()]);
    }
}
