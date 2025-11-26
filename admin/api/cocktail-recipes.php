<?php
require_once __DIR__ . '/../controllers/CocktailRecipeController.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/JsonMiddleware.php';

$method = $_SERVER['REQUEST_METHOD'];
$controller = new CocktailRecipeController();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                RateLimitMiddleware::check('cocktail_getById', 10, 60);
                $result = $controller->getById((int)$_GET['id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            if (isset($_GET['difficulty'])) {
                RateLimitMiddleware::check('cocktail_getByDifficulty', 10, 60);
                $result = $controller->getByDifficulty($_GET['difficulty']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            if (isset($_GET['search'])) {
                RateLimitMiddleware::check('cocktail_search', 10, 60);
                $result = $controller->searchByName($_GET['search']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            if (isset($_GET['limit']) && isset($_GET['offset'])) {
                RateLimitMiddleware::check('cocktail_getAllPaginated', 5, 60);
                $result = $controller->getAllPaginated((int)$_GET['limit'], (int)$_GET['offset']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            RateLimitMiddleware::check('cocktail_getAll', 5, 60);
            $result = $controller->getAll();
            JsonMiddleware::sendResponse($result, 200);
            break;

        case 'POST':
            RateLimitMiddleware::check('cocktail_create', 5, 60);
            $body = json_decode(file_get_contents('php://input'), true);
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, 201);
            break;

        case 'PUT':
            if (!isset($_GET['id'])) throw new Exception("ID required for update", 400);
            RateLimitMiddleware::check('cocktail_update', 10, 60);
            $body = json_decode(file_get_contents('php://input'), true);
            $result = $controller->update((int)$_GET['id'], $body);
            JsonMiddleware::sendResponse($result, 200);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) throw new Exception("ID required for delete", 400);
            RateLimitMiddleware::check('cocktail_delete', 5, 60);
            $result = $controller->delete((int)$_GET['id']);
            JsonMiddleware::sendResponse($result, 200);
            break;

        default:
            throw new Exception("Method not allowed", 405);
    }
} catch (Exception $e) {
    JsonMiddleware::sendResponse(['success' => false, 'message' => $e->getMessage(), 'code' => $e->getCode() ?: 500], $e->getCode() ?: 500);
}

?>