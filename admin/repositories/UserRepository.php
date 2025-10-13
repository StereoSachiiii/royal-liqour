<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/User.php';


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
     * @param string $name
     * @param string $email
     * @param string|null $phone
     * @param string $password
     * @return UserModel|null
     * @throws InvalidArgumentException If input validation fails
     * @throws RuntimeException If database operation fails
     */
    public function createUser(string $name, string $email, ?string $phone, string $password): ?UserModel
    {
        if (strlen($name) < 1 || strlen($name) > 100) {
            throw new InvalidArgumentException('Name must be between 1 and 100 characters.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            throw new InvalidArgumentException('Invalid email format or too long.');
        }

        if ($phone !== null && !preg_match('/^\+?\d{0,15}$/', $phone)) {
            throw new InvalidArgumentException('Invalid phone number format.');
        }

        if (strlen($password) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters.');
        }

        if ($this->getUserByEmail($email)) {
            throw new InvalidArgumentException('Email already registered.');
        }

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
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result === false || !isset($result['user_id'])) {
            throw new RuntimeException('Failed to create user.');
        }

        $userId = (int) $result['user_id'];
        return $this->getUserById($userId);
    }

    /**
     * Create a new admin user using the sp_create_user stored procedure.
     *
     * @param string $name
     * @param string $email
     * @param string|null $phone
     * @param string $password
     * @return UserModel|null
     * @throws InvalidArgumentException If input validation fails
     * @throws RuntimeException If database operation fails
     */
    public function createAdminUser(string $name, string $email, ?string $phone, string $password): ?UserModel
    {
        if (strlen($name) < 1 || strlen($name) > 100) {
            throw new InvalidArgumentException('Name must be between 1 and 100 characters.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            throw new InvalidArgumentException('Invalid email format or too long.');
        }

        if ($phone !== null && !preg_match('/^\+?\d{0,15}$/', $phone)) {
            throw new InvalidArgumentException('Invalid phone number format.');
        }

        if (strlen($password) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters.');
        }

        if ($this->getUserByEmail($email)) {
            throw new InvalidArgumentException('Email already registered.');
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare(
            'INSERT INTO users(name, email, phone, password_hash, is_admin) 
             VALUES (:name, :email, :phone, :password_hash, :is_admin) 
             RETURNING id'
        );
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':password_hash' => $passwordHash,
            ':is_admin' => true
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result === false || !isset($result['id'])) {
            throw new RuntimeException('Failed to create an Admin user.');
        }

        $userId = (int) $result['id'];
        return $this->getUserById($userId);
    }

    /**
     * Update user profile data.
     *
     * @param int $userId
     * @param string|null $name
     * @param string|null $email
     * @param string|null $phone
     * @param string|null $password
     * @param string|null $profileImageUrl
     * @return UserModel|null
     * @throws InvalidArgumentException If input validation fails
     * @throws RuntimeException If database operation fails
     */
    public function updateUser(
        int $userId,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $password = null,
        ?string $profileImageUrl = null
    ): ?UserModel {
        if ($name !== null && (strlen($name) < 1 || strlen($name) > 100)) {
            throw new InvalidArgumentException('Name must be between 1 and 100 characters.');
        }

        if ($email !== null) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
                throw new InvalidArgumentException('Invalid email format or too long.');
            }
            if ($email !== $this->getUserById($userId)?->getEmail() && $this->getUserByEmail($email)) {
                throw new InvalidArgumentException('Email already registered.');
            }
        }

        if ($phone !== null && !preg_match('/^\+?\d{0,15}$/', $phone)) {
            throw new InvalidArgumentException('Invalid phone number format.');
        }

        if ($password !== null && strlen($password) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters.');
        }

        if ($profileImageUrl !== null && strlen($profileImageUrl) > 500) {
            throw new InvalidArgumentException('Profile image URL too long.');
        }

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
            throw new InvalidArgumentException('No fields provided to update.');
        }

        $query .= implode(', ', $updates) . ', updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Failed to update user or user not found.');
        }

        return $this->getUserById($userId);
    }

    /**
     * Find user by email.
     *
     * @param string $email
     * @return UserModel|null
     */
    public function getUserByEmail(string $email): ?UserModel
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email AND deleted_at IS NULL');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
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
     * @param int $userId
     * @return UserModel|null
     */
    public function getUserById(int $userId): ?UserModel
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
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
     * @param int $userId
     * @return int Number of affected rows
     */
    public function updateLastLogin(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sp_update_last_login(:user_id)');
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['affected_rows'] ?? 0);
    }

    /**
     * Anonymize a user (GDPR compliance).
     *
     * @param int $userId
     * @return int Number of affected rows
     */
    public function anonymizeUser(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sp_anonymize_user(:user_id)');
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['affected_rows'] ?? 0);
    }

    /**
     * Get user addresses.
     *
     * @param int $userId
     * @param string|null $addressType Must be 'billing', 'shipping', 'both', or null
     * @return array
     * @throws InvalidArgumentException If addressType is invalid
     */
    public function getUserAddresses(int $userId, ?string $addressType = null): array
    {
        if ($addressType !== null && !in_array($addressType, ['billing', 'shipping', 'both'])) {
            throw new InvalidArgumentException('Invalid address_type. Must be billing, shipping, or both.');
        }

        $query = 'SELECT * FROM sp_get_user_addresses(:user_id, :address_type)';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':address_type' => $addressType
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a user address.
     *
     * @param int $userId
     * @param string $addressType Must be 'billing', 'shipping', or 'both'
     * @param string $addressLine1
     * @param string|null $addressLine2
     * @param string $city
     * @param string|null $state
     * @param string $postalCode
     * @param string $country
     * @param bool $isDefault
     * @return int|null Address ID or null on failure
     * @throws InvalidArgumentException If addressType is invalid
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
            throw new InvalidArgumentException('Invalid address_type. Must be billing, shipping, or both.');
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
     * Update a user address.
     *
     * @param int $addressId
     * @param string $addressType Must be 'billing', 'shipping', or 'both'
     * @param string $addressLine1
     * @param string|null $addressLine2
     * @param string $city
     * @param string|null $state
     * @param string $postalCode
     * @param string $country
     * @param bool $isDefault
     * @return int Number of affected rows
     * @throws InvalidArgumentException If addressType is invalid
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
            throw new InvalidArgumentException('Invalid address_type. Must be billing, shipping, or both.');
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
     * Soft delete a user address.
     *
     * @param int $addressId
     * @return int Number of affected rows
     */
    public function softDeleteAddress(int $addressId): int
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sp_soft_delete_address(:address_id)');
        $stmt->execute([':address_id' => $addressId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['affected_rows'] ?? 0);
    }

    /**
     * Get all users (for admin).
     *
     * @param int $limit
     * @param int $offset
     * @return UserModel[]
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