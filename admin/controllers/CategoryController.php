<?php  
require_once __DIR__.'/../../core/Database.php'; 
require_once __DIR__.'/../../core/Session.php';
require_once __DIR__.'/../models/Category.php';
require_once __DIR__.'/../repositories/CategoryRepository.php';



class CategoryController{

    private CategoryRepository $categoryRepository;
    
    public function __construct(){

        $this->categoryRepository = new CategoryRepository();

    } 

    /**
     * Get all users (for admin).
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */

public function getAllCategories(int $limit, int $offset){

    try{

        $categories = $this->categoryRepository->getAllCategories($limit, $offset);

        $CategoryData = array_map(function ($category){

            return [
                "id" => $category->getId(),
                "name" => $category->getName(),
                "description" => $category->getDescription(),
                "imageUrl" => $category->getImageUrl(),
                "createdAt" => $category->getCreatedAt(),
                "deletedAt" => $category->getDeletedAt(),
                "updatedAt" => $category->getUpdatedAt(),
            ];

        },$categories);

        return [ 'success' => true , 'categories' => $CategoryData];

    }catch(Exception $exception){

        return [ 'success' => false , 'message' => "failed fetching categories" . $exception->getMessage()];

    }
}

}



?>