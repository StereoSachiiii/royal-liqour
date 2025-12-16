<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../validators/OrderValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class OrderService
{
    public function __construct(
        private OrderRepository $repo,
    ) {}

    public function create(array $data): array
    {
        OrderValidator::validateCreate($data);
        $order = $this->repo->create($data);
        return $order->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $orders = $this->repo->getAll($limit, $offset);
        return array_map(fn($o) => $o->toArray(), $orders);
    }

    public function getById(int $id): array
    {
        $order = $this->repo->getById($id);
        if (!$order) {
            throw new NotFoundException('Order not found');
        }
        return $order->toArray();
    }

    public function getDetailedOrderById(int $id): array
    {
        $orderModel = $this->repo->getById($id);
        if (!$orderModel) {
            throw new NotFoundException('Order not found');
        }

        $detailedOrder = $this->repo->getDetailedOrderById($id);
        if (!$detailedOrder) {
            throw new NotFoundException('Order not found');
        }

        return $detailedOrder;
    }

    public function getByOrderNumber(string $orderNumber): array
    {
        $order = $this->repo->getByOrderNumber($orderNumber);
        if (!$order) {
            throw new NotFoundException('Order not found');
        }
        return $order->toArray();
    }

    public function getByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        $orders = $this->repo->getByUser($userId, $limit, $offset);
        return array_map(fn($o) => $o->toArray(), $orders);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $orders = $this->repo->search($query, $limit, $offset);
        return array_map(fn($o) => $o->toArray(), $orders);
    }

    public function update(int $id, array $data): array
    {
        OrderValidator::validateUpdate($data);
        if (!$this->repo->getById($id)) {
            throw new NotFoundException('Order not found');
        }
        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new NotFoundException('Order not found');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Order not found');
        }
    }

    public function cancel(int $id, int $currentUserId, bool $isAdmin): array
    {
        $order = $this->repo->getById($id);
        if (!$order) {
            throw new NotFoundException('Order not found');
        }

        if (!$isAdmin && $order->user_id !== $currentUserId) {
            throw new NotFoundException('Order not found or access denied');
        }

        if (in_array($order->status, ['shipped', 'delivered', 'refunded', 'cancelled'])) {
            throw new ValidationException('Order cannot be cancelled in its current state: ' . $order->status);
        }

        $cancelledOrder = $this->repo->cancelOrder($id);
        if (!$cancelledOrder) {
            throw new DatabaseException('Failed to update order status during cancellation');
        }

        return $cancelledOrder->toArray();
    }
}
