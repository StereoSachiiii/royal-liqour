<?php
class RecipeIngredientController {
    private RecipeIngredientRepository $repository;
    private RecipeIngredientValidator $validator;

    public function __construct() {
        $this->repository = new RecipeIngredientRepository();
        $this->validator = new RecipeIngredientValidator();
    }

    private function successResponse(string $message, $data, int $code = 200): array {
        return ['success' => true, 'message' => $message, 'data' => $data, 'code' => $code, 'context' => []];
    }

    private function errorResponse(Exception $error): array {
        error_log("[" . date('Y-m-d H:i:s') . "] RecipeIngredientController: " . $error->getMessage());
        return ['success' => false, 'message' => $error->getMessage(), 'code' => 500, 'context' => []];
    }

    private function handleRequest(callable $callback): array {
        try { return $callback(); } catch(Exception $ex) { return $this->errorResponse($ex); }
    }

    public function create($data): array {
        return $this->handleRequest(function() use ($data) {
            RecipeIngredientValidator::validateCreate($data);
            $result = $this->repository->create($data);
            return $this->successResponse("Recipe ingredient created successfully.", $result->toArray(), 201);
        });
    }

    public function getById(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $ingredient = $this->repository->getById($id);
            if (!$ingredient) throw new NotFoundException("Recipe ingredient not found.");
            return $this->successResponse("Recipe ingredient retrieved successfully.", $ingredient->toArray());
        });
    }

    public function getByRecipeId(int $recipeId): array {
        return $this->handleRequest(function() use ($recipeId) {
            $ingredients = $this->repository->getByRecipeId($recipeId);
            return $this->successResponse("Recipe ingredients retrieved successfully.", array_map(fn($i) => $i->toArray(), $ingredients));
        });
    }

    public function update(int $id, array $data): array {
        return $this->handleRequest(function() use ($id, $data) {
            RecipeIngredientValidator::validateUpdate($data);
            $updated = $this->repository->update($id, $data);
            if (!$updated) throw new NotFoundException("Recipe ingredient not found.");
            return $this->successResponse("Recipe ingredient updated successfully.", $updated->toArray());
        });
    }

    public function delete(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $result = $this->repository->delete($id);
            if (!$result) throw new NotFoundException("Recipe ingredient not found.");
            return $this->successResponse("Recipe ingredient deleted successfully.", ['id' => $id]);
        });
    }
}
?>