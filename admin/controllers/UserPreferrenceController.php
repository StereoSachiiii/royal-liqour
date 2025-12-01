<?php
require_once __DIR__ . '/../repositories/UserPreferrencesRepository.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../models/UserPreferrenceModel.php';

class UserPreferrenceController {
    private UserPreferrenceRepository $repository;

    public function __construct() {
        $this->repository = new UserPreferrenceRepository();
    }

    private function successResponse(string $message, $data, int $code = 200): array {
        return ['success' => true, 'message' => $message, 'data' => $data, 'code' => $code];
    }

    private function errorResponse(Exception $error, int $code = 500): array {
        error_log("[" . date('Y-m-d H:i:s') . "] UserPreferenceController: " . $error->getMessage());
        return ['error' => true, 'message' => $error->getMessage(), 'code' => $code];
    }

    private function handleRequest(callable $callback): array {
        try { 
            return $callback(); 
        } catch(NotFoundException $ex) { 
            return $this->errorResponse($ex, 404); 
        } catch(ValidationException $ex) { 
            return $this->errorResponse($ex, 400); 
        } catch(Exception $ex) { 
            return $this->errorResponse($ex, 500); 
        }
    }

    public function create($data): array {
        return $this->handleRequest(function() use ($data) {
         //   UserPreferenceValidator::validateCreate($data);
            if($this->repository->existsByUserId($data['user_id'])) {
                throw new ValidationException("User preference for this user already exists.");
            }
            $result = $this->repository->create($data);
            return $result; // Repository already returns proper format
        });
    }

    public function getById(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $pref = $this->repository->getById($id);
            if (!$pref) throw new NotFoundException("User preference not found.");
            return $pref; // Repository already returns proper format
        });
    }

    public function getByUserId(int $userId): array {
        return $this->handleRequest(function() use ($userId) {
            $pref = $this->repository->getByUserId($userId);
            if (!$pref) throw new NotFoundException("User preference not found for this user.");
            return $pref;
        });
    }

    public function update(int $id, array $data): array {
        return $this->handleRequest(function() use ($id, $data) {
           // UserPreferenceValidator::validateUpdate($data);
            $updated = $this->repository->update($id, $data);
            if (!$updated) throw new NotFoundException("User preference not found.");
            return $updated;
        });
    }

    public function delete(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $result = $this->repository->delete($id);
            if (!$result) throw new NotFoundException("User preference not found.");
            return ['id' => $id, 'deleted' => true];
        });
    }

    public function getAll(): array {
        return $this->handleRequest(function() {
            return $this->repository->getAll();
        });
    }
}