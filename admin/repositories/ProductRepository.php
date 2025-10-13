<?php
declare(strict_types=1); 
require_once __DIR__.'/../../core/Database.php';
require_once __DIR__.'/../models/ProductModel.php';



class ProductRepository{

    private ?PDO $pdo ;

    public function __construct() {
        $this->pdo = Database::getPdo();
    }

   public function getAllProducts(int $LIMIT = 50, int $OFFSET = 0): array {
    $products = [];

    try {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vw_active_products ORDER BY created_at DESC LIMIT :LIMIT OFFSET :OFFSET"
        );

        // Use bindValue for safety
        $stmt->bindValue(':LIMIT', $LIMIT, PDO::PARAM_INT);
        $stmt->bindValue(':OFFSET', $OFFSET, PDO::PARAM_INT);

        // Execute and check for errors
        if (!$stmt->execute()) {
            $error = $stmt->errorInfo();
            throw new Exception("SQL Error: " . $error[2]);
        }

        // Fetch as associative array
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Type casting to match strict types
            $products[] = new ProductModel(
                (int) $row['id'],
                $row['name'],
                $row['description'],
                (float) $row['price'],
                $row['image_url'],
                (int) $row['category_id'],
                $row['category_name'],
                (int) $row['supplier_id'],
                $row['supplier_name'],
                (bool) $row['is_active'],
                (int) $row['total_stock'],
                $row['created_at'],
                $row['updated_at']
            );
        }

    } catch (Exception $e) {
        // Debug output
        echo "<pre>Exception: " . $e->getMessage() . "</pre>";
    }

    return $products;
}


    public function getProductById(int $id): ?ProductModel {
        $stmt = $this->pdo->prepare("SELECT * FROM vw_active_products WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new ProductModel(
                (int) $row['id'],
                $row['name'],
                $row['description'],
                (float) $row['price'],
                $row['image_url'],
                (int) $row['category_id'],
                $row['category_name'],
                (int) $row['supplier_id'],
                $row['supplier_name'],
                (bool) $row['is_active'],
                (int) $row['total_stock'],
                $row['created_at'],
                $row['updated_at']
            );
        }
        return null;
    }

    public function createProduct(array $data): ?int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO products (name, description, price, image_url, category_id, supplier_id, is_active, total_stock) 
             VALUES (:name, :description, :price, :image_url, :category_id, :supplier_id, :is_active, :total_stock)"
        );
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(':image_url', $data['image_url'], PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':supplier_id', $data['supplier_id'], PDO::PARAM_INT);
        $stmt->bindValue(':is_active', $data['is_active'], PDO::PARAM_BOOL);
        $stmt->bindValue(':total_stock', $data['total_stock'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return (int)$this->pdo->lastInsertId();
        }
        return null;
    }



    public function updateProduct(int $id, array $data): bool {
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
                updated_at = NOW()
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
        return $stmt->execute();
    }

}












?>