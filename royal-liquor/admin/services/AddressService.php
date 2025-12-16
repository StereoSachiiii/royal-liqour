<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/AddressRepository.php';
require_once __DIR__ . '/../validators/AddressValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class AddressService
{
    public function __construct(
        private AddressRepository $repo,
    ) {}

    public function create(array $data): array
    {
        AddressValidator::validateCreate($data);
        $address = $this->repo->create($data);
        return $address->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $addresses = $this->repo->getAll($limit, $offset);
        return array_map(fn($a) => $a->toArray(), $addresses);
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        // Repository returns array with user data already
        return $this->repo->getAllPaginated($limit, $offset);
    }

    public function getById(int $id): array
    {
        $address = $this->repo->getById($id);
        if (!$address) {
            throw new NotFoundException('Address not found');
        }
        return $address->toArray();
    }

    public function getByIdEnriched(int $id): array
    {
        $address = $this->repo->getByIdEnriched($id);
        if (!$address) {
            throw new NotFoundException('Address not found');
        }
        // Repository already returns array with enriched data
        return $address;
    }

    public function getByUser(int $userId): array
    {
        $addresses = $this->repo->getByUser($userId);
        return array_map(fn($a) => $a->toArray(), $addresses);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function update(int $id, array $data): array
    {
        AddressValidator::validateUpdate($data);
        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new NotFoundException('Address not found');
        }
        return $updated->toArray();
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('Address not found');
        }
    }
}