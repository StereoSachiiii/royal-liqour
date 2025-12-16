<?php
declare(strict_types=1);

use Core\Request;

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../core/Router.php';

/** @var Router|null $router */

// Guard against direct access - must be loaded via router
if (!isset($router) || !$router instanceof Router) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed. Use /api/v1/user-preferences routes.']);
    exit;
}

$router->group('/api/v1', function (Router $router): void {
    // GET /api/v1/user-preferences
    $router->get('/user-preferences', function (Request $request): array {
        $controller = $GLOBALS['container']->get(UserPreferenceController::class);
        
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        
        return $controller->getAll($limit, $offset);
    });

    // GET /api/v1/user-preferences/:id
    $router->get('/user-preferences/:id', function (Request $request, array $params): array {
        RateLimitMiddleware::check('user_pref_getById', 10, 60);
        $controller = $GLOBALS['container']->get(UserPreferenceController::class);

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'ID required',
                'code'    => 400,
            ];
        }
        return $controller->getById($id);
    });

    // GET /api/v1/user-preferences/user/:user_id
    $router->get('/user-preferences/user/:user_id', function (Request $request, array $params): array {
        RateLimitMiddleware::check('user_pref_getByUserId', 10, 60);
        $controller = $GLOBALS['container']->get(UserPreferenceController::class);

        $userId = (int)($params['user_id'] ?? 0);
        return $controller->getByUserId($userId);
    });

    // POST /api/v1/user-preferences
    $router->post('/user-preferences', function (Request $request): array {
        RateLimitMiddleware::check('user_pref_create', 5, 60);
        $controller = $GLOBALS['container']->get(UserPreferenceController::class);

        $body   = $request->getAllBody();
        $result = $controller->create($body);
        $result['code'] = $result['code'] ?? 201;
        return $result;
    });

    // PUT /api/v1/user-preferences/:id
    $router->put('/user-preferences/:id', function (Request $request, array $params): array {
        RateLimitMiddleware::check('user_pref_update', 10, 60);
        $controller = $GLOBALS['container']->get(UserPreferenceController::class);

        $body = $request->getAllBody();
        $id   = (int)($params['id'] ?? ($body['id'] ?? 0));
        
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'ID required for update',
                'code'    => 400,
            ];
        }

        return $controller->update($id, $body);
    });

    // DELETE /api/v1/user-preferences/:id
    $router->delete('/user-preferences/:id', function (Request $request, array $params): array {
        RateLimitMiddleware::check('user_pref_delete', 5, 60);
        $controller = $GLOBALS['container']->get(UserPreferenceController::class);

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'ID required for delete',
                'code'    => 400,
            ];
        }

        return $controller->delete($id);
    });
});