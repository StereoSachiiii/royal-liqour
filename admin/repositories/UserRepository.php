<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function create(string $name, string $email, ?string $phone, string $passwordHash): UserModel
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, phone, password_hash)
            VALUES (:name, :email, :phone, :hash)
            RETURNING *
        ");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':hash' => $passwordHash
        ]);

        $row = $stmt->fetch();
        if (!$row) throw new DatabaseException('Failed to create user');
        return $this->mapToModel($row);
    }

    public function createAdmin(string $name, string $email, ?string $phone, string $passwordHash): UserModel
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, phone, password_hash, is_admin)
            VALUES (:name, :email, :phone, :hash, true)
            RETURNING *
        ");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':hash' => $passwordHash
        ]);

        $row = $stmt->fetch();
        if (!$row) throw new DatabaseException('Failed to create admin');
        return $this->mapToModel($row);
    }

    public function findByEmail(string $email): ?UserModel
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE email = :email AND deleted_at IS NULL AND is_active = TRUE
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ? $this->mapToModel($row) : null;
    }

    public function findById(int $id): ?UserModel
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users WHERE id = :id AND deleted_at IS NULL AND is_anonymized = FALSE
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapToModel($row) : null;
    }

    public function updateLastLogin(int $userId): void
    {
        $this->pdo->prepare("
            UPDATE users SET last_login_at = NOW() 
            WHERE id = :id AND deleted_at IS NULL
        ")->execute([':id' => $userId]);
    }

    public function anonymizeUser(int $userId): int
    {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET name = 'Anonymized User',
                email = CONCAT('deleted_', id, '@deleted.local'),
                phone = NULL,
                password_hash = '',
                profile_image_url = NULL,
                is_anonymized = TRUE,
                anonymized_at = NOW(),
                updated_at = NOW()
            WHERE id = :id AND deleted_at IS NULL
        ");
        $stmt->execute([':id' => $userId]);
        return $stmt->rowCount();
    }

    public function updateProfile(int $userId, array $data): UserModel
    {
        $sets = ['updated_at = NOW()'];
        $params = [':id' => $userId];

        foreach (['name', 'email', 'phone', 'profile_image_url'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (isset($data['password'])) {
            $sets[] = "password_hash = :hash";
            $params[':hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id AND deleted_at IS NULL RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        if (!$row) throw new NotFoundException('User not found');
        return $this->mapToModel($row);
    }

    public function getUserAddresses(int $userId, ?string $addressType = null): array
    {
        $sql = "SELECT * FROM user_addresses WHERE user_id = :user_id AND deleted_at IS NULL";
        $params = [':user_id' => $userId];

        if ($addressType) {
            $sql .= " AND address_type = :type";
            $params[':type'] = $addressType;
        }

        $sql .= " ORDER BY is_default DESC, created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAddress(int $userId, array $data): int
    {
        $sql = "
            INSERT INTO user_addresses 
            (user_id, address_type, recipient_name, phone, address_line1, address_line2, city, state, postal_code, country, is_default)
            VALUES 
            (:user_id, :type, :recipient, :phone, :line1, :line2, :city, :state, :postal, :country, :default)
            RETURNING id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $data['address_type'] ?? 'both',
            ':recipient' => $data['recipient_name'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':line1' => $data['address_line1'],
            ':line2' => $data['address_line2'] ?? null,
            ':city' => $data['city'],
            ':state' => $data['state'] ?? null,
            ':postal' => $data['postal_code'],
            ':country' => $data['country'] ?? 'Sri Lanka',
            ':default' => $data['is_default'] ?? false
        ]);

        $row = $stmt->fetch();
        return $row['id'] ?? 0;
    }

    public function updateAddress(int $addressId, array $data): int
    {
        $sets = [];
        $params = [':id' => $addressId];

        $fields = [
            'address_type', 'recipient_name', 'phone', 'address_line1', 'address_line2',
            'city', 'state', 'postal_code', 'country', 'is_default'
        ];

        foreach ($fields as $field) {
            $key = match($field) {
                'is_default' => 'is_default',
                default => $field
            };
            if (isset($data[$key])) {
                $col = $field;
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$key];
            }
        }

        if (empty($sets)) return 0;

        $sql = "UPDATE user_addresses SET " . implode(', ', $sets) . ", updated_at = NOW() 
                WHERE id = :id AND deleted_at IS NULL RETURNING id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function softDeleteAddress(int $addressId): int
    {
        $stmt = $this->pdo->prepare("
            UPDATE user_addresses SET deleted_at = NOW(), updated_at = NOW()
            WHERE id = :id AND deleted_at IS NULL
        ");
        $stmt->execute([':id' => $addressId]);
        return $stmt->rowCount();
    }

    public function getAllUsers(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE deleted_at IS NULL 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $users = [];
        while ($row = $stmt->fetch()) {
            $users[] = $this->mapToModel($row);
        }
        return $users;
    }

    private function mapToModel(array $row): UserModel
    {
        return new UserModel(
            id: (int)$row['id'],
            name: $row['name'],
            email: $row['email'],
            phone: $row['phone'],
            passwordHash: $row['password_hash'],
            profileImageUrl: $row['profile_image_url'],
            isActive: (bool)$row['is_active'],
            isAdmin: (bool)$row['is_admin'],
            isAnonymized: (bool)$row['is_anonymized'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
            deletedAt: $row['deleted_at'],
            anonymizedAt: $row['anonymized_at'],
            lastLoginAt: $row['last_login_at']
        );
    }
}