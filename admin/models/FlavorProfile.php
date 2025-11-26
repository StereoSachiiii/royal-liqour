<?php

class FlavorProfile {
    private ?int $id;
    private ?int $product_id;
    private ?int $sweetness;
    private ?int $bitterness;
    private ?int $strength;
    private ?int $smokiness;
    private ?int $fruitiness;
    private ?int $spiciness;
    private ?array $tags;
    private ?bool $is_active;
    private ?string $created_at;
    private ?string $updated_at;

    public function __construct(
        ?int $id,
        ?int $product_id,
        ?int $sweetness,
        ?int $bitterness,
        ?int $strength,
        ?int $smokiness,
        ?int $fruitiness,
        ?int $spiciness,
        ?array $tags,
        ?bool $is_active,
        ?string $created_at,
        ?string $updated_at
    ) {
        $this->id = $id;
        $this->product_id = $product_id;
        $this->sweetness = $sweetness;
        $this->bitterness = $bitterness;
        $this->strength = $strength;
        $this->smokiness = $smokiness;
        $this->fruitiness = $fruitiness;
        $this->spiciness = $spiciness;
        $this->tags = $tags;
        $this->is_active = $is_active;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getProductId(): ?int {
        return $this->product_id;
    }

    public function getSweetness(): ?int {
        return $this->sweetness;
    }

    public function getBitterness(): ?int {
        return $this->bitterness;
    }

    public function getStrength(): ?int {
        return $this->strength;
    }

    public function getSmokiness(): ?int {
        return $this->smokiness;
    }

    public function getFruitiness(): ?int {
        return $this->fruitiness;
    }

    public function getSpiciness(): ?int {
        return $this->spiciness;
    }

    public function getTags(): ?array {
        return $this->tags;
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

    // Setters
    public function setSweetness(int $sweetness): void {
        $this->sweetness = $sweetness;
    }

    public function setBitterness(int $bitterness): void {
        $this->bitterness = $bitterness;
    }

    public function setStrength(int $strength): void {
        $this->strength = $strength;
    }

    public function setSmokiness(int $smokiness): void {
        $this->smokiness = $smokiness;
    }

    public function setFruitiness(int $fruitiness): void {
        $this->fruitiness = $fruitiness;
    }

    public function setSpiciness(int $spiciness): void {
        $this->spiciness = $spiciness;
    }

    public function setTags(array $tags): void {
        $this->tags = $tags;
    }

    public function setIsActive(bool $is_active): void {
        $this->is_active = $is_active;
    }

    public function setUpdatedAt(string $updated_at): void {
        $this->updated_at = $updated_at;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sweetness' => $this->sweetness,
            'bitterness' => $this->bitterness,
            'strength' => $this->strength,
            'smokiness' => $this->smokiness,
            'fruitiness' => $this->fruitiness,
            'spiciness' => $this->spiciness,
            'tags' => $this->tags,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}

?>