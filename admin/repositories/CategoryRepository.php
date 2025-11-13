<?php 
require_once __DIR__.'/../../core/Database.php'; 
require_once __DIR__.'/../../core/Session.php';
require_once __DIR__.'/../models/Category.php';

class CategoryRepository{

    private ?PDO $pdo ;



    public function __construct(){

        $this->pdo = Database::getPdo();

    }


    
    /**
     * Get all users (for admin).
     *
     * @param int $limit
     * @param int $offset
     * @return Category[]
     */
    
    public function getAllCategories(int $limit, int $offset){

   

        $query =  "SELECT * FROM categories WHERE is_active = true ORDER BY created_at DESC LIMIT :limit OFFSET :offset ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

        $stmt->execute();

        $categories = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

            $categories[] = new Category(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['image_url'],
                $row['is_active'],
                $row['created_at'],
                $row['deleted_at'],
                $row['updated_at']
            );

        }
        return $categories;




    }















}
















?>