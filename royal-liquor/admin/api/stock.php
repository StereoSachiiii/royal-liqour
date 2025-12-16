<?php
declare(strict_types=1);

use Core\Request;

require_once __DIR__ . '/../repositories/StockRepository.php';
require_once __DIR__ . '/../services/StockService.php';
require_once __DIR__ . '/../controllers/StockController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CSRFMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../core/Router.php';

/** @var Router $router */

// Simple per-file DI for stock
$stockRepo       = new StockRepository();
$stockService    = new StockService($stockRepo);
$stockController = new StockController($stockService, Session::getInstance());

$router->group('/api/v1', function (Router $router) use ($stockController): void {
    // Available stock for a product
    $router->get('/stock/available/:product_id', function (Request $request, array $params) use ($stockController): array {
        $productId  = (int)($params['product_id'] ?? 0);
        return $stockController->getAvailableStock($productId);
    });

    // Stock summary for a product
    $router->get('/stock/summary/:product_id', function (Request $request, array $params) use ($stockController): array {
        $productId  = (int)($params['product_id'] ?? 0);
        return $stockController->getStockSummary($productId);
    });

    // All stock entries (paginated, with optional search)
    $router->get('/stock', function (Request $request) use ($stockController): array {
        $limit  = (int)$request->getQuery('limit', 50);
        $offset = (int)$request->getQuery('offset', 0);
        $search = trim((string)$request->getQuery('search', ''));
        
        // Use search method if query provided
        if ($search !== '') {
            return $stockController->search($search, $limit, $offset);
        }
        return $stockController->getAll($limit, $offset);
    });

    // Get by ID (enriched with product/warehouse names)
    $router->get('/stock/:id', function (Request $request, array $params) use ($stockController): array {
        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Stock ID required',
                'code'    => 400,
            ];
        }
        return $stockController->getByIdEnriched($id);
    });

    // Get by product AND warehouse
    $router->get('/stock/product/:product_id/warehouse/:warehouse_id', function (Request $request, array $params) use ($stockController): array {
        $productId    = (int)($params['product_id'] ?? 0);
        $warehouseId  = (int)($params['warehouse_id'] ?? 0);
        return $stockController->getByProductWarehouse($productId, $warehouseId);
    });

    // Get by product only
    $router->get('/stock/product/:product_id', function (Request $request, array $params) use ($stockController): array {
        $productId  = (int)($params['product_id'] ?? 0);
        return $stockController->getByProduct($productId);
    });

    // Get by warehouse only
    $router->get('/stock/warehouse/:warehouse_id', function (Request $request, array $params) use ($stockController): array {
        $warehouseId = (int)($params['warehouse_id'] ?? 0);
        return $stockController->getByWarehouse($warehouseId);
    });

    // Count stock rows
    $router->get('/stock/count', function (Request $request) use ($stockController): array {
        return $stockController->count();
    });

    // ORDER OPERATIONS (reserve/confirm/cancel/refund)
    $router->post('/stock/orders/:order_id/:action', function (Request $request, array $params) use ($stockController): array {
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('stock_order_action', 10, 60);

        $orderId    = (int)($params['order_id'] ?? 0);
        $action     = (string)($params['action'] ?? '');

        if ($orderId <= 0) {
            return [
                'success' => false,
                'message' => 'Valid order ID required',
                'code'    => 400,
            ];
        }

        return match ($action) {
            'reserve' => $stockController->reserveStock($orderId),
            'confirm' => $stockController->confirmPayment($orderId),
            'cancel'  => $stockController->cancelOrder($orderId),
            'refund'  => $stockController->refundOrder($orderId),
            default   => [
                'success' => false,
                'message' => 'Invalid action. Use: reserve, confirm, cancel, refund',
                'code'    => 400,
            ],
        };
    });

    // Warehouse transfer
    $router->post('/stock/transfer', function (Request $request) use ($stockController): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('stock_transfer', 10, 60);

        $body       = $request->getAllBody();

        if (!isset($body['product_id'], $body['from_warehouse_id'], $body['to_warehouse_id'], $body['quantity'])) {
            return [
                'success' => false,
                'message' => 'product_id, from_warehouse_id, to_warehouse_id, and quantity required',
                'code'    => 400,
            ];
        }

        $productId       = (int)$body['product_id'];
        $fromWarehouseId = (int)$body['from_warehouse_id'];
        $toWarehouseId   = (int)$body['to_warehouse_id'];
        $quantity        = (int)$body['quantity'];
        $reason          = $body['reason'] ?? null;

        return $stockController->transferStock($productId, $fromWarehouseId, $toWarehouseId, $quantity, $reason);
    });

    // Stock adjustment
    $router->post('/stock/adjust', function (Request $request) use ($stockController): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('stock_adjust', 10, 60);

        $body       = $request->getAllBody();

        if (!isset($body['product_id'], $body['warehouse_id'], $body['adjustment'])) {
            return [
                'success' => false,
                'message' => 'product_id, warehouse_id, and adjustment required',
                'code'    => 400,
            ];
        }

        $productId   = (int)$body['product_id'];
        $warehouseId = (int)$body['warehouse_id'];
        $adjustment  = (int)$body['adjustment'];
        $reason      = $body['reason'] ?? null;

        return $stockController->adjustStock($productId, $warehouseId, $adjustment, $reason);
    });

    // Regular stock creation (admin only)
    $router->post('/stock', function (Request $request) use ($stockController): array {
        AuthMiddleware::requireAdmin();
        RateLimitMiddleware::check('stock_create', 5, 60);

        $body       = $request->getAllBody();
        return $stockController->create($body);
    });

    // Update stock
    $router->put('/stock/:id', function (Request $request, array $params) use ($stockController): array {
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('stock_update', 5, 60);

        $body       = $request->getAllBody();
        $id         = (int)($params['id'] ?? 0);

        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Stock ID required',
                'code'    => 400,
            ];
        }

        return $stockController->update($id, $body);
    });

    // Update by product + warehouse
    $router->put('/stock/product/:product_id/warehouse/:warehouse_id', function (Request $request, array $params) use ($stockController): array {
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('stock_update', 5, 60);

        $body         = $request->getAllBody();
        $productId    = (int)($params['product_id'] ?? 0);
        $warehouseId  = (int)($params['warehouse_id'] ?? 0);

        return $stockController->updateByProductWarehouse($productId, $warehouseId, $body);
    });

    // Delete by ID or product+warehouse
    $router->delete('/stock/:id', function (Request $request, array $params) use ($stockController): array {
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('stock_delete', 5, 60);

        $id         = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Stock ID required',
                'code'    => 400,
            ];
        }

        return $stockController->delete($id);
    });

    $router->delete('/stock/product/:product_id/warehouse/:warehouse_id', function (Request $request, array $params) use ($stockController): array {
        AuthMiddleware::requireAdmin();
        CsrfMiddleware::verifyCsrf();
        RateLimitMiddleware::check('stock_delete', 5, 60);

        $productId    = (int)($params['product_id'] ?? 0);
        $warehouseId  = (int)($params['warehouse_id'] ?? 0);

        return $stockController->deleteByProductWarehouse($productId, $warehouseId);
    });
});