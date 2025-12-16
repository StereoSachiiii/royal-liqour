<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class CocktailRecipeValidator
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = "Name is required";
        }
        if (empty(trim($data['instructions'] ?? ''))) {
            $errors[] = "Instructions are required";
        }

        if (!empty($errors)) {
            throw new ValidationException("Validation failed", 400, $errors);
        }

        // Optional field checks (only if provided)
        self::validateOptionalFields($data, $errors);
    }

    public static function validateUpdate(array $data): void
    {
        $errors = [];

        // Only validate fields that are actually sent
        if (isset($data['name']) && trim($data['name']) === '') {
            $errors[] = "Name cannot be empty";
        }
        if (isset($data['instructions']) && trim($data['instructions']) === '') {
            $errors[] = "Instructions cannot be empty";
        }

        self::validateOptionalFields($data, $errors);

        if (!empty($errors)) {
            throw new ValidationException("Validation failed", 400, $errors);
        }
    }

    private static function validateOptionalFields(array $data, array &$errors): void
    {
        if (isset($data['difficulty']) && !in_array($data['difficulty'], ['easy', 'medium', 'hard'], true)) {
            $errors[] = "Difficulty must be easy, medium, or hard";
        }
        if (isset($data['preparation_time']) && (!is_numeric($data['preparation_time']) || $data['preparation_time'] < 0)) {
            $errors[] = "Preparation time must be a positive number";
        }
        if (isset($data['serves']) && (!is_numeric($data['serves']) || $data['serves'] < 1)) {
            $errors[] = "Serves must be at least 1";
        }
        if (isset($data['is_active']) && !is_bool($data['is_active']) && !in_array($data['is_active'], ['true', 'false', '1', '0', 1, 0, true, false], true)) {
            $errors[] = "is_active must be boolean";
        }
    }
}