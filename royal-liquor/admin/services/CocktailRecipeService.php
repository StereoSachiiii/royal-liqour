<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/CocktailRecipeRepository.php';
require_once __DIR__ . '/../validators/CocktailRecipeValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';

class CocktailRecipeService
{
    public function __construct(
        private CocktailRecipeRepository $repo,
    ) {}

    public function create(array $data): array
    {
        CocktailRecipeValidator::validateCreate($data);
        $recipe = $this->repo->create($data);
        return $recipe->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $recipes = $this->repo->getAll($limit, $offset);
        $data = array_map(fn($r) => $r->toArray(), $recipes);
        return [
            'items' => $data,
            'total' => $this->repo->count()
        ];
    }

    public function getById(int $id): array
    {
        $recipe = $this->repo->getById($id);
        if (!$recipe) {
            throw new NotFoundException('Cocktail recipe not found');
        }
        return $recipe->toArray();
    }

    public function update(int $id, array $data): array
    {
        CocktailRecipeValidator::validateUpdate($data);
        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new NotFoundException('Cocktail recipe not found');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Cocktail recipe not found');
        }
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $recipes = $this->repo->search($query, $limit, $offset);
        return array_map(fn($r) => $r->toArray(), $recipes);
    }

    public function count(): int
    {
        return $this->repo->count();
    }
}
