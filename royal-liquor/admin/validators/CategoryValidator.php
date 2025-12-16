<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class CategoryValidator 
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['name']) || strlen(trim($data['name'])) < 2 || strlen($data['name']) > 100) {
            $errors['name'] = 'Name must be 2–100 characters';
        }

        if (isset($data['slug']) && (empty($data['slug']) || strlen($data['slug']) > 120)) {
            $errors['slug'] = 'Slug must be 1-120 characters';
        }

        if (isset($data['description']) && strlen($data['description']) > 1000) {
            $errors['description'] = 'Description too long';
        }

        if (isset($data['image_url']) && strlen($data['image_url']) > 500) {
            $errors['image_url'] = 'Image URL too long';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }

    public static function validateUpdate(array $data): void
    {
        $errors = [];

        if (isset($data['name']) && (strlen(trim($data['name'])) < 2 || strlen($data['name']) > 100)) {
            $errors['name'] = 'Name must be 2–100 characters';
        }

        if (isset($data['slug']) && (empty($data['slug']) || strlen($data['slug']) > 120)) {
            $errors['slug'] = 'Slug must be 1-120 characters';
        }

        if (isset($data['description']) && strlen($data['description']) > 1000) {
            $errors['description'] = 'Description too long';
        }

        if (isset($data['image_url']) && strlen($data['image_url']) > 500) {
            $errors['image_url'] = 'Image URL too long';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }
}