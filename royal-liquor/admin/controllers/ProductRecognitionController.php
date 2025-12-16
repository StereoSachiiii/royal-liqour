<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ProductRecognitionService.php';

class ProductRecognitionController extends BaseController
{
    public function __construct(
        private ProductRecognitionService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->success('Product recognition created', $this->service->create($data), 201);
    }

    public function getById(int $id): array
    {
        return $this->success('Product recognition retrieved', $this->service->getById($id));
    }

    public function update(int $id, array $data): array
    {
        return $this->success('Product recognition updated', $this->service->update($id, $data));
    }

    public function getBySessionId(string $sessionId): array
    {
        return $this->success('Session recognitions retrieved', $this->service->getBySessionId($sessionId));
    }

    public function delete(int $id): array
    {
        $this->service->delete($id);
        return $this->success('Product recognition deleted');
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->success('Product recognitions retrieved', $this->service->getAll($limit, $offset));
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->success('Search results', $this->service->search($query, $limit, $offset));
    }

    public function count(): array
    {
        return $this->success('Count retrieved', ['count' => $this->service->count()]);
    }
}
