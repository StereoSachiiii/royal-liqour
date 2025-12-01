<?php
require_once __DIR__ . '/../controllers/ProductRecognitionController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/JsonMiddleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
$controller = new ProductRecognitionController();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('product_recognition_create', 5, 60);
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $controller->create($body);
        JsonMiddleware::sendResponse($result, $result['code']);
        break;
    
   

    case 'GET':
        if(!isset($_GET['id'])){
            AuthMiddleware::requireAuth();
            RateLimitMiddleware::check('product_recognition_get',100,10);
            $result =$controller->getAll();

            JsonMiddleware::sendResponse($result, $result['code']);
        }

    





        AuthMiddleware::requireAuth();
        $body = json_decode(file_get_contents('php://input'),true);
        if(!isset($_GET['id']) && !isset($body['id'])) throw new Exception("Product- recognition ID is not provided.");
        $id = $_GET['id'] ?? $body['id'];
        $result = $controller->getById(intval($id));
        JsonMiddleware::sendResponse($result, $result['code']);

        break;

    case 'PUT':
        AuthMiddleware::requireAdmin();
        $body= json_decode(file_get_contents('php://input'),true);
        if(!isset($_GET['id']) && !isset($body['id'])) throw new Exception("Product recognition ID is not provided");
        $id = $_GET['id'] ?? $body['id'];
        $result = $controller->update(intval($id),$body);
        JsonMiddleware::sendResponse($result, $result['code']);



    default:
        JsonMiddleware::sendResponse(['error' => 'Method Not Allowed'], 405);
        break;

    }


















?>