<?php
require_once __DIR__ . '/../controllers/AdminViewController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/JsonMiddleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Admin only
AuthMiddleware::requireAdmin();

$controller = new AdminViewController();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method !== 'GET') {
        throw new Exception("Method not allowed", 405);
    }

    RateLimitMiddleware::check('admin_view', 100, 60);

    // Dashboard stats
    if (isset($_GET['dashboard'])) {
        $result = $controller->getDashboardStats();
        JsonMiddleware::sendResponse($result, 200);
        exit;
    }

    // Entity required
    if (!isset($_GET['entity'])) {
        throw new Exception("Entity parameter required", 400);
    }

    $entity = $_GET['entity'];

    // Detail view (modal)
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        if ($id <= 0) throw new Exception("Valid ID required", 400);
        
        $result = $controller->getDetail($entity, $id);
        JsonMiddleware::sendResponse($result, $result['code']);
        exit;
    }

    // List view (table)
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
    $offset = max(0, (int)($_GET['offset'] ?? 0));
    $search = $_GET['search'] ?? null;

    $result = $controller->getList($entity, $limit, $offset, $search);
    JsonMiddleware::sendResponse($result, 200);

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