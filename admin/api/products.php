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
            if (isset($_GET['enriched']) && $_GET['enriched'] === 'true') {
        $limit  = (int)($_GET['limit'] ?? 24);
        $offset = (int)($_GET['offset'] ?? 0);

        $search      = trim($_GET['search'] ?? '');
        $categoryId  = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $minPrice    = !empty($_GET['min_price']) ? (int)$_GET['min_price'] : null;
        $maxPrice    = !empty($_GET['max_price']) ? (int)$_GET['max_price'] : null;
        $sort        = $_GET['sort'] ?? 'newest'; // newest, price_asc, price_desc, name_asc, name_desc, popularity

        $result = $controller->shopAllEnriched(
            limit: $limit,
            offset: $offset,
            search: $search,
            categoryId: $categoryId,
            minPrice: $minPrice,
            maxPrice: $maxPrice,
            sort: $sort
        );
        JsonMiddleware::sendResponse($result, 200);
        break;
    }
            if (!isset($_GET['id']) && !isset($_GET['search']) && !isset($_GET['count']) && !isset($_GET['includeInactive']) && !isset($_GET['enriched']) &&!isset($_GET['top'])) {
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
            if (isset($_GET['enriched']) && $_GET['enriched'] === 'true') {
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    $result = $controller->getAllEnriched($limit, $offset);
    JsonMiddleware::sendResponse($result, 200);
    break;
}

if (isset($_GET['top'])) {
    $limit = (int)($_GET['top'] ?? 10);
    $result = $controller->getTopSellers($limit);
    JsonMiddleware::sendResponse($result, 200);
    break;
}

if (isset($_GET['search']) && isset($_GET['enriched']) && $_GET['enriched'] === 'true') {
    $query = (string)$_GET['search'];
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    $result = $controller->searchEnriched($query, $limit, $offset);
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
                $query = (string)$_GET['search'] ??'';
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
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            if (!isset($_GET['id']) && !isset($body['id'])) throw new Exception("Product ID required!", 400);

            $id = $_GET['id'] ?? $body['id'] ?? null;
            if (!$id) throw new Exception("Product ID required", 400);
            $id = (int)$id;

            
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