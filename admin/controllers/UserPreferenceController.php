<?php
class UserPreferenceController {
    private UserPreferenceRepository $repository;
    private UserPreferenceValidator $validator;

    public function __construct() {
        $this->repository = new UserPreferenceRepository();
        $this->validator = new UserPreferenceValidator();
    }

    private function successResponse(string $message, $data, int $code = 200): array {
        return ['success' => true, 'message' => $message, 'data' => $data, 'code' => $code, 'context' => []];
    }

    private function errorResponse(Exception $error): array {
        error_log("[" . date('Y-m-d H:i:s') . "] UserPreferenceController: " . $error->getMessage());
        return ['success' => false, 'message' => $error->getMessage(), 'code' => 500, 'context' => []];
    }

    private function handleRequest(callable $callback): array {
        try { return $callback(); } catch(Exception $ex) { return $this->errorResponse($ex); }
    }

    public function create($data): array {
        return $this->handleRequest(function() use ($data) {
            UserPreferenceValidator::validateCreate($data);
            $result = $this->repository->create($data);
            return $this->successResponse("User preference created successfully.", $result->toArray(), 201);
        });
    }

    public function getById(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $pref = $this->repository->getById($id);
            if (!$pref) throw new NotFoundException("User preference not found.");
            return $this->successResponse("User preference retrieved successfully.", $pref->toArray());
        });
    }

    public function getByUserId(int $userId): array {
        return $this->handleRequest(function() use ($userId) {
            $pref = $this->repository->getByUserId($userId);
            if (!$pref) throw new NotFoundException("User preference not found.");
            return $this->successResponse("User preference retrieved successfully.", $pref->toArray());
        });
    }

    public function update(int $id, array $data): array {
        return $this->handleRequest(function() use ($id, $data) {
            UserPreferenceValidator::validateUpdate($data);
            $updated = $this->repository->update($id, $data);
            if (!$updated) throw new NotFoundException("User preference not found.");
            return $this->successResponse("User preference updated successfully.", $updated->toArray());
        });
    }

    public function delete(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $result = $this->repository->delete($id);
            if (!$result) throw new NotFoundException("User preference not found.");
            return $this->successResponse("User preference deleted successfully.", ['id' => $id]);
        });
    }
}

?>