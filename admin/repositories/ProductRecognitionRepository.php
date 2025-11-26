<?php

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/ProductRecognition.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class ProductRecognitionRepository {
    private ?PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getPdo();
    }

    private function mapRowToProductRecognition(array $row): ProductRecognition {
        $recognizedLabels = $row['recognized_labels'] ? $this->parsePostgresArray($row['recognized_labels']) : [];
        
        return new ProductRecognition(
            (int)$row['id'],
            isset($row['user_id']) ? (int)$row['user_id'] : null,
            $row['session_id'],
            $row['image_url'],
            $row['recognized_text'],
            $recognizedLabels,
            isset($row['matched_product_id']) ? (int)$row['matched_product_id'] : null,
            isset($row['confidence_score']) ? (float)$row['confidence_score'] : null,
            $row['api_provider'],
            $row['created_at']
        );
    }

    private function parsePostgresArray(?string $array): array {
        if (!$array) return [];
        $array = trim($array, '{}');
        if (empty($array)) return [];
        return explode(',', $array);
    }

    private function mapRowsToProductRecognitions(array $rows): array {
        return array_map(fn(array $row) => $this->mapRowToProductRecognition($row), $rows);
    }

    public function create(array $data): ProductRecognition {
        $recognizedLabels = isset($data['recognized_labels']) ? '{' . implode(',', $data['recognized_labels']) . '}' : null;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO product_recognition 
            (user_id, session_id, image_url, recognized_text, recognized_labels, matched_product_id, confidence_score, api_provider)
            VALUES (:user_id, :session_id, :image_url, :recognized_text, :recognized_labels, :matched_product_id, :confidence_score, :api_provider)
            RETURNING *
        ");
        
        $stmt->bindValue(":user_id", $data['user_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(":session_id", $data['session_id'], PDO::PARAM_STR);
        $stmt->bindValue(":image_url", $data['image_url'], PDO::PARAM_STR);
        $stmt->bindValue(":recognized_text", $data['recognized_text'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":recognized_labels", $recognizedLabels, PDO::PARAM_STR);
        $stmt->bindValue(":matched_product_id", $data['matched_product_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(":confidence_score", $data['confidence_score'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(":api_provider", $data['api_provider'] ?? null, PDO::PARAM_STR);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new DatabaseException("Failed to create product recognition.");
        }
        return $this->mapRowToProductRecognition($row);
    }

    public function getById(int $id): ?ProductRecognition {
        $stmt = $this->pdo->prepare("SELECT * FROM product_recognition WHERE id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToProductRecognition($row) : null;
    }

    public function getByUserId(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM product_recognition 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC
        ");
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->mapRowsToProductRecognitions($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getBySessionId(string $sessionId): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM product_recognition 
            WHERE session_id = :session_id 
            ORDER BY created_at DESC
        ");
        $stmt->bindValue(":session_id", $sessionId, PDO::PARAM_STR);
        $stmt->execute();
        
        return $this->mapRowsToProductRecognitions($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getRecent(int $limit = 10): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM product_recognition 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->mapRowsToProductRecognitions($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM product_recognition WHERE id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function getAll(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM product_recognition ORDER BY created_at DESC");
        $stmt->execute();
        
        return $this->mapRowsToProductRecognitions($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getAllPaginated(int $limit, int $offset): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM product_recognition 
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->mapRowsToProductRecognitions($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

?>