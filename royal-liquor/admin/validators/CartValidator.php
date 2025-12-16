<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class CartValidator 
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['session_id']) || strlen($data['session_id']) > 64) {
            $errors['session_id'] = 'Valid session ID required';
        }

        if (isset($data['user_id']) && !is_int($data['user_id'])) {
            $errors['user_id'] = 'Valid user ID if provided';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }

    public static function validateUpdate(array $data): void
    {
        $errors = [];

        if (isset($data['status']) && !in_array($data['status'], ['active', 'converted', 'abandoned', 'expired'])) {
            $errors['status'] = 'Invalid status';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }
}