<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/CategoryRepository.php';
require_once __DIR__ . '/../validators/CategoryValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';

class CategoryService
{
    public function __construct(
        private CategoryRepository $repo,
    ) {}

    public function create(array $data): array
    {
        CategoryValidator::validateCreate($data);

        if ($this->repo->getByName($data['name'])) {
            throw new DuplicateException('Category name already exists', ['name' => $data['name']]);
        }

        if (isset($data['slug']) && $this->repo->getBySlug($data['slug'])) {
            throw new DuplicateException('Category slug already exists', ['slug' => $data['slug']]);
        }

        $category = $this->repo->create($data);
        return $category->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $categories = $this->repo->getAll($limit, $offset);
        return array_map(fn($c) => $c->toArray(), $categories);
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        $categories = $this->repo->getAllIncludingInactive($limit, $offset);
        return array_map(fn($c) => $c->toArray(), $categories);
    }

    public function getById(int $id): array
    {
        $category = $this->repo->getById($id);
        if (!$category) {
            throw new NotFoundException('Category not found');
        }
        return $category->toArray();
    }

    public function getByIdAdmin(int $id): array
    {
        $category = $this->repo->getByIdAdmin($id);
        if (!$category) {
            throw new NotFoundException('Category not found');
        }
        return $category->toArray();
    }

    public function getByName(string $name): array
    {
        $category = $this->repo->getByName($name);
        if (!$category) {
            throw new NotFoundException('Category not found');
        }
        return $category->toArray();
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        if (empty(trim($query))) {
            $categories = $this->repo->getAll($limit, $offset);
        } else {
            $categories = $this->repo->search($query, $limit, $offset);
        }
        return array_map(fn($c) => $c->toArray(), $categories);
    }

    public function count(bool $includeInactive = false): int
    {
        return $includeInactive ? $this->repo->countAll() : $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        CategoryValidator::validateUpdate($data);

        $existing = $this->repo->getByIdAdmin($id);
        if (!$existing) {
            throw new NotFoundException('Category not found');
        }

        if (isset($data['name']) && $data['name'] !== $existing->getName()) {
            if ($this->repo->getByName($data['name'])) {
                throw new DuplicateException('Category name already exists', ['name' => $data['name']]);
            }
        }

        if (isset($data['slug']) && $data['slug'] !== $existing->getSlug()) {
            if ($this->repo->getBySlug($data['slug'])) {
                throw new DuplicateException('Category slug already exists', ['slug' => $data['slug']]);
            }
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
            throw new NotFoundException('Category not found');
        }

        $ok = $hard ? $this->repo->hardDelete($id) : $this->repo->delete($id);
        if (!$ok) {
            throw new DatabaseException($hard ? 'Hard delete failed' : 'Delete failed');
        }
    }

    public function restore(int $id): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Category not found');
        }

        if (!$this->repo->restore($id)) {
            throw new DatabaseException('Restore failed');
        }
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        return $this->repo->getAllEnriched($limit, $offset);
    }

    public function getByIdEnriched(int $id): array
    {
        $data = $this->repo->getByIdEnriched($id);
        if (!$data) {
            throw new NotFoundException('Category not found');
        }
        return $data;
    }

    public function searchEnriched(string $query, int $limit = 50, int $offset = 0): array
    {
        if (empty(trim($query))) {
            return $this->repo->getAllEnriched($limit, $offset);
        }
        return $this->repo->searchEnriched($query, $limit, $offset);
    }

    public function getProductsByCategoryIdEnriched(int $categoryId, int $limit = 50, int $offset = 0): array
    {
        $items = $this->repo->getProductsByCategoryIdEnriched($categoryId, $limit, $offset);
        $total = $this->repo->countProductsByCategoryId($categoryId);
        return [
            'items' => $items,
            'pagination' => [
                'total'   => $total,
                'limit'   => $limit,
                'offset'  => $offset,
                'pages'   => (int)ceil($total / $limit),
            ],
        ];
    }
}
