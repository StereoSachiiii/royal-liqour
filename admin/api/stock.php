<?php
require_once __DIR__ . '/../controllers/StockController.php';
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

$controller = new StockController();
$method = $_SERVER['REQUEST_METHOD'];

try {
    AuthMiddleware::requireAdmin(); // Assuming stock management is admin-only

    switch ($method) {
        case 'GET':
            if (!isset($_GET['id']) && !isset($_GET['product_id']) && !isset($_GET['warehouse_id']) && !isset($_GET['count'])) {
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $result = $controller->getAll($limit, $offset);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                if ($id <= 0) throw new Exception("Stock ID required", 400);
                $result = $controller->getById($id);
                JsonMiddleware::sendResponse($result, $result['code']);
                break;
            }

            if (isset($_GET['product_id']) && isset($_GET['warehouse_id'])) {
                $productId = (int)$_GET['product_id'];
                $warehouseId = (int)$_GET['warehouse_id'];
                $result = $controller->getByProductWarehouse($productId, $warehouseId);
                JsonMiddleware::sendResponse($result, $result['code']);
                break;
            }

            if (isset($_GET['product_id'])) {
                $productId = (int)$_GET['product_id'];
                $result = $controller->getByProduct($productId);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            if (isset($_GET['warehouse_id'])) {
                $warehouseId = (int)$_GET['warehouse_id'];
                $result = $controller->getByWarehouse($warehouseId);
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
            RateLimitMiddleware::check('stock_create', 5, 60);

            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'PUT':
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('stock_update', 5, 60);

            $body = json_decode(file_get_contents('php://input'), true) ?? [];

            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $result = $controller->update($id, $body);
            } elseif (isset($_GET['product_id']) && isset($_GET['warehouse_id'])) {
                $productId = (int)$_GET['product_id'];
                $warehouseId = (int)$_GET['warehouse_id'];
                $result = $controller->updateByProductWarehouse($productId, $warehouseId, $body);
            } else {
                throw new Exception("Stock ID or product/warehouse IDs required", 400);
            }

            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'DELETE':
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('stock_delete', 5, 60);

            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $result = $controller->delete($id);
            } elseif (isset($_GET['product_id']) && isset($_GET['warehouse_id'])) {
                $productId = (int)$_GET['product_id'];
                $warehouseId = (int)$_GET['warehouse_id'];
                $result = $controller->deleteByProductWarehouse($productId, $warehouseId);
            } else {
                throw new Exception("Stock ID or product/warehouse IDs required", 400);
            }

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