<?php

require_once __DIR__ . '/../controllers/FeedbackController.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/JsonMiddleware.php';

$method = $_SERVER['REQUEST_METHOD'];
$controller = new FeedbackController();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                RateLimitMiddleware::check('feedback_getById', 10, 60);
                $result = $controller->getById((int)$_GET['id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            if (isset($_GET['product_id'])) {
                RateLimitMiddleware::check('feedback_getByProductId', 10, 60);
                $result = $controller->getByProductId((int)$_GET['product_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            if (isset($_GET['user_id'])) {
                RateLimitMiddleware::check('feedback_getByUserId', 10, 60);
                $result = $controller->getByUserId((int)$_GET['user_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            if (isset($_GET['product_id']) && isset($_GET['avg_rating'])) {
                RateLimitMiddleware::check('feedback_getAvgRating', 10, 60);
                $result = $controller->getAverageRating((int)$_GET['product_id']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            if (isset($_GET['limit']) && isset($_GET['offset'])) {
                RateLimitMiddleware::check('feedback_getAllPaginated', 5, 60);
                $result = $controller->getAllPaginated((int)$_GET['limit'], (int)$_GET['offset']);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            RateLimitMiddleware::check('feedback_getAll', 5, 60);
            $result = $controller->getAll();
            JsonMiddleware::sendResponse($result, 200);
            break;

        case 'POST':
            RateLimitMiddleware::check('feedback_create', 5, 60);
            $body = json_decode(file_get_contents('php://input'), true);
            $result = $controller->create($body);
            JsonMiddleware::sendResponse($result, 201);
            break;

        case 'PUT':
            $body = json_decode(file_get_contents('php://input'), true);
            if (!isset($_GET['id'])&&!isset($body['id'])) throw new Exception("ID required for update", 400);
            RateLimitMiddleware::check('feedback_update', 10, 60);
            $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$body['id'];
            $result = $controller->update($id, $body);
            JsonMiddleware::sendResponse($result, 200);
            break;

        case 'DELETE':
            $body = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($_GET['id']) && !isset($body['id'])) throw new Exception("ID required for delete", 400);
            RateLimitMiddleware::check('feedback_delete', 5, 60);

            $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$body['id'];
            if($body['hard'] ?? boolval($body['hard'])==true) {
                $result = $controller->hardDelete($id);
                JsonMiddleware::sendResponse($result, 200);
                break;
            }
            $result = $controller->delete($id);
            JsonMiddleware::sendResponse($result, 200);
            break;

        default:
            throw new Exception("Method not allowed", 405);
    }
} catch (Exception $e) {
    JsonMiddleware::sendResponse(['success' => false, 'message' => $e->getMessage(), 'code' => $e->getCode() ?: 500], $e->getCode() ?: 500);
}
?>