<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/AddressRepository.php';
require_once __DIR__ . '/../validators/AddressValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class AddressController
{
    private AddressRepository $repo;
    private Session $session;

    public function __construct()
    {
        $this->repo = new AddressRepository();
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
            "[%s] AddressController Error: %s | File: %s:%d | Context: %s",
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

    public function create(int $userId, array $data): array
    {
        return $this->handle(function () use ($userId, $data) {
            AddressValidator::validateCreate($data);
            $address = $this->repo->create($userId, $data);
            return $this->success('Address created', $address->toArray(), 201);
        });
    }

    public function getAddresses(int $userId, ?string $type = null, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($userId, $type, $limit, $offset) {
            $addresses = $this->repo->getUserAddresses($userId, $type, $limit, $offset);
            $data = array_map(fn($a) => $a->toArray(), $addresses);
            return $this->success('Addresses retrieved', $data);
        });
    }

    public function getById(int $id): array
    {
        return $this->handle(function () use ($id) {
            $address = $this->repo->getById($id);
            if (!$address) throw new NotFoundException('Address not found');
            return $this->success('Address retrieved', $address->toArray());
        });
    }

    public function update(int $id, array $data): array
    {
        return $this->handle(function () use ($id, $data) {
            AddressValidator::validateUpdate($data);
            $updated = $this->repo->update($id, $data);
            if (!$updated) throw new NotFoundException('Address not found');
            return $this->success('Address updated', $updated->toArray());
        });
    }

    public function delete(int $id): array
    {
        return $this->handle(function () use ($id) {
            $deleted = $this->repo->delete($id);
            if (!$deleted) throw new NotFoundException('Address not found');
            return $this->success('Address deleted');
        });
    }
}