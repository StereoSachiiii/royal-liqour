<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../validators/CartValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class CartController
{
    private CartRepository $repo;
    private Session $session;

    public function __construct()
    {
        $this->repo = new CartRepository();
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
            "[%s] CartController Error: %s | File: %s:%d | Context: %s",
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
            CartValidator::validateCreate($data);
            $cart = $this->repo->create($data);
            return $this->success('Cart created', $cart->toArray(), 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $carts = $this->repo->getAll($limit, $offset);
            $data = array_map(fn($c) => $c->toArray(), $carts);
            return $this->success('Carts retrieved', $data);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $cart = $this->repo->getById($id);
            if (!$cart) throw new NotFoundException('Cart not found');
            return $this->success('Cart retrieved', $cart->toArray());
        });
    }

    public function getActiveByUser(int $userId): array
    {
        return $this->handle(function () use ($userId) {
            $cart = $this->repo->getActiveByUser($userId);
            if (!$cart) throw new NotFoundException('Active cart not found');
            return $this->success('Active cart retrieved', $cart->toArray());
        });
    }

    public function getActiveBySession(string $sessionId): array
    {
        return $this->handle(function () use ($sessionId) {
            $cart = $this->repo->getActiveBySession($sessionId);
            if (!$cart) throw new NotFoundException('Active cart not found');
            return $this->success('Active cart retrieved', $cart->toArray());
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
            CartValidator::validateUpdate($data);
            $updated = $this->repo->update($id, $data);
            if (!$updated) throw new NotFoundException('Cart not found');
            return $this->success('Cart updated', $updated->toArray());
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $deleted = $this->repo->delete($id);
            if (!$deleted) throw new NotFoundException('Cart not found');
            return $this->success('Cart deleted');
        });
    }
}