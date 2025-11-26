<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class ProductValidator 
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['name']) || strlen(trim($data['name'])) < 2 || strlen($data['name']) > 200) {
            $errors['name'] = 'Name must be 2–200 characters';
        }

        if (isset($data['slug']) && (empty($data['slug']) || strlen($data['slug']) > 220)) {
            $errors['slug'] = 'Slug must be 1-220 characters';
        }

        if (empty($data['price_cents']) || !is_int($data['price_cents']) || $data['price_cents'] <= 0) {
            $errors['price_cents'] = 'Price cents must be positive integer';
        }

        if (isset($data['image_url']) && strlen($data['image_url']) > 500) {
            $errors['image_url'] = 'Image URL too long';
        }

        if (empty($data['category_id']) || !is_int($data['category_id'])) {
            $errors['category_id'] = 'Valid category ID required';
        }

        if (isset($data['supplier_id']) && !is_int($data['supplier_id'])) {
            $errors['supplier_id'] = 'Valid supplier ID if provided';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }

    public static function validateUpdate(array $data): void
    {
        $errors = [];

        if (isset($data['name']) && (strlen(trim($data['name'])) < 2 || strlen($data['name']) > 200)) {
            $errors['name'] = 'Name must be 2–200 characters';
        }

        if (isset($data['slug']) && (empty($data['slug']) || strlen($data['slug']) > 220)) {
            $errors['slug'] = 'Slug must be 1-220 characters';
        }

        if (isset($data['price_cents']) && (!is_int($data['price_cents']) || $data['price_cents'] <= 0)) {
            $errors['price_cents'] = 'Price cents must be positive integer';
        }

        if (isset($data['image_url']) && strlen($data['image_url']) > 500) {
            $errors['image_url'] = 'Image URL too long';
        }

        if (isset($data['category_id']) && !is_int($data['category_id'])) {
            $errors['category_id'] = 'Valid category ID';
        }

        if (isset($data['supplier_id']) && !is_int($data['supplier_id'])) {
            $errors['supplier_id'] = 'Valid supplier ID';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }
}