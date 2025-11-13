<?php 

class Category{

    private $id;
    private $name;
    private $description;
    private $imageUrl;
    private $isActive;
    private $createdAt;
    private $updatedAt;
    private $deletedAt;


    /**
     * Constructor to initialize a Category object.
     *
     * @param int         $id          Category ID (primary key, auto-incremented).
     * @param string      $name        Name of the category.
     * @param string      $description Description of the category.
     * @param string      $imageUrl    URL of the category image.
     * @param bool        $isActive    Whether the category is active.
     * @param string|null $createdAt   Timestamp when the category was created.
     * @param string|null $deletedAt   Timestamp when the category was deleted (nullable).
     * @param string|null $updatedAt   Timestamp when the category was last updated.
     */
    public function __construct(
        $id,
        $name,
        $description,
        $imageUrl,
        $isActive,
        $createdAt,
        $deletedAt,
        $updatedAt
        
        ){

        $this->id = $id;
        $this->name = $name;
        $this->description=$description;
        $this->imageUrl = $imageUrl;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
        $this->deletedAt = $deletedAt;
        $this->updatedAt = $updatedAt;

    }

    public function getId(){
        return $this->id;
    }

    public function getName(){
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }
    
    public function getImageUrl(){
        return $this->imageUrl;
    }

    public function getCreatedAt(){
        return $this->createdAt;
    }

    public function getDeletedAt(){
        return $this->deletedAt;
    }


    public function getUpdatedAt(){
        return $this->updatedAt;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function setDescription($description){
        $this->description = $description;
    }

    public function setImageUrl($imageUrl){
        $this->imageUrl = $imageUrl;
    }

    public function setCreatedAt($createdAt){
        $this->createdAt = $createdAt;
    }


    public function setDeletedAt($deletedAt){
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return array
     */

    public function toArray():array{
        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "image_url" => $this->imageUrl,
            "is_active" => $this->isActive,
            "created_at" => $this->createdAt,
            "updated_at" => $this->updatedAt,
            "deleted_at" => $this->deletedAt,

        ];
    }






























}




















?>