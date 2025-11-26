<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/CartItemRepository.php';
require_once __DIR__ . '/../validators/CartItemValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class CartItemController
{
    private CartItemRepository $repo;
    private Session $session;

    public function __construct()
    {
        $this->repo = new CartItemRepository();
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
            "[%s] CartItemController Error: %s | File: %s:%d | Context: %s",
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
        } catch (ValidationException | NotFoundException | DatabaseException $e) {
            return $this->error($e);
        } catch (Throwable $e) {
            return $this->error(new Exception('Unexpected error: ' . $e->getMessage(), 500));
        }
    }

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            CartItemValidator::validateCreate($data);

            if ($this->repo->getByCartProduct($data['cart_id'], $data['product_id'])) {
                throw new DuplicateException('Item already in cart');
            }

            $item = $this->repo->create($data);
            return $this->success('Cart item added', $item->toArray(), 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $items = $this->repo->getAll($limit, $offset);
            $data = array_map(fn($i) => $i->toArray(), $items);
            return $this->success('Cart items retrieved', $data);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $item = $this->repo->getById($id);
            if (!$item) throw new NotFoundException('Cart item not found');
            return $this->success('Cart item retrieved', $item->toArray());
        });
    }

    public function getByCartProduct(int $cartId, int $productId): array
    {
        return $this->handle(function () use ($cartId, $productId) {
            $item = $this->repo->getByCartProduct($cartId, $productId);
            if (!$item) throw new NotFoundException('Cart item not found');
            return $this->success('Cart item retrieved', $item->toArray());
        });
    }

    public function getByCart(int $cartId): array
    {
        return $this->handle(function () use ($cartId) {
            $items = $this->repo->getByCart($cartId);
            $data = array_map(fn($i) => $i->toArray(), $items);
            return $this->success('Cart items retrieved', $data);
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = $this->repo->count();
            return $this->success('Count retrieved', $count);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            CartItemValidator::validateUpdate($data);
            $updated = $this->repo->update($id, $data);
            if (!$updated) throw new NotFoundException('Cart item not found');
            return $this->success('Cart item updated', $updated->toArray());
        });
    }

    public function updateByCartProduct(int $cartId, int $productId, array $data): array
    {
        return $this->handle(function () use ($cartId, $productId, $data) {
            CartItemValidator::validateUpdate($data);
            $updated = $this->repo->updateByCartProduct($cartId, $productId, $data);
            if (!$updated) throw new NotFoundException('Cart item not found');
            return $this->success('Cart item updated', $updated->toArray());
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $deleted = $this->repo->delete($id);
            if (!$deleted) throw new NotFoundException('Cart item not found');
            return $this->success('Cart item deleted');
        });
    }

    public function deleteByCartProduct(int $cartId, int $productId): array
    {
        return $this->handle(function () use ($cartId, $productId) {
            $deleted = $this->repo->deleteByCartProduct($cartId, $productId);
            if (!$deleted) throw new NotFoundException('Cart item not found');
            return $this->success('Cart item deleted');
        });
    }
}