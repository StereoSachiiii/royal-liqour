<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/FlavorProfileRepository.php';
// Assuming you have a validator or will create one similar to OrderValidator
require_once __DIR__ . '/../validators/FlavorProfileValidator.php'; 
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class FlavorProfileController
{
    private FlavorProfileRepository $repo;
    private Session $session;

    public function __construct()
    {
        $this->repo = new FlavorProfileRepository();
        $this->session = Session::getInstance();
    }

    private function success(string $message, $data = [], int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
            'context' => []
        ];
    }

    private function logError(Throwable $e, array $context = []): void
    {
        error_log(sprintf(
            "[%s] FlavorProfileController Error: %s | File: %s:%d | Context: %s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            json_encode($context)
        ));
    }

    private function error(Throwable $e): array
    {
        $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $context = method_exists($e, 'getContext') ? $e->getContext() : [];

        $this->logError($e, $context);

        return [
            'success' => false,
            'message' => $e->getMessage(),
            'code'    => $code,
            'context' => $context
        ];
    }

    private function handle(callable $callback): array
    {
        try {
            return $callback();
        } catch (ValidationException | NotFoundException | DatabaseException $e) {
            return $this->error($e);
        } catch (Throwable $e) {
            return $this->error(new Exception('Unexpected error: ' . $e->getMessage(), 500));
        }
    }

    public function create(array $data): array
    {
        return $this->handle(function () use ($data) {
            // Assumes a static validateCreate method exists
            FlavorProfileValidator::validateCreate($data); 
            $profile = $this->repo->create($data);
            return $this->success('Flavor profile created', $profile->toArray(), 201);
        });
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $profiles = $this->repo->getAll($limit, $offset);
            $data = array_map(fn($p) => $p->toArray(), $profiles);
            return $this->success('Flavor profiles retrieved', $data);
        });
    }

    public function getByProductId(int $productId): array
    {
        return $this->handle(function () use ($productId) {
            $profile = $this->repo->getByProductId($productId);
            if (!$profile) throw new NotFoundException('Flavor profile not found');
            return $this->success('Flavor profile retrieved', $profile->toArray());
        });
    }

    public function count(): array
    {
        return $this->handle(function () {
            $count = $this->repo->count();
            return $this->success('Count retrieved', $count);
        });
    }

    public function update(int $productId, array $data): array
    {
        return $this->handle(function () use ($productId, $data) {
            // Assumes a static validateUpdate method exists
            FlavorProfileValidator::validateUpdate($data);
            
            if(!$this->repo->getByProductId($productId)) {
                throw new NotFoundException('Flavor profile not found');
            }
            
            $updated = $this->repo->update($productId, $data);
            if (!$updated) throw new NotFoundException('Flavor profile not found');
            return $this->success('Flavor profile updated', $updated->toArray());
        });
    }

    public function delete(int $productId): array
    {
        return $this->handle(function () use ($productId) {
            $deleted = $this->repo->delete($productId);
            if (!$deleted) throw new NotFoundException('Flavor profile not found');
            return $this->success('Flavor profile deleted');
        });
    }
}