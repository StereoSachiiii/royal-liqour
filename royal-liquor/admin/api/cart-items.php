<?php
declare(strict_types=1);

use Core\Request;

require_once __DIR__ . '/../controllers/CartItemController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CSRFMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../core/Router.php';

/** @var Router $router */

// This block handles API calls directly without full router bootstrapping for specific admin usages
// (e.g., list views with pagination, search, enriched details)
if (basename($_SERVER['PHP_SELF']) === 'cart-items.php') {
    require_once __DIR__ . '/../../core/Database.php';
    require_once __DIR__ . '/../repositories/CartItemRepository.php';
    require_once __DIR__ . '/../controllers/CartItemController.php';
    require_once __DIR__ . '/../services/CartItemService.php';
    require_once __DIR__ . '/../validators/CartItemValidator.php';
    require_once __DIR__ . '/../controllers/BaseController.php';

    // CORS and JSON Headers
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *'); 
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    try {
        $repo = new CartItemRepository();
        $service = new CartItemService($repo);
        $controller = new CartItemController($service);

        // --- GET Requests ---
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Get By ID (Enriched)
            if (isset($_GET['id'])) {
                $response = $controller->getByIdEnriched((int)$_GET['id']);
                echo json_encode($response);
                exit;
            }

            // Get By Cart (Optional - direct filtering)
            if (isset($_GET['cart_id'])) {
                 $response = $controller->getByCart((int)$_GET['cart_id']);
                 echo json_encode($response);
                 exit;
            }

            // Get All Enriched / Search (Paginated)
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $search = $_GET['search'] ?? '';
            
            if ($search) {
                $response = $controller->search($search, $limit, $offset);
            } else {
                $response = $controller->getAllEnriched($limit, $offset);
            }
            echo json_encode($response);
            exit;
        }

        // --- PUT Requests (Update) ---
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);

            if ($id > 0 && !empty($input)) {
                $response = $controller->update($id, $input);
                echo json_encode($response);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid ID or input']);
            }
            exit;
        }

        // --- DELETE Requests ---
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id > 0) {
                $response = $controller->delete($id);
                echo json_encode($response);
            } else {
                 http_response_code(400);
                 echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            }
            exit;
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

$router->group('/api/v1', function (Router $router): void {
    // Admin list of cart items
    $router->get('/cart-items', function (Request $request): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        AuthMiddleware::requireAdmin();
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        $search = $request->getQuery('search', '');
        
        if ($search) {
            return $cartItemController->search($search, $limit, $offset);
        }
        return $cartItemController->getAllEnriched($limit, $offset);
    });

    // Get cart item by ID (enriched with product + cart data)
    $router->get('/cart-items/:id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart item ID required',
                'code'    => 400,
            ];
        }
        return $cartItemController->getByIdEnriched($id);
    });

    // Get cart item by cart and product
    $router->get('/cart-items/cart/:cart_id/product/:product_id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        $cartId     = (int)($params['cart_id'] ?? 0);
        $productId  = (int)($params['product_id'] ?? 0);
        return $cartItemController->getByCartProduct($cartId, $productId);
    });

    // Get all items for a cart
    $router->get('/cart-items/cart/:cart_id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        $cartId     = (int)($params['cart_id'] ?? 0);
        return $cartItemController->getByCart($cartId);
    });

    // Count cart items
    $router->get('/cart-items/count', function (Request $request): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        AuthMiddleware::requireAdmin();
        return $cartItemController->count();
    });

    // Create cart item
    $router->post('/cart-items', function (Request $request): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_item_create', 20, 60);

        $body       = $request->getAllBody();
        return $cartItemController->create($body);
    });

    // Update cart item
    $router->put('/cart-items/:id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_item_update', 20, 60);

        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart item ID required',
                'code'    => 400,
            ];
        }

        return $cartItemController->update($id, $body);
    });

    // Update by cart + product
    $router->put('/cart-items/cart/:cart_id/product/:product_id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_item_update', 20, 60);

        $body       = $request->getAllBody();
        $cartId     = (int)($params['cart_id'] ?? 0);
        $productId  = (int)($params['product_id'] ?? 0);

        return $cartItemController->updateByCartProduct($cartId, $productId, $body);
    });

    // Delete cart item
    $router->delete('/cart-items/:id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_item_delete', 10, 60);

        $id         = (int)($params['id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Cart item ID required',
                'code'    => 400,
            ];
        }

        return $cartItemController->delete($id);
    });

    // Delete by cart + product
    $router->delete('/cart-items/cart/:cart_id/product/:product_id', function (Request $request, array $params): array {
        $cartItemController = $GLOBALS['container']->get(CartItemController::class);

        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('cart_item_delete', 10, 60);

        $cartId     = (int)($params['cart_id'] ?? 0);
        $productId  = (int)($params['product_id'] ?? 0);

        return $cartItemController->deleteByCartProduct($cartId, $productId);
    });
});