<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../validators/UserValidator.php';

// Custom exceptions
require_once __DIR__ . '/../exceptions/DuplicateEmailException.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/UnauthorizedException.php';

/**
 * UserController handles all user-related operations including registration, authentication,
 * profile management, address handling, and admin-only user retrieval.
 *
 * All methods return a standardized array structure:
 * ```php
 * [
 *     'success' => bool,
 *     'message' => string,
 *     'data'    => array|null,   // Only present on success
 *     'code'    => int,          // HTTP-like status code
 *     'context' => array         // Additional debug/context info (errors)
 * ]
 * ```
 *
 * Validation methods in UserValidator are **static** and must be called using `::`.
 */
class UserController
{
    private UserRepository $userRepository;
    private ?UserValidator $userValidator;

    /**
     * Initialize repository and validator.
     */
    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->userValidator = new UserValidator();
    }

    /**
     * Register a new regular user.
     *
     * @param array{
     *     name: string,
     *     email: string,
     *     phone?: string|null,
     *     password: string
     * } $data User registration data.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array{id: int, name: string, email: string, is_admin: bool},
     *     code: int,
     *     context: array
     * }
     *
     * @throws DuplicateEmailException If email is already registered.
     * @throws ValidationException If input validation fails.
     * @throws RuntimeException On database or system failure.
     */
    public function register(array $data): array
    {
        try {
            // Check for duplicate email
            if ($this->userRepository->getUserByEmail($data['email'])) {
                throw new DuplicateEmailException(context: ['email' => $data['email']]);
            }

            // Validate input
            UserValidator::validateCreate($data);

            // Create user
            $user = $this->userRepository->createUser(
                $data['name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['password']
            );

            if ($user === null) {
                throw new NotFoundException("User was not created successfully.");
            }

            // Log user in
            Session::getInstance()->login([
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'is_admin' => $user->isAdmin()
            ]);

            return [
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'is_admin' => $user->isAdmin()
                ],
                'code' => 201,
                'context' => []
            ];
        } catch (DuplicateEmailException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Register a new admin user.
     *
     * @param array{
     *     name: string,
     *     email: string,
     *     phone?: string|null,
     *     password: string
     * } $data Admin user registration data.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array{id: int, name: string, email: string, is_admin: bool},
     *     code: int,
     *     context: array
     * }
     *
     * @throws ValidationException If input is invalid or email exists.
     * @throws RuntimeException On system failure.
     */
    public function registerAdminUser(array $data): array
    {
        try {
            // Check email uniqueness
            if ($this->userRepository->getUserByEmail($data['email'])) {
                throw new ValidationException('Email already registered.', context: ['email' => $data['email']]);
            }

            // Validate input
            UserValidator::validateCreate($data);

            // Create admin user
            $user = $this->userRepository->createAdminUser(
                $data['name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['password']
            );

            if ($user === null) {
                throw new RuntimeException('Failed to create admin user.');
            }

            // Log admin in
            Session::getInstance()->login([
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'is_admin' => $user->isAdmin()
            ]);

            return [
                'success' => true,
                'message' => 'Admin user registered successfully',
                'data' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'is_admin' => $user->isAdmin()
                ],
                'code' => 201,
                'context' => []
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Authenticate a user via email and password.
     *
     * @param array{email: string, password: string} $data Login credentials.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array{id: int, name: string, email: string, is_admin: bool},
     *     code: int,
     *     context: array
     * }
     *
     * @throws ValidationException If input is invalid.
     * @throws RuntimeException On system failure.
     */
    public function login(array $data): array
    {
        try {
            UserValidator::loginValidate($data);

            $user = $this->userRepository->getUserByEmail($data['email']);

            if (!$user || !password_verify($data['password'], $user->getPasswordHash())) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'data' => null,
                    'code' => 401,
                    'context' => []
                ];
            }

            // Update last login timestamp
            $this->userRepository->updateLastLogin($user->getId());

            // Start session
            Session::getInstance()->login([
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'is_admin' => $user->isAdmin()
            ]);

            return [
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'is_admin' => $user->isAdmin()
                ],
                'code' => 200,
                'context' => []
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Retrieve user profile by ID.
     *
     * @param int $userId The primary key of the user.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array{
     *         id: int,
     *         name: string,
     *         email: string,
     *         phone: ?string,
     *         profile_image_url: ?string,
     *         is_active: bool,
     *         is_admin: bool,
     *         created_at: string,
     *         last_login_at: ?string
     *     },
     *     code: int,
     *     context: array
     * }
     */
    public function getProfile(int $userId): array
    {
        try {
            UserValidator::validateProfileId($userId);

            $user = $this->userRepository->getUserById($userId);
            if (!$user) {
                throw new NotFoundException('User not found.', context: ['userId' => $userId]);
            }

            return [
                'success' => true,
                'message' => 'User profile retrieved successfully',
                'data' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'profile_image_url' => $user->getProfileImageUrl(),
                    'is_active' => $user->isActive(),
                    'is_admin' => $user->isAdmin(),
                    'created_at' => $user->getCreatedAt(),
                    'last_login_at' => $user->getLastLoginAt()
                ],
                'code' => 200,
                'context' => []
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve profile: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Update user profile.
     *
     * @param int $userId The user ID to update.
     * @param array{
     *     name?: ?string,
     *     email?: ?string,
     *     phone?: ?string,
     *     password?: ?string,
     *     profile_image_url?: ?string
     * } $data Fields to update (all optional).
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array{
     *         id: int,
     *         name: string,
     *         email: string,
     *         phone: ?string,
     *         profile_image_url: ?string,
     *         is_active: bool,
     *         is_admin: bool,
     *         created_at: string,
     *         last_login_at: ?string
     *     },
     *     code: int,
     *     context: array
     * }
     */
    public function updateProfile(int $userId, array $data): array
    {
        try {
            UserValidator::validateUpdate($data);

            $user = $this->userRepository->getUserById($userId);
            if (!$user) {
                throw new NotFoundException('User not found.', context: ['userId' => $userId]);
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
                throw new RuntimeException('Failed to update user profile.');
            }

            // Update session if email or name changed
            $session = Session::getInstance();
            if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
                $session->set('email', $updatedUser->getEmail());
            }
            if (isset($data['name']) && $data['name'] !== $user->getName()) {
                $session->set('name', $updatedUser->getName());
            }

            return [
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $updatedUser->getId(),
                    'name' => $updatedUser->getName(),
                    'email' => $updatedUser->getEmail(),
                    'phone' => $updatedUser->getPhone(),
                    'profile_image_url' => $updatedUser->getProfileImageUrl(),
                    'is_active' => $updatedUser->isActive(),
                    'is_admin' => $updatedUser->isAdmin(),
                    'created_at' => $updatedUser->getCreatedAt(),
                    'last_login_at' => $updatedUser->getLastLoginAt()
                ],
                'code' => 200,
                'context' => []
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Profile update failed: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Anonymize a user (GDPR compliance).
     *
     * @param int $userId The user ID to anonymize.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: null,
     *     code: int,
     *     context: array
     * }
     */
    public function anonymizeUser(int $userId): array
    {
        try {
            UserValidator::validateProfileId($userId);

            $affected = $this->userRepository->anonymizeUser($userId);

            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => 'User anonymized successfully',
                    'data' => null,
                    'code' => 200,
                    'context' => []
                ];
            }

            throw new RuntimeException('No user was anonymized.');
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Invalid user ID: ' . $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Anonymization failed: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Create a new address for a user.
     *
     * @param int $userId Owner of the address.
     * @param array{
     *     address_line1: string,
     *     address_line2?: ?string,
     *     city: string,
     *     state?: ?string,
     *     postal_code: string,
     *     country: string,
     *     address_type?: string,
     *     is_default?: bool
     * } $data Address details.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array{address_id: int},
     *     code: int,
     *     context: array
     * }
     */
    public function createAddress(int $userId, array $data): array
    {
        try {
            UserValidator::validateProfileId($userId);

            if (empty($data['address_line1']) || empty($data['city']) || empty($data['postal_code']) || empty($data['country'])) {
                throw new ValidationException('Required address fields are missing.', context: ['missing' => ['address_line1', 'city', 'postal_code', 'country']]);
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
                return [
                    'success' => true,
                    'message' => 'Address created successfully',
                    'data' => ['address_id' => $addressId],
                    'code' => 201,
                    'context' => []
                ];
            }

            throw new RuntimeException('Address creation failed.');
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Address creation failed: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Retrieve user addresses, optionally filtered by type.
     *
     * @param int $userId The user ID.
     * @param ?string $addressType Filter: 'billing', 'shipping', 'both', or null.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array,
     *     code: int,
     *     context: array
     * }
     */
    public function getAddresses(int $userId, ?string $addressType = null): array
    {
        try {
            if ($addressType !== null && !in_array($addressType, ['billing', 'shipping', 'both'])) {
                throw new ValidationException('Invalid address_type. Must be billing, shipping, or both.', context: ['addressType' => $addressType]);
            }

            $addresses = $this->userRepository->getUserAddresses($userId, $addressType);

            return [
                'success' => true,
                'message' => 'Addresses retrieved successfully',
                'data' => $addresses,
                'code' => 200,
                'context' => []
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve addresses: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Update an existing address.
     *
     * @param int $addressId The address ID to update.
     * @param array{
     *     address_line1?: string,
     *     address_line2?: ?string,
     *     city?: string,
     *     state?: ?string,
     *     postal_code?: string,
     *     country?: string,
     *     address_type?: string,
     *     is_default?: bool
     * } $data Fields to update.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: null,
     *     code: int,
     *     context: array
     * }
     */
    public function updateAddress(int $addressId, array $data): array
    {
        try {
            if (empty($data['address_line1']) || empty($data['city']) || empty($data['postal_code']) || empty($data['country'])) {
                throw new ValidationException('Required address fields are missing.', context: ['required' => ['address_line1', 'city', 'postal_code', 'country']]);
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
                return [
                    'success' => true,
                    'message' => 'Address updated successfully',
                    'data' => null,
                    'code' => 200,
                    'context' => []
                ];
            }

            throw new NotFoundException('Address not found or no changes made.', context: ['addressId' => $addressId]);
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Address update failed: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Soft-delete a user address.
     *
     * @param int $addressId The address ID to delete.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: null,
     *     code: int,
     *     context: array
     * }
     */
    public function deleteAddress(int $addressId): array
    {
        try {
            $affected = $this->userRepository->softDeleteAddress($addressId);

            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => 'Address deleted successfully',
                    'data' => null,
                    'code' => 200,
                    'context' => []
                ];
            }

            throw new NotFoundException('Address not found.', context: ['addressId' => $addressId]);
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Address deletion failed: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get paginated list of all users (admin only).
     *
     * @param int $limit Maximum number of users to return.
     * @param int $offset Pagination offset.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array<array{
     *         id: int,
     *         name: string,
     *         email: string,
     *         phone: ?string,
     *         is_active: bool,
     *         is_admin: bool,
     *         created_at: string,
     *         last_login_at: ?string
     *     }>,
     *     code: int,
     *     context: array
     * }
     */
    public function getAllUsers(int $limit = 50, int $offset = 0): array
    {
        try {
            if (!Session::getInstance()->isAdmin()) {
                throw new UnauthorizedException('Access denied: admin privileges required.', context: ['action' => 'getAllUsers']);
            }

            $users = $this->userRepository->getAllUsers($limit, $offset);

            $userData = array_map(fn($user) => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'is_active' => $user->isActive(),
                'is_admin' => $user->isAdmin(),
                'created_at' => $user->getCreatedAt(),
                'last_login_at' => $user->getLastLoginAt()
            ], $users);

            return [
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $userData,
                'code' => 200,
                'context' => []
            ];
        } catch (UnauthorizedException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve users: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Retrieve full user details by ID (admin or self).
     *
     * @param int $userId The user ID to fetch.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array{
     *         id: int,
     *         name: string,
     *         email: string,
     *         phone: ?string,
     *         profileImageUrl: ?string,
     *         isActive: bool,
     *         isAdmin: bool,
     *         isAnonymized: bool,
     *         createdAt: string,
     *         updatedAt: ?string,
     *         deleteAt: ?string,
     *         anonymizedAt: ?string,
     *         lastLoginAt: ?string
     *     },
     *     code: int,
     *     context: array
     * }
     */
    public function getUserById(int $userId): array
    {
        try {
            $user = $this->userRepository->getUserById($userId);
            if (!$user) {
                throw new NotFoundException('User not found.', context: ['userId' => $userId]);
            }

            return [
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'profileImageUrl' => $user->getProfileImageUrl(),
                    'isActive' => $user->isActive(),
                    'isAdmin' => $user->isAdmin(),
                    'isAnonymized' => $user->isAnonymized(),
                    'createdAt' => $user->getCreatedAt(),
                    'updatedAt' => $user->getUpdatedAt(),
                    'deleteAt' => $user->getDeletedAt(),
                    'anonymizedAt' => $user->getAnonymizedAt(),
                    'lastLoginAt' => $user->getLastLoginAt()
                ],
                'code' => 200,
                'context' => []
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching user: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }
}