<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/OrderItemRepository.php';
require_once __DIR__ . '/../repositories/StockRepository.php';
require_once __DIR__ . '/../validators/OrderItemValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class OrderItemController
{
    private OrderItemRepository $repo;
    private StockRepository $stockRepo;
    private Session $session;

    public function __construct()
    {
        $this->repo = new OrderItemRepository();
        $this->stockRepo = new StockRepository();
        $this->session = Session::getInstance();
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
            "[%s] OrderItemController Error: %s | File: %s:%d | Context: %s",
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

    /**
     * Adjust reserved stock when order item changes
     */
    private function adjustStockReservation(
        int $productId, 
        ?int $oldWarehouseId, 
        ?int $newWarehouseId, 
        int $oldQuantity, 
        int $newQuantity
    ): void {
        // Scenario 1: Warehouse changed
        if ($oldWarehouseId !== $newWarehouseId) {
            // Release from old warehouse
            if ($oldWarehouseId !== null) {
                $this->releaseReservation($productId, $oldWarehouseId, $oldQuantity);
            }
            
            // Reserve in new warehouse
            if ($newWarehouseId !== null) {
                $this->reserveInWarehouse($productId, $newWarehouseId, $newQuantity);
            }
        }
        // Scenario 2: Quantity changed (same warehouse)
        elseif ($oldQuantity !== $newQuantity && $oldWarehouseId !== null) {
            $quantityDiff = $newQuantity - $oldQuantity;
            
            if ($quantityDiff > 0) {
                // Increasing - reserve more
                $this->reserveInWarehouse($productId, $oldWarehouseId, $quantityDiff);
            } else {
                // Decreasing - release some
                $this->releaseReservation($productId, $oldWarehouseId, abs($quantityDiff));
            }
        }
    }

    /**
     * Reserve stock in a specific warehouse
     */
    private function reserveInWarehouse(int $productId, int $warehouseId, int $quantity): void
    {
        $stock = $this->stockRepo->getByProductAndWarehouse($productId, $warehouseId);
        
        if (!$stock) {
            throw new DatabaseException("No stock record for product $productId in warehouse $warehouseId");
        }
        
        $available = $stock->quantity - $stock->reserved;
        if ($available < $quantity) {
            throw new DatabaseException(
                "Insufficient stock in warehouse $warehouseId: available=$available, requested=$quantity"
            );
        }
        
        // Update reserved stock
        $this->stockRepo->updateByProductWarehouse($productId, $warehouseId, [
            'reserved' => $stock->reserved + $quantity
        ]);
    }

    /**
     * Release reserved stock from a warehouse
     */
    private function releaseReservation(int $productId, int $warehouseId, int $quantity): void
    {
        $stock = $this->stockRepo->getByProductAndWarehouse($productId, $warehouseId);
        
        if ($stock) {
            $newReserved = max(0, $stock->reserved - $quantity);
            $this->stockRepo->updateByProductWarehouse($productId, $warehouseId, [
                'reserved' => $newReserved
            ]);
        }
    }

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            OrderItemValidator::validateCreate($data);
            
            // Reserve stock if warehouse is assigned
            if (isset($data['warehouse_id']) && $data['warehouse_id'] !== null) {
                $this->reserveInWarehouse(
                    $data['product_id'], 
                    $data['warehouse_id'], 
                    $data['quantity']
                );
            }
            
            $item = $this->repo->create($data);
            return $this->success('Order item created', $item->toArray(), 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $items = $this->repo->getAll($limit, $offset);
            $data = array_map(fn($i) => $i->toArray(), $items);
            return $this->success('Order items retrieved', $data);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            OrderItemValidator::validateUpdate($data);
            
            // Get current state
            $current = $this->repo->getById($id);
            if (!$current) {
                throw new NotFoundException('Order item not found');
            }
            
            // Determine new values
            $newWarehouseId = $data['warehouse_id'] ?? $current->warehouse_id;
            $newQuantity = $data['quantity'] ?? $current->quantity;
            
            // Adjust stock reservations
            $this->adjustStockReservation(
                $current->product_id,
                $current->warehouse_id,
                $newWarehouseId,
                $current->quantity,
                $newQuantity
            );
            
            // Update the order item
            $item = $this->repo->update($id, $data);
            return $this->success('Order item updated', $item->toArray());
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $item = $this->repo->getById($id);
            if (!$item) throw new NotFoundException('Order item not found');
            return $this->success('Order item retrieved', $item->toArray());
        });
    }

    public function getByOrder(int $orderId): array
    {
        return $this->handle(function () use ($orderId) {
            $items = $this->repo->getByOrder($orderId);
            $data = array_map(fn($i) => $i->toArray(), $items);
            return $this->success('Order items retrieved', $data);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = $this->repo->count();
            return $this->success('Count retrieved', $count);
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            // Get current state to release stock
            $item = $this->repo->getById($id);
            if (!$item) {
                throw new NotFoundException('Order item not found');
            }
            
            // Release reserved stock if warehouse is assigned
            if ($item->warehouse_id !== null) {
                $this->releaseReservation(
                    $item->product_id, 
                    $item->warehouse_id, 
                    $item->quantity
                );
            }
            
            // Delete the order item
            $deleted = $this->repo->delete($id);
            if (!$deleted) throw new NotFoundException('Order item not found');
            
            return $this->success('Order item deleted');
        });
    }
}