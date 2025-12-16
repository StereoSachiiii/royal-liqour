<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/UserPreferenceRepository.php';
require_once __DIR__ . '/../validators/UserPreferenceValidator.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';

class UserPreferenceService
{
    public function __construct(
        private UserPreferenceRepository $repo,
    ) {}

    public function create(array $data): array
    {
        UserPreferenceValidator::validateCreate($data);
        
        if ($this->repo->existsByUserId($data['user_id'])) {
            throw new ValidationException('User preference for this user already exists');
        }
        
        // Repository now returns array directly
        return $this->repo->create($data);
    }

    public function getById(int $id): array
    {
        $pref = $this->repo->getById($id);
        if (!$pref) {
            throw new NotFoundException('User preference not found');
        }
        // Repository returns array, no need to call toArray()
        return $pref;
    }

    public function getByUserId(int $userId): array
    {
        $pref = $this->repo->getByUserId($userId);
        if (!$pref) {
            throw new NotFoundException('User preference not found');
        }
        return $pref;
    }

    public function update(int $id, array $data): array
    {
        UserPreferenceValidator::validateUpdate($data);
        $updated = $this->repo->update($id, $data);
        if (!$updated) {
            throw new NotFoundException('User preference not found');
        }
        return $updated;
    }

    public function delete(int $id): void
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new NotFoundException('User preference not found');
        }
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        // Repository returns array of arrays now
        return $this->repo->getAll($limit, $offset);
    }

    public function count(): int
    {
        return $this->repo->count();
    }
}

