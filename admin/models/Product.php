<?php 
class Product{
    private  $id;
    private  $name;
    private  $description;
    private  $price;
    private  $imageUrl;
    private  $categoryId;
    private  $supplierId;
    private  $isActive;
    private  $createdAt;
    private  $deletedAt;
    public function __construct(
         $id,
         $name,
         $description,
         $price,
         $imageUrl,
         $categoryId,
         $supplierId,
         $isActive,
         $createdAt,
         $deletedAt
    ){

        $this->id=$id;
        $this->name=$name;
        $this->description = $description;
        $this->price = $price;
        $this->imageUrl = $imageUrl;
        $this->categoryId = $categoryId;
        $this->supplierId = $supplierId;
        $this->isActive=$isActive;
        $this->createdAt = $createdAt;
        $this->deletedAt = $deletedAt;

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
    public function getDeletedAt(){
        return $this->deletedAt;
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
    public function setDeletedAt($deletedAt){
        $this->deletedAt=$deletedAt;
    }
    public function setCreatedAt($createdAt){
        $this->createdAt=$createdAt;
    }


}

?>