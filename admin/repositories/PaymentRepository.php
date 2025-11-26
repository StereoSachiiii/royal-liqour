<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/PaymentModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class PaymentRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM payments ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?PaymentModel
    {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getByOrder(int $orderId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $orderId]);
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function count(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM payments");
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): PaymentModel
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO payments (order_id, amount_cents, currency, gateway, 
              gateway_order_id, transaction_id, status, payload) 
             VALUES (:order_id, :amount_cents, :currency, :gateway, 
              :gateway_order_id, :transaction_id, :status, :payload) 
             RETURNING *"
        );
        $stmt->execute([
            ':order_id' => $data['order_id'],
            ':amount_cents' => $data['amount_cents'],
            ':currency' => $data['currency'] ?? 'LKR',
            ':gateway' => $data['gateway'],
            ':gateway_order_id' => $data['gateway_order_id'] ?? null,
            ':transaction_id' => $data['transaction_id'] ?? null,
            ':status' => $data['status'] ?? 'pending',
            ':payload' => isset($data['payload']) ? json_encode($data['payload']) : null
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create payment');
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?PaymentModel
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['status', 'transaction_id', 'payload'] as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $col === 'payload' ? json_encode($data[$col]) : $data[$col];
            }
        }

        if (empty($sets)) return null;

        $sql = "UPDATE payments SET " . implode(', ', $sets) . " 
                WHERE id = :id RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM payments WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapToModel(array $row): PaymentModel
    {
        return new PaymentModel(
            id: (int)$row['id'],
            order_id: (int)$row['order_id'],
            amount_cents: (int)$row['amount_cents'],
            currency: $row['currency'],
            gateway: $row['gateway'],
            gateway_order_id: $row['gateway_order_id'],
            transaction_id: $row['transaction_id'],
            status: $row['status'],
            payload: $row['payload'] ? json_decode($row['payload'], true) : null,
            created_at: $row['created_at']
        );
    }

    private function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}