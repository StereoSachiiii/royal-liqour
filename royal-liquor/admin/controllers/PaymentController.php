<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/PaymentService.php';

class PaymentController extends BaseController
{
    public function __construct(
        private PaymentService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->success('Payment created', $this->service->create($data), 201);
    }

    public function hardDelete(int $id): array
    {
        $this->service->hardDelete($id);
        return $this->success('Payment permanently deleted');
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->success('Payments retrieved', $this->service->getAll($limit, $offset));
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->success('Search results', $this->service->search($query, $limit, $offset));
    }

    public function getById(int $id): array
    {
        return $this->success('Payment retrieved', $this->service->getById($id));
    }

    public function getByOrder(int $orderId): array
    {
        return $this->success('Order payments retrieved', $this->service->getByOrder($orderId));
    }

    public function count(): array
    {
        return $this->success('Count retrieved', ['count' => $this->service->count()]);
    }

    public function update(int $id, array $data): array
    {
        return $this->success('Payment updated', $this->service->update($id, $data));
    }

    public function delete(int $id): array
    {
        $this->service->delete($id);
        return $this->success('Payment deleted');
    }
}
