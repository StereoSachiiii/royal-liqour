<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../../core/Session.php';

class OrderController extends BaseController
{
    public function __construct(
        private OrderService $service,
        private Session $session,
    ) {}

    public function cancel(int $id): array
    {
        $currentUserId = $this->session->get('user_id');
        $isAdmin = $this->session->get('is_admin');
        return $this->success($this->service->cancel($id, $currentUserId, $isAdmin));
    }

    public function create(array $data): array
    {
        return $this->success($this->service->create($data), 201);
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        try {
            $orders = $this->service->getAll($limit, $offset);
            return $this->success('Orders retrieved successfully', $orders);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getById(int $id): array
    {
        $order = $this->service->getById($id);
        if (!$order) {
            return $this->error('Order not found', 404);
        }
        return $this->success('Order retrieved successfully', $order);
    }

    public function getDetailedOrderById(int $id): array
    {
        $order = $this->service->getDetailedOrderById($id);
        if (!$order) {
            return $this->error('Order not found', 404);
        }
        return $this->success('Order details retrieved successfully', $order);
    }

    public function getByOrderNumber(string $orderNumber): array
    {
        return $this->success($this->service->getByOrderNumber($orderNumber));
    }

    public function getByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->success($this->service->getByUser($userId, $limit, $offset));
    }

    public function count(): array
    {
        return $this->success(['count' => $this->service->count()]);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            $orders = $this->service->search($query, $limit, $offset);
            return $this->success('Orders found', $orders);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->success($this->service->update($id, $data));
    }

    public function delete(int $id): array
    {
        $this->service->delete($id);
        return $this->success(['deleted' => true]);
    }
}