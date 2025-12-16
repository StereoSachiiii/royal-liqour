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
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed. Use /api/v1/admin/views/* routes.']);
    exit;
}

$router->group('/api/v1', function (Router $router): void {
    // GET /api/v1/admin/views/dashboard (dashboard stats)
    $router->get('/admin/views/dashboard', function (Request $request): array {
        // AuthMiddleware::requireAdmin();
        // RateLimitMiddleware::check('admin_view', 100, 60);

        $controller = $GLOBALS['container']->get(AdminViewController::class);
        return $controller->getDashboardStats();
    });

    // GET /api/v1/admin/views/:entity/:id (detail view)
    $router->get('/admin/views/:entity/:id', function (Request $request, array $params): array {
        // AuthMiddleware::requireAdmin();
        // RateLimitMiddleware::check('admin_view', 100, 60);

        $controller = $GLOBALS['container']->get(AdminViewController::class);
        $entity     = $params['entity'] ?? null;
        $id         = (int)($params['id'] ?? 0);

        if ($entity === null) {
            return [
                'success' => false,
                'message' => 'Entity parameter required',
                'code'    => 400,
            ];
        }

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Valid ID required',
                'code'    => 400,
            ];
        }

        return $controller->getDetail($entity, $id);
    });

    // GET /api/v1/admin/views/:entity (list view)
    $router->get('/admin/views/:entity', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('admin_view', 100, 60);

        $controller = $GLOBALS['container']->get(AdminViewController::class);
        $entity     = $params['entity'] ?? null;

        if ($entity === null) {
            return [
                'success' => false,
                'message' => 'Entity parameter required',
                'code'    => 400,
            ];
        }

        $limit  = (int)min(100, max(1, (int)$request->getQuery('limit', 50)));
        $offset = (int)max(0, (int)$request->getQuery('offset', 0));
        $search = $request->getQuery('search');

        return $controller->getList($entity, $limit, $offset, $search);
    });
});