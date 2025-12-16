<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class FlavorProfileValidator
{
    private const ALLOWED_RANGE = ['min' => 0, 'max' => 10];
    private const FLAVOR_FIELDS = [
        'sweetness', 'bitterness', 'strength', 
        'smokiness', 'fruitiness', 'spiciness'
    ];

    public static function validateCreate(array $data): void
    {
        $errors = [];

        // 1. Product ID is mandatory for creation
        if (!isset($data['product_id']) || !is_int($data['product_id']) || $data['product_id'] <= 0) {
            $errors['product_id'] = 'Valid Product ID is required';
        }

        // 2. Validate Flavor Metrics (0-10)
        foreach (self::FLAVOR_FIELDS as $field) {
            if (isset($data[$field])) {
                if (!is_int($data[$field])) {
                    $errors[$field] = ucfirst($field) . ' must be an integer';
                } elseif ($data[$field] < self::ALLOWED_RANGE['min'] || $data[$field] > self::ALLOWED_RANGE['max']) {
                    $errors[$field] = ucfirst($field) . ' must be between 0 and 10';
                }
            }
        }

        // 3. Validate Tags (Array of strings)
        if (isset($data['tags'])) {
            if (!is_array($data['tags'])) {
                $errors['tags'] = 'Tags must be an array of strings';
            } else {
                foreach ($data['tags'] as $tag) {
                    if (!is_string($tag)) {
                        $errors['tags'] = 'All tags must be strings';
                        break;
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    public static function validateUpdate(array $data): void
    {
        $errors = [];

        // 1. Validate Flavor Metrics if present
        foreach (self::FLAVOR_FIELDS as $field) {
            if (isset($data[$field])) {
                if (!is_int($data[$field])) {
                    $errors[$field] = ucfirst($field) . ' must be an integer';
                } elseif ($data[$field] < self::ALLOWED_RANGE['min'] || $data[$field] > self::ALLOWED_RANGE['max']) {
                    $errors[$field] = ucfirst($field) . ' must be between 0 and 10';
                }
            }
        }

        // 2. Validate Tags if present
        if (isset($data['tags'])) {
            if (!is_array($data['tags'])) {
                $errors['tags'] = 'Tags must be an array of strings';
            } else {
                foreach ($data['tags'] as $tag) {
                    if (!is_string($tag)) {
                        $errors['tags'] = 'All tags must be strings';
                        break;
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
}