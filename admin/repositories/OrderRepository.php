<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class OrderRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?OrderModel
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByOrderNumber(string $orderNumber): ?OrderModel
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE order_number = :order_number");
        $stmt->execute([':order_number' => $orderNumber]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM orders WHERE user_id = :user_id 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function count(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM orders");
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): OrderModel
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO orders (cart_id, user_id, total_cents, shipping_address_id, billing_address_id, notes) 
             VALUES (:cart_id, :user_id, :total_cents, :shipping_address_id, :billing_address_id, :notes) 
             RETURNING *"
        );
        $stmt->execute([
            ':cart_id' => $data['cart_id'],
            ':user_id' => $data['user_id'] ?? null,
            ':total_cents' => $data['total_cents'],
            ':shipping_address_id' => $data['shipping_address_id'] ?? null,
            ':billing_address_id' => $data['billing_address_id'] ?? null,
            ':notes' => $data['notes'] ?? null
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create order');
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?OrderModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['status', 'notes'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($sets)) return null;

        $sql = "UPDATE orders SET " . implode(', ', $sets) . ", updated_at = NOW() 
                WHERE id = :id RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM orders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapToModel(array $row): OrderModel
    {
        return new OrderModel(
            id: (int)$row['id'],
            order_number: $row['order_number'],
            cart_id: (int)$row['cart_id'],
            user_id: $row['user_id'] ? (int)$row['user_id'] : null,
            status: $row['status'],
            total_cents: (int)$row['total_cents'],
            shipping_address_id: $row['shipping_address_id'] ? (int)$row['shipping_address_id'] : null,
            billing_address_id: $row['billing_address_id'] ? (int)$row['billing_address_id'] : null,
            notes: $row['notes'],
            created_at: $row['created_at'],
            updated_at: $row['updated_at'],
            paid_at: $row['paid_at'],
            shipped_at: $row['shipped_at'],
            delivered_at: $row['delivered_at'],
            cancelled_at: $row['cancelled_at']
        );
    }

    private function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}