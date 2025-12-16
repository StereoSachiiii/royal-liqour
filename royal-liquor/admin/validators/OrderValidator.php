<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class OrderValidator 
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['cart_id']) || !is_int($data['cart_id'])) {
            $errors['cart_id'] = 'Valid cart ID required';
        }

        if (empty($data['total_cents']) || !is_int($data['total_cents']) || $data['total_cents'] <= 0) {
            $errors['total_cents'] = 'Total cents must be positive integer';
        }

        if (isset($data['shipping_address_id']) && !is_int($data['shipping_address_id'])) {
            $errors['shipping_address_id'] = 'Valid shipping address ID';
        }

        if (isset($data['billing_address_id']) && !is_int($data['billing_address_id'])) {
            $errors['billing_address_id'] = 'Valid billing address ID';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }

    public static function validateUpdate(array $data): void
    {
        $errors = [];

        if (isset($data['status']) && !in_array($data['status'], ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'failed'])) {
            $errors['status'] = 'Invalid status';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }
}