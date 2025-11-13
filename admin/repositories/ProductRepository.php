<?php
declare(strict_types=1);
require_once __DIR__.'/../../core/Database.php';
require_once __DIR__.'/../models/Product.php';

class ProductRepository {

    private ?PDO $pdo;

    public function __construct(){
        $this->pdo = Database::getPdo();
        // Ensure PDO throws exceptions
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Get all active products with pagination
     * 
     * @param int $LIMIT Maximum number of products to return
     * @param int $OFFSET Number of products to skip
     * @return Product[]
     * @throws PDOException If database query fails
     */
    public function getAllProducts(int $LIMIT = 50, int $OFFSET = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products ORDER BY created_at DESC LIMIT :LIMIT OFFSET :OFFSET"
        );
        
        $stmt->bindValue(':LIMIT', $LIMIT, PDO::PARAM_INT);
        $stmt->bindValue(':OFFSET', $OFFSET, PDO::PARAM_INT);
        $stmt->execute();

        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = new Product(
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

        return $products;
    }

    /**
     * Get a single product by ID
     * 
     * @param int $id Product ID
     * @return Product Returns null if product not found
     * @throws PDOException If database query fails
     */
    public function getProductById(int $id): Product {
        $stmt = $this->pdo->prepare("SELECT * FROM vw_active_products WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if( !$row ) {
            throw new NotFoundException("Product with ID  not found.");  
        }
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
     * Create a new product
     * 
     * @param array{name: string, description: string, price: float, image_url: string, category_id: int, supplier_id: int, is_active: bool, total_stock: int} $data Product data
     * @return Product The ID of the created product
     * @throws PDOException If database insert fails
     */
    public function createProduct(array $data): Product {
        $stmt = $this->pdo->prepare(
            "INSERT INTO products (name, description, price, image_url, category_id, supplier_id, is_active, total_stock) 
             VALUES (:name, :description, :price, :image_url, :category_id, :supplier_id, :is_active, :total_stock)
             RETURNING *"
        );
        
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(':image_url', $data['image_url'], PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':supplier_id', $data['supplier_id'], PDO::PARAM_INT);
        $stmt->bindValue(':is_active', $data['is_active'], PDO::PARAM_BOOL);
        $stmt->bindValue(':total_stock', $data['total_stock'], PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if( !$result ) {
            throw new DatabaseException("Failed to create product.");
        }

        return new Product(
            (int)$result['id'],
            (string)$result['name'],
            (string)$result['description'],
            (float)$result['price'],
            (string)$result['image_url'],
            (int)$result['category_id'],
            (int)$result['supplier_id'],
            (bool)$result['is_active'],
            (string)$result['created_at'],
            (string)$result['updated_at']

        );
    }

    /**
     * Update an existing product
     * 
     * @param int $id Product ID
     * @param array{name: string, description: string, price: float, image_url: string, category_id: int, supplier_id: int, is_active: bool, total_stock: int} $data Product data to update
     * @return Product True if update was successful
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
        $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(':image_url', $data['image_url'], PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':supplier_id', $data['supplier_id'], PDO::PARAM_INT);
        $stmt->bindValue(':is_active', $data['is_active'], PDO::PARAM_BOOL);
        $stmt->bindValue(':total_stock', $data['total_stock'], PDO::PARAM_INT);
        
       
        if(!$stmt->execute()){
            throw new DatabaseException("Failed to update product.");
        }
        return $this->getProductById($id);

        
    }

    /**
     * Soft delete a product (sets is_active to false)
     * 
     * @param int $id Product ID
     * @return bool True if delete was successful
     * @throws PDOException If database update fails
     */
    public function deleteProduct(int $id): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET is_active = false, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Permanently delete a product from database
     * 
     * @param int $id Product ID
     * @return bool True if delete was successful
     * @throws PDOException If database delete fails
     */
    public function hardDeleteProduct(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>