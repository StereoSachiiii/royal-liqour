<?php
declare(strict_types=1);

use Core\Request;

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CSRFMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../core/Router.php';

/** @var Router|null $router */

// Guard against direct access - must be loaded via router
if (!isset($router) || !$router instanceof Router) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed. Use /api/v1/categories routes.']);
    exit;
}

$router->group('/api/v1', function (Router $router): void {
    // Single category by ID (optional enriched)
    $router->get('/categories/:id', function (Request $request, array $params): array {
        $categoryController = $GLOBALS['container']->get(CategoryController::class);
        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid category ID',
                'code'    => 400,
            ];
        }

        $enriched = $request->getQuery('enriched') === 'true';
        return $enriched ? $categoryController->getByIdEnriched($id) : $categoryController->getById($id);
    });

    // Products in a category (enriched products list)
    $router->get('/categories/:id/products', function (Request $request, array $params): array {
        $categoryController = $GLOBALS['container']->get(CategoryController::class);
        $categoryId  = (int)($params['id'] ?? 0);
        if ($categoryId <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid category ID',
                'code'    => 400,
            ];
        }

        $limit  = (int)$request->getQuery('limit', 100);
        $offset = (int)$request->getQuery('offset', 0);
        return $categoryController->getProductsByCategoryIdEnriched($categoryId, $limit, $offset);
    });

    // List all categories (enriched / includeInactive / search)
    $router->get('/categories', function (Request $request): array {
        $categoryController = $GLOBALS['container']->get(CategoryController::class);
        $limit      = (int)$request->getQuery('limit', 50);
        $offset     = (int)$request->getQuery('offset', 0);
        $enriched   = $request->getQuery('enriched') === 'true';
        $includeInc = $request->getQuery('includeInactive') === 'true';
        $search     = trim((string)$request->getQuery('search', ''));

        // Search if query provided
        if ($search !== '') {
            return $enriched
                ? $categoryController->searchEnriched($search, $limit, $offset)
                : $categoryController->search($search, $limit, $offset);
        }

        if ($enriched) {
            return $categoryController->getAllEnriched($limit, $offset);
        }

        if ($includeInc) {
            AuthMiddleware::requireAdmin();
            return $categoryController->getAllIncludingInactive($limit, $offset);
        }

        return $categoryController->getAll($limit, $offset);
    });

    // Search categories / enriched search
    $router->get('/categories/search', function (Request $request): array {
        $categoryController = $GLOBALS['container']->get(CategoryController::class);
        $query      = trim((string)$request->getQuery('search', ''));
        $limit      = (int)$request->getQuery('limit', 50);
        $offset     = (int)$request->getQuery('offset', 0);
        $enriched   = $request->getQuery('enriched') === 'true';

        return $enriched
            ? $categoryController->searchEnriched($query, $limit, $offset)
            : $categoryController->search($query, $limit, $offset);
    });

    // By name
    $router->get('/categories/by-name', function (Request $request): array {
        $categoryController = $GLOBALS['container']->get(CategoryController::class);
        $name       = (string)$request->getQuery('name', '');
        return $categoryController->getByName($name);
    });

    // Counts
    $router->get('/categories/count', function (Request $request): array {
        $categoryController = $GLOBALS['container']->get(CategoryController::class);
        $includeInc = $request->getQuery('includeInactive') === 'true';

        if ($includeInc) {
            AuthMiddleware::requireAdmin();
            return $categoryController->countAll();
        }
        return $categoryController->count();
    });

    // Create category
    $router->post('/categories', function (Request $request): array {
        $categoryController = $GLOBALS['container']->get(CategoryController::class);
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('category_create', 5, 60);

        $body       = $request->getAllBody();
        return $categoryController->create($body);
    });

    // Update category
    $router->put('/categories/:id', function (Request $request, array $params): array {
        $categoryController = $GLOBALS['container']->get(CategoryController::class);
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('category_update', 5, 60);

        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? ($body['id'] ?? 0));

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Category ID required',
                'code'    => 400,
            ];
        }

        return $categoryController->update($id, $body);
    });

    // Delete / hard delete category
    $router->delete('/categories/:id', function (Request $request, array $params): array {
        $categoryController = $GLOBALS['container']->get(CategoryController::class);
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('category_delete', 5, 60);

        $id         = (int)($params['id'] ?? 0);
        $hard       = $request->getQuery('hard') === 'true';

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Category ID required',
                'code'    => 400,
            ];
        }

        return $categoryController->delete($id, $hard);
    });
});