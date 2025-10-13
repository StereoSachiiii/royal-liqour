<?php 
require_once __DIR__.'/../../core/Database.php';
require_once __DIR__.'/../models/Product.php';

declare(strict_types=1);

class ProductRepository{

    private ?PDO $pdo ;

    public function __construct() {
        $this->pdo = Database::getPdo();
    }

    public function getAllProducts(int $LIMIT = 50 , int $OFFSET= 0) : array {

        $stmt = $this->pdo->prepare("SELECT * from vw_active_products WHERE  DESC ORDER BY created_at LIMIT :LIMIT OFFSET:OFFSET");

        $result = $stmt->execute([
            ':LIMIT' => $LIMIT,
            ':OFFSET' => $OFFSET
        ]);

        $products = [];

        while($row=$stmt->fetch()){
            $products
        }
        
        
    }















}















?>