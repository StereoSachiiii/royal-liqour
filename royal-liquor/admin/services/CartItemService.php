<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/CartItemRepository.php';
require_once __DIR__ . '/../validators/CartItemValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';

class CartItemService
{
    public function __construct(
        private CartItemRepository $repo,
    ) {}

    public function create(array $data): array
    {
        CartItemValidator::validateCreate($data);

        if ($this->repo->getByCartProduct($data['cart_id'], $data['product_id'])) {
            throw new DuplicateException('Item already in cart');
        }

        $item = $this->repo->create($data);
        return $item->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $items = $this->repo->getAll($limit, $offset);
        return array_map(fn($i) => $i->toArray(), $items);
    }

    public function getAllPaginated(int $limit = 50, int $offset = 0): array
    {
        return $this->repo->getAllPaginated($limit, $offset);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->repo->search($query, $limit, $offset);
    }

    public function getById(int $id): array
    {
        $item = $this->repo->getById($id);
        if (!$item) {
            throw new NotFoundException('Cart item not found');
        }
        return $item->toArray();
    }

    public function getByIdEnriched(int $id): array
    {
        $item = $this->repo->getByIdEnriched($id);
        if (!$item) {
            throw new NotFoundException('Cart item not found');
        }
        return $item;
    }

    public function getByCartProduct(int $cartId, int $productId): array
    {
        $item = $this->repo->getByCartProduct($cartId, $productId);
        if (!$item) {
            throw new NotFoundException('Cart item not found');
        }
        return $item->toArray();
    }

    public function getByCart(int $cartId): array
    {
        $items = $this->repo->getByCart($cartId);
        return array_map(fn($i) => $i->toArray(), $items);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        CartItemValidator::validateUpdate($data);
        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new NotFoundException('Cart item not found');
        }
        return $updated->toArray();
    }

    public function updateByCartProduct(int $cartId, int $productId, array $data): array
    {
        CartItemValidator::validateUpdate($data);
        $updated = $this->repo->updateByCartProduct($cartId, $productId, $data);
        if (!$updated) {
            throw new NotFoundException('Cart item not found');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Cart item not found');
        }
    }

    public function deleteByCartProduct(int $cartId, int $productId): void
    {
        $deleted = $this->repo->deleteByCartProduct($cartId, $productId);
        if (!$deleted) {
            throw new NotFoundException('Cart item not found');
        }
    }
}
