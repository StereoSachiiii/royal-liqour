<?php
require_once __DIR__ . '/../controllers/RecipeIngredientController.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/JsonMiddleware.php';

$method = $_SERVER['REQUEST_METHOD'];
$controller = new RecipeIngredientController();

try {
    switch ($method) {
        case 'GET':
            // Get single ingredient by ID
            if (isset($_GET['id']) && !isset($_GET['action'])) {
                RateLimitMiddleware::check('recipe_ingredient_getById', 30, 60);
                $result = $controller->getById((int)$_GET['id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Get all ingredients for a recipe
            if (isset($_GET['recipe_id']) && !isset($_GET['action'])) {
                RateLimitMiddleware::check('recipe_ingredient_getByRecipeId', 30, 60);
                $result = $controller->getByRecipeId((int)$_GET['recipe_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Get required ingredients only
            if (isset($_GET['recipe_id']) && isset($_GET['action']) && $_GET['action'] === 'required') {
                RateLimitMiddleware::check('recipe_ingredient_getRequired', 30, 60);
                $result = $controller->getRequiredByRecipeId((int)$_GET['recipe_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Get recipes using a specific product
            if (isset($_GET['product_id'])) {
                RateLimitMiddleware::check('recipe_ingredient_getByProduct', 30, 60);
                $result = $controller->getByProductId((int)$_GET['product_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Get recipe cost
            if (isset($_GET['recipe_id']) && isset($_GET['action']) && $_GET['action'] === 'cost') {
                RateLimitMiddleware::check('recipe_ingredient_getCost', 30, 60);
                $includeOptional = isset($_GET['include_optional']) ? (bool)$_GET['include_optional'] : false;
                $result = $controller->getRecipeCost((int)$_GET['recipe_id'], $includeOptional);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Get low stock ingredients
            if (isset($_GET['recipe_id']) && isset($_GET['action']) && $_GET['action'] === 'low_stock') {
                RateLimitMiddleware::check('recipe_ingredient_getLowStock', 30, 60);
                $threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 10;
                $result = $controller->getLowStockIngredients((int)$_GET['recipe_id'], $threshold);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Check if recipe-product combination exists
            if (isset($_GET['recipe_id']) && isset($_GET['product_id']) && isset($_GET['action']) && $_GET['action'] === 'exists') {
                RateLimitMiddleware::check('recipe_ingredient_checkExists', 30, 60);
                $result = $controller->checkExists((int)$_GET['recipe_id'], (int)$_GET['product_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Get ingredient count for a recipe
            if (isset($_GET['recipe_id']) && isset($_GET['action']) && $_GET['action'] === 'count') {
                RateLimitMiddleware::check('recipe_ingredient_getCount', 30, 60);
                $result = $controller->getCount((int)$_GET['recipe_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Search by product name
            if (isset($_GET['search'])) {
                RateLimitMiddleware::check('recipe_ingredient_search', 30, 60);
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                $result = $controller->searchByProduct($_GET['search'], $limit, $offset);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Get all ingredients (paginated)
            RateLimitMiddleware::check('recipe_ingredient_getAll', 30, 60);
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $result = $controller->getAll($limit, $offset);
            JsonMiddleware::sendResponse($result, 200);
            break;

        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true);
            throw new Exception("Not implemented", 501);
            
            // Bulk create ingredients for a recipe
            if (isset($_GET['recipe_id']) && isset($_GET['action']) && $_GET['action'] === 'bulk') {
                RateLimitMiddleware::check('recipe_ingredient_createBulk', 5, 60);
                $ingredients = $body['ingredients'] ?? [];
                $result = $controller->createBulk((int)$_GET['recipe_id'], $ingredients);
                JsonMiddleware::sendResponse($result, 201);
                break;
            }

            // Replace all ingredients for a recipe
            if (isset($_GET['recipe_id']) && isset($_GET['action']) && $_GET['action'] === 'replace') {
                RateLimitMiddleware::check('recipe_ingredient_replace', 5, 60);
                $ingredients = $body['ingredients'] ?? [];
                $result = $controller->replaceRecipeIngredients((int)$_GET['recipe_id'], $ingredients);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Create single ingredient
            RateLimitMiddleware::check('recipe_ingredient_create', 10, 60);
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, 201);
            break;

        case 'PUT':
        case 'PATCH':
            $body = json_decode(file_get_contents('php://input'), true);
            if (!isset($_GET['id'])&&!isset($body['id'])) {
                throw new Exception("ID required for update", 400);
            }
            $id = $_GET['id'] ??  $body['id']  ; 
            $id = intval($id);
            RateLimitMiddleware::check('recipe_ingredient_update', 10, 60);
            $result = $controller->update($id, $body);
            JsonMiddleware::sendResponse($result, 200);
            break;

        case 'DELETE':
            // Delete all ingredients for a recipe
            if (isset($_GET['recipe_id']) && isset($_GET['action']) && $_GET['action'] === 'all') {
                RateLimitMiddleware::check('recipe_ingredient_deleteAll', 5, 60);
                $result = $controller->deleteByRecipeId((int)$_GET['recipe_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // Delete single ingredient
            if (!isset($_GET['id'])) {
                throw new Exception("ID required for delete", 400);
            }
            RateLimitMiddleware::check('recipe_ingredient_delete', 10, 60);
            $result = $controller->delete((int)$_GET['id']);
            JsonMiddleware::sendResponse($result, 200);
            break;

        default:
            throw new Exception("Method not allowed", 405);
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    JsonMiddleware::sendResponse([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $code
    ], $code);
}
?>