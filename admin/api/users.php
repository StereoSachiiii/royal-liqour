<?php
declare(strict_types=1);

require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/JsonMiddleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$controller = new UserController();
$method     = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {


        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];

            $action = $body['action'] ?? ($_GET['action'] ?? ($_POST['action'] ?? ''));

            switch ($action) {
                case 'register':
                    RateLimitMiddleware::check('user_register', 10, 3600); // 10 per hour
                    $result = $controller->register($body);
                    JsonMiddleware::sendResponse($result, $result['code'] ?? 400);
                    break;

                case 'login':
                    RateLimitMiddleware::check('user_login', 15, 900); // 15 per 15 mins
                    $result = $controller->login($body);
                    JsonMiddleware::sendResponse($result, $result['code'] ?? 401);
                    break;

                default:
                    AuthMiddleware::requireAuth();
                    CsrfMiddleware::verifyCsrf();
                    RateLimitMiddleware::check('user_post', 30, 60);

                    switch ($action) {
                        case 'logout':
                            Session::getInstance()->logout();
                            JsonMiddleware::sendResponse([
                                'success' => true,
                                'message' => 'Logged out successfully'
                            ], 200);
                            break;

                        case 'updateProfile':
                            $userId = (int)($_GET['id'] ?? $body['id'] ?? null);
                            $result = $controller->updateProfile($userId, $body);
                            JsonMiddleware::sendResponse($result, $result['code'] ?? 400);
                            break;

                        case 'anonymizeUser':
                            $userId = AuthMiddleware::requireAuth();
                            $result = $controller->anonymizeUser($userId);
                            if ($result['success']) {
                                Session::getInstance()->logout();
                            }
                            JsonMiddleware::sendResponse($result, $result['code'] ?? 400);
                            break;

                        case 'createAddress':
                            $userId = AuthMiddleware::requireAuth();
                            $result = $controller->createAddress($userId, $body);
                            JsonMiddleware::sendResponse($result, $result['code'] ?? 400);
                            break;

                        case 'updateAddress':
                            $userId = AuthMiddleware::requireAuth();
                            $addressId = (int)($body['address_id'] ?? 0);
                            if ($addressId <= 0) {
                                JsonMiddleware::sendResponse([
                                    'success' => false,
                                    'message' => 'address_id is required'
                                ], 400);
                            }
                            $result = $controller->updateAddress($addressId, $body);
                            JsonMiddleware::sendResponse($result, $result['code'] ?? 400);
                            break;

                        default:
                            JsonMiddleware::sendResponse([
                                'success' => false,
                                'message' => 'Invalid action'
                            ], 400);
                    }
                    break;
            }
            break;

        case 'GET':
            // Public session check
            if (isset($_GET['session'])) {
                $session = Session::getInstance();
                JsonMiddleware::sendResponse([
                    'success' => true,
                    'data' => [
                        'logged_in'  => $session->isLoggedIn(),
                        'is_admin'   => $session->isAdmin(),
                        'user_id'    => $session->get('user_id'),
                        'name'       => $session->get('name'),
                        'email'      => $session->get('email'),
                        'csrf_token' => $session->getCsrfInstance()->getToken()
                    ]
                ], 200);
                break;
            }

            
            // All other GETs require login
            $userId = AuthMiddleware::requireAuth();
            RateLimitMiddleware::check('users_get', 60, 60);
            if(!isset($_GET['profile']) && !isset($_GET['addresses']) && !isset($_GET['admin_users'])&&!isset($_GET['search'])) {
                $limit = $_GET['limit'] ?? 10;
                $offset = $_GET['offset'] ?? 0;
                $result = $controller->getAllUsers((int)$limit, (int)$offset);
                JsonMiddleware::sendResponse($result, $result['code'] ?? 400);
            }

            if (isset($_GET['profile'])) {
                $result = $controller->getProfile($userId);
                JsonMiddleware::sendResponse($result, $result['code'] ?? 404);
            }

            elseif (isset($_GET['addresses'])) {
                $type = $_GET['type'] ?? null;
                $result = $controller->getAddresses($userId, $type);
                JsonMiddleware::sendResponse($result, 200);
            }

            elseif (isset($_GET['admin_users'])) {
                AuthMiddleware::requireAdmin();
                $limit  = min(100, max(1, (int)($_GET['limit'] ?? 50)));
                $offset = max(0, (int)($_GET['offset'] ?? 0));
                $result = $controller->getAllUsers($limit, $offset);
                JsonMiddleware::sendResponse($result, 200);
            }
            elseif (isset($_GET['search'])) {
          //  AuthMiddleware::requireAdmin();
            $query = $_GET['search'] ?? '';
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            $result = $controller->searchUsers($query, (int)$limit, (int)$offset);
            JsonMiddleware::sendResponse($result, $result['code'] ?? 400);
        }

            else {
                JsonMiddleware::sendResponse([
                    'success' => false,
                    'message' => 'Invalid endpoint'
                ], 400);
            }
            break;

        // ====================================================================
        // DELETE: Address only
        // ====================================================================
        case 'DELETE':
            AuthMiddleware::requireAuth();
            CsrfMiddleware::verifyCsrf();
            RateLimitMiddleware::check('user_delete', 20, 60);

            parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
            $addressId = (int)($query['address_id'] ?? 0);

            if ($addressId <= 0) {
                JsonMiddleware::sendResponse([
                    'success' => false,
                    'message' => 'address_id is required'
                ], 400);
            }

            $result = $controller->deleteAddress($addressId);
            JsonMiddleware::sendResponse($result, $result['code'] ?? 400);
            break;

        // ====================================================================
        // METHOD NOT ALLOWED
        // ====================================================================
        default:
            JsonMiddleware::sendResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
    }

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    $code = ($code >= 400 && $code < 600) ? $code : 500;

    JsonMiddleware::sendResponse([
        'success' => false,
        'message' => $e->getMessage(),
        'data'    => null,
        'code'    => $code,
        'context' => []
    ], $code);
}