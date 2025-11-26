<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class CartRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM carts ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?CartModel
    {
        $stmt = $this->pdo->prepare("SELECT * FROM carts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getActiveByUser(int $userId): ?CartModel
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM carts WHERE user_id = :user_id AND status = 'active'"
        );
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getActiveBySession(string $sessionId): ?CartModel
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM carts WHERE session_id = :session_id AND status = 'active'"
        );
        $stmt->execute([':session_id' => $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function count(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM carts");
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): CartModel
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO carts (user_id, session_id) 
             VALUES (:user_id, :session_id) 
             RETURNING *"
        );
        $stmt->execute([
            ':user_id' => $data['user_id'] ?? null,
            ':session_id' => $data['session_id']
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create cart');
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?CartModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['user_id', 'session_id', 'status', 'total_cents', 'item_count'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($sets)) return null;

        $sql = "UPDATE carts SET " . implode(', ', $sets) . ", updated_at = NOW() 
                WHERE id = :id RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM carts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapToModel(array $row): CartModel
    {
        return new CartModel(
            id: (int)$row['id'],
            user_id: $row['user_id'] ? (int)$row['user_id'] : null,
            session_id: $row['session_id'],
            status: $row['status'],
            total_cents: (int)$row['total_cents'],
            item_count: (int)$row['item_count'],
            created_at: $row['created_at'],
            updated_at: $row['updated_at'],
            converted_at: $row['converted_at'],
            abandoned_at: $row['abandoned_at']
        );
    }

    private function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}