<?php

declare(strict_types=1);

require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../../core/Session.php';

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost'); // Adjust for your frontend
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Initialize session
$session = Session::getInstance();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$userController = new UserController();

/**
 * Helper function to check authentication
 * @return int User ID
 */
function requireAuth(): int {
    $session = Session::getInstance();
    
    if (!$session->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized - Please login',
            'code' => 401
        ]);
        exit;
    }
    
    return (int)$session->get('user_id');
}

/**
 * Helper function to check admin role
 * @return int User ID
 */
function requireAdmin(): int {
    $session = Session::getInstance();
    
    if (!$session->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized - Please login',
            'code' => 401
        ]);
        exit;
    }
    
    if (!$session->isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Forbidden - Admin access required',
            'code' => 403
        ]);
        exit;
    }
    
    return (int)$session->get('user_id');
}

/**
 * Helper function to send JSON response and exit
 * @param array $data Response data
 * @param int $statusCode HTTP status code
 */
function sendResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Verify CSRF token for state-changing operations
 */
function verifyCsrf(): void {
    $session = Session::getInstance();
    $csrf = $session->getCsrfInstance();
    
    // Get token from header or POST data
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
    
    if (!$token || !$csrf->validateToken($token)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token',
            'code' => 403
        ]);
        exit;
    }
}

try {
    switch ($method) {
        case 'POST':
            // Verify CSRF for all POST requests (except login/register)
            if (!in_array($action, ['login', 'register', 'registerAdmin'])) {
                verifyCsrf();
            }
            
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            switch ($action) {
                case 'register':
                    $result = $userController->register($data);
                    sendResponse($result, $result['success'] ? 201 : ($result['code'] ?? 400));
                    
                case 'registerAdmin':
                    $result = $userController->registerAdminUser($data);
                    sendResponse($result, $result['success'] ? 201 : ($result['code'] ?? 400));
                    
                case 'login':
                    $result = $userController->login($data);
                    sendResponse($result, $result['success'] ? 200 : ($result['code'] ?? 401));
                    
                case 'logout':
                    $userId = requireAuth();
                    $session->logout();
                    sendResponse([
                        'success' => true,
                        'message' => 'Logged out successfully',
                        'code' => 200
                    ], 200);
                    
                case 'updateProfile':
                    $userId = requireAuth();
                    $result = $userController->updateProfile($userId, $data);
                    sendResponse($result, $result['success'] ? 200 : ($result['code'] ?? 400));
                    
                case 'anonymizeUser':
                    $userId = requireAuth();
                    $result = $userController->anonymizeUser($userId);
                    
                    // If successful, logout the user
                    if ($result['success']) {
                        $session->logout();
                    }
                    
                    sendResponse($result, $result['success'] ? 200 : ($result['code'] ?? 400));
                    
                case 'createAddress':
                    $userId = requireAuth();
                    $result = $userController->createAddress($userId, $data);
                    sendResponse($result, $result['success'] ? 201 : ($result['code'] ?? 400));
                    
                case 'updateAddress':
                    $userId = requireAuth();
                    $addressId = (int)($data['address_id'] ?? 0);
                    
                    if ($addressId <= 0) {
                        sendResponse([
                            'success' => false,
                            'message' => 'Invalid address ID',
                            'code' => 400
                        ], 400);
                    }
                    
                    $result = $userController->updateAddress($addressId, $data);
                    sendResponse($result, $result['success'] ? 200 : ($result['code'] ?? 400));
                    
                default:
                    sendResponse([
                        'success' => false,
                        'message' => 'Invalid action for POST method',
                        'code' => 400
                    ], 400);
            }
            break;

        case 'GET':
            switch ($action) {
                case 'getProfile':
                    $userId = requireAuth();
                    $result = $userController->getProfile($userId);
                    sendResponse($result, $result['success'] ? 200 : ($result['code'] ?? 404));
                    
                case 'getSession':
                    // Return current session info (useful for frontend)
                    sendResponse([
                        'success' => true,
                        'data' => [
                            'logged_in' => $session->isLoggedIn(),
                            'is_admin' => $session->isAdmin(),
                            'is_guest' => $session->isGuest(),
                            'user_id' => $session->get('user_id'),
                            'name' => $session->get('name'),
                            'email' => $session->get('email'),
                            'csrf_token' => $session->getCsrfInstance()->getToken()
                        ],
                        'code' => 200
                    ], 200);
                    
                case 'getCsrfToken':
                    // Get CSRF token for forms
                    $csrf = $session->getCsrfInstance();
                    sendResponse([
                        'success' => true,
                        'data' => [
                            'csrf_token' => $csrf->getToken()
                        ],
                        'code' => 200
                    ], 200);
                    
                case 'getAddresses':
                    $userId = requireAuth();
                    $addressType = $_GET['type'] ?? null;
                    $result = $userController->getAddresses($userId, $addressType);
                    sendResponse($result, $result['success'] ? 200 : ($result['code'] ?? 404));
                    
                case 'getUserById':
                    requireAdmin(); // Admin only
                    $id = (int)($_GET['id'] ?? 0);
                    
                    if ($id <= 0) {
                        sendResponse([
                            'success' => false,
                            'message' => 'Invalid user ID',
                            'code' => 400
                        ], 400);
                    }
                    
                    $result = $userController->getUserById($id);
                    sendResponse($result, $result['success'] ? 200 : ($result['code'] ?? 404));
                    
                case 'getAllUsers':
                    requireAdmin(); // Admin only
                    $limit = (int)($_GET['limit'] ?? 50);
                    $offset = (int)($_GET['offset'] ?? 0);
                    
                    // Validate pagination params
                    if ($limit < 1 || $limit > 100) {
                        sendResponse([
                            'success' => false,
                            'message' => 'Limit must be between 1 and 100',
                            'code' => 400
                        ], 400);
                    }
                    
                    if ($offset < 0) {
                        sendResponse([
                            'success' => false,
                            'message' => 'Offset must be non-negative',
                            'code' => 400
                        ], 400);
                    }
                    
                    $result = $userController->getAllUsers($limit, $offset);
                    sendResponse($result, $result['success'] ? 200 : ($result['code'] ?? 500));
                    
                default:
                    sendResponse([
                        'success' => false,
                        'message' => 'Invalid action for GET method',
                        'code' => 400
                    ], 400);
            }
            break;

        case 'DELETE':
            verifyCsrf(); // Verify CSRF for DELETE
            
            switch ($action) {
                case 'deleteAddress':
                    $userId = requireAuth();
                    $addressId = (int)($_GET['address_id'] ?? 0);
                    
                    if ($addressId <= 0) {
                        sendResponse([
                            'success' => false,
                            'message' => 'Invalid address ID',
                            'code' => 400
                        ], 400);
                    }
                    
                    $result = $userController->deleteAddress($addressId);
                    sendResponse($result, $result['success'] ? 200 : ($result['code'] ?? 400));
                    
                default:
                    sendResponse([
                        'success' => false,
                        'message' => 'Invalid action for DELETE method',
                        'code' => 400
                    ], 400);
            }
            break;

        default:
            sendResponse([
                'success' => false,
                'message' => 'Method not allowed',
                'code' => 405
            ], 405);
    }
    
} catch (Exception $e) {
    // Log error for debugging
    error_log('API Error [' . $action . ']: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    sendResponse([
        'success' => false,
        'message' => 'Server error occurred',
        'code' => 500
    ], 500);
}
?>