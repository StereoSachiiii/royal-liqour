<?php
require_once __DIR__ . '/../repositories/ProductRecognitionRepository.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
class ProductRecognitionController {
    private ProductRecognitionRepository $repository;
    private ProductRecognitionValidator $validator;

    public function __construct() {
        $this->repository = new ProductRecognitionRepository();
       // $this->validator = new ProductRecognitionValidator();
    }

    private function successResponse(string $message, $data, int $code = 200): array {
        return ['success' => true, 'message' => $message, 'data' => $data, 'code' => $code, 'context' => []];
    }

    private function errorResponse(Exception $error): array {
        error_log("[" . date('Y-m-d H:i:s') . "] ProductRecognitionController: " . $error->getMessage());
        return ['success' => false, 'message' => $error->getMessage(), 'code' => 500, 'context' => []];
    }

    private function handleRequest(callable $callback): array {
        try { return $callback(); } catch(Exception $ex) { return $this->errorResponse($ex); }
    }

    public function create($data): array {
        return $this->handleRequest(function() use ($data) {
           // ProductRecognitionValidator::validateCreate($data);
            $result = $this->repository->create($data);
            return $this->successResponse("Product recognition created successfully.", $result->toArray(), 201);
        });
    }

    public function getById(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $recognition = $this->repository->getById($id);
            if (!$recognition) throw new NotFoundException("Recognition record not found.");
            return $this->successResponse("Recognition retrieved successfully.", $recognition->toArray());
        });
    }

    public function update(int $id, array $body){
        return $this->handleRequest(function() use ($id, $body){
            $result = $this->repository->update($id,$body);
            return $this->successResponse("Product recognition updaed succesfully", $result->toArray(),201);
        });
    }

    public function getBySessionId(string $sessionId): array {
        return $this->handleRequest(function() use ($sessionId) {
            $recognitions = $this->repository->getBySessionId($sessionId);
            return $this->successResponse("Recognition history retrieved successfully.", array_map(fn($r) => $r->toArray(), $recognitions));
        });
    }

    public function getRecent(int $limit = 10): array {
        return $this->handleRequest(function() use ($limit) {
            $recognitions = $this->repository->getRecent($limit);
            return $this->successResponse("Recent recognitions retrieved successfully.", array_map(fn($r) => $r->toArray(), $recognitions));
        });
    }

    public function delete(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $result = $this->repository->delete($id);
            if (!$result) throw new NotFoundException("Recognition record not found.");
            return $this->successResponse("Recognition deleted successfully.", ['id' => $id]);
        });
    }

    public function getAll(): array {
        return $this->handleRequest(function() {
            $recognitions = $this->repository->getAll();
            return $this->successResponse("All recognitions retrieved successfully.", array_map(fn($r) => $r->toArray(), $recognitions));
        });
    }

    public function getAllPaginated(int $limit, int $offset): array {
        return $this->handleRequest(function() use ($limit, $offset) {
          //  ProductRecognitionValidator::paginationParams($limit, $offset);
            $recognitions = $this->repository->getAllPaginated($limit, $offset);
            return $this->successResponse("Recognitions retrieved successfully.", array_map(fn($r) => $r->toArray(), $recognitions));
        });
    }
}

?>