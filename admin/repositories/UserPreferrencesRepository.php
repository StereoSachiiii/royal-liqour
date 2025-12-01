<?php
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/UserPreferrenceModel.php';

class UserPreferrenceRepository{
    private PDO $db;

    public function __construct() {
        $this->db = Database::getPdo();
    }
    
    public function existsByUserId(int $userId): bool {
        $sql = "SELECT COUNT(*) FROM user_preferences WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }

    public function mapRowtoUserPreference(array $row): UserPreferrenceModel {
        return new UserPreferrenceModel([
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'preferred_sweetness' => $row['preferred_sweetness'],
            'preferred_bitterness' => $row['preferred_bitterness'],
            'preferred_strength' => $row['preferred_strength'],
            'preferred_smokiness' => $row['preferred_smokiness'],
            'preferred_fruitiness' => $row['preferred_fruitiness'],
            'preferred_spiciness' => $row['preferred_spiciness'],
            'favorite_categories' => $row['favorite_categories'],
        ]);
    }
    
    public function mapRowsToUserPreferences(array $rows): array {
        $preferences = [];
        foreach ($rows as $row) {
            $preferences[] = $this->mapRowtoUserPreference($row);
        }
        return $preferences;
    }

    public function create(array $data): array {
        $sql = "INSERT INTO user_preferences (
            user_id, 
            preferred_sweetness, 
            preferred_bitterness, 
            preferred_strength, 
            preferred_smokiness, 
            preferred_fruitiness, 
            preferred_spiciness, 
            favorite_categories
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        
        $favoriteCategories = null;
        if (isset($data['favorite_categories']) && is_array($data['favorite_categories']) && !empty($data['favorite_categories'])) {
            $favoriteCategories = '{' . implode(',', $data['favorite_categories']) . '}';
        }
        
        $stmt->bindValue(1, $data['user_id'], PDO::PARAM_INT);  
        $stmt->bindValue(2, $data['preferred_sweetness'] ?? null, 
            isset($data['preferred_sweetness']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(3, $data['preferred_bitterness'] ?? null, 
            isset($data['preferred_bitterness']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(4, $data['preferred_strength'] ?? null, 
            isset($data['preferred_strength']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(5, $data['preferred_smokiness'] ?? null, 
            isset($data['preferred_smokiness']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(6, $data['preferred_fruitiness'] ?? null, 
            isset($data['preferred_fruitiness']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(7, $data['preferred_spiciness'] ?? null, 
            isset($data['preferred_spiciness']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(8, $favoriteCategories, 
            $favoriteCategories === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        
        $stmt->execute();

        $id = (int)$this->db->lastInsertId();
        return $this->getById($id);
    }
    
    public function getById(int $id): ?array {
        $sql = "SELECT 
            up.*,
            u.name as user_name,
            u.email as user_email
        FROM user_preferences up
        LEFT JOIN users u ON up.user_id = u.id
        WHERE up.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) return null;

        return $this->formatUserPreference($result);
    }

    public function getByUserId(int $userId): ?array {
        $sql = "SELECT 
            up.*,
            u.name as user_name,
            u.email as user_email
        FROM user_preferences up
        LEFT JOIN users u ON up.user_id = u.id
        WHERE up.user_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) return null;

        return $this->formatUserPreference($result);
    }

    public function update(int $id, array $data): ?array {
        // Build dynamic update query
        $fields = [];
        $values = [];

        $allowedFields = [
            'user_id', 
            'preferred_sweetness', 
            'preferred_bitterness', 
            'preferred_strength', 
            'preferred_smokiness', 
            'preferred_fruitiness', 
            'preferred_spiciness'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (array_key_exists('favorite_categories', $data)) {
            $fields[] = "favorite_categories = ?";
            
            // Check if the value is a non-empty array
            if (is_array($data['favorite_categories']) && !empty($data['favorite_categories'])) {
                // Convert non-empty array to PostgreSQL array format: {1,3,5,7}
                $values[] = '{' . implode(',', $data['favorite_categories']) . '}';
            } elseif (is_array($data['favorite_categories']) && empty($data['favorite_categories'])) {
                // Correctly handle an empty array by setting the value to an empty array string: {}
                $values[] = '{}';
            } else {
                // Handle cases where the value is missing or not an array (and you want it null)
                $values[] = null;
            }
        }

        if (empty($fields)) {
            return $this->getById($id);
        }

        $values[] = $id;
        $sql = "UPDATE user_preferences SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $this->getById($id);
    }
    
    public function delete(int $id): bool {
        $sql = "DELETE FROM user_preferences WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function getAll(): array {
        $sql = "SELECT 
            up.*,
            u.name as user_name,
            u.email as user_email
        FROM user_preferences up
        LEFT JOIN users u ON up.user_id = u.id
        ORDER BY up.id DESC";

        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'formatUserPreference'], $results);
    }

    private function formatUserPreference(array $row): array {
        // Parse PostgreSQL array format {1,3,5} to PHP array
        $favoriteCategories = [];
        $categoriesString = trim($row['favorite_categories'] ?? '');
        
        if (!empty($categoriesString) && $categoriesString !== '{}') {
            // Remove leading/trailing braces and split by comma
            // Example: "{1,3,5}" -> "1,3,5" -> ["1", "3", "5"]
            $stringWithoutBraces = trim($categoriesString, '{}');
            $stringArray = explode(',', $stringWithoutBraces);
            // Convert string IDs to integers
            $favoriteCategories = array_map('intval', $stringArray);
        }
        
        // Get category names
        $favoriteCategoryNames = [];
        if (!empty($favoriteCategories)) {
            $placeholders = implode(',', array_fill(0, count($favoriteCategories), '?'));
            $sql = "SELECT name FROM categories WHERE id IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($favoriteCategories);
            $favoriteCategoryNames = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return [
            'id' => (int)$row['id'],
            'user_id' => (int)$row['user_id'],
            'user_name' => $row['user_name'] ?? null,
            'user_email' => $row['user_email'] ?? null,
            'preferred_sweetness' => $row['preferred_sweetness'] !== null ? (int)$row['preferred_sweetness'] : null,
            'preferred_bitterness' => $row['preferred_bitterness'] !== null ? (int)$row['preferred_bitterness'] : null,
            'preferred_strength' => $row['preferred_strength'] !== null ? (int)$row['preferred_strength'] : null,
            'preferred_smokiness' => $row['preferred_smokiness'] !== null ? (int)$row['preferred_smokiness'] : null,
            'preferred_fruitiness' => $row['preferred_fruitiness'] !== null ? (int)$row['preferred_fruitiness'] : null,
            'preferred_spiciness' => $row['preferred_spiciness'] !== null ? (int)$row['preferred_spiciness'] : null,
            'favorite_categories' => $favoriteCategories,
            'favorite_category_names' => $favoriteCategoryNames,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null
        ];
    }
}
?>