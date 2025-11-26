<?php

class ProductRecognition {
    private ?int $id;
    private ?int $user_id;
    private ?string $session_id;
    private ?string $image_url;
    private ?string $recognized_text;
    private ?array $recognized_labels;
    private ?int $matched_product_id;
    private ?float $confidence_score;
    private ?string $api_provider;
    private ?string $created_at;

    public function __construct(
        ?int $id,
        ?int $user_id,
        ?string $session_id,
        ?string $image_url,
        ?string $recognized_text,
        ?array $recognized_labels,
        ?int $matched_product_id,
        ?float $confidence_score,
        ?string $api_provider,
        ?string $created_at
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->session_id = $session_id;
        $this->image_url = $image_url;
        $this->recognized_text = $recognized_text;
        $this->recognized_labels = $recognized_labels;
        $this->matched_product_id = $matched_product_id;
        $this->confidence_score = $confidence_score;
        $this->api_provider = $api_provider;
        $this->created_at = $created_at;
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getUserId(): ?int {
        return $this->user_id;
    }

    public function getSessionId(): ?string {
        return $this->session_id;
    }

    public function getImageUrl(): ?string {
        return $this->image_url;
    }

    public function getRecognizedText(): ?string {
        return $this->recognized_text;
    }

    public function getRecognizedLabels(): ?array {
        return $this->recognized_labels;
    }

    public function getMatchedProductId(): ?int {
        return $this->matched_product_id;
    }

    public function getConfidenceScore(): ?float {
        return $this->confidence_score;
    }

    public function getApiProvider(): ?string {
        return $this->api_provider;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    // Setters
    public function setRecognizedText(string $recognized_text): void {
        $this->recognized_text = $recognized_text;
    }

    public function setRecognizedLabels(array $recognized_labels): void {
        $this->recognized_labels = $recognized_labels;
    }

    public function setMatchedProductId(int $matched_product_id): void {
        $this->matched_product_id = $matched_product_id;
    }

    public function setConfidenceScore(float $confidence_score): void {
        $this->confidence_score = $confidence_score;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'session_id' => $this->session_id,
            'image_url' => $this->image_url,
            'recognized_text' => $this->recognized_text,
            'recognized_labels' => $this->recognized_labels,
            'matched_product_id' => $this->matched_product_id,
            'confidence_score' => $this->confidence_score,
            'api_provider' => $this->api_provider,
            'created_at' => $this->created_at
        ];
    }
}

?>