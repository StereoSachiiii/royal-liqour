<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/AddressModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class AddressRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function getUserAddresses(int $userId, ?string $addressType = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM user_addresses 
                WHERE user_id = :user_id AND deleted_at IS NULL";
        $params = [':user_id' => $userId];

        if ($addressType) {
            $sql .= " AND address_type = :type";
            $params[':type'] = $addressType;
        }

        $sql .= " ORDER BY is_default DESC, created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        $stmt->execute($params);
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?AddressModel
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM user_addresses WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function create(int $userId, array $data): AddressModel
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO user_addresses 
             (user_id, address_type, recipient_name, phone, address_line1, address_line2, 
              city, state, postal_code, country, is_default) 
             VALUES 
             (:user_id, :type, :recipient, :phone, :line1, :line2, :city, :state, 
              :postal, :country, :default) 
             RETURNING *"
        );
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
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create address');
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?AddressModel
    {
        $sets = [];
        $params = [':id' => $id];

        $fields = [
            'address_type', 'recipient_name', 'phone', 'address_line1', 'address_line2',
            'city', 'state', 'postal_code', 'country', 'is_default'
        ];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $sets[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($sets)) return null;

        $sql = "UPDATE user_addresses SET " . implode(', ', $sets) . ", updated_at = NOW() 
                WHERE id = :id AND deleted_at IS NULL RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE user_addresses SET deleted_at = NOW(), updated_at = NOW() 
             WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapToModel(array $row): AddressModel
    {
        return new AddressModel(
            id: (int)$row['id'],
            user_id: (int)$row['user_id'],
            address_type: $row['address_type'],
            recipient_name: $row['recipient_name'],
            phone: $row['phone'],
            address_line1: $row['address_line1'],
            address_line2: $row['address_line2'],
            city: $row['city'],
            state: $row['state'],
            postal_code: $row['postal_code'],
            country: $row['country'],
            is_default: (bool)$row['is_default'],
            created_at: $row['created_at'],
            updated_at: $row['updated_at'],
            deleted_at: $row['deleted_at']
        );
    }

    private function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}