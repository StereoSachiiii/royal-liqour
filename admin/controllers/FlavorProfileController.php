<?php
class FlavorProfileController {
    private FlavorProfileRepository $repository;
    private FlavorProfileValidator $validator;

    public function __construct() {
        $this->repository = new FlavorProfileRepository();
        $this->validator = new FlavorProfileValidator();
    }

    private function successResponse(string $message, $data, int $code = 200): array {
        return ['success' => true, 'message' => $message, 'data' => $data, 'code' => $code, 'context' => []];
    }

    private function errorResponse(Exception $error): array {
        error_log("[" . date('Y-m-d H:i:s') . "] FlavorProfileController: " . $error->getMessage());
        return ['success' => false, 'message' => $error->getMessage(), 'code' => 500, 'context' => []];
    }

    private function handleRequest(callable $callback): array {
        try { return $callback(); } catch(Exception $ex) { return $this->errorResponse($ex); }
    }

    public function create($data): array {
        return $this->handleRequest(function() use ($data) {
            FlavorProfileValidator::validateCreate($data);
            $result = $this->repository->create($data);
            return $this->successResponse("Flavor profile created successfully.", $result->toArray(), 201);
        });
    }

    public function getById(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $profile = $this->repository->getById($id);
            if (!$profile) throw new NotFoundException("Flavor profile not found.");
            return $this->successResponse("Flavor profile retrieved successfully.", $profile->toArray());
        });
    }

    public function getByProductId(int $productId): array {
        return $this->handleRequest(function() use ($productId) {
            $profile = $this->repository->getByProductId($productId);
            if (!$profile) throw new NotFoundException("Flavor profile not found for product.");
            return $this->successResponse("Flavor profile retrieved successfully.", $profile->toArray());
        });
    }

    public function searchByFlavors(array $criteria): array {
        return $this->handleRequest(function() use ($criteria) {
            $profiles = $this->repository->searchByFlavors($criteria);
            return $this->successResponse("Matching flavor profiles retrieved successfully.", array_map(fn($p) => $p->toArray(), $profiles));
        });
    }

    public function update(int $id, array $data): array {
        return $this->handleRequest(function() use ($id, $data) {
            FlavorProfileValidator::validateUpdate($data);
            $updated = $this->repository->update($id, $data);
            if (!$updated) throw new NotFoundException("Flavor profile not found.");
            return $this->successResponse("Flavor profile updated successfully.", $updated->toArray());
        });
    }

    public function delete(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $result = $this->repository->delete($id);
            if (!$result) throw new NotFoundException("Flavor profile not found.");
            return $this->successResponse("Flavor profile deleted successfully.", ['id' => $id]);
        });
    }

    public function getAll(): array {
        return $this->handleRequest(function() {
            $profiles = $this->repository->getAll();
            return $this->successResponse("Flavor profiles retrieved successfully.", array_map(fn($p) => $p->toArray(), $profiles));
        });
    }
}
?>