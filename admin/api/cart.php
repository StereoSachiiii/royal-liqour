<?php
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/JsonMiddleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$controller = new CartController();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (!isset($_GET['id']) && !isset($_GET['user_id']) && !isset($_GET['session_id']) && !isset($_GET['count'])) {
                AuthMiddleware::requireAdmin();
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $result = $controller->getAll($limit, $offset);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                if ($id <= 0) throw new Exception("Cart ID required", 400);
                $result = $controller->getById($id);
                JsonMiddleware::sendResponse($result, $result['code']);
                break;
            }
            //get by user id
            if (isset($_GET['user_id'])) {
                $userId = (int)$_GET['user_id'];
                $result = $controller->getActiveByUser($userId);
                JsonMiddleware::sendResponse($result, $result['code']);
                break;
            }

            if (isset($_GET['session_id'])) {
                $sessionId = (string)$_GET['session_id'];
                $result = $controller->getActiveBySession($sessionId);
                JsonMiddleware::sendResponse($result, $result['code']);
                break;
            }

            if (isset($_GET['count']) && $_GET['count'] === 'true') {
                AuthMiddleware::requireAdmin();
                $result = $controller->count();
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            throw new Exception("Invalid GET parameters", 400);

        case 'POST':
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('cart_create', 10, 60);

            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'PUT':
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('cart_update', 10, 60);
            $body = json_decode(json: file_get_contents('php://input'), associative: true) ?? [];

            if (!isset($_GET['id'])&&!isset($body['id'])) throw new Exception("Cart ID required", 400);

            $id = (int)($_GET['id'] ?? $body['id']);
            $result = $controller->update($id, $body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'DELETE':
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('cart_delete', 5, 60);

            if (!isset($_GET['id'])) throw new Exception("Cart ID required", 400);

            $id = (int)$_GET['id'];
            $result = $controller->delete($id);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        default:
            throw new Exception("Method not allowed", 405);
    }

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    JsonMiddleware::sendResponse([
        'success' => false,
        'message' => $e->getMessage(),
        'data'    => null,
        'code'    => $code,
        'context' => []
    ], $code);
}