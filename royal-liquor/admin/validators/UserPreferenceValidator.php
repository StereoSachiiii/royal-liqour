<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/Validator.php';

class UserPreferenceValidator implements ValidatorInterface
{
    public static function validateCreate(array $data): void
    {
        ValidationRunner::run([
            [self::class, 'validateUserIdRequired'],
            [self::class, 'validateFlavorProfileOptional'],
            [self::class, 'validateFavoriteCategoriesOptional'],
        ], $data, 'User preference validation failed');
    }

    public static function validateUpdate(array $data): void
    {
        ValidationRunner::run([
            [self::class, 'validateFlavorProfileOptional'],
            [self::class, 'validateFavoriteCategoriesOptional'],
        ], $data, 'User preference validation failed');
    }

    public static function validateUserIdRequired(array $data): array
    {
        if (empty($data['user_id']) || !filter_var($data['user_id'], FILTER_VALIDATE_INT)) {
            return ['user_id' => 'Valid user_id is required'];
        }
        return [];
    }

    public static function validateFlavorProfileOptional(array $data): array
    {
        $errors = [];
        $flavorFields = [
            'preferred_sweetness',
            'preferred_bitterness',
            'preferred_strength',
            'preferred_smokiness',
            'preferred_fruitiness',
            'preferred_spiciness'
        ];

        foreach ($flavorFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $value = $data[$field];
                if (!is_numeric($value) || $value < 0 || $value > 10) {
                    $errors[$field] = ucfirst(str_replace('preferred_', '', $field)) . ' must be between 0 and 10';
                }
            }
        }

        return $errors;
    }

    public static function validateFavoriteCategoriesOptional(array $data): array
    {
        if (!array_key_exists('favorite_categories', $data) || $data['favorite_categories'] === null) {
            return [];
        }

        $cats = $data['favorite_categories'];

        if (!is_array($cats)) {
            return ['favorite_categories' => 'Favorite categories must be an array'];
        }

        foreach ($cats as $catId) {
            if (!is_int($catId) && !ctype_digit((string)$catId)) {
                return ['favorite_categories' => 'All category IDs must be integers'];
            }
        }

        return [];
    }
}

