<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/Validator.php';

class FeedbackValidator implements ValidatorInterface
{
    public static function validateCreate(array $data): void
    {
        ValidationRunner::run([
            [self::class, 'validateUserId'],
            [self::class, 'validateProductId'],
            [self::class, 'validateRatingRequired'],
            [self::class, 'validateComment'],
            [self::class, 'validateFlags'],
        ], $data, 'Feedback validation failed');
    }

    public static function validateUpdate(array $data): void
    {
        ValidationRunner::run([
            [self::class, 'validateRatingOptional'],
            [self::class, 'validateComment'],
            [self::class, 'validateFlags'],
        ], $data, 'Feedback validation failed');
    }

    public static function validateUserId(array $data): array
    {
        if (empty($data['user_id']) || !filter_var($data['user_id'], FILTER_VALIDATE_INT)) {
            return ['user_id' => 'Valid user_id is required'];
        }

        return [];
    }

    public static function validateProductId(array $data): array
    {
        if (empty($data['product_id']) || !filter_var($data['product_id'], FILTER_VALIDATE_INT)) {
            return ['product_id' => 'Valid product_id is required'];
        }

        return [];
    }

    public static function validateRatingRequired(array $data): array
    {
        if (!isset($data['rating'])) {
            return ['rating' => 'Rating is required'];
        }

        if (!filter_var($data['rating'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]])) {
            return ['rating' => 'Rating must be an integer between 1 and 5'];
        }

        return [];
    }

    public static function validateRatingOptional(array $data): array
    {
        if (!isset($data['rating'])) {
            return [];
        }

        if (!filter_var($data['rating'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]])) {
            return ['rating' => 'Rating must be an integer between 1 and 5'];
        }

        return [];
    }

    public static function validateComment(array $data): array
    {
        if (isset($data['comment']) && $data['comment'] !== null && strlen((string)$data['comment']) > 1000) {
            return ['comment' => 'Comment must be at most 1000 characters'];
        }

        return [];
    }

    public static function validateFlags(array $data): array
    {
        $errors = [];

        if (isset($data['is_verified_purchase'])
            && !is_bool($data['is_verified_purchase'])
            && !in_array($data['is_verified_purchase'], ['0', '1', 0, 1, 'true', 'false', true, false], true)
        ) {
            $errors['is_verified_purchase'] = 'Invalid is_verified_purchase flag';
        }

        if (isset($data['is_active'])
            && !is_bool($data['is_active'])
            && !in_array($data['is_active'], ['0', '1', 0, 1, 'true', 'false', true, false], true)
        ) {
            $errors['is_active'] = 'Invalid is_active flag';
        }

        return $errors;
    }
}
