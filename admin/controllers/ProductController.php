<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../validators/ProductValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';

class ProductController
{
    private ProductRepository $repo;
    private Session $session;

    public function __construct()
    {
        $this->repo = new ProductRepository();
        $this->session = Session::getInstance();
    }

    private function success(string $message, $data = [], int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
            'context' => []
        ];
    }

    private function logError(Throwable $e, array $context = []): void
    {
        error_log(sprintf(
            "[%s] ProductController Error: %s | File: %s:%d | Context: %s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            json_encode($context)
        ));
    }

    private function error(Throwable $e): array
    {
        $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $context = method_exists($e, 'getContext') ? $e->getContext() : [];

        $this->logError($e, $context);

        return [
            'success' => false,
            'message' => $e->getMessage(),
            'code'    => $code,
            'context' => $context
        ];
    }

    private function handle(callable $callback): array
    {
        try {
            return $callback();
        } catch (ValidationException | NotFoundException | DatabaseException | DuplicateException $e) {
            return $this->error($e);
        } catch (Throwable $e) {
            return $this->error(new Exception('Unexpected error: ' . $e->getMessage(), 500));
        }
    }

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            ProductValidator::validateCreate($data);

            if ($this->repo->getByName($data['name'])) {
                throw new DuplicateException('Product name already exists');
            }

            if (isset($data['slug']) && $this->repo->getBySlug($data['slug'])) {
                throw new DuplicateException('Product slug already exists');
            }

            $product = $this->repo->create($data);
            return $this->success('Product created', $product->toArray(), 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $products = $this->repo->getAll($limit, $offset);
            $data = array_map(fn($p) => $p->toArray(), $products);
            return $this->success('Products retrieved', $data);
        });
    }

    public function getAllIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $products = $this->repo->getAllIncludingInactive($limit, $offset);
            $data = array_map(fn($p) => $p->toArray(), $products);
            return $this->success('All products retrieved', $data);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $product = $this->repo->getById($id);
            if (!$product) throw new NotFoundException('Product not found');
            return $this->success('Product retrieved', $product->toArray());
        });
    }

    public function getByIdAdmin(int $id): array
    {
        return $this->handle(function () use ($id) {
            $product = $this->repo->getByIdAdmin($id);
            if (!$product) throw new NotFoundException('Product not found');
            return $this->success('Product retrieved', $product->toArray());
        });
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            if (empty(trim($query))) throw new ValidationException('Search query required');
            $products = $this->repo->search($query, $limit, $offset);
            $data = array_map(fn($p) => $p->toArray(), $products);
            return $this->success('Search results', $data);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = $this->repo->count();
            return $this->success('Count retrieved', $count);
        });
    }

    public function countAll(): array
    {
        return $this->handle(function () {
            $count = $this->repo->countAll();
            return $this->success('Total count retrieved', $count);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            ProductValidator::validateUpdate($data);

            $existing = $this->repo->getByIdAdmin($id);
            if (!$existing) throw new NotFoundException('Product not found');

            if (isset($data['name']) && $data['name'] !== $existing->getName()) {
                if ($this->repo->getByName($data['name'])) {
                    throw new DuplicateException('Product name already exists');
                }
            }

            if (isset($data['slug']) && $data['slug'] !== $existing->getSlug()) {
                if ($this->repo->getBySlug($data['slug'])) {
                    throw new DuplicateException('Product slug already exists');
                }
            }

            $updated = $this->repo->update($id, $data);
            if (!$updated) throw new DatabaseException('Update failed');
            return $this->success('Product updated', $updated->toArray());
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            if (!$this->repo->getByIdAdmin($id)) throw new NotFoundException('Product not found');

            $deleted = $this->repo->delete($id);
            if (!$deleted) throw new DatabaseException('Delete failed');
            return $this->success('Product deleted');
        });
    }

    public function restore(int $id): array
    {
        return $this->handle(function () use ($id) {
            if (!$this->repo->getByIdAdmin($id)) throw new NotFoundException('Product not found');

            $restored = $this->repo->restore($id);
            if (!$restored) throw new DatabaseException('Restore failed');
            return $this->success('Product restored');
        });
    }

    public function hardDelete(int $id): array
    {
        return $this->handle(function () use ($id) {
            if (!$this->repo->getByIdAdmin($id)) throw new NotFoundException('Product not found');

            $deleted = $this->repo->hardDelete($id);
            if (!$deleted) throw new DatabaseException('Hard delete failed');
            return $this->success('Product permanently deleted');
        });
    }
}