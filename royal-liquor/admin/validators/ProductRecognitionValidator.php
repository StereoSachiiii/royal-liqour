<?php

require_once __DIR__ . '/../exceptions/ValidationException.php';

class ProductRecognitionValidator {
    
    private const VALID_API_PROVIDERS = ['google_vision', 'clarifai', 'imagga'];

    public static function validateCreate(array $data): void {
        $errors = [];

        if (empty($data['session_id'])) {
            $errors[] = "Session ID is required.";
        }

        if (empty($data['image_url'])) {
            $errors[] = "Image URL is required.";
        } elseif (!self::isValidImagePath($data['image_url'])) {
            $errors[] = "Image URL must be a valid URL or path.";
        }

        if (isset($data['confidence_score'])) {
            $score = (float)$data['confidence_score'];
            if (!is_numeric($data['confidence_score']) || $score < 0 || $score > 100) {
                $errors[] = "Confidence score must be between 0 and 100.";
            }
        }

        if (isset($data['api_provider']) && !empty($data['api_provider']) && !in_array($data['api_provider'], self::VALID_API_PROVIDERS)) {
            $errors[] = "API provider must be one of: " . implode(', ', self::VALID_API_PROVIDERS) . ".";
        }

        if (isset($data['recognized_labels']) && !is_array($data['recognized_labels'])) {
            $errors[] = "Recognized labels must be an array.";
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(" ", $errors));
        }
    }

    public static function validateUpdate(array $data): void {
        $errors = [];

        // All fields optional for update
        if (isset($data['image_url']) && !empty($data['image_url']) && !self::isValidImagePath($data['image_url'])) {
            $errors[] = "Image URL must be a valid URL or path.";
        }

        if (isset($data['confidence_score'])) {
            $score = (float)$data['confidence_score'];
            if (!is_numeric($data['confidence_score']) || $score < 0 || $score > 100) {
                $errors[] = "Confidence score must be between 0 and 100.";
            }
        }

        if (isset($data['api_provider']) && !empty($data['api_provider']) && !in_array($data['api_provider'], self::VALID_API_PROVIDERS)) {
            $errors[] = "API provider must be one of: " . implode(', ', self::VALID_API_PROVIDERS) . ".";
        }

        if (isset($data['recognized_labels']) && !is_array($data['recognized_labels'])) {
            $errors[] = "Recognized labels must be an array.";
        }

        if (!empty($errors)) {
            throw new ValidationException(implode(" ", $errors));
        }
    }

    /**
     * Check if path is a valid URL or relative path
     */
    private static function isValidImagePath(string $path): bool {
        // Allow full URLs
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return true;
        }
        // Allow relative paths starting with / or containing common image paths
        if (str_starts_with($path, '/') || str_contains($path, 'uploads/') || str_contains($path, 'images/')) {
            return true;
        }
        // Allow data URLs (base64)
        if (str_starts_with($path, 'data:image/')) {
            return true;
        }
        return false;
    }

    public static function paginationParams(?int $limit, ?int $offset): void {
        if ($limit === null || $offset === null) {
            throw new ValidationException("Both limit and offset are required for pagination.", code:400);
        }
        if (!is_int($limit) || $limit <= 0) {
            throw new ValidationException("Limit must be a positive integer.", code:400);
        }
        if (!is_int($offset) || $offset < 0) {
            throw new ValidationException("Offset must be a non-negative integer.", code:400);
        }
    }
}

?>