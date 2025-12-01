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
                        switch ($method) {
                            case 'GET':
                                if (isset($_GET['available']) && isset($_GET['product_id'])) {
                                    AuthMiddleware::requireAdmin();
                            $productId = (int)$_GET['product_id'];
                            $result = $controller->getAvailableStock($productId);
                            JsonMiddleware::sendResponse($result, 200);
                            break;
                                }    
                                
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
                                
                                $body = json_decode(file_get_contents('php://input'), true) ?? [];
                                
                                // Check for order operations (action + order_id)
                                if (isset($body['action']) && isset($body['order_id'])) {
                                    RateLimitMiddleware::check('stock_order_action', 10, 60);
                                    
                                    $orderId = (int)$body['order_id'];
                                    if ($orderId <= 0) throw new Exception("Valid order ID required", 400);
                                    
                                    $action = $body['action'];
                                    
                                    switch ($action) {
                                        case 'reserve':
                                            $result = $controller->reserveStock($orderId);
                                            break;
                                        case 'confirm':
                                            $result = $controller->confirmPayment($orderId);
                                            break;
                                        case 'cancel':
                                            $result = $controller->cancelOrder($orderId);
                                            break;
                                        case 'refund':
                                            $result = $controller->refundOrder($orderId);
                                            break;
                                        default:
                                            throw new Exception("Invalid action. Use: reserve, confirm, cancel, refund", 400);
                                    }
                                                   
                JsonMiddleware::sendResponse($result, $result['code']);
                break;
            }
            
            // After the order operations, before admin-only stock creation:
            
// Manual stock adjustment (admin only)
if (isset($body['adjust'])) {
    AuthMiddleware::requireAdmin();
    RateLimitMiddleware::check('stock_adjust', 10, 60);
    
    if (!isset($body['product_id']) || !isset($body['warehouse_id']) || !isset($body['adjustment'])) {
        throw new Exception("product_id, warehouse_id, and adjustment required", 400);
    }
    

    $productId = (int)$body['product_id'];
    $warehouseId = (int)$body['warehouse_id'];
    $adjustment = (int)$body['adjustment'];
    $reason = $body['reason'] ?? null;
    
    $result = $controller->adjustStock($productId, $warehouseId, $adjustment, $reason);
    JsonMiddleware::sendResponse($result, $result['code']);
    break;
}
            // Regular stock creation (admin only)
            AuthMiddleware::requireAdmin();
            RateLimitMiddleware::check('stock_create', 5, 60);
            
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'PUT':
            AuthMiddleware::requireAdmin();
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
            AuthMiddleware::requireAdmin();
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