<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class CartItemValidator 
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['cart_id']) || !is_int($data['cart_id'])) {
            $errors['cart_id'] = 'Valid cart ID required';
        }

        if (empty($data['product_id']) || !is_int($data['product_id'])) {
            $errors['product_id'] = 'Valid product ID required';
        }

        if (empty($data['quantity']) || !is_int($data['quantity']) || $data['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity must be positive integer';
        }

        if (empty($data['price_at_add_cents']) || !is_int($data['price_at_add_cents']) || $data['price_at_add_cents'] <= 0) {
            $errors['price_at_add_cents'] = 'Price at add must be positive integer';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }

    public static function validateUpdate(array $data): void
    {
        $errors = [];

        if (isset($data['quantity']) && (!is_int($data['quantity']) || $data['quantity'] <= 0)) {
            $errors['quantity'] = 'Quantity must be positive integer';
        }

        if (isset($data['price_at_add_cents']) && (!is_int($data['price_at_add_cents']) || $data['price_at_add_cents'] <= 0)) {
            $errors['price_at_add_cents'] = 'Price at add must be positive integer';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }
}