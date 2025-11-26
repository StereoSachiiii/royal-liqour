<?php

class Feedback {
    private ?int $id;
    private ?int $user_id;
    private ?int $product_id;
    private ?int $rating;
    private ?string $comment;
    private ?bool $is_verified_purchase;
    private ?bool $is_active;
    private ?string $created_at;
    private ?string $updated_at;
    private ?string $deleted_at;

    public function __construct(
        ?int $id,
        ?int $user_id,
        ?int $product_id,
        ?int $rating,
        ?string $comment,
        ?bool $is_verified_purchase,
        ?bool $is_active,
        ?string $created_at,
        ?string $updated_at,
        ?string $deleted_at
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->product_id = $product_id;
        $this->rating = $rating;
        $this->comment = $comment;
        $this->is_verified_purchase = $is_verified_purchase;
        $this->is_active = $is_active;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->deleted_at = $deleted_at;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getUserId(): ?int {
        return $this->user_id;
    }

    public function getProductId(): ?int {
        return $this->product_id;
    }

    public function getRating(): ?int {
        return $this->rating;
    }

    public function getComment(): ?string {
        return $this->comment;
    }

    public function getIsVerifiedPurchase(): ?bool {
        return $this->is_verified_purchase;
    }

    public function getIsActive(): ?bool {
        return $this->is_active;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?string {
        return $this->updated_at;
    }

    public function getDeletedAt(): ?string {
        return $this->deleted_at;
    }

    // Setters
    public function setRating(int $rating): void {
        $this->rating = $rating;
    }

    public function setComment(string $comment): void {
        $this->comment = $comment;
    }

    public function setIsActive(bool $is_active): void {
        $this->is_active = $is_active;
    }

    public function setUpdatedAt(string $updated_at): void {
        $this->updated_at = $updated_at;
    }

    public function setDeletedAt(string $deleted_at): void {
        $this->deleted_at = $deleted_at;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_verified_purchase' => $this->is_verified_purchase,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}

?>