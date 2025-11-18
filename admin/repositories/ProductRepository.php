<?php
declare(strict_types=1);
require_once __DIR__.'/../../core/Database.php';
require_once __DIR__.'/../models/Product.php';
require_once __DIR__.'/../exceptions/NotFoundException.php';
require_once __DIR__.'/../exceptions/DatabaseException.php';

class ProductRepository {

    private ?PDO $pdo;

    public function __construct(){
        $this->pdo = Database::getPdo();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Get all active products with pagination
     * 
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getAllProducts(int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get all products including inactive ones (admin view)
     * 
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getAllProductsIncludingInactive(int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get a single product by ID
     * 
     * @param int $id Product ID
     * @return Product
     * @throws NotFoundException If product not found
     * @throws PDOException If database query fails
     */
    public function getProductById(int $id): Product {
        $stmt = $this->pdo->prepare("SELECT * FROM vw_active_products WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException("Product with ID {$id} not found.");
        }

        return $this->mapRowToProduct($row);
    }

    /**
     * Get product by ID including inactive (admin)
     * 
     * @param int $id Product ID
     * @return Product
     * @throws NotFoundException If product not found
     * @throws PDOException If database query fails
     */
    public function getProductByIdAdmin(int $id): Product {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new NotFoundException("Product with ID {$id} not found.");
        }

        return $this->mapRowToProduct($row);
    }

    /**
     * Search products by name or description
     * 
     * @param string $query Search query
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function searchProducts(string $query, int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products 
             WHERE name ILIKE :query OR description ILIKE :query 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        
        $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Count search results
     * 
     * @param string $query Search query
     * @return int Count of matching products
     * @throws PDOException If database query fails
     */
    public function countSearchResults(string $query): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM vw_active_products 
             WHERE name ILIKE :query OR description ILIKE :query"
        );
        $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get products by category
     * 
     * @param int $categoryId Category ID
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getProductsByCategory(int $categoryId, int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products 
             WHERE category_id = :category_id 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get products by supplier
     * 
     * @param int $supplierId Supplier ID
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getProductsBySupplier(int $supplierId, int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products 
             WHERE supplier_id = :supplier_id 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        
        $stmt->bindValue(':supplier_id', $supplierId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get products within price range
     * 
     * @param float $minPrice Minimum price
     * @param float $maxPrice Maximum price
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getProductsByPriceRange(float $minPrice, float $maxPrice, int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products 
             WHERE price >= :min_price AND price <= :max_price 
             ORDER BY price ASC LIMIT :limit OFFSET :offset"
        );
        
        $stmt->bindValue(':min_price', $minPrice, PDO::PARAM_STR);
        $stmt->bindValue(':max_price', $maxPrice, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get products sorted by date (newest or oldest)
     * 
     * @param string $order 'DESC' for newest, 'ASC' for oldest
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getProductsByDate(string $order = 'DESC', int $limit = 50, int $offset = 0): array {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products 
             ORDER BY created_at {$order} LIMIT :limit OFFSET :offset"
        );
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get products sorted by price
     * 
     * @param string $order 'ASC' for lowest to highest, 'DESC' for highest to lowest
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getProductsByPrice(string $order = 'ASC', int $limit = 50, int $offset = 0): array {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products 
             ORDER BY price {$order} LIMIT :limit OFFSET :offset"
        );
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get products by multiple IDs (for cart/order operations)
     * 
     * @param array $ids Array of product IDs
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getProductsByIds(array $ids): array {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM vw_active_products WHERE id IN ({$placeholders})");
        
        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 1, (int)$id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get low stock products
     * 
     * @param int $threshold Stock threshold
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getLowStockProducts(int $threshold = 10, int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products 
             WHERE total_stock <= :threshold 
             ORDER BY total_stock ASC LIMIT :limit OFFSET :offset"
        );
        
        $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get out of stock products
     * 
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getOutOfStockProducts(int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products 
             WHERE total_stock = 0 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->mapRowsToProducts($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Get product count (all active products)
     * 
     * @return int Total count of active products
     * @throws PDOException If database query fails
     */
    public function countProducts(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM vw_active_products");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get total count of all products including inactive
     * 
     * @return int Total count of all products
     * @throws PDOException If database query fails
     */
    public function countAllProducts(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get product count by category
     * 
     * @param int $categoryId Category ID
     * @return int Count of products in category
     * @throws PDOException If database query fails
     */
    public function countProductsByCategory(int $categoryId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM vw_active_products WHERE category_id = :category_id");
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get product count by supplier
     * 
     * @param int $supplierId Supplier ID
     * @return int Count of products by supplier
     * @throws PDOException If database query fails
     */
    public function countProductsBySupplier(int $supplierId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM vw_active_products WHERE supplier_id = :supplier_id");
        $stmt->bindValue(':supplier_id', $supplierId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get low stock count
     * 
     * @param int $threshold Stock threshold
     * @return int Count of low stock products
     * @throws PDOException If database query fails
     */
    public function countLowStockProducts(int $threshold = 10): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM vw_active_products WHERE total_stock <= :threshold");
        $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get product stock by ID
     * 
     * @param int $id Product ID
     * @return int Stock quantity
     * @throws NotFoundException If product not found
     * @throws PDOException If database query fails
     */
    public function getProductStock(int $id): int {
        $stmt = $this->pdo->prepare("SELECT total_stock FROM products WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchColumn();

        if ($result === false) {
            throw new NotFoundException("Product with ID {$id} not found.");
        }

        return (int)$result;
    }

    /**
     * Check if product exists
     * 
     * @param int $id Product ID
     * @return bool True if product exists
     * @throws PDOException If database query fails
     */
    public function productExists(int $id): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM products WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Create a new product
     * 
     * @param array $data Product data
     * @return Product The created product
     * @throws DatabaseException If creation fails
     * @throws PDOException If database insert fails
     */
    public function createProduct(array $data): Product {
        $stmt = $this->pdo->prepare(
            "INSERT INTO products (name, description, price, image_url, category_id, supplier_id, is_active, total_stock) 
             VALUES (:name, :description, :price, :image_url, :category_id, :supplier_id, :is_active, :total_stock)
             RETURNING *"
        );
        
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(':image_url', $data['image_url'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':supplier_id', $data['supplier_id'], PDO::PARAM_INT);
        $stmt->bindValue(':is_active', $data['is_active'] ?? true, PDO::PARAM_BOOL);
        $stmt->bindValue(':total_stock', $data['total_stock'] ?? 0, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new DatabaseException("Failed to create product.");
        }

        return $this->mapRowToProduct($result);
    }

    /**
     * Update an existing product (all fields)
     * 
     * @param int $id Product ID
     * @param array $data Product data to update
     * @return Product Updated product
     * @throws DatabaseException If update fails
     * @throws NotFoundException If product not found
     * @throws PDOException If database update fails
     */
    public function updateProduct(int $id, array $data): Product {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET 
                name = :name, 
                description = :description, 
                price = :price, 
                image_url = :image_url, 
                category_id = :category_id, 
                supplier_id = :supplier_id, 
                is_active = :is_active, 
                total_stock = :total_stock,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(':image_url', $data['image_url'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':supplier_id', $data['supplier_id'], PDO::PARAM_INT);
        $stmt->bindValue(':is_active', $data['is_active'] ?? true, PDO::PARAM_BOOL);
        $stmt->bindValue(':total_stock', $data['total_stock'] ?? 0, PDO::PARAM_INT);
        
        if (!$stmt->execute() || $stmt->rowCount() === 0) {
            throw new DatabaseException("Failed to update product.");
        }

        return $this->getProductByIdAdmin($id);
    }

    /**
     * Partial update product (only provided fields)
     * 
     * @param int $id Product ID
     * @param array $data Partial product data
     * @return Product Updated product
     * @throws DatabaseException If update fails
     * @throws NotFoundException If product not found
     * @throws PDOException If database update fails
     */
    public function partialUpdateProduct(int $id, array $data): Product {
        $allowedFields = ['name', 'description', 'price', 'image_url', 'category_id', 'supplier_id', 'is_active', 'total_stock'];
        $updates = [];
        $bindings = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "{$field} = :{$field}";
                $bindings[$field] = $value;
            }
        }

        if (empty($updates)) {
            throw new DatabaseException("No valid fields provided for update.");
        }

        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        foreach ($bindings as $field => $value) {
            $stmt->bindValue(":{$field}", $value);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute() || $stmt->rowCount() === 0) {
            throw new DatabaseException("Failed to update product.");
        }

        return $this->getProductByIdAdmin($id);
    }

    /**
     * Update product stock
     * 
     * @param int $id Product ID
     * @param int $quantity New stock quantity
     * @return bool True if successful
     * @throws DatabaseException If update fails
     * @throws PDOException If database update fails
     */
    public function updateStock(int $id, int $quantity): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET total_stock = :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        
        if (!$stmt->execute() || $stmt->rowCount() === 0) {
            throw new DatabaseException("Failed to update stock.");
        }

        return true;
    }

    /**
     * Decrement product stock
     * 
     * @param int $id Product ID
     * @param int $quantity Quantity to decrement
     * @return bool True if successful
     * @throws DatabaseException If update fails or insufficient stock
     * @throws PDOException If database update fails
     */
    public function decrementStock(int $id, int $quantity): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET total_stock = total_stock - :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND total_stock >= :quantity"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        
        if (!$stmt->execute() || $stmt->rowCount() === 0) {
            throw new DatabaseException("Failed to decrement stock. Insufficient stock available.");
        }

        return true;
    }

    /**
     * Increment product stock
     * 
     * @param int $id Product ID
     * @param int $quantity Quantity to increment
     * @return bool True if successful
     * @throws DatabaseException If update fails
     * @throws PDOException If database update fails
     */
    public function incrementStock(int $id, int $quantity): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET total_stock = total_stock + :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        
        if (!$stmt->execute() || $stmt->rowCount() === 0) {
            throw new DatabaseException("Failed to increment stock.");
        }

        return true;
    }

    /**
     * Soft delete a product (sets is_active to false)
     * 
     * @param int $id Product ID
     * @return bool True if successful
     * @throws DatabaseException If delete fails
     * @throws PDOException If database update fails
     */
    public function deleteProduct(int $id): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET is_active = false, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if (!$stmt->execute() || $stmt->rowCount() === 0) {
            throw new DatabaseException("Failed to delete product.");
        }

        return true;
    }

    /**
     * Restore a soft-deleted product
     * 
     * @param int $id Product ID
     * @return bool True if successful
     * @throws DatabaseException If restore fails
     * @throws PDOException If database update fails
     */
    public function restoreProduct(int $id): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET is_active = true, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if (!$stmt->execute() || $stmt->rowCount() === 0) {
            throw new DatabaseException("Failed to restore product.");
        }

        return true;
    }

    /**
     * Permanently delete a product from database
     * 
     * @param int $id Product ID
     * @return bool True if successful
     * @throws DatabaseException If delete fails
     * @throws PDOException If database delete fails
     */
    public function hardDeleteProduct(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if (!$stmt->execute() || $stmt->rowCount() === 0) {
            throw new DatabaseException("Failed to permanently delete product.");
        }

        return true;
    }

    /**
     * Helper function to map row to Product object
     * 
     * @param array $row Database row
     * @return Product
     */
    private function mapRowToProduct(array $row): Product {
        return new Product(
            (int)$row['id'],
            (string)$row['name'],
            (string)$row['description'],
            (float)$row['price'],
            (string)$row['image_url'],
            (int)$row['category_id'],
            (int)$row['supplier_id'],
            (bool)$row['is_active'],
            (string)$row['created_at'],
            (string)$row['updated_at']
        );
    }

    /**
     * Helper function to map multiple rows to Product objects
     * 
     * @param array $rows Array of database rows
     * @return Product[]
     */
    private function mapRowsToProducts(array $rows): array {
        return array_map(fn(array $row) => $this->mapRowToProduct($row), $rows);
    }
}
?>