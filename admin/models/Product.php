<?php 
class Product{
    private ?int $id;
    private ?string $name;
    private ?string $description;
    private int|float|null $price;
    private  ?string  $imageUrl;
    private  ?int $categoryId;
    private  ?int $supplierId;
   
    private  ?bool $isActive;
    private  ?string $createdAt;
    private  ?string $updatedAt;
    /**
     * @param ?int  $id
     * @param ?string  $name
     * @param ?string  $description
     * @param int|float|null $price
     * @param ?string $imageUrl
     * @param ?int $categoryId
     * @param ?int $supplierId
     * @param ?bool $isActive
     * @param ?string $createdAt
     * @param ?string $updatedAt
     */
  
    /**
     * Summary of __construct
     * @param mixed $id
     * @param mixed $name
     * @param mixed $description
     * @param float|null|int $price
     * @param mixed $imageUrl
     * @param mixed $categoryId
     * @param mixed $supplierId
     * @param mixed $isActive
     * @param mixed $createdAt
     * @param mixed $updatedAt
     */
    public function __construct(
        ?int $id,
        ?string $name,
        ?string $description,
        float|null|int $price,
        ?string $imageUrl,
        ?int $categoryId,
        ?int $supplierId,
        ?bool $isActive,     
        ?string $createdAt,
        ?string $updatedAt,
         
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

    /**
     * Summary of toArray
     * returns the object as an assosiative array
     * @return array{category_id: int|null, created_at: string|null, description: string|null, id: int|null, image_url: string|null, is_active: bool|null, name: string|null, price: float|int|null, supplier_id: int|null, updated_at: string|null}
     */
    public function toArray():array{
        $product = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'image_url' => $this->imageUrl,
            'category_id'  => $this->categoryId,
            'supplier_id'  => $this->supplierId,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
        return $product;
    }
}

?>