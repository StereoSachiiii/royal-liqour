<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../models/User.php';

class UserController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        try {
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                return ['success' => false, 'message' => 'Name, email, and password are required'];
            }

            $user = $this->userRepository->createUser(
                $data['name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['password']
            );

            if ($user === null) {
                return ['success' => false, 'message' => 'Registration failed'];
            }

            // Start session and set data
            session_start();
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['is_admin'] = $user->isAdmin();
            $_SESSION['email'] = $user->getEmail();

            return [
                'success' => true,
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'is_admin' => $user->isAdmin()
                ]
            ];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    /**
     * Login user.
     *
     * @param array $data
     * @return array
     */
    public function login(array $data): array
    {
        try {
            if (empty($data['email']) || empty($data['password'])) {
                return ['success' => false, 'message' => 'Email and password are required'];
            }

            $user = $this->userRepository->getUserByEmail($data['email']);
            if (!$user || !password_verify($data['password'], $user->getPasswordHash())) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            if (!$user->isActive()) {
                return ['success' => false, 'message' => 'Account is inactive'];
            }

            // Update last login
            $this->userRepository->updateLastLogin($user->getId());

            // Start session
            session_start();
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['is_admin'] = $user->isAdmin();
            $_SESSION['email'] = $user->getEmail();

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'is_admin' => $user->isAdmin()
                ]
            ];
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get user profile.
     *
     * @param int $userId
     * @return array
     */
    public function getProfile(int $userId): array
    {
        try {
            $user = $this->userRepository->getUserById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            return [
                'success' => true,
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'profile_image_url' => $user->getProfileImageUrl(),
                    'is_active' => $user->isActive(),
                    'is_admin' => $user->isAdmin(),
                    'created_at' => $user->getCreatedAt(),
                    'last_login_at' => $user->getLastLoginAt()
                ]
            ];
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Failed to retrieve profile: ' . $e->getMessage()];
        }
    }

    /**
     * Update user profile.
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateProfile(int $userId, array $data): array
    {
        try {
            $user = $this->userRepository->getUserById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            $updatedUser = $this->userRepository->updateUser(
                userId: $userId,
                name: $data['name'] ?? null,
                email: $data['email'] ?? null,
                phone: $data['phone'] ?? null,
                password: $data['password'] ?? null,
                profileImageUrl: $data['profile_image_url'] ?? null
            );

            if ($updatedUser === null) {
                return ['success' => false, 'message' => 'Profile update failed'];
            }

            // Update session data if necessary
            session_start();
            if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
                $_SESSION['email'] = $updatedUser->getEmail();
            }

            return [
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $updatedUser->getId(),
                    'name' => $updatedUser->getName(),
                    'email' => $updatedUser->getEmail(),
                    'phone' => $updatedUser->getPhone(),
                    'profile_image_url' => $updatedUser->getProfileImageUrl(),
                    'is_active' => $updatedUser->isActive(),
                    'is_admin' => $updatedUser->isAdmin(),
                    'created_at' => $updatedUser->getCreatedAt(),
                    'last_login_at' => $updatedUser->getLastLoginAt()
                ]
            ];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Profile update failed: ' . $e->getMessage()];
        }
    }

    /**
     * Anonymize user.
     *
     * @param int $userId
     * @return array
     */
    public function anonymizeUser(int $userId): array
    {
        try {
            $affected = $this->userRepository->anonymizeUser($userId);
            if ($affected > 0) {
                session_start();
                session_destroy();
                return ['success' => true, 'message' => 'User anonymized successfully'];
            }
            return ['success' => false, 'message' => 'Anonymization failed'];
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Anonymization failed: ' . $e->getMessage()];
        }
    }

    /**
     * Create a user address.
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function createAddress(int $userId, array $data): array
    {
        try {
            if (empty($data['address_line1']) || empty($data['city']) || empty($data['postal_code']) || empty($data['country'])) {
                return ['success' => false, 'message' => 'Required address fields are missing'];
            }

            $addressId = $this->userRepository->createAddress(
                $userId,
                $data['address_type'] ?? 'both',
                $data['address_line1'],
                $data['address_line2'] ?? null,
                $data['city'],
                $data['state'] ?? null,
                $data['postal_code'],
                $data['country'],
                $data['is_default'] ?? false
            );

            if ($addressId) {
                return ['success' => true, 'message' => 'Address created successfully', 'address_id' => $addressId];
            }
            return ['success' => false, 'message' => 'Address creation failed'];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Address creation failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get user addresses.
     *
     * @param int $userId
     * @param string|null $addressType
     * @return array
     */
    public function getAddresses(int $userId, ?string $addressType = null): array
    {
        try {
            $addresses = $this->userRepository->getUserAddresses($userId, $addressType);
            return ['success' => true, 'addresses' => $addresses];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Failed to retrieve addresses: ' . $e->getMessage()];
        }
    }

    /**
     * Update a user address.
     *
     * @param int $addressId
     * @param array $data
     * @return array
     */
    public function updateAddress(int $addressId, array $data): array
    {
        try {
            if (empty($data['address_line1']) || empty($data['city']) || empty($data['postal_code']) || empty($data['country'])) {
                return ['success' => false, 'message' => 'Required address fields are missing'];
            }

            $affected = $this->userRepository->updateAddress(
                $addressId,
                $data['address_type'] ?? 'both',
                $data['address_line1'],
                $data['address_line2'] ?? null,
                $data['city'],
                $data['state'] ?? null,
                $data['postal_code'],
                $data['country'],
                $data['is_default'] ?? false
            );

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Address updated successfully'];
            }
            return ['success' => false, 'message' => 'Address update failed'];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Address update failed: ' . $e->getMessage()];
        }
    }

    /**
     * Delete a user address.
     *
     * @param int $addressId
     * @return array
     */
    public function deleteAddress(int $addressId): array
    {
        try {
            $affected = $this->userRepository->softDeleteAddress($addressId);
            if ($affected > 0) {
                return ['success' => true, 'message' => 'Address deleted successfully'];
            }
            return ['success' => false, 'message' => 'Address deletion failed'];
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Address deletion failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get all users (for admin).
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllUsers(int $limit = 50, int $offset = 0): array
    {
        try {
            session_start();
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                return ['success' => false, 'message' => 'Unauthorized'];
            }

            $users = $this->userRepository->getAllUsers($limit, $offset);
            $userData = array_map(function ($user) {
                return [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'is_active' => $user->isActive(),
                    'is_admin' => $user->isAdmin(),
                    'created_at' => $user->getCreatedAt(),
                    'last_login_at' => $user->getLastLoginAt()
                ];
            }, $users);

            return ['success' => true, 'users' => $userData];
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Failed to retrieve users: ' . $e->getMessage()];
        }
    }
}

?>