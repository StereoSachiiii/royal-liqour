<?php 
class ProductModel{
    private  $id;
    private  $name;
    private  $description;
    private  $price;
    private  $imageUrl;
    private  $categoryId;
    private $categoryName;
    private  $supplierId;
    private $supplierName;  
    private $totalStock;
    private  $isActive;
    private  $createdAt;
    private  $updatedAt;
    public function __construct(
         $id,
         $name,
         $description,
         $price,
         $imageUrl,
         $categoryId,
         $categoryName,
         $supplierId,
         $supplierName,
         $isActive,
         $totalStock,
         $createdAt,
         $updatedAt
         
    ){

        $this->id=$id;
        $this->name=$name;
        $this->description = $description;
        $this->price = $price;
        $this->imageUrl = $imageUrl;
        $this->categoryId = $categoryId;
        $this->categoryName=$categoryName;
        $this->supplierId = $supplierId;
        $this->supplierName = $supplierName;
        $this->totalStock = $totalStock;
        $this->isActive=$isActive;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        

    }

    public function getId(){
        return $this->id;
    }

    public function getName(){
        return $this->name;
    }

    public function getDescription(){
        return $this->description;
    }
    public function getPrice(){
        return $this->price;
    }
    public function getImageUrl(){
        return $this->imageUrl;
    }
    public function getCategoryId(){
        return $this->categoryId;
    }   
    public function getSupplierId(){
        return $this->supplierId;
    }
    
    public function getIsActive(){
        return $this->isActive;
    }
    public function getCreatedAt(){
        return $this->createdAt;
    }
     
    public function setName($name){
        $this->name=$name;
    }
    public function setDescription($description){
        $this->description=$description;
    }
    public function setPrice($price){
        $this->price=$price;
    }
    public function setImageUrl($imageUrl){
        $this->imageUrl=$imageUrl;
    }
    public function setCategoryId($categoryId){
        $this->categoryId=$categoryId;
    }
    public function setSupplierId($supplierId){
        $this->supplierId=$supplierId;
    }
    public function setIsActive($isActive){
        $this->isActive=$isActive;
    }
   
    public function setCreatedAt($createdAt){
        $this->createdAt=$createdAt;
    }
    public function toArray():array{

        $product = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'image_url' => $this->imageUrl,
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'supplier_id' => $this->supplierId,
            'supplier_name' => $this->supplierName,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
        return $product;
        
        





    }



}

?>