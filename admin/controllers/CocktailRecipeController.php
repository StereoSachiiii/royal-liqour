<?
class CocktailRecipeController {
    private CocktailRecipeRepository $repository;
    private CocktailRecipeValidator $validator;
    private RecipeIngredientRepository $ingredientRepo;

    public function __construct() {
        $this->repository = new CocktailRecipeRepository();
        $this->validator = new CocktailRecipeValidator();
        $this->ingredientRepo = new RecipeIngredientRepository();
    }

    private function successResponse(string $message, $data, int $code = 200): array {
        return ['success' => true, 'message' => $message, 'data' => $data, 'code' => $code, 'context' => []];
    }

    private function errorResponse(Exception $error): array {
        error_log("[" . date('Y-m-d H:i:s') . "] CocktailRecipeController: " . $error->getMessage());
        return ['success' => false, 'message' => $error->getMessage(), 'code' => 500, 'context' => []];
    }

    private function handleRequest(callable $callback): array {
        try { return $callback(); } catch(Exception $ex) { return $this->errorResponse($ex); }
    }

    public function create($data): array {
        return $this->handleRequest(function() use ($data) {
            CocktailRecipeValidator::validateCreate($data);
            $result = $this->repository->create($data);
            return $this->successResponse("Cocktail recipe created successfully.", $result->toArray(), 201);
        });
    }

    public function getById(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $recipe = $this->repository->getById($id);
            if (!$recipe) throw new NotFoundException("Recipe not found.");
            
            $ingredients = $this->ingredientRepo->getByRecipeId($id);
            $recipeData = $recipe->toArray();
            $recipeData['ingredients'] = array_map(fn($i) => $i->toArray(), $ingredients);
            
            return $this->successResponse("Recipe retrieved successfully.", $recipeData);
        });
    }

    public function getByDifficulty(string $difficulty): array {
        return $this->handleRequest(function() use ($difficulty) {
            $recipes = $this->repository->getByDifficulty($difficulty);
            return $this->successResponse("Recipes retrieved successfully.", array_map(fn($r) => $r->toArray(), $recipes));
        });
    }

    public function searchByName(string $name): array {
        return $this->handleRequest(function() use ($name) {
            $recipes = $this->repository->searchByName($name);
            return $this->successResponse("Recipes retrieved successfully.", array_map(fn($r) => $r->toArray(), $recipes));
        });
    }

    public function update(int $id, array $data): array {
        return $this->handleRequest(function() use ($id, $data) {
            CocktailRecipeValidator::validateUpdate($data);
            $updated = $this->repository->update($id, $data);
            if (!$updated) throw new NotFoundException("Recipe not found.");
            return $this->successResponse("Recipe updated successfully.", $updated->toArray());
        });
    }

    public function delete(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $result = $this->repository->delete($id);
            if (!$result) throw new NotFoundException("Recipe not found.");
            return $this->successResponse("Recipe deleted successfully.", ['id' => $id]);
        });
    }

    public function getAll(): array {
        return $this->handleRequest(function() {
            $recipes = $this->repository->getAll();
            return $this->successResponse("Recipes retrieved successfully.", array_map(fn($r) => $r->toArray(), $recipes));
        });
    }

    public function getAllPaginated(int $limit, int $offset): array {
        return $this->handleRequest(function() use ($limit, $offset) {
            CocktailRecipeValidator::paginationParams($limit, $offset);
            $recipes = $this->repository->getAllPaginated($limit, $offset);
            return $this->successResponse("Recipes retrieved successfully.", array_map(fn($r) => $r->toArray(), $recipes));
        });
    }
}
?>