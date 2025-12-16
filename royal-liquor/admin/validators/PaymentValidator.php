<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class PaymentValidator 
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['order_id']) || !is_int($data['order_id'])) {
            $errors['order_id'] = 'Valid order ID required';
        }

        if (empty($data['amount_cents']) || !is_int($data['amount_cents']) || $data['amount_cents'] <= 0) {
            $errors['amount_cents'] = 'Amount cents must be positive integer';
        }

        if (isset($data['currency']) && strlen($data['currency']) !== 3) {
            $errors['currency'] = 'Currency must be 3-letter code';
        }

        if (empty($data['gateway']) || strlen($data['gateway']) > 50) {
            $errors['gateway'] = 'Gateway required, max 50 characters';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }

    public static function validateUpdate(array $data): void
    {
        $errors = [];

        if (isset($data['status']) && !in_array($data['status'], ['pending', 'captured', 'failed', 'refunded', 'voided'])) {
            $errors['status'] = 'Invalid status';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }
}