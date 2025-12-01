<?php
require_once __DIR__ . '/../controllers/PaymentController.php';
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

$controller = new PaymentController();
$method = $_SERVER['REQUEST_METHOD'];

try {
    AuthMiddleware::requireAdmin(); // Assuming payment management is admin-only for minimal CRUD

    switch ($method) {
        case 'GET':
            if (!isset($_GET['id']) && !isset($_GET['order_id']) && !isset($_GET['count'])) {
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $result = $controller->getAll($limit, $offset);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                if ($id <= 0) throw new Exception("Payment ID required", 400);
                $result = $controller->getById($id);
                JsonMiddleware::sendResponse($result, $result['code']);
                break;
            }

            if (isset($_GET['order_id'])) {
                $orderId = (int)$_GET['order_id'];
                $result = $controller->getByOrder($orderId);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            if (isset($_GET['count']) && $_GET['count'] === 'true') {
                $result = $controller->count();
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            throw new Exception("Invalid GET parameters", 400);

        case 'POST':
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('payment_create', 5, 60);

            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'PUT':
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('payment_update', 5, 60);
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            if (!isset($_GET['id']) && !isset($body['id'])) throw new Exception("Payment ID required", 400);

            $id = $_GET['id'] ?? $body['id'];
            $result = $controller->update(intval($id), $body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'DELETE':
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('payment_delete', 5, 60);
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            if (!isset($_GET['id']) && !isset($body['id'])) throw new Exception("Payment ID required", 400);
            if ((isset($_GET['hard']) || isset($body['hard']))&&($_GET['hard'] || $body['hard'])) {
                RateLimitMiddleware::check('payment_hard_delete', 2, 60);
                $result = $controller->hardDelete(intval($id));
                JsonMiddleware::sendResponse($result, $result['code']);
                break;
            }
            $id = $_GET['id'] ?? $body['id'];
            $result = $controller->delete(intval($id));
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