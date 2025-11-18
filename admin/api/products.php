<?php
declare(strict_types=1);

require_once __DIR__.'/../controllers/ProductController.php';
require_once __DIR__.'/../repositories/ProductRepository.php';
require_once __DIR__.'/../../core/Session.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

$session = Session::getInstance();
$productRepository = new ProductRepository();
$productController = new ProductController($productRepository, $session);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

function requireAuth(): int {
    $session = Session::getInstance();
    if (!$session->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    return $session->getUserId();
}

function requireAdmin(): int {
    $session = Session::getInstance();
    if (!$session->isLoggedIn() || !$session->isAdmin()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized - Admins only']);
        exit;
    }
    return $session->getUserId();
}

function verifyCsrf(): void {
    $session = Session::getInstance();
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

    if (!$token || !$session->getCsrfInstance()->validateToken($token)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF Token',
            'code' => 403,
            'context' => []
        ]);
        exit;
    }
}

function sendResponse($data, $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    if ($method === 'GET') {
        switch ($action) {
            case 'getAllProducts':
                $offset = intval($_GET['offset'] ?? 0);
                $limit = intval($_GET['limit'] ?? 50);
                $result = $productController->getAllProducts($limit, $offset);
                sendResponse($result);
                break;

            case 'getAllProductsIncludingInactive':
                requireAdmin();
                $offset = intval($_GET['offset'] ?? 0);
                $limit = intval($_GET['limit'] ?? 50);
                $result = $productController->getAllProductsIncludingInactive($limit, $offset);
                sendResponse($result);
                break;

            case 'getProductById':
                $productId = intval($_GET['productId'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->getProductById($productId);
                sendResponse($result);
                break;

            case 'getProductByIdAdmin':
                requireAdmin();
                $productId = intval($_GET['productId'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->getProductByIdAdmin($productId);
                sendResponse($result);
                break;

            case 'searchProducts':
                $query = trim($_GET['query'] ?? '');
                $offset = intval($_GET['offset'] ?? 0);
                $limit = intval($_GET['limit'] ?? 50);
                $result = $productController->searchProducts($query, $limit, $offset);
                sendResponse($result);
                break;

            case 'countSearchResults':
                $query = trim($_GET['query'] ?? '');
                $result = $productController->countSearchResults($query);
                sendResponse($result);
                break;

            case 'getProductsByCategory':
                $categoryId = intval($_GET['categoryId'] ?? 0);
                $offset = intval($_GET['offset'] ?? 0);
                $limit = intval($_GET['limit'] ?? 50);
                if ($categoryId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Category ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->getProductsByCategory($categoryId, $limit, $offset);
                sendResponse($result);
                break;

            case 'getProductsBySupplier':
                $supplierId = intval($_GET['supplierId'] ?? 0);
                $offset = intval($_GET['offset'] ?? 0);
                $limit = intval($_GET['limit'] ?? 50);
                if ($supplierId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Supplier ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->getProductsBySupplier($supplierId, $limit, $offset);
                sendResponse($result);
                break;

            case 'getProductsByPriceRange':
                $minPrice = floatval($_GET['minPrice'] ?? 0);
                $maxPrice = floatval($_GET['maxPrice'] ?? PHP_FLOAT_MAX);
                $offset = intval($_GET['offset'] ?? 0);
                $limit = intval($_GET['limit'] ?? 50);
                $result = $productController->getProductsByPriceRange($minPrice, $maxPrice, $limit, $offset);
                sendResponse($result);
                break;

            case 'getProductsByDate':
                $order = strtoupper($_GET['order'] ?? 'DESC');
                if (!in_array($order, ['ASC', 'DESC'])) {
                    $order = 'DESC';
                }
                $offset = intval($_GET['offset'] ?? 0);
                $limit = intval($_GET['limit'] ?? 50);
                $result = $productController->getProductsByDate($order, $limit, $offset);
                sendResponse($result);
                break;

            case 'getProductsByPrice':
                $order = strtoupper($_GET['order'] ?? 'ASC');
                if (!in_array($order, ['ASC', 'DESC'])) {
                    $order = 'ASC';
                }
                $offset = intval($_GET['offset'] ?? 0);
                $limit = intval($_GET['limit'] ?? 50);
                $result = $productController->getProductsByPrice($order, $limit, $offset);
                sendResponse($result);
                break;

            case 'getProductsByIds':
                $ids = $_GET['ids'] ?? '';
                if (empty($ids)) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product IDs are required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $idArray = array_map('intval', explode(',', $ids));
                $result = $productController->getProductsByIds($idArray);
                sendResponse($result);
                break;

            case 'getLowStockProducts':
                requireAdmin();
                $threshold = intval($_GET['threshold'] ?? 10);
                $offset = intval($_GET['offset'] ?? 0);
                $limit = intval($_GET['limit'] ?? 50);
                $result = $productController->getLowStockProducts($threshold, $limit, $offset);
                sendResponse($result);
                break;

            case 'getOutOfStockProducts':
                requireAdmin();
                $offset = intval($_GET['offset'] ?? 0);
                $limit = intval($_GET['limit'] ?? 50);
                $result = $productController->getOutOfStockProducts($limit, $offset);
                sendResponse($result);
                break;

            case 'countProducts':
                $result = $productController->countProducts();
                sendResponse($result);
                break;

            case 'countAllProducts':
                requireAdmin();
                $result = $productController->countAllProducts();
                sendResponse($result);
                break;

            case 'countProductsByCategory':
                $categoryId = intval($_GET['categoryId'] ?? 0);
                if ($categoryId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Category ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->countProductsByCategory($categoryId);
                sendResponse($result);
                break;

            case 'countProductsBySupplier':
                $supplierId = intval($_GET['supplierId'] ?? 0);
                if ($supplierId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Supplier ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->countProductsBySupplier($supplierId);
                sendResponse($result);
                break;

            case 'countLowStockProducts':
                requireAdmin();
                $threshold = intval($_GET['threshold'] ?? 10);
                $result = $productController->countLowStockProducts($threshold);
                sendResponse($result);
                break;

            case 'getProductStock':
                requireAuth();
                $productId = intval($_GET['productId'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->getProductStock($productId);
                sendResponse($result);
                break;

            case 'productExists':
                $productId = intval($_GET['productId'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->productExists($productId);
                sendResponse($result);
                break;

            default:
                sendResponse([
                    'success' => false,
                    'message' => 'Unknown action',
                    'code' => 400,
                    'context' => []
                ], 400);
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        switch ($action) {
            case 'createProduct':
                requireAdmin();
                verifyCsrf();
                $result = $productController->createProduct($data);
                sendResponse($result, $result['code']);
                break;

            case 'updateProduct':
                requireAdmin();
                verifyCsrf();
                $productId = intval($data['id'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->updateProduct($productId, $data);
                sendResponse($result, $result['code']);
                break;

            case 'partialUpdateProduct':
                requireAdmin();
                verifyCsrf();
                $productId = intval($data['id'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->partialUpdateProduct($productId, $data);
                sendResponse($result, $result['code']);
                break;

            case 'updateStock':
                requireAdmin();
                verifyCsrf();
                $productId = intval($data['id'] ?? 0);
                $quantity = intval($data['quantity'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->updateStock($productId, $quantity);
                sendResponse($result, $result['code']);
                break;

            case 'decrementStock':
                requireAdmin();
                verifyCsrf();
                $productId = intval($data['id'] ?? 0);
                $quantity = intval($data['quantity'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->decrementStock($productId, $quantity);
                sendResponse($result, $result['code']);
                break;

            case 'incrementStock':
                requireAdmin();
                verifyCsrf();
                $productId = intval($data['id'] ?? 0);
                $quantity = intval($data['quantity'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->incrementStock($productId, $quantity);
                sendResponse($result, $result['code']);
                break;

            case 'deleteProduct':
                requireAdmin();
                verifyCsrf();
                $productId = intval($data['id'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->deleteProduct($productId);
                sendResponse($result, $result['code']);
                break;

            case 'restoreProduct':
                requireAdmin();
                verifyCsrf();
                $productId = intval($data['id'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->restoreProduct($productId);
                sendResponse($result, $result['code']);
                break;

            case 'hardDeleteProduct':
                requireAdmin();
                verifyCsrf();
                $productId = intval($data['id'] ?? 0);
                if ($productId <= 0) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Product ID is required',
                        'code' => 400,
                        'context' => []
                    ], 400);
                }
                $result = $productController->hardDeleteProduct($productId);
                sendResponse($result, $result['code']);
                break;

            default:
                sendResponse([
                    'success' => false,
                    'message' => 'Unknown action',
                    'code' => 400,
                    'context' => []
                ], 400);
        }
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Method not allowed',
            'code' => 405,
            'context' => []
        ], 405);
    }
} catch (RuntimeException $e) {
    sendResponse([
        'success' => false,
        'message' => 'Failed to process request: ' . $e->getMessage(),
        'data' => null,
        'code' => 500,
        'context' => []
    ], 500);
}
?>