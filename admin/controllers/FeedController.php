
<?php
class FeedbackController {
    private FeedbackRepository $repository;
    private FeedbackValidator $validator;

    public function __construct() {
        $this->repository = new FeedbackRepository();
        $this->validator = new FeedbackValidator();
    }

    private function successResponse(string $message, $data, int $code = 200): array {
        return ['success' => true, 'message' => $message, 'data' => $data, 'code' => $code, 'context' => []];
    }

    private function errorResponse(Exception $error): array {
        $statusCode = method_exists($error, 'getStatusCode') ? $error->getStatusCode() : 500;
        error_log("[" . date('Y-m-d H:i:s') . "] FeedbackController: " . $error->getMessage());
        return ['success' => false, 'message' => $error->getMessage(), 'code' => $statusCode, 'context' => []];
    }

    private function handleRequest(callable $callback): array {
        try { return $callback(); } catch(Exception $ex) { return $this->errorResponse($ex); }
    }

    public function create($data): array {
        return $this->handleRequest(function() use ($data) {
            FeedbackValidator::validateCreate($data);
            $result = $this->repository->create($data);
            return $this->successResponse("Feedback created successfully.", $result->toArray(), 201);
        });
    }

    public function getById(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $feedback = $this->repository->getById($id);
            if (!$feedback) throw new NotFoundException("Feedback not found.");
            return $this->successResponse("Feedback retrieved successfully.", $feedback->toArray());
        });
    }

    public function getByProductId(int $productId): array {
        return $this->handleRequest(function() use ($productId) {
            $feedbacks = $this->repository->getByProductId($productId);
            return $this->successResponse("Product reviews retrieved successfully.", array_map(fn($f) => $f->toArray(), $feedbacks));
        });
    }

    public function getByUserId(int $userId): array {
        return $this->handleRequest(function() use ($userId) {
            $feedbacks = $this->repository->getByUserId($userId);
            return $this->successResponse("User reviews retrieved successfully.", array_map(fn($f) => $f->toArray(), $feedbacks));
        });
    }

    public function getAverageRating(int $productId): array {
        return $this->handleRequest(function() use ($productId) {
            $avgRating = $this->repository->getAverageRating($productId);
            return $this->successResponse("Average rating retrieved successfully.", ['product_id' => $productId, 'average_rating' => $avgRating]);
        });
    }

    public function update(int $id, array $data): array {
        return $this->handleRequest(function() use ($id, $data) {
            FeedbackValidator::validateUpdate($data);
            $updated = $this->repository->update($id, $data);
            if (!$updated) throw new NotFoundException("Feedback not found.");
            return $this->successResponse("Feedback updated successfully.", $updated->toArray());
        });
    }

    public function delete(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $result = $this->repository->delete($id);
            if (!$result) throw new NotFoundException("Feedback not found.");
            return $this->successResponse("Feedback deleted successfully.", ['id' => $id]);
        });
    }

    public function getAll(): array {
        return $this->handleRequest(function() {
            $feedbacks = $this->repository->getAll();
            return $this->successResponse("All feedback retrieved successfully.", array_map(fn($f) => $f->toArray(), $feedbacks));
        });
    }

    public function getAllPaginated(int $limit, int $offset): array {
        return $this->handleRequest(function() use ($limit, $offset) {
            FeedbackValidator::paginationParams($limit, $offset);
            $feedbacks = $this->repository->getAllPaginated($limit, $offset);
            return $this->successResponse("Feedback retrieved successfully.", array_map(fn($f) => $f->toArray(), $feedbacks));
        });
    }
}


?>