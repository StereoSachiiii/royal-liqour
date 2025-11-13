<?php
declare(strict_types=1);


require_once __DIR__.'/../controllers/ProductController.php';
require_once __DIR__.'/../repositories/ReportRepository.php';
require_once __DIR__.'/../../core/Session.php';

header('Content-Type: application/json');

$session = Session::getInstance(); 
$repo = new ProductRepository();
$product = new ProductController($repo, $session);




$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

function requireAuth():int{
    $session = Session::getInstance();
    if(!$session->isLoggedIn()){
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Unauthorized']);
        exit;
    }
    return $session->getUserId();
        
}
function requireAdmin():int{
    $session = Session::getInstance();
    if(!$session->isLoggedIn() || !$session->isAdmin()){
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Unauthorized - Admins only']);
        exit;
    }
    return $session->getUserId();
}

function verifyCsrf($token): void {
    $session = Session::getInstance();
    
    $token = $_POST['csrf_token'] ?? null;

    if(!$token || $session->getCsrfInstance()->validateToken($token)){
        http_response_code(403);
        echo json_encode([
            'success'=>false,
            'message'=>'Invalid CSRF Token',
            'code' => 403,
            'context' => []
        ]);
    }

}

function sendResponse($data, $statusCode = 200){
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}
try{

if($method === 'GET'){

    switch ($action) {
        //does not require auth
        case 'getAllProducts':
            $offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;
            $limit = $_GET['limit'] ?? $_POST['limit'] ??50;

            break;
        case 'getProductById':
            $productId = $_GET['productId'] ?? $_POST['productId'] ?? null;
            if(!$productId){
                sendResponse([
                    'success'=>false,
                    'message'=>'Product ID is required',
                    'code' => 400,
                    'context' => []
                ],400);
            }
            echo json_encode( $product->getProductById(intval($productId)));
            break;
        case 'createProduct':
            requireAdmin();
            verifyCsrf($_POST['csrf_token'] ?? null);
            $
            break;
        default:
            # code...
            break;
    }



}
}
 catch (RuntimeException $e) {
    return [
        'success' => false,
        'message' => 'Failed to process request: ' . $e->getMessage(),
        'data' => null,
        'code' => 500,
        'context' => [67]
    ];
}






















?>