<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../admin/controllers/UserController.php';
require_once __DIR__ . '/../../admin/middleware/JsonMiddleware.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    JsonMiddleware::sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    exit;
}

$session = Session::getInstance();
$controller = new UserController();

// Read JSON or form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    // Fallback to $_POST (for FormData from auth.php)
    $input = $_POST;
}

$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'register':
            $result = $controller->register([
                'name'     => $input['name'] ?? '',
                'email'    => $input['email'] ?? '',
                'phone'    => $input['phone'] ?? null,
                'password' => $input['password'] ?? '',
                'confirm_password' => $input['confirm_password'] ?? ''
            ]);
            JsonMiddleware::sendResponse($result, $result['code'] ?? 400);
            
            break;

        case 'login':
            $result = $controller->login([
                'email'    => $input['email'] ?? '',
                'password' => $input['password'] ?? ''
            ]);
            $session->login([
                'user_id' => $result['data']['id'],
                 'name' => $result['data']['name'],
                 'email' => $result['data']['email'],
                 'is_admin' => $result['data']['is_admin']
            ]);
            JsonMiddleware::sendResponse($result, $result['code'] ?? 401);
            break;

        default:
            JsonMiddleware::sendResponse([
                'success' => false,
                'message' => 'Invalid action'
            ], 400);
    }
} catch (Throwable $e) {
    error_log("[auth-handler] Error: " . $e->getMessage() . " | " . json_encode($input));
    JsonMiddleware::sendResponse([
        'success' => false,
        'message' => 'Server error',
        'context' => []
    ], 500);
}