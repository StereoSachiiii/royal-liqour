<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/PaymentRepository.php';
require_once __DIR__ . '/../validators/PaymentValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class PaymentService
{
    public function __construct(
        private PaymentRepository $repo,
    ) {}

    public function create(array $data): array
    {
        PaymentValidator::validateCreate($data);
        $payment = $this->repo->create($data);
        return $payment->toArray();
    }

    public function hardDelete(int $id): void
    {
        $deleted = $this->repo->hardDelete($id);
        if (!$deleted) {
            throw new NotFoundException('Payment not found');
        }
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $payments = $this->repo->getAll($limit, $offset);
        return array_map(fn($p) => $p->toArray(), $payments);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $payments = $this->repo->search($query, $limit, $offset);
        return array_map(fn($p) => $p->toArray(), $payments);
    }

    public function getById(int $id): array
    {
        $payment = $this->repo->getById($id);
        if (!$payment) {
            throw new NotFoundException('Payment not found');
        }
        return $payment->toArray();
    }

    public function getByOrder(int $orderId): array
    {
        $payments = $this->repo->getByOrder($orderId);
        return array_map(fn($p) => $p->toArray(), $payments);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        PaymentValidator::validateUpdate($data);
        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new NotFoundException('Payment not found');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Payment not found');
        }
    }
}
