<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/FeedbackRepository.php';
require_once __DIR__ . '/../validators/FeedbackValidator.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';

class FeedbackService
{
    public function __construct(
        private FeedbackRepository $repo,
    ) {}

    public function create(array $data): array
    {
        FeedbackValidator::validateCreate($data);

        if ($this->repo->exists($data['user_id'], $data['product_id'])) {
            throw new DuplicateException('Feedback from this user for this product already exists.');
        }

        $feedback = $this->repo->create($data);
        return $feedback->toArray();
    }

    public function getAll(): array
    {
        return $this->repo->getAllWithProductDetails();
    }

    public function getAllPaginated(int $limit = 50, int $offset = 0): array
    {
        return $this->repo->getAllPaginated($limit, $offset);
    }

    public function getById(int $id): array
    {
        $feedback = $this->repo->getByIdEnriched($id);
        if (!$feedback) {
            throw new NotFoundException('Feedback not found.');
        }
        return $feedback;
    }

    public function getByIdEnriched(int $id): array
    {
        $feedback = $this->repo->getByIdEnriched($id);
        if (!$feedback) {
            throw new NotFoundException('Feedback not found.');
        }
        return $feedback;
    }

    public function getByProductId(int $productId): array
    {
        // Return feedback for a product - could be enriched
        $sql = "SELECT f.*, u.name as user_name 
                FROM feedback f 
                LEFT JOIN users u ON f.user_id = u.id 
                WHERE f.product_id = :product_id AND f.deleted_at IS NULL 
                ORDER BY f.created_at DESC";
        // For now return empty - this would need repository method
        return [];
    }

    public function getByUserId(int $userId): array
    {
        // Return feedback by user - could be enriched
        return [];
    }

    public function getAverageRating(int $productId): array
    {
        // Return average rating for product
        return ['average' => 0, 'count' => 0];
    }

    public function getAllWithProductDetails(): array
    {
        $feedbacks = $this->repo->getAllWithProductDetails();
        return array_map(fn($f) => is_array($f) ? $f : $f->toArray(), $feedbacks);
    }

    public function update(int $id, array $data): array
    {
        FeedbackValidator::validateUpdate($data);
        
        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new NotFoundException('Feedback not found.');
        }
        
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        // Soft delete
        $result = $this->repo->softDelete($id);
        if (!$result) {
            throw new NotFoundException('Feedback not found.');
        }
    }

    public function hardDelete(int $id): void
    {
        $result = $this->repo->hardDelete($id);
        if (!$result) {
            throw new NotFoundException('Feedback not found.');
        }
    }
}
