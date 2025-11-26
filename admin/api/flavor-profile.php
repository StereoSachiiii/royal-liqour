<?php
require_once __DIR__ . '/../controllers/FlavorProfileController.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/JsonMiddleware.php';

$method = $_SERVER['REQUEST_METHOD'];
$controller = new FlavorProfileController();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                RateLimitMiddleware::check('flavor_getById', 10, 60);
                $result = $controller->getById((int)$_GET['id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            if (isset($_GET['product_id'])) {
                RateLimitMiddleware::check('flavor_getByProductId', 10, 60);
                $result = $controller->getByProductId((int)$_GET['product_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            if (isset($_GET['search_flavors'])) {
                RateLimitMiddleware::check('flavor_search', 10, 60);
                $criteria = [
                    'min_sweetness' => $_GET['min_sweetness'] ?? 0,
                    'max_sweetness' => $_GET['max_sweetness'] ?? 10,
                    'min_strength' => $_GET['min_strength'] ?? 0,
                    'max_strength' => $_GET['max_strength'] ?? 10
                ];
                $result = $controller->searchByFlavors($criteria);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            RateLimitMiddleware::check('flavor_getAll', 5, 60);
            $result = $controller->getAll();
            JsonMiddleware::sendResponse($result, 200);
            break;

        case 'POST':
            RateLimitMiddleware::check('flavor_create', 5, 60);
            $body = json_decode(file_get_contents('php://input'), true);
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, 201);
            break;

        case 'PUT':
            if (!isset($_GET['id'])) throw new Exception("ID required for update", 400);
            RateLimitMiddleware::check('flavor_update', 10, 60);
            $body = json_decode(file_get_contents('php://input'), true);
            $result = $controller->update((int)$_GET['id'], $body);
            JsonMiddleware::sendResponse($result, 200);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) throw new Exception("ID required for delete", 400);
            RateLimitMiddleware::check('flavor_delete', 5, 60);
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