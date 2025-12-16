<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/CartService.php';

class CartController extends BaseController
{
    public function __construct(
        private CartService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->success('Cart created', $this->service->create($data), 201);
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->success('Carts retrieved', $this->service->getAll($limit, $offset));
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        return $this->success('Carts retrieved', $this->service->getAllPaginated($limit, $offset));
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->success('Carts retrieved', $this->service->search($query, $limit, $offset));
    }

    public function getById(int $id): array
    {
        return $this->success('Cart retrieved', $this->service->getById($id));
    }
    
    public function getByIdEnriched(int $id): array
    {
        return $this->success('Cart retrieved', $this->service->getByIdEnriched($id));
    }

    public function getActiveByUser(int $userId): array
    {
        return $this->success('Active cart retrieved', $this->service->getActiveByUser($userId));
    }

    public function getActiveBySession(string $sessionId): array
    {
        return $this->success('Active cart retrieved', $this->service->getActiveBySession($sessionId));
    }

    public function count(): array
    {
        return $this->success('Count retrieved', ['count' => $this->service->count()]);
    }

    public function update(int $id, array $data): array
    {
        return $this->success('Cart updated', $this->service->update($id, $data));
    }

    public function delete(int $id): array
    {
        $this->service->delete($id);
        return $this->success('Cart deleted', ['deleted' => true]);
    }
}