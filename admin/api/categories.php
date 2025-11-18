<?php 

require_once __DIR__ . '/../controllers/CategoryController.php';
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
$categoryController = new CategoryController();




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

if($method === "GET"){
    switch ($action) {
        case 'getAllCategories':
            $limit = intval($_GET['limit']) ?? 50;
            $offset = intval($_GET['offset']) ?? 0;
            sendResponse($categoryController->getAllCategories($limit, $offset));
            break;  
        
        case 'getCategoryById':
            $id = intval($_GET['id']);
            sendResponse($categoryController->getCategoryById($id));
            break;
        
        case 'getCategoryByName':
            $name = strval($_GET['name']);
            sendResponse($categoryController->getCategoryByname($name));
            break;
        
        case 'getActiveCategories':
            $limit = $_GET['limit']?? 50;
            $offset = $_GET['offset'] ?? 0;
            sendResponse($categoryController->getActiveCategories($limit, $offset));
            break;  
        
        case 'searchCategories':
            $limit = $_GET['limit']?? 50;
            $offset = $_GET['offset'] ?? 0;
            $searchTerm = $_GET['searchTerm'] ?? '';
            sendResponse($categoryController->searchCategories($searchTerm,$limit,$offset));
            break;
        
        case 'getCategoriesWithProductCount':
            $limit = $_GET['limit']?? 50;
            $offset = $_GET['offset'] ?? 0;
            sendResponse($categoryController->getCategoriesWithProductCount($limit, $offset));
            break;
        
        case 'getCategoryCountActive':
            sendResponse($categoryController->getCategoryCountActive());
            break;

        case 'getCategoryCountAll':
            sendResponse($categoryController->getCatergoryCountAll());
            break;
        case 'categoryNameExists':
            $name = $_GET['name'] ?? '';
            sendResponse($categoryController->categoryNameExists($name));
            break;

        default:
            
            break;
    }

}

if($method === 'POST'){
    $data = json_decode(file_get_contents(filename: 'php://input'), true) ?? $_POST;

    switch($action){
        case 'createCategory':
            //requireAuth();
            //requireAdmin();
            sendResponse($categoryController->createCategory($data));
    
        case 'softDeleteCategory':
            //requireAuth();
            //requireAdmin();
            sendResponse($categoryController->softDeleteCategory($data));   
            break;

        case 'restoreCateogry':
            //requireAuth();
            //requireAdmin();
            sendResponse($categoryController->restoreCategory($data));
            break;
        
        case 'updateCategory':
            //requireAuth();
            //requireAdmin();
            sendResponse($categoryController->updateCategory($data));
            break;

        case 'hardDeleteCategory':
            //requireAuth();
            //requireAdmin();
            sendResponse($categoryController->hardDeleteCategory($data));
            break;
        
        default:
            break;
        }

    



}















?>