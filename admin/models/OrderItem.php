<?php 
class OrderItem{
    private ?int  $id;
    private ?int $orderId;
    private ?int $productId ;

    private ?int $quantity;
    private null|float|int $price;//check later
    private ?bool $isActive;
    private ?string $createdAt;
    private ?string $updatedAt;


    /**
     * @param null|int $id 
     * @param null|int $orderId
     * @param null|int $productId
     * @param null|int $quantity
     * @param null|float|int $price
     * @param null|bool $isActive
     * @param null|string $createdAt
     * @param null|string $updatedAt
     * 
     */

public function __construct(
    $id = null,
    $orderId = null,
    $productId = null, 
    $quantity = null,
    $price = null,
    $isActive = null,
    $createdAt = null,
    $updatedAt = null
)
{
    $this->id = $id;
    $this->orderId = $orderId;
    $this->productId = $productId ;
    $this->quantity = $quantity;
    $this->price =  $price ;
    $this->isActive = $isActive;
    $this->createdAt = $createdAt;
    $this->updatedAt = $updatedAt;
}
    public function getId() : int {
        return $this->id;
    }

    public function getOrderId() : int {
        return $this->orderId;
    }

    public function getProductId():int{
        return $this->productId;
    }

    public function getQuantity():int|float{
        return $this->quantity;
    }

    public function getPrice():int{
        return $this->price;
    }

    public function getIsActive():bool{
        return $this->isActive;
    }

    public function getCreatedAt():string{
        return $this->createdAt;
    }

    public function getUpdatedAt():string{
        return $this->updatedAt;
    }

















}











?>