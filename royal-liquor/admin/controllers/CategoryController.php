<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/CategoryService.php';

class CategoryController extends BaseController
{
    public function __construct(
        private CategoryService $service,
    ) {}

    public function create(array $data): array
    {
        $created = $this->service->create($data);
        return $this->success('Category created successfully', $created);
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $categories = $this->service->getAll($limit, $offset);
        return $this->success('Categories retrieved successfully', $categories);
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        $categories = $this->service->getAllIncludingInactive($limit, $offset);
        return $this->success('All categories (including inactive) retrieved successfully', $categories);
    }

    public function getProductsByCategoryIdEnriched(int $categoryId, int $limit = 50, int $offset = 0): array
    {
        $data = $this->service->getProductsByCategoryIdEnriched($categoryId, $limit, $offset);
        return $this->success('Category products retrieved successfully', $data);
    }

    public function getById(int $id): array
    {
        $category = $this->service->getById($id);
        return $this->success('Category retrieved successfully', $category);
    }

    public function getByIdAdmin(int $id): array
    {
        $category = $this->service->getByIdAdmin($id);
        return $this->success('Category (admin) retrieved successfully', $category);
    }

    public function getByName(string $name): array
    {
        $category = $this->service->getByName($name);
        return $this->success('Category retrieved successfully', $category);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $results = $this->service->search($query, $limit, $offset);
        return $this->success('Categories search results', $results);
    }

    public function count(bool $includeInactive = false): array
    {
        $count = ['count' => $this->service->count($includeInactive)];
        return $this->success('Category count retrieved successfully', $count);
    }

    public function countAll(): array
    {
        $count = ['count' => $this->service->count(true)];
        return $this->success('Category total count retrieved successfully', $count);
    }

    public function update(int $id, array $data): array
    {
        $updated = $this->service->update($id, $data);
        return $this->success('Category updated successfully', $updated);
    }

    public function delete(int $id, bool $hard = false): array
    {
        $this->service->delete($id, $hard);
        return $this->success('Category deleted successfully', ['deleted' => true]);
    }

    public function restore(int $id): array
    {
        $this->service->restore($id);
        return $this->success(['restored' => true]);
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        $data = $this->service->getAllEnriched($limit, $offset);
        return $this->success('Categories (enriched) retrieved successfully', $data);
    }

    public function getByIdEnriched(int $id): array
    {
        $data = $this->service->getByIdEnriched($id);
        return $this->success('Category (enriched) retrieved successfully', $data);
    }

    public function searchEnriched(string $query, int $limit = 50, int $offset = 0): array
    {
        $results = $this->service->searchEnriched($query, $limit, $offset);
        return $this->success('Categories search results (enriched)', $results);
    }
}