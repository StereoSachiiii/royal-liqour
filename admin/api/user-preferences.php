<?php 
require_once __DIR__ . '/../controllers/UserPreferenceController.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/JsonMiddleware.php';

$method = $_SERVER['REQUEST_METHOD'];
$controller = new UserPreferenceController();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                RateLimitMiddleware::check('user_pref_getById', 10, 60);
                $result = $controller->getById((int)$_GET['id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            if (isset($_GET['user_id'])) {
                RateLimitMiddleware::check('user_pref_getByUserId', 10, 60);
                $result = $controller->getByUserId((int)$_GET['user_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            break;

        case 'POST':
            RateLimitMiddleware::check('user_pref_create', 5, 60);
            $body = json_decode(file_get_contents('php://input'), true);
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, 201);
            break;

        case 'PUT':
            if (!isset($_GET['id'])) throw new Exception("ID required for update", 400);
            RateLimitMiddleware::check('user_pref_update', 10, 60);
            $body = json_decode(file_get_contents('php://input'), true);
            $result = $controller->update((int)$_GET['id'], $body);
            JsonMiddleware::sendResponse($result, 200);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) throw new Exception("ID required for delete", 400);
            RateLimitMiddleware::check('user_pref_delete', 5, 60);
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