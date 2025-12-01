<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/StockRepository.php';
require_once __DIR__ . '/../validators/StockValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class StockController
{
    private StockRepository $repo;
    private Session $session;

    public function __construct()
    {
        $this->repo = new StockRepository();
        $this->session = Session::getInstance();
    }
    public function reserveStock(int $orderId): array
{
    return $this->handle(function () use ($orderId) {
        $this->repo->reserveStock($orderId);
        return $this->success('Stock reserved for order');
    });
}


public function getAvailableStock(int $productId): array
{
    return $this->handle(function () use ($productId) {
        $available = $this->repo->getAvailableStockByProduct($productId);
        
        return $this->success('Available stock retrieved', [
            'product_id' => $productId,
            'available' => $available,
            'in_stock' => $available > 0
        ]);
    });
}
public function confirmPayment(int $orderId): array
{
    return $this->handle(function () use ($orderId) {
        $this->repo->confirmPayment($orderId);
        return $this->success('Payment confirmed, stock deducted');
    });
}
public function adjustStock(int $productId, int $warehouseId, int $adjustment, ?string $reason = null): array
{
    return $this->handle(function () use ($productId, $warehouseId, $adjustment, $reason) {
        $stock = $this->repo->getByProductAndWarehouse($productId, $warehouseId);
        if (!$stock) throw new NotFoundException('Stock not found');
        
        $newQuantity = $stock->getQuantity() + $adjustment;
        if ($newQuantity < $stock->getReserved()) {
            throw new ValidationException('Cannot reduce quantity below reserved amount');
        }
        
        $updated = $this->repo->update($stock->getId(), ['quantity' => $newQuantity]);
        
        // Optional: log the adjustment
        error_log("Stock adjusted: Product $productId, Warehouse $warehouseId, Change: $adjustment, Reason: $reason");
        
        return $this->success('Stock adjusted', $updated->toArray());
    });
}
public function cancelOrder(int $orderId): array
{
    return $this->handle(function () use ($orderId) {
        $this->repo->cancelOrder($orderId);
        return $this->success('Order cancelled, stock returned');
    });
}

public function refundOrder(int $orderId): array
{
    return $this->handle(function () use ($orderId) {
        $this->repo->refundOrder($orderId);
        return $this->success('Order refunded, stock returned');
    });
}
    private function success(string $message, $data = [], int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
            'context' => []
        ];
    }

    private function logError(Throwable $e, array $context = []): void
    {
        error_log(sprintf(
            "[%s] StockController Error: %s | File: %s:%d | Context: %s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            json_encode($context)
        ));
    }

    private function error(Throwable $e): array
    {
        $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $context = method_exists($e, 'getContext') ? $e->getContext() : [];

        $this->logError($e, $context);

        return [
            'success' => false,
            'message' => $e->getMessage(),
            'code'    => $code,
            'context' => $context
        ];
    }

    private function handle(callable $callback): array
    {
        try {
            return $callback();
        } catch (ValidationException | NotFoundException | DatabaseException $e) {
            return $this->error($e);
        } catch (Throwable $e) {
            return $this->error(new Exception('Unexpected error: ' . $e->getMessage(), 500));
        }
    }

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            StockValidator::validateCreate($data);

            if ($this->repo->getByProductAndWarehouse($data['product_id'], $data['warehouse_id'])) {
                throw new DuplicateException('Stock entry already exists for this product and warehouse');
            }

            $stock = $this->repo->create($data);
            return $this->success('Stock created', $stock->toArray(), 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $stocks = $this->repo->getAll($limit, $offset);
            $data = array_map(fn($s) => $s->toArray(), $stocks);
            return $this->success('Stocks retrieved', $data);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $stock = $this->repo->getById($id);
            if (!$stock) throw new NotFoundException('Stock not found');
            return $this->success('Stock retrieved', $stock->toArray());
        });
    }

    public function getByProductWarehouse(int $productId, int $warehouseId): array
    {
        return $this->handle(function () use ($productId, $warehouseId) {
            $stock = $this->repo->getByProductAndWarehouse($productId, $warehouseId);
            if (!$stock) throw new NotFoundException('Stock not found');
            return $this->success('Stock retrieved', $stock->toArray());
        });
    }

    public function getByProduct(int $productId): array
    {
        return $this->handle(function () use ($productId) {
            $stocks = $this->repo->getByProduct($productId);
            $data = array_map(fn($s) => $s->toArray(), $stocks);
            return $this->success('Product stocks retrieved', $data);
        });
    }

    public function getByWarehouse(int $warehouseId): array
    {
        return $this->handle(function () use ($warehouseId) {
            $stocks = $this->repo->getByWarehouse($warehouseId);
            $data = array_map(fn($s) => $s->toArray(), $stocks);
            return $this->success('Warehouse stocks retrieved', $data);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = $this->repo->count();
            return $this->success('Count retrieved', $count);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            StockValidator::validateUpdate($data);

            $updated = $this->repo->update($id, $data);
            if (!$updated) throw new NotFoundException('Stock not found');
            return $this->success('Stock updated', $updated->toArray());
        });
    }

    public function updateByProductWarehouse(int $productId, int $warehouseId, array $data): array
    {
        return $this->handle(function () use ($productId, $warehouseId, $data) {
            StockValidator::validateUpdate($data);

            $updated = $this->repo->updateByProductWarehouse($productId, $warehouseId, $data);
            if (!$updated) throw new NotFoundException('Stock not found');
            return $this->success('Stock updated', $updated->toArray());
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $deleted = $this->repo->delete($id);
            if (!$deleted) throw new NotFoundException('Stock not found');
            return $this->success('Stock deleted');
        });
    }

    public function deleteByProductWarehouse(int $productId, int $warehouseId): array
    {
        return $this->handle(function () use ($productId, $warehouseId) {
            $deleted = $this->repo->deleteByProductWarehouse($productId, $warehouseId);
            if (!$deleted) throw new NotFoundException('Stock not found');
            return $this->success('Stock deleted');
        });
    }
}