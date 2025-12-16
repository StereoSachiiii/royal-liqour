<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../validators/ProductValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';

class ProductService
{
    public function __construct(
        private ProductRepository $repo,
    ) {}

    public function create(array $data): array
    {
        ProductValidator::validateCreate($data);

        if ($this->repo->getByName($data['name'])) {
            throw new DuplicateException('Product name already exists', ['name' => $data['name']]);
        }

        if (isset($data['slug']) && $this->repo->getBySlug($data['slug'])) {
            throw new DuplicateException('Product slug already exists', ['slug' => $data['slug']]);
        }

        $product = $this->repo->create($data);
        return $product->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $products = $this->repo->getAll($limit, $offset);
        return array_map(fn($p) => $p->toArray(), $products);
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        $products = $this->repo->getAllIncludingInactive($limit, $offset);
        return array_map(fn($p) => $p->toArray(), $products);
    }

    public function getById(int $id): array
    {
        $product = $this->repo->getById($id);
        if (!$product) {
            throw new NotFoundException('Product not found');
        }
        return $product->toArray();
    }

    public function getByIdAdmin(int $id): array
    {
        $product = $this->repo->getByIdAdmin($id);
        if (!$product) {
            throw new NotFoundException('Product not found');
        }
        return $product->toArray();
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        if (empty(trim($query))) {
            $products = $this->repo->getAll($limit, $offset);
        } else {
            $products = $this->repo->search($query, $limit, $offset);
        }
        return array_map(fn($p) => $p->toArray(), $products);
    }

    public function count(bool $includeInactive = false): int
    {
        return $includeInactive ? $this->repo->countAll() : $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        ProductValidator::validateUpdate($data);

        $existing = $this->repo->getByIdAdmin($id);
        if (!$existing) {
            throw new NotFoundException('Product not found');
        }

        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new DatabaseException('Update failed');
        }

        return $updated->toArray();
    }

    public function delete(int $id, bool $hard = false): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Product not found');
        }

        $ok = $hard ? $this->repo->hardDelete($id) : $this->repo->delete($id);
        if (!$ok) {
            throw new DatabaseException($hard ? 'Hard delete failed' : 'Delete failed');
        }
    }

    public function restore(int $id): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Product not found');
        }

        if (!$this->repo->restore($id)) {
            throw new DatabaseException('Restore failed');
        }
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        return $this->repo->getAllEnriched($limit, $offset);
    }

    public function getTopSellers(int $limit = 10): array
    {
        return $this->repo->getTopSellers($limit);
    }

    public function shopAllEnriched(
        int $limit = 24,
        int $offset = 0,
        string $search = '',
        ?int $categoryId = null,
        ?int $minPrice = null,
        ?int $maxPrice = null,
        string $sort = 'newest'
    ): array {
        return $this->repo->shopAllEnriched($limit, $offset, $search, $categoryId, $minPrice, $maxPrice, $sort);
    }

    public function searchEnriched(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->repo->searchEnriched($query, $limit, $offset);
    }
}
