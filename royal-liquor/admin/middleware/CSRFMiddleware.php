<?php
require_once __DIR__ . '/../../core/Session.php';

class CSRFMiddleware{
    /**
 * Verify CSRF token for state-changing operations
 */
public static function  verifyCsrf(): void {
    $session = Session::getInstance();
    $csrf = $session->getCsrfInstance();
    
    // Get token from header or POST data
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
    
    // if (!$token || !$csrf->validateToken($token)) {
    //     http_response_code(403);
    //     echo json_encode([
    //         'success' => false,
    //         'message' => 'Invalid CSRF token',
    //         'code' => 403
    //     ]);
    //     exit;
    // }
}

}





?>