<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/FlavorProfileRepository.php';
require_once __DIR__ . '/../validators/FlavorProfileValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class FlavorProfileService
{
    public function __construct(
        private FlavorProfileRepository $repo,
    ) {}

    public function create(array $data): array
    {
        FlavorProfileValidator::validateCreate($data);
        
        if ($this->repo->exists((int)$data['product_id'])) {
            throw new DuplicateException('Flavor profile already exists for this product');
        }

        $profile = $this->repo->create($data);
        return $profile->toArray();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $profiles = $this->repo->getAll($limit, $offset);
        return array_map(fn($p) => $p->toArray(), $profiles);
    }

    public function getByProductId(int $productId): array
    {
        $profile = $this->repo->getByProductIdEnriched($productId);
        if (!$profile) {
            throw new NotFoundException('Flavor profile not found');
        }
        return $profile;
    }

    public function getByName(string $name): array
    {
        // Not used widely, can remain or be removed. Keeping for now but careful with mapping.
        $profile = $this->repo->getByName($name);
        if (!$profile) {
            throw new NotFoundException('Flavor profile not found');
        }
        return $profile->toArray();
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        // Search already returns arrays from repo
        return $this->repo->search($query, $limit, $offset);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function update(int $productId, array $data): array
    {
        FlavorProfileValidator::validateUpdate($data);
        $updated = $this->repo->update($productId, $data);
        if (!$updated) {
            throw new NotFoundException('Flavor profile not found');
        }
        return $updated->toArray();
    }

    public function delete(int $productId): void
    {
        $deleted = $this->repo->delete($productId);
        if (!$deleted) {
            throw new NotFoundException('Flavor profile not found');
        }
    }
}
