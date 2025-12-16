<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class StockValidator 
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['product_id']) || !is_int($data['product_id'])) {
            $errors['product_id'] = 'Valid product ID required';
        }

        if (empty($data['warehouse_id']) || !is_int($data['warehouse_id'])) {
            $errors['warehouse_id'] = 'Valid warehouse ID required';
        }

        if (isset($data['quantity']) && (!is_int($data['quantity']) || $data['quantity'] < 0)) {
            $errors['quantity'] = 'Quantity must be non-negative integer';
        }

        if (isset($data['reserved']) && (!is_int($data['reserved']) || $data['reserved'] < 0)) {
            $errors['reserved'] = 'Reserved must be non-negative integer';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }

    public static function validateUpdate(array $data): void
    {
        $errors = [];

        if (isset($data['quantity']) && (!is_int($data['quantity']) || $data['quantity'] < 0)) {
            $errors['quantity'] = 'Quantity must be non-negative integer';
        }

        if (isset($data['reserved']) && (!is_int($data['reserved']) || $data['reserved'] < 0)) {
            $errors['reserved'] = 'Reserved must be non-negative integer';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }
}