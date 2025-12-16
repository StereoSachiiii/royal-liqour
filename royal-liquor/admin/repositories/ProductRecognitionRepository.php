<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../models/ProductRecognitionModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class ProductRecognitionRepository extends BaseRepository
{
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT pr.*, u.name as user_name, p.name as matched_product_name
                FROM product_recognition pr
                LEFT JOIN users u ON pr.user_id = u.id
                LEFT JOIN products p ON pr.matched_product_id = p.id
                ORDER BY pr.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        return $this->mapToModels($rows);
    }

    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT pr.*, u.name as user_name, p.name as matched_product_name
                FROM product_recognition pr
                LEFT JOIN users u ON pr.user_id = u.id
                LEFT JOIN products p ON pr.matched_product_id = p.id
                WHERE pr.session_id ILIKE :query 
                   OR u.name ILIKE :query 
                   OR p.name ILIKE :query
                   OR pr.recognized_text ILIKE :query
                ORDER BY pr.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $rows = $this->fetchAll($sql, [
            ':query' => '%' . $query . '%',
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        return $this->mapToModels($rows);
    }

    public function getById(int $id): ?ProductRecognitionModel
    {
        $sql = "SELECT pr.*, u.name as user_name, p.name as matched_product_name
                FROM product_recognition pr
                LEFT JOIN users u ON pr.user_id = u.id
                LEFT JOIN products p ON pr.matched_product_id = p.id
                WHERE pr.id = :id";
        $row = $this->fetchOne($sql, [':id' => $id]);
        return $row ? $this->mapToModel($row) : null;
    }

    public function getBySessionId(string $sessionId): array
    {
        $sql = "SELECT pr.*, u.name as user_name, p.name as matched_product_name
                FROM product_recognition pr
                LEFT JOIN users u ON pr.user_id = u.id
                LEFT JOIN products p ON pr.matched_product_id = p.id
                WHERE pr.session_id = :session_id 
                ORDER BY pr.created_at DESC";
        $rows = $this->fetchAll($sql, [':session_id' => $sessionId]);
        return $this->mapToModels($rows);
    }

    public function create(array $data): ProductRecognitionModel
    {
        $sql = "INSERT INTO product_recognition (
                    user_id, session_id, image_url, recognized_text, 
                    recognized_labels, matched_product_id, confidence_score, api_provider
                ) VALUES (
                    :user_id, :session_id, :image_url, :recognized_text,
                    :recognized_labels, :matched_product_id, :confidence_score, :api_provider
                ) RETURNING *";
        
        $stmt = $this->executeStatement($sql, [
            ':user_id' => $data['user_id'] ?? null,
            ':session_id' => $data['session_id'],
            ':image_url' => $data['image_url'],
            ':recognized_text' => $data['recognized_text'] ?? null,
            ':recognized_labels' => isset($data['recognized_labels']) ? '{' . implode(',', $data['recognized_labels']) . '}' : null,
            ':matched_product_id' => $data['matched_product_id'] ?? null,
            ':confidence_score' => $data['confidence_score'] ?? null,
            ':api_provider' => $data['api_provider'] ?? null
        ]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new DatabaseException('Failed to create product recognition');
        }
        return $this->mapToModel($row);
    }

    public function update(int $id, array $data): ?ProductRecognitionModel
    {
        $sets = [];
        $params = [':id' => $id];

        // All updatable columns
        $columns = [
            'image_url', 'recognized_text', 'matched_product_id', 
            'confidence_score', 'api_provider', 'user_id'
        ];

        foreach ($columns as $col) {
            if (array_key_exists($col, $data)) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        // Handle recognized_labels array separately
        if (array_key_exists('recognized_labels', $data)) {
            $sets[] = "recognized_labels = :recognized_labels";
            $params[':recognized_labels'] = is_array($data['recognized_labels']) 
                ? '{' . implode(',', $data['recognized_labels']) . '}' 
                : $data['recognized_labels'];
        }

        if (empty($sets)) {
            return null;
        }

        $sql = "UPDATE product_recognition SET " . implode(', ', $sets) . " 
                WHERE id = :id RETURNING *";
        
        $stmt = $this->executeStatement($sql, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->executeStatement("DELETE FROM product_recognition WHERE id = :id", [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        return (int)$this->fetchColumn("SELECT COUNT(*) FROM product_recognition");
    }

    protected function mapToModel(array $row): ProductRecognitionModel
    {
        return new ProductRecognitionModel(
            id: (int)$row['id'],
            session_id: $row['session_id'] ?? null,
            image_data: $row['image_url'] ?? null,  // Map image_url to image_data for model
            confidence_score: isset($row['confidence_score']) ? (float)$row['confidence_score'] : null,
            recognized_product_id: isset($row['matched_product_id']) ? (int)$row['matched_product_id'] : null,
            processing_time: null,  // Not in this schema
            status: null,  // Not in this schema
            created_at: $row['created_at'] ?? null
        );
    }

    protected function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}
