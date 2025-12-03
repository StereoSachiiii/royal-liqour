<?php
require_once __DIR__ . '/../controllers/CategoryController.php';
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

$controller = new CategoryController();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // ────── 1. Single category by ID (with optional enriched) ──────
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                if ($id <= 0) throw new Exception("Invalid category ID", 400);

                if (isset($_GET['enriched']) && $_GET['enriched'] === 'true') {
                    $result = $controller->getByIdEnriched($id);
                } else {
                    $result = $controller->getById($id);
                }
                JsonMiddleware::sendResponse($result, $result['code'] ?? 200);
                break;
            }

            // ────── 2. Products in a category (highest priority) ──────
            if (isset($_GET['category_id'])) {
                $categoryId = (int)$_GET['category_id'];
                if ($categoryId <= 0) throw new Exception("Invalid category ID", 400);

                $limit  = (int)($_GET['limit'] ?? 100);
                $offset = (int)($_GET['offset'] ?? 0);

                $result = $controller->getProductsByCategoryIdEnriched($categoryId, $limit, $offset);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // ────── 3. List all categories (enriched or not) ──────
            if (!isset($_GET['id']) && !isset($_GET['category_id']) && !isset($_GET['search']) && !isset($_GET['name']) && !isset($_GET['count'])) {
                $limit  = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);

                if (isset($_GET['enriched']) && $_GET['enriched'] === 'true') {
                    $result = $controller->getAllEnriched($limit, $offset);
                } elseif (isset($_GET['includeInactive']) && $_GET['includeInactive'] === 'true') {
                    AuthMiddleware::requireAdmin();
                    $result = $controller->getAllIncludingInactive($limit, $offset);
                } else {
                    $result = $controller->getAll($limit, $offset);
                }
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // ────── 4. Search categories ──────
            if (isset($_GET['search'])) {
                $query  = trim($_GET['search']);
                $limit  = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);

                if (isset($_GET['enriched']) && $_GET['enriched'] === 'true') {
                    $result = $controller->searchEnriched($query, $limit, $offset);
                } else {
                    $result = $controller->search($query, $limit, $offset);
                }
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            // ────── 5. By name ──────
            if (isset($_GET['name'])) {
                $result = $controller->getByName((string)$_GET['name']);
                JsonMiddleware::sendResponse($result, $result['code'] ?? 200);
                break;
            }

            // ────── 6. Count ──────
            if (isset($_GET['count']) && $_GET['count'] === 'true') {
                if (isset($_GET['includeInactive']) && $_GET['includeInactive'] === 'true') {
                    AuthMiddleware::requireAdmin();
                    $result = $controller->countAll();
                } else {
                    $result = $controller->count();
                }
                JsonMiddleware::sendResponse($result, 200);
                break;
            }

            throw new Exception("Invalid GET parameters", 400);

        // ────── POST / PUT / DELETE (unchanged) ──────
        case 'POST':
            AuthMiddleware::requireAdmin();
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('category_create', 5, 60);
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'PUT':
            AuthMiddleware::requireAdmin();
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('category_update', 5, 60);
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            if (!isset($_GET['id']) && !isset($body['id'])) throw new Exception("Category ID required", 400);
            $id = $_GET['id'] ?? $body['id'];
            $result = $controller->update((int)$id, $body);
            JsonMiddleware::sendResponse($result, $result['code']);
            break;

        case 'DELETE':
            AuthMiddleware::requireAdmin();
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('category_delete', 5, 60);
            if (!isset($_GET['id'])) throw new Exception("Category ID required", 400);
            $id = (int)$_GET['id'];
            $hard = isset($_GET['hard']) && $_GET['hard'] === 'true';
            $result = $hard ? $controller->hardDelete($id) : $controller->delete($id);
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