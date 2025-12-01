<?php
require_once __DIR__ . '/../controllers/FlavorProfileController.php';
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

$controller = new FlavorProfileController();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (!isset($_GET['product_id']) && !isset($_GET['count'])) {
                // Usually only admins or specific views need to list ALL flavor profiles
                AuthMiddleware::requireAdmin(); 
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $result = $controller->getAll($limit, $offset);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            if (isset($_GET['product_id'])) {
                $id = (int)$_GET['product_id'];
                if ($id <= 0) throw new Exception("Product ID required", 400);
                $result = $controller->getByProductId($id);
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
            AuthMiddleware::requireAdmin(); // Creation restricted to Admin
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('flavor_profile_create', 5, 60);

            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'PUT':
            AuthMiddleware::requireAdmin(); // Update restricted to Admin
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('flavor_profile_update', 5, 60);
            $body = json_decode(file_get_contents('php://input'), true) ?? [];

            if (!isset($_GET['product_id']) && !isset($body['product_id'])) throw new Exception("Product ID required", 400);

            $id = $_GET['product_id'] ?? $body['product_id'] ?? null;
            $result = $controller->update(intval($id), $body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'DELETE':
            AuthMiddleware::requireAdmin(); // Deletion restricted to Admin
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('flavor_profile_delete', 5, 60);

            if (!isset($_GET['product_id'])) throw new Exception("Product ID required", 400);

            $id = (int)$_GET['product_id'];
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