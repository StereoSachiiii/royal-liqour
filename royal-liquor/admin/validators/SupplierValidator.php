<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class SupplierValidator 
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['name']) || strlen(trim($data['name'])) < 2 || strlen($data['name']) > 100) {
            $errors['name'] = 'Name must be 2–100 characters';
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (isset($data['phone']) && $data['phone'] !== null && !preg_match('/^\+?[0-9]{8,15}$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone number';
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

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (isset($data['phone']) && $data['phone'] !== null && !preg_match('/^\+?[0-9]{8,15}$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone number';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }
}