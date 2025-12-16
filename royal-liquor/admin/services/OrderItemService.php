<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/OrderItemRepository.php';
require_once __DIR__ . '/../repositories/StockRepository.php';
require_once __DIR__ . '/../validators/OrderItemValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class OrderItemService
{
    public function __construct(
        private OrderItemRepository $repo,
        private StockRepository $stockRepo,
    ) {}

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
        return $item->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $items = $this->repo->getAll($limit, $offset);
        return array_map(fn($i) => $i->toArray(), $items);
    }

    public function getAllPaginated(int $limit = 50, int $offset = 0): array
    {
        return $this->repo->getAllPaginated($limit, $offset);
    }

    public function getByIdEnriched(int $id): ?array
    {
        return $this->repo->getByIdEnriched($id);
    }

    public function update(int $id, array $data): array
    {
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
        
        // Update order item
        $item = $this->repo->update($id, $data);
        return $item->toArray();
    }

    public function getById(int $id): array
    {
        $item = $this->repo->getById($id);
        if (!$item) {
            throw new NotFoundException('Order item not found');
        }
        return $item->toArray();
    }

    public function getByOrder(int $orderId): array
    {
        $items = $this->repo->getByOrder($orderId);
        return array_map(fn($i) => $i->toArray(), $items);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function delete(int $id): void
    {
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
        if (!$deleted) {
            throw new NotFoundException('Order item not found');
        }
    }
}
