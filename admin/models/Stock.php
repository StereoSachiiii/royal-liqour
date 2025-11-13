<?php
class StockModel{


    private ?int $id;
    private ?int $productId;
    private ?int $warehouseId;
    private null|int|float $quantity;
    private ?bool $isActive;
    private ?string $createdAt;
    private ?string $updatedAt;

    /**
     * @param int|null $id
     * @param int|null $productId
     * @param int|null $warehouseId
     * @param int|null|float $quantity
     * @param bool|null $isActive
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        $id = null,
        $productId = null,
        $warehouseId = null,
        $quantity = null,
        $isActive = null,
        $createdAt = null,
        $updatedAt = null
    ){
        $this->id = $id;
        $this->productId = $productId;
        $this->warehouseId = $warehouseId;
        $this->quantity = $quantity;
        $this->isActive =$isActive;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId():int|null{
        return $this->id;
    }

    public function getProductId():int|null{
        return $this->productId;
    }

    public function getWarehouseId():int|null{
        return $this->warehouseId;
    }

    public function getQuantity():int|null{
        return $this->quantity;
    }

    public function getIsActive():bool|null{
        return $this->isActive;
    }
    public function getCreatedAt():string|null{
        return $this->createdAt;
    }

    public function getUpdatedAt():string|null{
        return $this->updatedAt;
    }

    public function setId(int|null $id):void{
        $this->id = $id;
    }

    public function setProductId(int|null $productId):void{
        $this->productId = $productId;
    }

    public function setWarehouseId(int|null $warehouseId):void{
        $this->warehouseId  = $warehouseId;
    }

    public function setQuantity(int|null $quantity):void{
        $this->quantity = $quantity;
    }

    public function setIsActive(bool|null $isActive):void{
        $this->isActive = $isActive;
    }

    public function setCreatedAt(string|null $createdAt):void{
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(string|null $updatedAt):void{
        $this->updatedAt =  $updatedAt;
    }

    public function toArray():array{
        return [
            "id" => $this->id,
            "product_id" => $this->productId,
            "warehouse_id" => $this->warehouseId,
            "quantity" => $this->quantity,
            "is_active" => $this->isActive,
            "created_at" => $this->createdAt,
            "updated_at" => $this->updatedAt
        ];
    }





}


?>