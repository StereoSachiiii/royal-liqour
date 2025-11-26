<?php
require_once __DIR__ . '/../controllers/ProductController.php';
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

$controller = new ProductController();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (!isset($_GET['id']) && !isset($_GET['search']) && !isset($_GET['count']) && !isset($_GET['includeInactive'])) {
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $result = $controller->getAll($limit, $offset);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            if (isset($_GET['includeInactive']) && $_GET['includeInactive'] === 'true') {
                AuthMiddleware::requireAdmin();
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $result = $controller->getAllIncludingInactive($limit, $offset);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                if ($id <= 0) throw new Exception("Product ID required", 400);
                $result = $controller->getById($id);
                JsonMiddleware::sendResponse($result, $result['code']);
                break;
            }

            if (isset($_GET['search'])) {
                $query = (string)$_GET['search'];
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $result = $controller->search($query, $limit, $offset);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            if (isset($_GET['count']) && $_GET['count'] === 'true') {
                $includeInactive = isset($_GET['includeInactive']) && $_GET['includeInactive'] === 'true';
                if ($includeInactive) {
                    AuthMiddleware::requireAdmin();
                    $result = $controller->countAll();
                } else {
                    $result = $controller->count();
                }
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            throw new Exception("Invalid GET parameters", 400);

        case 'POST':
            AuthMiddleware::requireAdmin();
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('product_create', 5, 60);

            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'PUT':
            AuthMiddleware::requireAdmin();
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('product_update', 5, 60);

            if (!isset($_GET['id'])) throw new Exception("Product ID required", 400);

            $id = (int)$_GET['id'];
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $result = $controller->update($id, $body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'DELETE':
            AuthMiddleware::requireAdmin();
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('product_delete', 5, 60);

            if (!isset($_GET['id'])) throw new Exception("Product ID required", 400);

            $id = (int)$_GET['id'];
            $hard = isset($_GET['hard']) && $_GET['hard'] === 'true';

            if ($hard) {
                $result = $controller->hardDelete($id);
            } else {
                $result = $controller->delete($id);
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