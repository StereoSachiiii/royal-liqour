<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/WarehouseRepository.php';
require_once __DIR__ . '/../validators/WarehouseValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';

class WarehouseService
{
    public function __construct(
        private WarehouseRepository $repo,
    ) {}

    public function create(array $data): array
    {
        WarehouseValidator::validateCreate($data);
        
        if ($this->repo->existsByName($data['name'])) {
            throw new DuplicateException('Warehouse with this name already exists');
        }
        
        $warehouse = $this->repo->create($data);
        return $warehouse->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $warehouses = $this->repo->getAll($limit, $offset);
        return array_map(fn($w) => $w->toArray(), $warehouses);
    }

    public function getById(int $id): array
    {
        $warehouse = $this->repo->getById($id);
        if (!$warehouse) {
            throw new NotFoundException('Warehouse not found');
        }
        return $warehouse->toArray();
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        WarehouseValidator::validateUpdate($data);
        
        if (isset($data['name']) && $this->repo->existsByName($data['name'], $id)) {
            throw new DuplicateException('Warehouse with this name already exists');
        }
        
        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new NotFoundException('Warehouse not found');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Warehouse not found');
        }
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        $warehouses = $this->repo->getAllIncludingInactive($limit, $offset);
        return array_map(fn($w) => $w->toArray(), $warehouses);
    }

    public function getByIdAdmin(int $id): array
    {
        $warehouse = $this->repo->getByIdAdmin($id);
        if (!$warehouse) {
            throw new NotFoundException('Warehouse not found');
        }
        return $warehouse->toArray();
    }

    public function getByName(string $name): array
    {
        $warehouse = $this->repo->getByName($name);
        if (!$warehouse) {
            throw new NotFoundException('Warehouse not found');
        }
        return $warehouse->toArray();
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $warehouses = $this->repo->search($query, $limit, $offset);
        return array_map(fn($w) => $w->toArray(), $warehouses);
    }

    public function countAll(): int
    {
        return $this->repo->countAll();
    }

    public function partialUpdate(int $id, array $data): array
    {
        WarehouseValidator::validateUpdate($data);
        
        $existing = $this->repo->getByIdAdmin($id);
        if (!$existing) {
            throw new NotFoundException('Warehouse not found');
        }
        
        if (isset($data['name']) && $data['name'] !== $existing->getName()) {
            if ($this->repo->getByName($data['name'])) {
                throw new DuplicateException('Warehouse name already exists');
            }
        }
        
        $updated = $this->repo->partialUpdate($id, $data);
        if (!$updated) {
            throw new DatabaseException('Update failed');
        }
        return $updated->toArray();
    }

    public function restore(int $id): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Warehouse not found');
        }
        
        $restored = $this->repo->restore($id);
        if (!$restored) {
            throw new DatabaseException('Restore failed');
        }
    }

    public function hardDelete(int $id): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Warehouse not found');
        }
        
        $deleted = $this->repo->hardDelete($id);
        if (!$deleted) {
            throw new DatabaseException('Hard delete failed');
        }
    }
}