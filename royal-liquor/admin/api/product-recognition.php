<?php
declare(strict_types=1);

use Core\Request;

require_once __DIR__ . '/../controllers/ProductRecognitionController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CSRFMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../core/Router.php';

/** @var Router $router */

$router->group('/api/v1', function (Router $router): void {
    // Search product recognition - MUST be before :id routes
    $router->get('/product-recognition/search', function (Request $request): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('product_recognition_search', 30, 60);
        $controller = $GLOBALS['container']->get(ProductRecognitionController::class);
        $query  = (string)$request->getQuery('search', '');
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        return $controller->search($query, $limit, $offset);
    });

    // POST /api/v1/product-recognition  (create)
    $router->post('/product-recognition', function (Request $request): array {
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('product_recognition_create', 5, 60);
        $controller = $GLOBALS['container']->get(ProductRecognitionController::class);
        $body       = $request->getAllBody();
        return $controller->create($body);
    });

    // GET /api/v1/product-recognition  (list all for admin)
    $router->get('/product-recognition', function (Request $request): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('product_recognition_get', 100, 10);
        $controller = $GLOBALS['container']->get(ProductRecognitionController::class);
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        return $controller->getAll($limit, $offset);
    });

    // GET /api/v1/product-recognition/:id
    $router->get('/product-recognition/:id', function (Request $request, array $params): array {
        AuthMiddleware::requireAuth();
        $controller = $GLOBALS['container']->get(ProductRecognitionController::class);
        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Product recognition ID is not provided.',
                'code'    => 400,
            ];
        }
        return $controller->getById($id);
    });

    // PUT /api/v1/product-recognition/:id
    $router->put('/product-recognition/:id', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        $controller = $GLOBALS['container']->get(ProductRecognitionController::class);
        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? ($body['id'] ?? 0));

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Product recognition ID is not provided',
                'code'    => 400,
            ];
        }

        return $controller->update($id, $body);
    });
});


?>