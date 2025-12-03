
<?php
require_once __DIR__ . '/../repositories/FeedbackRepository.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';

require_once __DIR__ . '/../models/FeedbackModel.php';
//require_once __DIR__ . '/../validators/FeedbackValidator.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';
class FeedbackController {
    private FeedbackRepository $repository;
   // private FeedbackValidator $validator;

    public function __construct() {
        $this->repository = new FeedbackRepository();
        //$this->validator = new FeedbackValidator();
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
    public function getAllWithProductDetails(): array {
    return $this->handleRequest(function() {
        $feedbacks = $this->repository->getAllWithProductDetails();
        return $this->successResponse("All feedback with product details retrieved successfully.", $feedbacks);
    });
}
    public function hardDelete(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $result = $this->repository->hardDelete($id);
            if (!$result) throw new NotFoundException("Feedback not found.");
            return $this->successResponse("Feedback permanently deleted successfully.", ['id' => $id]);
        });
    }
    public function create($data): array {
        return $this->handleRequest(function() use ($data) {
           // FeedbackValidator::validateCreate($data);


            if ($this->repository->exists($data['user_id'], $data['product_id'])) {
                throw new DuplicateException("Feedback from this user for this product already exists.");
            }
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

            if (empty($data)) {
                throw new InvalidArgumentException("No data provided for update.");
            }

            if(!$this->repository->exists($data['user_id'] ?? 0, $data['product_id'] ?? 0)) {
                throw new DuplicateException("Feedback from this user for this product does not exist.");
            }
     //       FeedbackValidator::validateUpdate($data);
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
    public function exists(int $userId, int $productId): array {
        return $this->handleRequest(function() use ($userId, $productId) {
            $exists = $this->repository->exists($userId, $productId);
            return $this->successResponse("Existence check completed successfully.", ['exists' => $exists]);
        });
    }

    public function getAllPaginated(int $limit, int $offset): array {
        return $this->handleRequest(function() use ($limit, $offset) {
      //      FeedbackValidator::paginationParams($limit, $offset);
            $feedbacks = $this->repository->getAllPaginated($limit, $offset);
            return $this->successResponse("Feedback retrieved successfully.", array_map(fn($f) => $f->toArray(), $feedbacks));
        });
    }
}


?>