<?php 
require_once __DIR__.'/../../core/Database.php'; 
require_once __DIR__.'/../../core/Session.php';
require_once __DIR__.'/../models/Category.php';
require_once __DIR__.'/../exceptions/DatabaseException.php';
require_once __DIR__.'/../exceptions/NotFoundException.php';
require_once __DIR__.'/../exceptions/ValidationException.php';

class CategoryRepository {

    private ?PDO $pdo;
    
    public function __construct() {
        $this->pdo = Database::getPdo();
    }

    /**
     * Map database row to Category object
     * @param array $category
     * @return Category
     */
    private function mapRowToCategory(array $category): Category {
        return new Category(
            $category['id'],
            $category['name'],
            $category['description'],
            $category['image_url'],
            $category['is_active'],
            $category['created_at'],
            $category['deleted_at'],
            $category['updated_at']
        );
    }

    /**
     * Map multiple rows to Category objects
     * @param array $categories
     * @return Category[]
     */
    private function mapRowsToCategories(array $categories): array {
        return array_map(fn($category) => $this->mapRowToCategory($category), $categories);
    }

    /**
     * Get all categories with pagination
     * @param int $limit
     * @param int $offset
     * @param bool $includeInactive Include inactive categories (admin view)
     * @return Category[]
     */
    public function getAllCategories(int $limit = 50, int $offset = 0, bool $includeInactive = false): array {
        $query = "SELECT * FROM categories";
        
        if (!$includeInactive) {
            $query .= " WHERE is_active = true AND deleted_at IS NULL";
        } else {
            $query .= " WHERE deleted_at IS NULL";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToCategories($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get category by ID
     * @param int $id
     * @return Category|null
     */
    public function getCategoryById(int $id): ?Category {
        $query = "SELECT * FROM categories WHERE id = :id AND deleted_at IS NULL";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $this->mapRowToCategory($row) : null;
    }

    /**
     * Get category by ID
     * @param int $id
     * @return Category|null
     */
    public function getCategoryByIdAdmin(int $id): ?Category {
        $query = "SELECT * FROM categories WHERE id = :id ";   
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $this->mapRowToCategory($row) : null;
    }

    /**
     * Get category by name
     * @param string $name
     * @return Category|null
     */
    public function getCategoryByName(string $name): ?Category {
        $query = "SELECT * FROM categories WHERE name = :name AND deleted_at IS NULL";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $this->mapRowToCategory($row) : null;
    }

    /**
     * Get only active categories
     * @param int $limit
     * @param int $offset
     * @return Category[]
     */
    public function getActiveCategories(int $limit = 50, int $offset = 0): array {
        $query = "SELECT * FROM categories 
                  WHERE is_active = true AND deleted_at IS NULL 
                  ORDER BY name ASC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$result){
            throw new DatabaseException("Failed to fetch active categories");
        }

        return $this->mapRowsToCategories($result);
    }

    /**
     * Search categories by name or description
     * @param string $searchTerm
     * @param int $limit
     * @param int $offset
     * @return Category[]
     */
    public function searchCategories(string $searchTerm, int $limit = 50, int $offset = 0): array {
        $query = "SELECT * FROM categories 
                  WHERE (name ILIKE :search OR description ILIKE :search) 
                  AND is_active = true 
                  AND deleted_at IS NULL 
                  ORDER BY name ASC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($query);
        $searchParam = "%{$searchTerm}%";
        $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToCategories($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Create a new category
     * @param array $category
     * @return Category
     * @throws DatabaseException
     */
    public function createCategory(array $category): Category {
        try {
            $query = "INSERT INTO categories (name, description, image_url, is_active)
                      VALUES (:name, :description, :image_url, :is_active)
                      RETURNING *";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':name', $category['name'], PDO::PARAM_STR);
            $stmt->bindValue(':description', $category['description'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':image_url', $category['image_url'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':is_active', $category['is_active'] ?? true, PDO::PARAM_BOOL);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new DatabaseException("Failed to create category");
            }
            
            return $this->mapRowToCategory($row);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage());
        }
    }

    /**
     * Update an existing category
     * @param int $id
     * @param array $data
     * @return Category
     * @throws NotFoundException
     * @throws DatabaseException
     */
    public function updateCategory(array $data): Category {
        // Check if category exists
        $id = $data['id'];
        $existing = $this->getCategoryById($data['id']);
        if (!$existing) {
            throw new NotFoundException("Category with ID {$id} not found");
        }

        try {
            $updateFields = [];
            $params = [':id' => $data['id']];

            if (isset($data['name'])) {
                $updateFields[] = "name = :name";
                $params[':name'] = $data['name'];
            }

            if (isset($data['description'])) {
                $updateFields[] = "description = :description";
                $params[':description'] = $data['description'];
            }

            if (isset($data['image_url'])) {
                $updateFields[] = "image_url = :image_url";
                $params[':image_url'] = $data['image_url'];
            }

            if (isset($data['is_active'])) {
                $updateFields[] = "is_active = :is_active";
                $params[':is_active'] = $data['is_active'];
            }

            if (empty($updateFields)) {
                return $existing; // Nothing to update
            }

            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
            
            $query = "UPDATE categories 
                      SET " . implode(', ', $updateFields) . " 
                      WHERE id = :id AND deleted_at IS NULL 
                      RETURNING *";

            $stmt = $this->pdo->prepare($query);
            
            foreach ($params as $key => $value) {
                $type = is_bool($value) ? PDO::PARAM_BOOL : 
                        (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
                $stmt->bindValue($key, $value, $type);
            }
            
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new DatabaseException("Failed to update category");
            }

            return $this->mapRowToCategory($row);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage());
        }
    }

    /**
     * Update category status (active/inactive)
     * @param int $id
     * @param bool $isActive
     * @return Category
     * @throws NotFoundException
     * @throws DatabaseException
     */
    public function updateCategoryStatus(int $id, bool $isActive): Category {
        $existing = $this->getCategoryById($id);
        if (!$existing) {
            throw new NotFoundException("Category with ID {$id} not found");
        }

        try {
            $query = "UPDATE categories 
                      SET is_active = :is_active, updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id AND deleted_at IS NULL 
                      RETURNING *";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':is_active', $isActive, PDO::PARAM_BOOL);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new DatabaseException("Failed to update category status");
            }

            return $this->mapRowToCategory($row);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage());
        }
    }

    /**
     * Soft delete a category
     * @param int $id
     * @return bool
     * @throws NotFoundException
     * @throws DatabaseException
     */
    public function softDeleteCategory(int $id): bool {
    $existing = $this->getCategoryByIdAdmin($id);
    if (!$existing) {
        throw new NotFoundException("Category with ID {$id} not found");
    }

    $query = "UPDATE categories
              SET deleted_at = CURRENT_TIMESTAMP,
                  updated_at = CURRENT_TIMESTAMP,
                  is_active = false
              WHERE id = :id AND deleted_at IS NULL";

    $stmt = $this->pdo->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}


    /**
     * Hard delete a category (permanent)
     * @param int $id
     * @return bool
     * @throws NotFoundException
     * @throws DatabaseException
     */
   public function deleteCategory(int $id): bool {
    $existing = $this->getCategoryById($id);
    if (!$existing) {
        throw new NotFoundException("Category with ID {$id} not found");
    }

    $query = "DELETE FROM categories WHERE id = :id";

    $stmt = $this->pdo->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}

    /**
     * Restore a soft-deleted category
     * @param int $id
     * @return Category
     * @throws NotFoundException
     * @throws DatabaseException
     */
    public function restoreCategory(int $id): Category {
    $query = "UPDATE categories
              SET deleted_at = NULL,
                  updated_at = CURRENT_TIMESTAMP,
                  is_active = true
              WHERE id = :id AND deleted_at IS NOT NULL
              RETURNING *";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new NotFoundException("Category with ID {$id} not found or not deleted");
    }

    return $this->mapRowToCategory($row);
}

