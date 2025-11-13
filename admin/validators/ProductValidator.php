<?php
declare(strict_types=1);

require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';

class ProductValidator  {

    private static ?ProductRepository $productRepository = null;

    public function __construct() {
        if (self::$productRepository === null) {
            self::$productRepository = new ProductRepository();
        }
    }

    /**
     * Validate product data for creation
     *
     * @param array{name: string, description: string, price: float, image_url: string, category_id: int, supplier_id: int, is_active: bool, total_stock: int} $data
     * @return array{success: bool}
     * @throws ValidationException
     */
    public static function validateCreate(array $data): array {
        // Required fields
        $required = ['name', 'description', 'price', 'image_url', 'category_id', 'supplier_id', 'is_active', 'total_stock'];
        $missing = array_filter($required, fn($field) => !isset($data[$field]));
        if (!empty($missing)) {
            throw new ValidationException(
                message: "Missing required fields",
                context: ['missing_fields' => $missing]
            );
        }

        // Name
        if (strlen($data['name']) < 1 || strlen($data['name']) > 255) {
            throw new ValidationException(
                message: "Invalid name length",
                context: ['field' => 'name', 'value' => $data['name']]
            );
        }

        // Description
        if (strlen($data['description']) < 1 || strlen($data['description']) > 1000) {
            throw new ValidationException(
                message: "Invalid description length",
                context: ['field' => 'description', 'value' => $data['description']]
            );
        }

        // Price
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            throw new ValidationException(
                message: "Price must be a non-negative number",
                context: ['field' => 'price', 'value' => $data['price']]
            );
        }

        // Image URL
        if (strlen($data['image_url']) > 500) {
            throw new ValidationException(
                message: "Image URL too long",
                context: ['field' => 'image_url', 'value' => $data['image_url']]
            );
        }

        // Category ID and Supplier ID
        if (!is_int($data['category_id']) || !is_int($data['supplier_id'])) {
            throw new ValidationException(
                message: "Category ID and Supplier ID must be integers",
                context: ['category_id' => $data['category_id'], 'supplier_id' => $data['supplier_id']]
            );
        }

        // is_active
        if (!is_bool($data['is_active'])) {
            throw new ValidationException(
                message: "is_active must be boolean",
                context: ['field' => 'is_active', 'value' => $data['is_active']]
            );
        }

        // total_stock
        if (!is_int($data['total_stock']) || $data['total_stock'] < 0) {
            throw new ValidationException(
                message: "Total stock must be a non-negative integer",
                context: ['field' => 'total_stock', 'value' => $data['total_stock']]
            );
        }

        return ['success' => true];
    }

    /**
     * Validate product data for update
     *
     * @param int $id
     * @param array{name?: string, description?: string, price?: float, image_url?: string, category_id?: int, supplier_id?: int, is_active?: bool, total_stock?: int} $data
     * @return array{success: bool}
     * @throws ValidationException
     */
    public static function validateUpdate(int $id, array $data): array {
        if ($id <= 0) {
            throw new ValidationException(
                message: "Invalid product ID",
                context: ['id' => $id]
            );
        }

        // Optional fields
        if (isset($data['name']) && (strlen($data['name']) < 1 || strlen($data['name']) > 255)) {
            throw new ValidationException(
                message: "Invalid name length",
                context: ['field' => 'name', 'value' => $data['name']]
            );
        }

        if (isset($data['description']) && (strlen($data['description']) < 1 || strlen($data['description']) > 1000)) {
            throw new ValidationException(
                message: "Invalid description length",
                context: ['field' => 'description', 'value' => $data['description']]
            );
        }

        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            throw new ValidationException(
                message: "Price must be a non-negative number",
                context: ['field' => 'price', 'value' => $data['price']]
            );
        }

        if (isset($data['image_url']) && strlen($data['image_url']) > 500) {
            throw new ValidationException(
                message: "Image URL too long",
                context: ['field' => 'image_url', 'value' => $data['image_url']]
            );
        }

        if (isset($data['category_id']) && !is_int($data['category_id'])) {
            throw new ValidationException(
                message: "Category ID must be integer",
                context: ['field' => 'category_id', 'value' => $data['category_id']]
            );
        }

        if (isset($data['supplier_id']) && !is_int($data['supplier_id'])) {
            throw new ValidationException(
                message: "Supplier ID must be integer",
                context: ['field' => 'supplier_id', 'value' => $data['supplier_id']]
            );
        }

        if (isset($data['is_active']) && !is_bool($data['is_active'])) {
            throw new ValidationException(
                message: "is_active must be boolean",
                context: ['field' => 'is_active', 'value' => $data['is_active']]
            );
        }

        if (isset($data['total_stock']) && (!is_int($data['total_stock']) || $data['total_stock'] < 0)) {
            throw new ValidationException(
                message: "Total stock must be a non-negative integer",
                context: ['field' => 'total_stock', 'value' => $data['total_stock']]
            );
        }

        return ['success' => true];
    }

    /**
     * Validate product ID for deletion
     *
     * @param int $id
     * @return array{success: bool}
     * @throws ValidationException
     */
    public static function validateDelete(int $id): array {
        if ($id <= 0) {
            throw new ValidationException(
                message: "Invalid product ID",
                context: ['id' => $id]
            );
        }

        return ['success' => true];
    }
}
?>
