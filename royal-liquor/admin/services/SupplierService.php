<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/SupplierRepository.php';
require_once __DIR__ . '/../validators/SupplierValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';

class SupplierService
{
    public function __construct(
        private SupplierRepository $repo,
    ) {}

    public function create(array $data): array
    {
        SupplierValidator::validateCreate($data);
        
        if ($this->repo->existsByName($data['name'])) {
            throw new DuplicateException('Supplier with this name already exists');
        }
        
        $supplier = $this->repo->create($data);
        return $supplier->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $suppliers = $this->repo->getAll($limit, $offset);
        return array_map(fn($s) => $s->toArray(), $suppliers);
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        $suppliers = $this->repo->getAllIncludingInactive($limit, $offset);
        return array_map(fn($s) => $s->toArray(), $suppliers);
    }

    public function getById(int $id): array
    {
        $supplier = $this->repo->getById($id);
        if (!$supplier) {
            throw new NotFoundException('Supplier not found');
        }
        return $supplier->toArray();
    }

    public function getByIdEnriched(int $id): array
    {
        $supplier = $this->repo->getByIdEnriched($id);
        if (!$supplier) {
            throw new NotFoundException('Supplier not found');
        }
        return $supplier;
    }

    public function getByIdAdmin(int $id): array
    {
        $supplier = $this->repo->getByIdAdmin($id);
        if (!$supplier) {
            throw new NotFoundException('Supplier not found');
        }
        return $supplier->toArray();
    }

    public function getByName(string $name): array
    {
        $supplier = $this->repo->getByName($name);
        if (!$supplier) {
            throw new NotFoundException('Supplier not found');
        }
        return $supplier->toArray();
    }

    public function getByEmail(string $email): array
    {
        $supplier = $this->repo->getByEmail($email);
        if (!$supplier) {
            throw new NotFoundException('Supplier not found');
        }
        return $supplier->toArray();
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $suppliers = $this->repo->search($query, $limit, $offset);
        return array_map(fn($s) => $s->toArray(), $suppliers);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function countAll(): int
    {
        return $this->repo->countAll();
    }

    public function update(int $id, array $data): array
    {
        SupplierValidator::validateUpdate($data);
        
        if (isset($data['name']) && $this->repo->existsByName($data['name'], $id)) {
            throw new DuplicateException('Supplier with this name already exists');
        }
        
        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new NotFoundException('Supplier not found');
        }
        return $updated->toArray();
    }

    public function partialUpdate(int $id, array $data): array
    {
        SupplierValidator::validateUpdate($data);
        
        $existing = $this->repo->getByIdAdmin($id);
        if (!$existing) {
            throw new NotFoundException('Supplier not found');
        }
        
        if (isset($data['name']) && $data['name'] !== $existing->getName()) {
            if ($this->repo->getByName($data['name'])) {
                throw new DuplicateException('Supplier name already exists');
            }
        }
        
        $updated = $this->repo->partialUpdate($id, $data);
        if (!$updated) {
            throw new DatabaseException('Update failed');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Supplier not found');
        }
    }

    public function restore(int $id): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Supplier not found');
        }
        
        $restored = $this->repo->restore($id);
        if (!$restored) {
            throw new DatabaseException('Restore failed');
        }
    }

    public function hardDelete(int $id): void
    {
        if (!$this->repo->getByIdAdmin($id)) {
            throw new NotFoundException('Supplier not found');
        }
        
        $deleted = $this->repo->hardDelete($id);
        if (!$deleted) {
            throw new DatabaseException('Hard delete failed');
        }
    }
}