<?php
declare(strict_types=1);

// This file defines all user-related routes on the shared $router
// created in api/index.php (Laravel-style per-model routes).

use Core\Request;

require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CSRFMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/JsonMiddleware.php';
require_once __DIR__ . '/../core/Router.php';

// Router-based registration for users API
// Direct file access removed - all requests must go through index.php router

/** @var Router $router */


$router->group('/api/v1', function (Router $router): void {
    // ---------------------------------------------------------------------
    // AUTH & PUBLIC
    // ---------------------------------------------------------------------

    // POST /api/v1/users/register
    $router->post('/users/register', function (Request $request): array {
        RateLimitMiddleware::check('user_register', 10, 3600); // 10 per hour
        $controller = new UserController();
        $body       = $request->getAllBody();
        return $controller->register($body);
    });

    // POST /api/v1/users/login
    $router->post('/users/login', function (Request $request): array {
        RateLimitMiddleware::check('user_login', 15, 900); // 15 per 15 mins
        $controller = new UserController();
        $body       = $request->getAllBody();
        return $controller->login($body);
    });

    // GET /api/v1/users/session  (public session check)
    $router->get('/users/session', function (Request $request): array {
        $session = Session::getInstance();

        return [
            'success' => true,
            'data'    => [
                'logged_in'  => $session->isLoggedIn(),
                'is_admin'   => $session->isAdmin(),
                'user_id'    => $session->get('user_id'),
                'name'       => $session->get('name'),
                'email'      => $session->get('email'),
                'csrf_token' => $session->getCsrfInstance()->getToken(),
            ],
            'code'    => 200,
        ];
    });

    // ---------------------------------------------------------------------
    // AUTHENTICATED USER ROUTES
    // ---------------------------------------------------------------------

    // POST /api/v1/users/logout
    $router->post('/users/logout', function (Request $request): array {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('user_post', 30, 60);

        Session::getInstance()->logout();

        return [
            'success' => true,
            'message' => 'Logged out successfully',
            'code'    => 200,
        ];
    });

    // PUT /api/v1/users/profile
    $router->put('/users/profile', function (Request $request): array {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('user_post', 30, 60);

        $controller = new UserController();
        $body       = $request->getAllBody();
        $userId     = (int)($request->getQuery('id') ?? ($body['id'] ?? 0));

        return $controller->updateProfile($userId, $body);
    });

    // POST /api/v1/users/anonymize
    $router->post('/users/anonymize', function (Request $request): array {
        $userId = AuthMiddleware::requireAuth();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('user_post', 30, 60);

        $controller = new UserController();
        $result     = $controller->anonymizeUser($userId);

        if (!empty($result['success'])) {
            Session::getInstance()->logout();
        }

        return $result;
    });

    // ---------------------------------------------------------------------
    // ADDRESSES
    // ---------------------------------------------------------------------

    // POST /api/v1/users/addresses
    $router->post('/users/addresses', function (Request $request): array {
        $userId = AuthMiddleware::requireAuth();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('user_post', 30, 60);

        $controller = new UserController();
        $body       = $request->getAllBody();

        return $controller->createAddress($userId, $body);
    });

    // PUT /api/v1/users/addresses/{address_id}
    $router->put('/users/addresses/:address_id', function (Request $request, array $params): array {
        $userId = AuthMiddleware::requireAuth();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('user_post', 30, 60);

        $addressId = (int)($params['address_id'] ?? 0);
        if ($addressId <= 0) {
            return [
                'success' => false,
                'message' => 'address_id is required',
                'code'    => 400,
            ];
        }

        $controller = new UserController();
        $body       = $request->getAllBody();

        return $controller->updateAddress($addressId, $body);
    });

    // DELETE /api/v1/users/addresses/{address_id}
    $router->delete('/users/addresses/:address_id', function (Request $request, array $params): array {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('user_delete', 20, 60);

        $addressId = (int)($params['address_id'] ?? 0);
        if ($addressId <= 0) {
            return [
                'success' => false,
                'message' => 'address_id is required',
                'code'    => 400,
            ];
        }

        $controller = new UserController();
        return $controller->deleteAddress($addressId);
    });

    // GET /api/v1/users/profile
    $router->get('/users/profile', function (Request $request): array {
        $userId = AuthMiddleware::requireAuth();
        RateLimitMiddleware::check('users_get', 60, 60);

        $controller = new UserController();
        return $controller->getProfile($userId);
    });

    // GET /api/v1/users/addresses
    $router->get('/users/addresses', function (Request $request): array {
        $userId = AuthMiddleware::requireAuth();
        RateLimitMiddleware::check('users_get', 60, 60);

        $controller = new UserController();
        $type       = $request->getQuery('type');

        return $controller->getAddresses($userId, $type);
    });

    // ---------------------------------------------------------------------
    // ADMIN / LIST / SEARCH
    // ---------------------------------------------------------------------

    // GET /api/v1/admin/users
    $router->get('/admin/users', function (Request $request): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('users_get', 60, 60);

        $controller = new UserController();

        $limit  = (int)min(100, max(1, (int)($request->getQuery('limit', 50))));
        $offset = (int)max(0, (int)($request->getQuery('offset', 0)));

        return $controller->getAllUsers($limit, $offset);
    });

    // GET /api/v1/users (with optional search)
    $router->get('/users', function (Request $request): array {
        AuthMiddleware::requireAdmin(); // Admin only for user list
        RateLimitMiddleware::check('users_get', 60, 60);

        $controller = new UserController();

        $limit  = (int)$request->getQuery('limit', 20);
        $offset = (int)$request->getQuery('offset', 0);
        $search = trim((string)$request->getQuery('search', ''));

        // Use search method if query provided, otherwise getAll
        if ($search !== '') {
            return $controller->searchUsers($search, $limit, $offset);
        }
        return $controller->getAllUsers($limit, $offset);
    });

    // GET /api/v1/users/search
    $router->get('/users/search', function (Request $request): array {
        // AuthMiddleware::requireAdmin(); // keep behavior same as original commented
        $controller = new UserController();

        $query  = (string)$request->getQuery('search', '');
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);

        return $controller->searchUsers($query, $limit, $offset);
    });

    // GET /api/v1/users/:id - Get single user by ID (admin enriched)
    $router->get('/users/:id', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('users_get', 60, 60);

        $controller = new UserController();
        $id = (int)($params['id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'User ID required',
                'code'    => 400,
            ];
        }

        return $controller->getByIdEnriched($id);
    });

    // PUT /api/v1/users/:id - Update user (admin)
    $router->put('/users/:id', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('users_put', 30, 60);

        $controller = new UserController();
        $id = (int)($params['id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'User ID required',
                'code'    => 400,
            ];
        }

        $body = $request->getAllBody();
        return $controller->updateProfile($id, $body);
    });

    // DELETE /api/v1/users/:id - Delete user (admin)
    $router->delete('/users/:id', function (Request $request, array $params): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('users_delete', 20, 60);

        $controller = new UserController();
        $id = (int)($params['id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'User ID required',
                'code'    => 400,
            ];
        }

        $hard = $request->getQuery('hard', 'false') === 'true';
        return $controller->delete($id, $hard);
    });
});