<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../validators/UserValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/DuplicateException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class UserService
{
    public function __construct(
        private UserRepository $repo,
    ) {}

    public function register(array $data): array
    {
        UserValidator::validateCreate($data);

        if ($this->repo->findByEmail($data['email'])) {
            throw new DuplicateException('Email already registered', ['email' => $data['email']]);
        }

        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        $user = isset($data['is_admin'])
            ? $this->repo->createAdmin($data['name'], $data['email'], $data['phone'] ?? null, $passwordHash)
            : $this->repo->create($data['name'], $data['email'], $data['phone'] ?? null, $passwordHash);

        return $user->toArray();
    }

    public function login(array $data): array
    {
        UserValidator::loginValidate($data);

        $user = $this->repo->findByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user->getPasswordHash())) {
            throw new ValidationException('Invalid credentials', [], 401);
        }

        if (!$user->isActive()) {
            throw new ValidationException('Account is disabled', [], 403);
        }

        $this->repo->updateLastLogin($user->getId());

        return $user->toArray();
    }

    public function getProfile(int $userId): array
    {
        $user = $this->repo->findById($userId);
        if (!$user) {
            throw new NotFoundException('User not found');
        }
        return $user->toArray();
    }

    public function updateProfile(int $userId, array $data): array
    {
        UserValidator::validateUpdate($data);

        if (!empty($data['email'])) {
            $existing = $this->repo->findByEmail($data['email']);
            if ($existing && $existing->getId() !== $userId) {
                throw new DuplicateException('Email already taken', ['email' => $data['email']]);
            }
        }

        $updates = [];
        if (isset($data['name'])) $updates['name'] = $data['name'];
        if (isset($data['email'])) $updates['email'] = $data['email'];
        if (isset($data['phone'])) $updates['phone'] = $data['phone'];
        if (isset($data['profileImageUrl'])) $updates['profile_image_url'] = $data['profileImageUrl'];
        if (!empty($data['password'])) $updates['password'] = $data['password'];

        $user = $this->repo->updateProfile($userId, $updates);
        if (!$user) {
            throw new DatabaseException('Failed to update profile');
        }

        return $user->toArray();
    }

    public function anonymizeUser(int $userId): void
    {
        $affected = $this->repo->anonymizeUser($userId);
        if ($affected === 0) {
            throw new NotFoundException('User not found');
        }
    }

    public function getAddresses(int $userId, ?string $type = null): array
    {
        return $this->repo->getUserAddresses($userId, $type);
    }

    public function createAddress(int $userId, array $data): int
    {
        UserValidator::validateCreateAddress($data);
        $id = $this->repo->createAddress($userId, $data);
        if (!$id) {
            throw new DatabaseException('Failed to create address');
        }
        return $id;
    }

    public function updateAddress(int $addressId, array $data): void
    {
        UserValidator::validateUpdateAddress($data);
        $affected = $this->repo->updateAddress($addressId, $data);
        if ($affected === 0) {
            throw new NotFoundException('Address not found');
        }
    }

    public function deleteAddress(int $addressId): void
    {
        $affected = $this->repo->softDeleteAddress($addressId);
        if ($affected === 0) {
            throw new NotFoundException('Address not found');
        }
    }

    public function getAllUsers(int $limit = 50, int $offset = 0): array
    {
        $users = $this->repo->getAllUsers($limit, $offset);
        return array_map(fn($u) => $u->toArray(), $users);
    }

    public function searchUsers(string $query, int $limit = 50, int $offset = 0): array
    {
        if (empty(trim($query))) {
            $users = $this->repo->getAllUsers($limit, $offset);
        } else {
            $users = $this->repo->searchUsers(trim($query), $limit, $offset);
        }
        return array_map(fn($u) => $u->toArray(), $users);
    }
}
