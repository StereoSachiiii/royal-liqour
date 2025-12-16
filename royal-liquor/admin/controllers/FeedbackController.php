<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/FeedbackService.php';

class FeedbackController extends BaseController
{
    public function __construct(
        private FeedbackService $service,
    ) {}

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            $feedback = $this->service->create($data);
            return $this->success('Feedback created', $feedback, 201);
        });
    }

    public function getAll(): array
    {
        return $this->handle(function () {
            $data = $this->service->getAll();
            return $this->success('Feedback retrieved', $data);
        });
    }

    public function getAllPaginated(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $data = $this->service->getAllPaginated($limit, $offset);
            return $this->success('Feedback retrieved', $data);
        });
    }

    public function getAllWithProductDetails(): array
    {
        return $this->handle(function () {
            $data = $this->service->getAllWithProductDetails();
            return $this->success('Feedback with product details retrieved', $data);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $data = $this->service->getById($id);
            return $this->success('Feedback retrieved', $data);
        });
    }

    public function getByIdEnriched(int $id): array
    {
        return $this->handle(function () use ($id) {
            $data = $this->service->getByIdEnriched($id);
            return $this->success('Feedback retrieved', $data);
        });
    }

    public function getByProductId(int $productId): array
    {
        return $this->handle(function () use ($productId) {
            $data = $this->service->getByProductId($productId);
            return $this->success('Product feedback retrieved', $data);
        });
    }

    public function getByUserId(int $userId): array
    {
        return $this->handle(function () use ($userId) {
            $data = $this->service->getByUserId($userId);
            return $this->success('User feedback retrieved', $data);
        });
    }

    public function getAverageRating(int $productId): array
    {
        return $this->handle(function () use ($productId) {
            $data = $this->service->getAverageRating($productId);
            return $this->success('Average rating retrieved', $data);
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            $updated = $this->service->update($id, $data);
            return $this->success('Feedback updated', $updated);
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->delete($id);
            return $this->success('Feedback deleted', ['deleted' => true]);
        });
    }

    public function hardDelete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $this->service->hardDelete($id);
            return $this->success('Feedback permanently deleted', ['deleted' => true]);
        });
    }
}
