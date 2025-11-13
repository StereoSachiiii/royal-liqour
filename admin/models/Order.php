<?php
class Order {
    private ?int $id;
    private int $userId;
    private ?int $shippingAddressId;
    private ?int $billingAddressId;
    private string $status;
    private float $total;
    private bool $isActive;
    private bool $isAnonymized;
    private ?string $createdAt;
    private ?string $updatedAt;
    private ?string $deletedAt;
    private ?string $anonymizedAt;

        /**
     
     *
     * @param int|null    $id                Order ID (primary key, auto-incremented).
     * @param int         $userId            ID of the user who placed the order.
     * @param int|null    $shippingAddressId Shipping address ID (nullable).
     * @param int|null    $billingAddressId  Billing address ID (nullable).
     * @param string      $status            Current order status (e.g., 'pending', 'shipped').
     * @param float       $total             Total order amount.
     * @param bool        $isActive          Whether the order is active.
     * @param bool        $isAnonymized      Whether the order has been anonymized.
     * @param string|null $createdAt         Timestamp when the order was created.
     * @param string|null $updatedAt         Timestamp when the order was last updated.
     * @param string|null $deletedAt         Timestamp when the order was deleted (nullable).
     * @param string|null $anonymizedAt      Timestamp when the order was anonymized (nullable).
     */

    public function __construct(
        ?int $id = null,
        int $userId = 0,
        ?int $shippingAddressId = null,
        ?int $billingAddressId = null,
        string $status = 'pending',
        float $total = 0.0,
        bool $isActive = true,
        bool $isAnonymized = false,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $deletedAt = null,
        ?string $anonymizedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->shippingAddressId = $shippingAddressId;
        $this->billingAddressId = $billingAddressId;
        $this->status = $status;
        $this->total = $total;
        $this->isActive = $isActive;
        $this->isAnonymized = $isAnonymized;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
        $this->anonymizedAt = $anonymizedAt;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getShippingAddressId(): ?int { return $this->shippingAddressId; }
    public function getBillingAddressId(): ?int { return $this->billingAddressId; }
    public function getStatus(): string { return $this->status; }
    public function getTotal(): float { return $this->total; }
    public function isActive(): bool { return $this->isActive; }
    public function isAnonymized(): bool { return $this->isAnonymized; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getDeletedAt(): ?string { return $this->deletedAt; }
    public function getAnonymizedAt(): ?string { return $this->anonymizedAt; }
}
?>
