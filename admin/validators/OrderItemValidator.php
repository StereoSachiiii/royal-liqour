<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class OrderItemValidator 
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['order_id']) || !is_int($data['order_id'])) {
            $errors['order_id'] = 'Valid order ID required';
        }

        if (empty($data['product_id']) || !is_int($data['product_id'])) {
            $errors['product_id'] = 'Valid product ID required';
        }

        if (empty($data['product_name']) || strlen($data['product_name']) > 200) {
            $errors['product_name'] = 'Product name required, max 200 characters';
        }

        if (isset($data['product_image_url']) && strlen($data['product_image_url']) > 500) {
            $errors['product_image_url'] = 'Image URL too long';
        }

        if (empty($data['price_cents']) || !is_int($data['price_cents']) || $data['price_cents'] <= 0) {
            $errors['price_cents'] = 'Price cents must be positive integer';
        }

        if (empty($data['quantity']) || !is_int($data['quantity']) || $data['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity must be positive integer';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }
}