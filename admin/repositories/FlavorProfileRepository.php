<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/FlavorProfileModel.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

class FlavorProfileRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }
    
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM flavor_profiles ORDER BY product_id ASC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapToModels($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getByProductId(int $productId): ?FlavorProfileModel
    {
        $stmt = $this->pdo->prepare("SELECT * FROM flavor_profiles WHERE product_id = :product_id");
        $stmt->bindValue(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function count(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM flavor_profiles");
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): FlavorProfileModel
    {
        // Convert PHP array to Postgres array string format "{tag1,tag2}"
        $tagsSql = isset($data['tags']) && is_array($data['tags']) 
            ? '{' . implode(',', array_map(fn($t) => '"' . str_replace('"', '\"', $t) . '"', $data['tags'])) . '}' 
            : '{}';

        $stmt = $this->pdo->prepare(
            "INSERT INTO flavor_profiles (product_id, sweetness, bitterness, strength, smokiness, fruitiness, spiciness, tags) 
             VALUES (:product_id, :sweetness, :bitterness, :strength, :smokiness, :fruitiness, :spiciness, :tags) 
             RETURNING *"
        );
        
        $stmt->execute([
            ':product_id' => $data['product_id'],
            ':sweetness'  => $data['sweetness'] ?? 5,
            ':bitterness' => $data['bitterness'] ?? 5,
            ':strength'   => $data['strength'] ?? 5,
            ':smokiness'  => $data['smokiness'] ?? 5,
            ':fruitiness' => $data['fruitiness'] ?? 5,
            ':spiciness'  => $data['spiciness'] ?? 5,
            ':tags'       => $tagsSql
        ]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new DatabaseException('Failed to create flavor profile');
        return $this->mapToModel($row);
    }

    public function update(int $productId, array $data): ?FlavorProfileModel
    {
        $sets = [];
        $params = [':product_id' => $productId];

        $intCols = ['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'];
        
        foreach ($intCols as $col) {
            if (isset($data[$col])) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (isset($data['tags']) && is_array($data['tags'])) {
            $sets[] = "tags = :tags";
            // Format PHP array to Postgres array string
            $params[':tags'] = '{' . implode(',', array_map(fn($t) => '"' . str_replace('"', '\"', $t) . '"', $data['tags'])) . '}';
        }

        if (empty($sets)) return null;

        $sql = "UPDATE flavor_profiles SET " . implode(', ', $sets) . " 
                WHERE product_id = :product_id RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapToModel($row) : null;
    }

    public function delete(int $productId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM flavor_profiles WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $productId]);
        return $stmt->rowCount() > 0;
    }

    private function mapToModel(array $row): FlavorProfileModel
    {
        // Parse Postgres array string "{tag1,tag2}" back to PHP array
        $tags = [];
        if (isset($row['tags']) && $row['tags'] !== '{}') {
            $cleaned = trim($row['tags'], '{}');
            // Basic parsing assuming no commas inside the tags themselves for simplicity, 
            // or use str_getcsv if needed for complex strings
            $tags = $cleaned ? str_getcsv($cleaned) : [];
        }

        return new FlavorProfileModel(
            product_id: (int)$row['product_id'],
            sweetness: (int)$row['sweetness'],
            bitterness: (int)$row['bitterness'],
            strength: (int)$row['strength'],
            smokiness: (int)$row['smokiness'],
            fruitiness: (int)$row['fruitiness'],
            spiciness: (int)$row['spiciness'],
            tags: $tags
        );
    }

    private function mapToModels(array $rows): array
    {
        return array_map(fn($row) => $this->mapToModel($row), $rows);
    }
}