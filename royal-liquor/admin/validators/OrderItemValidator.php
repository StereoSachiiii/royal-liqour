<?php
declare(strict_types=1);

require_once __DIR__ . '/../exceptions/ValidationException.php';

class OrderItemValidator 
{
    public static function validateUpdate(array $data): void
    {
        $errors = [];
        
        // At least one field must be provided
        if (!isset($data['quantity']) && !isset($data['warehouse_id'])) {
            throw new ValidationException('At least one field (quantity or warehouse_id) must be provided');
        }
        
        // Validate quantity if provided
        if (isset($data['quantity'])) {
            if (!is_numeric($data['quantity'])) {
                $errors['quantity'] = 'Quantity must be a number';
            } elseif ((int)$data['quantity'] < 1) {
                $errors['quantity'] = 'Quantity must be at least 1';
            } elseif ((int)$data['quantity'] > 10000) {
                $errors['quantity'] = 'Quantity cannot exceed 10,000';
            }
        }
        
        // Validate warehouse_id if provided
        if (isset($data['warehouse_id'])) {
            // Allow null to unassign warehouse
            if ($data['warehouse_id'] !== null) {
                if (!is_numeric($data['warehouse_id'])) {
                    $errors['warehouse_id'] = 'Warehouse ID must be a number';
                } elseif ((int)$data['warehouse_id'] < 1) {
                    $errors['warehouse_id'] = 'Invalid warehouse ID';
                }
            }
        }
        
        // Reject any fields that aren't allowed
        $allowedFields = ['id', 'quantity', 'warehouse_id'];
        $extraFields = array_diff(array_keys($data), $allowedFields);
        if (!empty($extraFields)) {
            $errors['fields'] = 'Only quantity and warehouse_id can be updated. Invalid fields: ' . implode(', ', $extraFields);
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    public static function validateCreate(array $data): void
    {
        $errors = [];

        // Order ID validation
        if (!isset($data['order_id'])) {
            $errors['order_id'] = 'Order ID is required';
        } elseif (!is_numeric($data['order_id'])) {
            $errors['order_id'] = 'Order ID must be a number';
        } elseif ((int)$data['order_id'] < 1) {
            $errors['order_id'] = 'Invalid order ID';
        }

        // Product ID validation
        if (!isset($data['product_id'])) {
            $errors['product_id'] = 'Product ID is required';
        } elseif (!is_numeric($data['product_id'])) {
            $errors['product_id'] = 'Product ID must be a number';
        } elseif ((int)$data['product_id'] < 1) {
            $errors['product_id'] = 'Invalid product ID';
        }

        // Product name validation
        if (!isset($data['product_name'])) {
            $errors['product_name'] = 'Product name is required';
        } elseif (!is_string($data['product_name'])) {
            $errors['product_name'] = 'Product name must be a string';
        } elseif (trim($data['product_name']) === '') {
            $errors['product_name'] = 'Product name cannot be empty';
        } elseif (strlen($data['product_name']) > 200) {
            $errors['product_name'] = 'Product name cannot exceed 200 characters';
        }

        // Product image URL validation (optional)
        if (isset($data['product_image_url']) && $data['product_image_url'] !== null) {
            if (!is_string($data['product_image_url'])) {
                $errors['product_image_url'] = 'Image URL must be a string';
            } elseif (strlen($data['product_image_url']) > 500) {
                $errors['product_image_url'] = 'Image URL cannot exceed 500 characters';
            }
        }

        // Price validation
        if (!isset($data['price_cents'])) {
            $errors['price_cents'] = 'Price is required';
        } elseif (!is_numeric($data['price_cents'])) {
            $errors['price_cents'] = 'Price must be a number';
        } elseif ((int)$data['price_cents'] < 0) {
            $errors['price_cents'] = 'Price cannot be negative';
        } elseif ((int)$data['price_cents'] > 1000000000) {
            $errors['price_cents'] = 'Price exceeds maximum allowed value';
        }

        // Quantity validation
        if (!isset($data['quantity'])) {
            $errors['quantity'] = 'Quantity is required';
        } elseif (!is_numeric($data['quantity'])) {
            $errors['quantity'] = 'Quantity must be a number';
        } elseif ((int)$data['quantity'] < 1) {
            $errors['quantity'] = 'Quantity must be at least 1';
        } elseif ((int)$data['quantity'] > 10000) {
            $errors['quantity'] = 'Quantity cannot exceed 10,000';
        }

        // Warehouse ID validation (optional)
        if (isset($data['warehouse_id']) && $data['warehouse_id'] !== null) {
            if (!is_numeric($data['warehouse_id'])) {
                $errors['warehouse_id'] = 'Warehouse ID must be a number';
            } elseif ((int)$data['warehouse_id'] < 1) {
                $errors['warehouse_id'] = 'Invalid warehouse ID';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
}