    /**
     * Bulk delete categories
     * @param array $ids
     * @return int Number of deleted categories
     * @throws DatabaseException
     */
    public function bulkDeleteCategories(array $ids): int {
    if (empty($ids)) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $query = "UPDATE categories
              SET deleted_at = CURRENT_TIMESTAMP,
                  updated_at = CURRENT_TIMESTAMP,
                  is_active = false
              WHERE id IN ({$placeholders}) AND deleted_at IS NULL";

    $stmt = $this->pdo->prepare($query);
    $stmt->execute($ids);

    return $stmt->rowCount();
}

    /**
     * Get total count of categories
     * @param bool $activeOnly
     * @return int
     */
   public function getCategoryCount(bool $activeOnly = false): int {
    $query = "SELECT COUNT(*) AS count FROM categories WHERE deleted_at IS NULL";

    if ($activeOnly) {
        $query .= " AND is_active = true";
    }

    $stmt = $this->pdo->prepare($query);
    $stmt->execute();

    $result = $stmt->fetchColumn();
    return (int)$result;
}

    /**
     * Check if category name exists (for validation)
     * @param string $name
     * @param int|null $excludeId ID to exclude from check (for updates)
     * @return bool
     */
    public function categoryNameExists(string $name, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) as count FROM categories WHERE name = :name AND deleted_at IS NULL";
        
        if ($excludeId !== null) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        
        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)($result['count'] ?? 0) > 0;
    }

    /**
     * Get categories with product count
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getCategoriesWithProductCount(int $limit = 50, int $offset = 0): array {
        $query = "SELECT c.*, 
                  COALESCE(COUNT(p.id), 0) as product_count
                  FROM categories c
                  LEFT JOIN products p ON c.id = p.category_id AND p.deleted_at IS NULL
                  WHERE c.deleted_at IS NULL AND c.is_active = true
                  GROUP BY c.id
                  ORDER BY c.name ASC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>