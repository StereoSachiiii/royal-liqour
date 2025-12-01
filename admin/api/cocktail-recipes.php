<?php
require_once __DIR__ . '/../controllers/CocktailRecipeController.php';
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

$controller = new CocktailRecipeController();
$method = $_SERVER['REQUEST_METHOD'];

// Pure JSON input — this is what you actually use
$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $controller->getById((int)$_GET['id']);
            } elseif (isset($_GET['count'])) {
                AuthMiddleware::requireAdmin();
                $result = $controller->count();
            } else {
                AuthMiddleware::requireAdmin();
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $result = $controller->getAll($limit, $offset);
            }
            break;

        case 'POST':
            AuthMiddleware::requireAdmin();
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('cocktail_create', 10, 60);
            $result = $controller->create($input);
            break;

        case 'PUT':
            AuthMiddleware::requireAdmin();
            CsrfMiddleware::verifyCsrf();
            if (!isset($_GET['id']) && !isset($input['id']) ) throw new Exception("ID required", 400);
            $id =  $_GET['id'] ?? $input['id'];
            $result = $controller->update(intval($id), $input);
            break;

        case 'DELETE':
            AuthMiddleware::requireAdmin();
            CsrfMiddleware::verifyCsrf();
            if (!isset($_GET['id'])) throw new Exception("ID required", 400);
            $hard = !empty($_GET['hard']);
            $result = $controller->delete((int)$_GET['id'], $hard);
            break;

        default:
            throw new Exception("Method not allowed", 405);
    }

    JsonMiddleware::sendResponse($result, $result['code'] ?? 200);

} catch (Throwable $e) {
    $code = $e->getCode() && $e->getCode() >= 100 ? $e->getCode() : 500;
    JsonMiddleware::sendResponse([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null,
        'code' => $code
    ], $code);
}