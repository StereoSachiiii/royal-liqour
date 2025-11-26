<?php
declare(strict_types=1);

class StockModel
{
    public function __construct(
        private ?int $id = null,
        private ?int $product_id = null,
        private ?int $warehouse_id = null,
        private int $quantity = 0,
        private int $reserved = 0,
        private ?string $created_at = null,
        private ?string $updated_at = null
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getProductId(): ?int { return $this->product_id; }
    public function getWarehouseId(): ?int { return $this->warehouse_id; }
    public function getQuantity(): int { return $this->quantity; }
    public function getReserved(): int { return $this->reserved; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'quantity'     => $this->quantity,
            'reserved'     => $this->reserved,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}