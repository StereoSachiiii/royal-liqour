<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/ProductRecognitionRepository.php';
require_once __DIR__ . '/../validators/ProductRecognitionValidator.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';

class ProductRecognitionService
{
    public function __construct(
        private ProductRecognitionRepository $repo,
    ) {}

    public function create(array $data): array
    {
        ProductRecognitionValidator::validateCreate($data);
        $result = $this->repo->create($data);
        return $result->toArray();
    }

    public function getById(int $id): array
    {
        $recognition = $this->repo->getById($id);
        if (!$recognition) {
            throw new NotFoundException('Recognition record not found');
        }
        return $recognition->toArray();
    }

    public function update(int $id, array $data): array
    {
        ProductRecognitionValidator::validateUpdate($data);
        $result = $this->repo->update($id, $data);
        if (!$result) {
            // No fields to update or record not found
            $existing = $this->repo->getById($id);
            if (!$existing) {
                throw new NotFoundException('Recognition record not found');
            }
            return $existing->toArray();
        }
        return $result->toArray();
    }

    public function getBySessionId(string $sessionId): array
    {
        $recognitions = $this->repo->getBySessionId($sessionId);
        return array_map(fn($r) => $r->toArray(), $recognitions);
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Recognition record not found');
        }
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $recognitions = $this->repo->getAll($limit, $offset);
        return array_map(fn($r) => $r->toArray(), $recognitions);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $recognitions = $this->repo->search($query, $limit, $offset);
        return array_map(fn($r) => $r->toArray(), $recognitions);
    }

    public function count(): int
    {
        return $this->repo->count();
    }
}
