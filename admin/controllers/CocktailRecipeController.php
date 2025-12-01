<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/CocktailRecipeRepository.php';
require_once __DIR__ . '/../validators/CocktailRecipeValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';

class CocktailRecipeController
{
    private CocktailRecipeRepository $repo;

    public function __construct()
    {
        $this->repo = new CocktailRecipeRepository();
    }

    private function success(string $message, $data = [], int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code
        ];
    }

    private function error(Throwable $e): array
    {
        $code = $e instanceof ValidationException ? 400 : ($e->getCode() ?: 500);
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'data'    => null,
            'code'    => $code
        ];
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $recipes = $this->repo->getAll($limit, $offset);
        $data = array_map(fn($r) => $r->toArray(), $recipes);
        return $this->success('Cocktail recipes retrieved', ['items' => $data, 'total' => $this->repo->count()]);
    }

    public function getById(int $id): array
    {
        $recipe = $this->repo->getById($id);
        if (!$recipe) throw new NotFoundException('Cocktail recipe not found');
        return $this->success('Recipe retrieved', $recipe->toArray());
    }

    public function create(array $data): array
    {
      //  CocktailRecipeValidator::validateCreate($data);
        $recipe = $this->repo->create($data);
        return $this->success('Cocktail recipe created', $recipe->toArray(), 201);
    }

    public function update(int $id, array $data): array
    {
       // CocktailRecipeValidator::validateUpdate($data);

        $existing = $this->repo->getById($id);
        if (!$existing) throw new NotFoundException('Cocktail recipe not found');

        $updated = $this->repo->update($id, $data);
        if (!$updated) throw new Exception('Failed to update recipe');

        return $this->success('Cocktail recipe updated', $updated->toArray());
    }

    public function delete(int $id, bool $hard = false): array
    {
        $exists = $this->repo->getById($id, true);
        if (!$exists) throw new NotFoundException('Cocktail recipe not found');

        $success = $hard ? $this->repo->hardDelete($id) : $this->repo->softDelete($id);

        if (!$success) throw new Exception('Failed to delete recipe');

        return $this->success($hard ? 'Recipe permanently deleted' : 'Recipe deactivated');
    }

    public function count(): array
    {
        return $this->success('Count retrieved', $this->repo->count());
    }
}