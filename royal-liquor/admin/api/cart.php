<?php
declare(strict_types=1);

use Core\Request;

require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CSRFMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../core/Router.php';

/** @var Router $router */

// --- Direct Access Handling for Admin Panel ---
// This block handles API calls directly without full router bootstrapping for specific admin usages
// (e.g., list views with pagination, search, enriched details)
if (basename($_SERVER['PHP_SELF']) === 'cart.php') {
    require_once __DIR__ . '/../../core/Database.php';
    require_once __DIR__ . '/../repositories/CartRepository.php';
    require_once __DIR__ . '/../controllers/CartController.php';
    require_once __DIR__ . '/../services/CartService.php';

    // Manual Dependency Injection
    $cartRepository = new CartRepository();
    $cartService = new CartService($cartRepository);
    $cartController = new CartController($cartService);

    // Standard API Headers
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

    // Handle Preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $entity = $_GET['entity'] ?? '';
    // Use action (standard) or legacy params
    // Our new standard is RESTful: GET(id), GET, POST, PUT(id), DELETE(id)
    // But direct access sometimes needs distinct actions
    $action = $_GET['action'] ?? '';

    try {
        if ($method === 'GET') {
            if ($id) {
                // Detailed view
                $response = $cartController->getByIdEnriched($id);
            } else {
                // List view
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                $search = $_GET['search'] ?? '';
                
                if ($search) {
                    $response = $cartController->search($search, $limit, $offset);
                } else {
                    $response = $cartController->getAllEnriched($limit, $offset);
                }
            }
            echo json_encode($response);
        } elseif ($method === 'PUT') {
            // Update
            $input = json_decode(file_get_contents('php://input'), true);
            $updateId = $id ?? ($input['id'] ?? null);
            if (!$updateId) throw new Exception('ID required for update');
            
            $response = $cartController->update((int)$updateId, $input);
            echo json_encode($response);
        } elseif ($method === 'DELETE') {
            // Delete
            if (!$id) throw new Exception('ID required for delete');
            $response = $cartController->delete($id);
            echo json_encode($response);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$router->group('/api/v1', function (Router $router): void {
    // Admin list of all carts
    $router->get('/carts', function (Request $request): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        // Only when no specific filters are applied (same as original condition)
        AuthMiddleware::requireAdmin();
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        $search = $request->getQuery('search', '');
        
        if ($search) {
            return $cartController->search($search, $limit, $offset);
        }
        return $cartController->getAllEnriched($limit, $offset);
    });

    // Get cart by ID (enriched with user + items)
    $router->get('/carts/:id', function (Request $request, array $params): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart ID required',
                'code'    => 400,
            ];
        }
        return $cartController->getByIdEnriched($id);
    });

    // Active cart by user ID
    $router->get('/carts/by-user/:user_id', function (Request $request, array $params): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        $userId     = (int)($params['user_id'] ?? 0);
        return $cartController->getActiveByUser($userId);
    });

    // Active cart by session ID
    $router->get('/carts/by-session/:session_id', function (Request $request, array $params): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        $sessionId  = (string)($params['session_id'] ?? '');
        return $cartController->getActiveBySession($sessionId);
    });

    // Count carts
    $router->get('/carts/count', function (Request $request): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        AuthMiddleware::requireAdmin();
        return $cartController->count();
    });

    // Create cart
    $router->post('/carts', function (Request $request): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_create', 10, 60);

        $body       = $request->getAllBody();
        return $cartController->create($body);
    });

    // Update cart
    $router->put('/carts/:id', function (Request $request, array $params): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_update', 10, 60);

        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? ($body['id'] ?? 0));

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart ID required',
                'code'    => 400,
            ];
        }

        return $cartController->update($id, $body);
    });

    // Delete cart
    $router->delete('/carts/:id', function (Request $request, array $params): array {
        $cartController = $GLOBALS['container']->get(CartController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_delete', 5, 60);

        $id         = (int)($params['id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart ID required',
                'code'    => 400,
            ];
        }

        return $cartController->delete($id);
    });
});