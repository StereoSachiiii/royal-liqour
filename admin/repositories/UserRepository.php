<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/User.php';

//exceptions
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

/**
 * UserRepository
 * 
 * Handles all database operations related to users including CRUD operations,
 * authentication, address management, and GDPR compliance (anonymization).
 */
class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    /**
     * Create a new user using the sp_create_user stored procedure.
     *
     * @param string $name The user's full name
     * @param string $email The user's email address
     * @param string|null $phone The user's phone number (optional)
     * @param string $password The plain text password (will be hashed)
     * @return UserModel The newly created user model
     * @throws DatabaseException If database operation fails
     */
    public function createUser(string $name, string $email, ?string $phone, string $password): UserModel
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare(
            'SELECT * FROM sp_create_user(:name, :email, :phone, :password_hash)'
        );
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':password_hash' => $passwordHash
        ]);
        $row = $stmt->fetch();

        if ($row === false) {
            throw new DatabaseException('Failed to create user.');
        }

        return new UserModel(
            id: (int) $row['id'],
            name: $row['name'],
            email: $row['email'],
            phone: $row['phone'],
            passwordHash: $row['password_hash'],
            profileImageUrl: $row['profile_image_url'],
            isActive: (bool) $row['is_active'],
            isAdmin: (bool) $row['is_admin'],
            isAnonymized: (bool) $row['is_anonymized'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
            deletedAt: $row['deleted_at'],
            anonymizedAt: $row['anonymized_at'],
            lastLoginAt: $row['last_login_at']
        );
    }

    /**
     * Create a new admin user with elevated privileges.
     *
     * @param string $name The admin user's full name
     * @param string $email The admin user's email address
     * @param string|null $phone The admin user's phone number (optional)
     * @param string $password The plain text password (will be hashed)
     * @return UserModel The newly created admin user model
     * @throws DatabaseException If database operation fails
     */
    public function createAdminUser(string $name, string $email, ?string $phone, string $password): UserModel
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare(
            'INSERT into users(name,email,phone,password_hash,is_admin) VALUES (:name, :email, :phone,
            :password_hash, :is_admin)
            RETURNING * '
        );

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':password_hash' => $passwordHash,
            ':is_admin' => true
        ]);

        $row = $stmt->fetch();

        if ($row === false) {
            throw new DatabaseException('Failed to create an Admin user.');
        }

        return new UserModel(
            id: (int) $row['id'],
            name: $row['name'],
            email: $row['email'],
            phone: $row['phone'],
            passwordHash: $row['password_hash'],
            profileImageUrl: $row['profile_image_url'],
            isActive: (bool) $row['is_active'],
            isAdmin: (bool) $row['is_admin'],
            isAnonymized: (bool) $row['is_anonymized'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
            deletedAt: $row['deleted_at'],
            anonymizedAt: $row['anonymized_at'],
            lastLoginAt: $row['last_login_at']
        );
    }

    /**
     * Update user information. Only provided fields will be updated.
     *
     * @param int $userId The ID of the user to update
     * @param string|null $name New name (optional)
     * @param string|null $email New email (optional)
     * @param string|null $phone New phone number (optional)
     * @param string|null $password New password - will be hashed (optional)
     * @param string|null $profileImageUrl New profile image URL (optional)
     * @return UserModel The updated user model
     * @throws ValidationException If no fields provided to update
     * @throws NotFoundException If user not found or already deleted
     */
    public function updateUser(
        int $userId,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $password = null,
        ?string $profileImageUrl = null
    ): UserModel {
        $query = 'UPDATE users SET ';
        $params = [':id' => $userId];
        $updates = [];

        if ($name !== null) {
            $updates[] = 'name = :name';
            $params[':name'] = $name;
        }
        if ($email !== null) {
            $updates[] = 'email = :email';
            $params[':email'] = $email;
        }
        if ($phone !== null) {
            $updates[] = 'phone = :phone';
            $params[':phone'] = $phone;
        }
        if ($password !== null) {
            $updates[] = 'password_hash = :password_hash';
            $params[':password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }
        if ($profileImageUrl !== null) {
            $updates[] = 'profile_image_url = :profile_image_url';
            $params[':profile_image_url'] = $profileImageUrl;
        }

        if (empty($updates)) {
            throw new ValidationException('No fields provided to update.');
        }

        $query .= implode(', ', $updates) . ', updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            throw new NotFoundException('Failed to update user or user not found.');
        }

        return $this->getUserById($userId);
    }

    /**
     * Find user by email address.
     *
     * @param string $email The email address to search for
     * @return UserModel The user model matching the email
     * @throws NotFoundException If no user found with given email or user is deleted
     */
    public function getUserByEmail(string $email): UserModel
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email AND deleted_at IS NULL');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new NotFoundException('No user found for this email', ['email' => $email]);
        }

        return new UserModel(
            id: (int) $row['id'],
            name: $row['name'],
            email: $row['email'],
            phone: $row['phone'],
            passwordHash: $row['password_hash'],
            profileImageUrl: $row['profile_image_url'],
            isActive: (bool) $row['is_active'],
            isAdmin: (bool) $row['is_admin'],
            isAnonymized: (bool) $row['is_anonymized'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
            deletedAt: $row['deleted_at'],
            anonymizedAt: $row['anonymized_at'],
            lastLoginAt: $row['last_login_at']
        );
    }

    /**
     * Find user by ID.
     *
     * @param int $userId The user ID to search for
     * @return UserModel The user model matching the ID
     * @throws NotFoundException If no user found with given ID or user is deleted
     */
    public function getUserById(int $userId): UserModel
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new NotFoundException('No user found for user_id', ['userId' => $userId]);
        }

        return new UserModel(
            id: (int) $row['id'],
            name: $row['name'],
            email: $row['email'],
            phone: $row['phone'],
            passwordHash: $row['password_hash'],
            profileImageUrl: $row['profile_image_url'],
            isActive: (bool) $row['is_active'],
            isAdmin: (bool) $row['is_admin'],
            isAnonymized: (bool) $row['is_anonymized'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
            deletedAt: $row['deleted_at'],
            anonymizedAt: $row['anonymized_at'],
            lastLoginAt: $row['last_login_at']
        );
    }

    /**
     * Update the last login timestamp for a user.
     *
     * @param int $userId The ID of the user who logged in
     * @return int Number of affected rows (should be 1 on success)
     * @throws DatabaseException If user not found or no rows were updated
     */
    public function updateLastLogin(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sp_update_last_login(:user_id)');
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $affectedRows = (int)($result['affected_rows'] ?? 0);

        if ($affectedRows === 0) {
            throw new DatabaseException('User not found or no rows were updated', ['userId' => $userId]);
        }

        return $affectedRows;
    }

    /**
     * Anonymize a user for GDPR compliance.
     * Replaces personal information with anonymized data.
     *
     * @param int $userId The ID of the user to anonymize
     * @return int Number of affected rows (should be 1 on success)
     * @throws DatabaseException If user not found or no rows were updated
     */
    public function anonymizeUser(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sp_anonymize_user(:user_id)');
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $affectedRows = (int)($result['affected_rows'] ?? 0);

        if ($affectedRows === 0) {
            throw new DatabaseException('User not found or no rows were updated', ['userId' => $userId]);
        }

        return $affectedRows;
    }

    /**
     * Get user addresses filtered by address type.
     *
     * @param int $userId The ID of the user
     * @param string|null $addressType Filter by type: 'billing', 'shipping', 'both', or null for all
     * @return array Array of address records
     */
    public function getUserAddresses(int $userId, ?string $addressType = null): array
    {
        $query = 'SELECT * FROM sp_get_user_addresses(:user_id, :address_type)';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':address_type' => $addressType
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new address for a user.
     *
     * @param int $userId The ID of the user
     * @param string $addressType Must be 'billing', 'shipping', or 'both'
     * @param string $addressLine1 First line of the address
     * @param string|null $addressLine2 Second line of the address (optional)
     * @param string $city City name
     * @param string|null $state State or province (optional)
     * @param string $postalCode Postal or ZIP code
     * @param string $country Country name or code
     * @param bool $isDefault Whether this is the default address
     * @return int|null The newly created address ID, or null on failure
     * @throws ValidationException If addressType is not 'billing', 'shipping', or 'both'
     */
    public function createAddress(
        int $userId,
        string $addressType,
        string $addressLine1,
        ?string $addressLine2,
        string $city,
        ?string $state,
        string $postalCode,
        string $country,
        bool $isDefault
    ): ?int {
        if (!in_array($addressType, ['billing', 'shipping', 'both'])) {
            throw new ValidationException('Invalid address_type. Must be billing, shipping, or both.');
        }

        $stmt = $this->pdo->prepare(
            'SELECT * FROM sp_create_address(:user_id, :address_type, :address_line1, :address_line2, :city, :state, :postal_code, :country, :is_default)'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':address_type' => $addressType,
            ':address_line1' => $addressLine1,
            ':address_line2' => $addressLine2,
            ':city' => $city,
            ':state' => $state,
            ':postal_code' => $postalCode,
            ':country' => $country,
            ':is_default' => $isDefault
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($result['address_id']) ? (int) $result['address_id'] : null;
    }

    /**
     * Update an existing address.
     *
     * @param int $addressId The ID of the address to update
     * @param string $addressType Must be 'billing', 'shipping', or 'both'
     * @param string $addressLine1 First line of the address
     * @param string|null $addressLine2 Second line of the address (optional)
     * @param string $city City name
     * @param string|null $state State or province (optional)
     * @param string $postalCode Postal or ZIP code
     * @param string $country Country name or code
     * @param bool $isDefault Whether this is the default address
     * @return int Number of affected rows (should be 1 on success)
     * @throws ValidationException If addressType is not 'billing', 'shipping', or 'both'
     */
    public function updateAddress(
        int $addressId,
        string $addressType,
        string $addressLine1,
        ?string $addressLine2,
        string $city,
        ?string $state,
        string $postalCode,
        string $country,
        bool $isDefault
    ): int {
        if (!in_array($addressType, ['billing', 'shipping', 'both'])) {
            throw new ValidationException('Invalid address_type. Must be billing, shipping, or both.');
        }

        $stmt = $this->pdo->prepare(
            'SELECT * FROM sp_update_address(:address_id, :address_type, :address_line1, :address_line2, :city, :state, :postal_code, :country, :is_default)'
        );
        $stmt->execute([
            ':address_id' => $addressId,
            ':address_type' => $addressType,
            ':address_line1' => $addressLine1,
            ':address_line2' => $addressLine2,
            ':city' => $city,
            ':state' => $state,
            ':postal_code' => $postalCode,
            ':country' => $country,
            ':is_default' => $isDefault
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['affected_rows'] ?? 0);
    }

    /**
     * Soft delete a user address (marks as deleted without removing from database).
     *
     * @param int $addressId The ID of the address to delete
     * @return int Number of affected rows (should be 1 on success)
     */
    public function softDeleteAddress(int $addressId): int
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sp_soft_delete_address(:address_id)');
        $stmt->execute([':address_id' => $addressId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['affected_rows'] ?? 0);
    }

    /**
     * Get all users with pagination (admin function).
     *
     * @param int $limit Maximum number of users to return (default: 50)
     * @param int $offset Number of users to skip for pagination (default: 0)
     * @return UserModel[] Array of user models
     */
    public function getAllUsers(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new UserModel(
                id: (int) $row['id'],
                name: $row['name'],
                email: $row['email'],
                phone: $row['phone'],
                passwordHash: $row['password_hash'],
                profileImageUrl: $row['profile_image_url'],
                isActive: (bool) $row['is_active'],
                isAdmin: (bool) $row['is_admin'],
                isAnonymized: (bool) $row['is_anonymized'],
                createdAt: $row['created_at'],
                updatedAt: $row['updated_at'],
                deletedAt: $row['deleted_at'],
                anonymizedAt: $row['anonymized_at'],
                lastLoginAt: $row['last_login_at']
            );
        }
        return $users;
    }
}

?>