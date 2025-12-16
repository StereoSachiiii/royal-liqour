<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../validators/UserValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/UnauthorizedException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class UserController
{
    private UserRepository $repo;
    private Session $session;

    public function __construct()
    {
        $this->repo    = new UserRepository();
        $this->session = Session::getInstance();
    }

    // ====================================================================
    // RESPONSE HELPERS
    // ====================================================================
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
            "[%s] UserController Error: %s | File: %s:%d | Context: %s",
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
        } catch (ValidationException | NotFoundException | UnauthorizedException | DatabaseException $e) {
            return $this->error($e);
        } catch (Throwable $e) {
            return $this->error(new Exception('Unexpected error: ' . $e->getMessage(), 500));
        }
    }

    // ====================================================================
    // AUTH & PROFILE
    // ====================================================================

    public function register(array $data): array
    {
        return $this->handle(function () use ($data) {
            UserValidator::validateCreate($data);

            if ($this->repo->findByEmail($data['email'])) {
                throw new ValidationException('Email already registered', ['errors' => ['email' => 'Taken']]);
            }
            $profileImageUrl = $data['profile_image_url'] ?? $data['profileImageUrl'] ?? null;

            if (isset($data['is_admin'])) {
                $user = $this->repo->createAdmin(
                    $data['name'],
                    $data['email'],
                    $data['phone'] ?? null,
                    password_hash($data['password'], PASSWORD_BCRYPT),
                    $profileImageUrl
                );
            } else {
                $user = $this->repo->create(
                    $data['name'],
                    $data['email'],
                    $data['phone'] ?? null,
                    password_hash($data['password'], PASSWORD_BCRYPT),
                    $profileImageUrl
                );
            }

            $this->session->login([
                'user_id'  => $user->getId(),
                'name'     => $user->getName(),
                'email'    => $user->getEmail(),
                'is_admin' => $user->isAdmin()
            ]);

            return $this->success('Registered successfully', $user->toArray(), 201);
        });
    }

    public function login(array $data): array
    {
        return $this->handle(function () use ($data) {
            UserValidator::loginValidate($data);

            $user = $this->repo->findByEmail($data['email']);
            if (!$user || !password_verify($data['password'], $user->getPasswordHash())) {
                throw new ValidationException('Invalid credentials', code: 401);
            }

            if (!$user->isActive()) {
                throw new ValidationException('Account is disabled', code: 403);
            }

            $this->repo->updateLastLogin($user->getId());

            $this->session->login([
                'user_id'  => $user->getId(),
                'name'     => $user->getName(),
                'email'    => $user->getEmail(),
                'is_admin' => $user->isAdmin()
            ]);

            return $this->success('Login successful', $user->toArray());
        });
    }

    public function getProfile(int $userId): array
    {
        return $this->handle(function () use ($userId) {
            $user = $this->repo->findById($userId);
            if (!$user) throw new NotFoundException('User not found');
            return $this->success('Profile retrieved', $user->toArray());
        });
    }

    public function updateProfile(int $userId, array $data): array
    {
        return $this->handle(function () use ($userId, $data) {
            UserValidator::validateUpdate($data);

            if (!empty($data['email'])) {
                $existing = $this->repo->findByEmail($data['email']);
                if ($existing && $existing->getId() !== $userId) {
                    throw new ValidationException('Email already taken', ['errors' => ['email' => 'In use']]);
                }
            }

            $updates = [];
            if (isset($data['name'])) $updates['name'] = $data['name'];
            if (isset($data['email'])) $updates['email'] = $data['email'];
            if (isset($data['phone'])) $updates['phone'] = $data['phone'];
            if (isset($data['profile_image_url'])) $updates['profile_image_url'] = $data['profile_image_url'];
            elseif (isset($data['profileImageUrl'])) $updates['profile_image_url'] = $data['profileImageUrl'];
            if (isset($data['is_active'])) $updates['is_active'] = (bool)$data['is_active'];
            if (isset($data['is_admin'])) $updates['is_admin'] = (bool)$data['is_admin'];
            if (!empty($data['password'])) $updates['password'] = $data['password'];

            $user = $this->repo->updateProfile($userId, $updates);

            // Sync session
            $this->session->set('name', $user->getName());
            if (isset($data['email'])) $this->session->set('email', $user->getEmail());

            return $this->success('Profile updated', $user->toArray());
        });
    }

    public function anonymizeUser(int $userId): array
    {
        return $this->handle(function () use ($userId) {
            $affected = $this->repo->anonymizeUser($userId);
            if ($affected === 0) throw new NotFoundException('User not found');
            return $this->success('User anonymized successfully');
        });
    }

    // ====================================================================
    // ADDRESSES
    // ====================================================================

    public function createAddress(int $userId, array $data): array
    {
        return $this->handle(function () use ($userId, $data) {
            UserValidator::validateCreateAddress($data);
            $id = $this->repo->createAddress($userId, $data);
            if (!$id) throw new DatabaseException('Failed to create address');
            return $this->success('Address created', ['address_id' => $id], 201);
        });
    }
    public function searchUsers(string $query, int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($query, $limit, $offset) {
            // if (!$this->session->isAdmin()) {
            //     throw new UnauthorizedException('Admin access required');
            // }

            if (empty(trim($query))) {
                $users = $this->repo->getAllUsers($limit,$offset);
                $data = array_map(fn($u) => $u->toArray(), $users);
                
            }
            else{
            $users = $this->repo->searchUsers(trim($query), $limit, $offset);
            $data = array_map(fn($u) => $u->toArray(), $users);
            }
            return $this->success('Search results retrieved', $data);
        });
    }
    public function getAddresses(int $userId, ?string $type = null): array
    {
        return $this->handle(function () use ($userId, $type) {
            $addresses = $this->repo->getUserAddresses($userId, $type);
            return $this->success('Addresses retrieved', $addresses);
        });
    }

    public function updateAddress(int $addressId, array $data): array
    {
        return $this->handle(function () use ($addressId, $data) {
            UserValidator::validateUpdateAddress($data);
            $affected = $this->repo->updateAddress($addressId, $data);
            if ($affected === 0) throw new NotFoundException('Address not found');
            return $this->success('Address updated');
        });
    }

    public function deleteAddress(int $addressId): array
    {
        return $this->handle(function () use ($addressId) {
            $affected = $this->repo->softDeleteAddress($addressId);
            if ($affected === 0) throw new NotFoundException('Address not found');
            return $this->success('Address deleted');
        });
    }

    // ====================================================================
    // ADMIN ONLY
    // ====================================================================

    public function getAllUsers(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            // Admin check is handled by middleware usually, but if called directly:
            // if (!$this->session->isAdmin()) throw new UnauthorizedException('Admin access required');

            // Old method returns models, but we need arrays for JSON mostly
            // We'll keep this one returning data structure as existing, or update to use getAllPaginated
            // For now, let's just make sure it returns arrays validly
             $users = $this->repo->getAllUsers($limit, $offset);
             $data = array_map(fn($u) => $u->toArray(), $users);
             return $this->success('Users retrieved', $data);
        });
    }

    public function getAllEnriched(int $limit = 50, int $offset = 0): array
    {
        return $this->handle(function () use ($limit, $offset) {
            $users = $this->repo->getAllPaginated($limit, $offset);
            return $this->success('Users retrieved', $users);
        });
    }

    public function getByIdEnriched(int $id): array
    {
        return $this->handle(function () use ($id) {
            $user = $this->repo->getByIdEnriched($id);
            if (!$user) throw new NotFoundException('User not found');
            return $this->success('User details retrieved', $user);
        });
    }

    public function delete(int $id, bool $hard = false): array
    {
        return $this->handle(function () use ($id, $hard) {
             if ($hard) {
                 $this->repo->hardDelete($id);
                 return $this->success('User permanently deleted');
             } else {
                 $this->repo->softDelete($id);
                 return $this->success('User soft deleted');
             }
        });
    }
}