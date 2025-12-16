<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/SupplierService.php';

class SupplierController extends BaseController
{
    public function __construct(
        private SupplierService $service,
    ) {}

    public function create(array $data): array
    {
        $created = $this->service->create($data);
        return $this->success('Supplier created successfully', $created, 201);
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $suppliers = $this->service->getAll($limit, $offset);
        return $this->success('Suppliers retrieved successfully', $suppliers);
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        $suppliers = $this->service->getAllIncludingInactive($limit, $offset);
        return $this->success('Suppliers (including inactive) retrieved successfully', $suppliers);
    }

    public function getById(int $id): array
    {
        $supplier = $this->service->getById($id);
        return $this->success('Supplier retrieved successfully', $supplier);
    }

    public function getByIdAdmin(int $id): array
    {
        $supplier = $this->service->getByIdAdmin($id);
        return $this->success('Supplier (admin) retrieved successfully', $supplier);
    }

    public function getByIdEnriched(int $id): array
    {
        $supplier = $this->service->getByIdEnriched($id);
        return $this->success('Supplier details loaded successfully', $supplier);
    }

    public function getByName(string $name): array
    {
        $supplier = $this->service->getByName($name);
        return $this->success('Supplier retrieved successfully', $supplier);
    }

    public function getByEmail(array $data): array
    {
        $supplier = $this->service->getByEmail($data['email']);
        return $this->success('Supplier retrieved successfully', $supplier);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $results = $this->service->search($query, $limit, $offset);
        return $this->success('Suppliers search results', $results);
    }

    public function count(): array
    {
        $count = ['count' => $this->service->count()];
        return $this->success('Supplier count retrieved successfully', $count);
    }

    public function countAll(): array
    {
        $count = ['count' => $this->service->countAll()];
        return $this->success('Supplier total count retrieved successfully', $count);
    }

    public function update(int $id, array $data): array
    {
        $updated = $this->service->update($id, $data);
        return $this->success('Supplier updated successfully', $updated);
    }

    public function partialUpdate(int $id, array $data): array
    {
        $updated = $this->service->partialUpdate($id, $data);
        return $this->success('Supplier partially updated successfully', $updated);
    }

    public function delete(int $id): array
    {
        $this->service->delete($id);
        return $this->success('Supplier deleted', ['deleted' => true]);
    }

    public function restore(int $id): array
    {
        $this->service->restore($id);
        return $this->success('Supplier restored', ['restored' => true]);
    }

    public function hardDelete(int $id): array
    {
        $this->service->hardDelete($id);
        return $this->success('Supplier permanently deleted', ['deleted' => true]);
    }
}