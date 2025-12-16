<?php
declare(strict_types=1);


require_once __DIR__ . '/../exceptions/ValidationException.php';

class UserValidator 
{
    public static function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['name']) || strlen(trim($data['name'])) < 2 || strlen($data['name']) > 100) {
            $errors['name'] = 'Name must be 2–100 characters';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }

        if (!empty($data['phone']) && !preg_match('/^\+?[0-9]{8,15}$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone number';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $data['password'] ?? '')) {
            $errors['password'] = 'Password must contain uppercase, lowercase, number & special char';
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

        if (isset($data['password']) && strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if (isset($data['profileImageUrl']) && strlen($data['profileImageUrl']) > 500) {
            $errors['profileImageUrl'] = 'Image URL too long';
        }

        if ($errors) {
            throw new ValidationException('Validation failed', ['errors' => $errors]);
        }
    }

    public static function loginValidate(array $data): void
    {
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Valid email is required');
        }
        if (empty($data['password'])) {
            throw new ValidationException('Password is required');
        }
    }

    public static function validateCreateAddress(array $data): void
    {
        $errors = [];

        if (empty($data['address_line1'])) {
            $errors['address_line1'] = 'Address line 1 is required';
        } elseif (strlen($data['address_line1']) > 255) {
            $errors['address_line1'] = 'Address line 1 must be at most 255 characters';
        }

        if (isset($data['address_line2']) && strlen($data['address_line2']) > 255) {
            $errors['address_line2'] = 'Address line 2 must be at most 255 characters';
        }

        if (empty($data['city'])) {
            $errors['city'] = 'City is required';
        } elseif (strlen($data['city']) > 100) {
            $errors['city'] = 'City must be at most 100 characters';
        }

        if (isset($data['state']) && strlen($data['state']) > 100) {
            $errors['state'] = 'State must be at most 100 characters';
        }

        if (empty($data['postal_code'])) {
            $errors['postal_code'] = 'Postal code is required';
        } elseif (strlen($data['postal_code']) > 20) {
            $errors['postal_code'] = 'Postal code must be at most 20 characters';
        }

        if (isset($data['country']) && strlen($data['country']) > 100) {
            $errors['country'] = 'Country must be at most 100 characters';
        }

        if (isset($data['recipient_name']) && strlen($data['recipient_name']) > 100) {
            $errors['recipient_name'] = 'Recipient name must be at most 100 characters';
        }

        if (isset($data['phone']) && !preg_match('/^\+?[0-9]{8,15}$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone number';
        }

        $validTypes = ['billing', 'shipping', 'both'];
        $addressType = $data['address_type'] ?? 'both';
        if (!in_array($addressType, $validTypes)) {
            $errors['address_type'] = 'Invalid address type; must be billing, shipping, or both';
        }

        if ($errors) {
            throw new ValidationException('Address validation failed', ['errors' => $errors]);
        }
    }

    public static function validateUpdateAddress(array $data): void
    {
        $errors = [];

        if (isset($data['address_line1']) && (empty($data['address_line1']) || strlen($data['address_line1']) > 255)) {
            $errors['address_line1'] = 'Address line 1 must be 1-255 characters';
        }

        if (isset($data['address_line2']) && strlen($data['address_line2']) > 255) {
            $errors['address_line2'] = 'Address line 2 must be at most 255 characters';
        }

        if (isset($data['city']) && (empty($data['city']) || strlen($data['city']) > 100)) {
            $errors['city'] = 'City must be 1-100 characters';
        }

        if (isset($data['state']) && strlen($data['state']) > 100) {
            $errors['state'] = 'State must be at most 100 characters';
        }

        if (isset($data['postal_code']) && (empty($data['postal_code']) || strlen($data['postal_code']) > 20)) {
            $errors['postal_code'] = 'Postal code must be 1-20 characters';
        }

        if (isset($data['country']) && strlen($data['country']) > 100) {
            $errors['country'] = 'Country must be at most 100 characters';
        }

        if (isset($data['recipient_name']) && strlen($data['recipient_name']) > 100) {
            $errors['recipient_name'] = 'Recipient name must be at most 100 characters';
        }

        if (isset($data['phone']) && $data['phone'] !== null && !preg_match('/^\+?[0-9]{8,15}$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone number';
        }

        if (isset($data['address_type'])) {
            $validTypes = ['billing', 'shipping', 'both'];
            if (!in_array($data['address_type'], $validTypes)) {
                $errors['address_type'] = 'Invalid address type; must be billing, shipping, or both';
            }
        }

        if ($errors) {
            throw new ValidationException('Address validation failed', ['errors' => $errors]);
        }
    }
}