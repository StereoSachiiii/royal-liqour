<?php
declare(strict_types=1);

class ProductRecognitionModel
{
    public function __construct(
        private ?int $id = null,
        private ?string $session_id = null,
        private ?string $image_data = null,
        private ?float $confidence_score = null,
        private ?int $recognized_product_id = null,
        private ?float $processing_time = null,
        private ?string $status = null,
        private ?string $created_at = null
    ) {}

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    public function getImageData(): ?string
    {
        return $this->image_data;
    }

    public function getConfidenceScore(): ?float
    {
        return $this->confidence_score;
    }

    public function getRecognizedProductId(): ?int
    {
        return $this->recognized_product_id;
    }

    public function getProcessingTime(): ?float
    {
        return $this->processing_time;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    // Setters
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setSessionId(string $session_id): void
    {
        $this->session_id = $session_id;
    }

    public function setImageData(string $image_data): void
    {
        $this->image_data = $image_data;
    }

    public function setConfidenceScore(?float $confidence_score): void
    {
        $this->confidence_score = $confidence_score;
    }

    public function setRecognizedProductId(?int $recognized_product_id): void
    {
        $this->recognized_product_id = $recognized_product_id;
    }

    public function setProcessingTime(?float $processing_time): void
    {
        $this->processing_time = $processing_time;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    // toArray for JSON serialization
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'session_id' => $this->session_id,
            'image_data' => $this->image_data,
            'confidence_score' => $this->confidence_score,
            'recognized_product_id' => $this->recognized_product_id,
            'processing_time' => $this->processing_time,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }
}
