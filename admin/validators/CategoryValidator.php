<?php
require_once __DIR__. '/Validator.php';
require_once __DIR__. '/../repositories/CategoryRepository.php';
require_once __DIR__. '/../exceptions/ValidationException.php';

class CategoryValidator implements ValidatorInterface {

    private static ?CategoryRepository $categoryRepository = null;

    public function __construct() {
        if (self::$categoryRepository === null) {
            self::$categoryRepository = new CategoryRepository();
        }
    }

    /**
     * Validate category data for creation.
     *
     * @param array{
     *     name: string,
     *     description?: string|null,
     *     imageUrl?: string|null,
     *     isActive?: bool
     * } $data
     *
     * @throws ValidationException if any validation rule fails
     * @return array{success: bool}
     */
    public static function validateCreate(array $data): array
    {
        // Name is required
        if (empty($data['name'])) {
            throw new ValidationException(
                message: "Name is required",
                context: [
                    'missing_fields' => ['name']
                ]
            );
        }

        // Name length validation
        $nameLength = strlen($data['name']);
        if ($nameLength < 2 || $nameLength > 100) {
            throw new ValidationException(
                message: "Invalid argument: name must be 2-100 characters long",
                context: ['field' => 'name', 'value' => $data['name']]
            );
        }

        // Check if category name already exists
        if (self::$categoryRepository && self::$categoryRepository->getCategoryByName($data['name'])) {
            throw new ValidationException(
                message: "Category name already exists",
                context: ['field' => 'name', 'value' => $data['name']]
            );
        }

        // Description validation (optional)
        if (isset($data['description']) && $data['description'] !== null) {
            $descLength = strlen($data['description']);
            if ($descLength > 1000) {
                throw new ValidationException(
                    message: "Invalid argument: description must not exceed 1000 characters",
                    context: ['field' => 'description', 'length' => $descLength]
                );
            }
        }

        // Image URL validation (optional)
        if (isset($data['imageUrl']) && $data['imageUrl'] !== null) {
            $urlLength = strlen($data['imageUrl']);
            if ($urlLength > 500) {
                throw new ValidationException(
                    message: "Invalid argument: image URL must not exceed 500 characters",
                    context: ['field' => 'imageUrl', 'length' => $urlLength]
                );
            }

            // Basic URL format validation
            if (!filter_var($data['imageUrl'], FILTER_VALIDATE_URL)) {
                throw new ValidationException(
                    message: "Invalid argument: image URL format is invalid",
                    context: ['field' => 'imageUrl', 'value' => $data['imageUrl']]
                );
            }
        }

        // isActive validation (optional, should be boolean)
        if (isset($data['isActive']) && !is_bool($data['isActive'])) {
            throw new ValidationException(
                message: "Invalid argument: isActive must be a boolean",
                context: ['field' => 'isActive', 'value' => $data['isActive']]
            );
        }

        return ['success' => true];
    }

    /**
     * Validate category data for update.
     *
     * @param array{
     *     id?: int|null,
     *     name?: string|null,
     *     description?: string|null,
     *     imageUrl?: string|null,
     *     isActive?: bool|null
     * } $data
     *
     * @throws ValidationException if any validation rule fails
     * @return array{success: bool}
     */
    public static function validateUpdate(array $data): array
    {
        // Category ID is required
        if (!isset($data['id']) || !is_int($data['id']) || $data['id'] <= 0) {
            throw new ValidationException(
                message: "Invalid argument: id must be a positive integer",
                context: ['field' => 'id', 'value' => $data['id'] ?? null]
            );
        }

        // Check if category exists
        if (self::$categoryRepository && !self::$categoryRepository->getCategoryById($data['id'])) {
            throw new ValidationException(
                message: "Category not found",
                context: ['field' => 'id', 'value' => $data['id']]
            );
        }

        // Name validation (optional for update)
        if (isset($data['name']) && $data['name'] !== null) {
            $nameLength = strlen($data['name']);
            if ($nameLength < 2 || $nameLength > 100) {
                throw new ValidationException(
                    message: "Invalid argument: name must be 2-100 characters long",
                    context: ['field' => 'name', 'value' => $data['name']]
                );
            }

            // Check if new name already exists (excluding current category)
            $existingCategory = self::$categoryRepository->getCategoryById($data['id']);
            if ($existingCategory && $existingCategory->getName() !== $data['name']) {
                $duplicate = self::$categoryRepository->getCategoryByName($data['name']);
                if ($duplicate) {
                    throw new ValidationException(
                        message: "Category name already exists",
                        context: ['field' => 'name', 'value' => $data['name']]
                    );
                }
            }
        }

        // Description validation (optional)
        if (isset($data['description']) && $data['description'] !== null) {
            $descLength = strlen($data['description']);
            if ($descLength > 1000) {
                throw new ValidationException(
                    message: "Invalid argument: description must not exceed 1000 characters",
                    context: ['field' => 'description', 'length' => $descLength]
                );
            }
        }

        // Image URL validation (optional)
        if (isset($data['imageUrl']) && $data['imageUrl'] !== null) {
            $urlLength = strlen($data['imageUrl']);
            if ($urlLength > 500) {
                throw new ValidationException(
                    message: "Invalid argument: image URL must not exceed 500 characters",
                    context: ['field' => 'imageUrl', 'length' => $urlLength]
                );
            }

            if (!filter_var($data['imageUrl'], FILTER_VALIDATE_URL)) {
                throw new ValidationException(
                    message: "Invalid argument: image URL format is invalid",
                    context: ['field' => 'imageUrl', 'value' => $data['imageUrl']]
                );
            }
        }

        // isActive validation (optional)
        if (isset($data['isActive']) && $data['isActive'] !== null && !is_bool($data['isActive'])) {
            throw new ValidationException(
                message: "Invalid argument: isActive must be a boolean",
                context: ['field' => 'isActive', 'value' => $data['isActive']]
            );
        }

        return ['success' => true];
    }

    /**
     * Validate category ID.
     *
     * @param int $categoryId
     * @throws ValidationException
     * @return array{success: bool}
     */
    public static function validateCategoryId(int $categoryId): array
    {
        if (!is_int($categoryId) || $categoryId <= 0) {
            throw new ValidationException(
                message: "Invalid argument: categoryId must be a positive integer",
                context: ['field' => 'categoryId', 'value' => $categoryId]
            );
        }

        // Check if category exists
        if (self::$categoryRepository && !self::$categoryRepository->getCategoryByIdAdmin($categoryId)) {
            throw new ValidationException(
                message: "Category not found",
                context: ['field' => 'categoryId', 'value' => $categoryId]
            );
        }

        return ['success' => true];
    }

    /**
     * Validate category slug/name for uniqueness check.
     *
     * @param string $name $data
     * @throws ValidationException
     * @return array{success: bool, available: bool}
     */
    public static function validateCategoryName(string $name): array
    {
        if (empty($name)) {
            throw new ValidationException(
                message: "Name is required",
                context: ['field' => 'name']
            );
        }

        $nameLength = strlen($name);
        if ($nameLength < 2 || $nameLength > 100) {
            throw new ValidationException(
                message: "Invalid argument: name must be 2-100 characters long",
                context: ['field' => 'name', 'value' => $name]
            );
        }

        
        // If excludeId is provided, check if the existing category is not the one being updated
    

        return [
            'success' => true
        ];
    }

    /**
     * Validate bulk delete operation.
     *
     * @param array{categoryIds: int[]} $data
     * @throws ValidationException
     * @return array{success: bool}
     */
    public static function validateBulkDelete(array $data): array
    {
        if (empty($data['categoryIds']) || !is_array($data['categoryIds'])) {
            throw new ValidationException(
                message: "Invalid argument: categoryIds must be a non-empty array",
                context: ['field' => 'categoryIds']
            );
        }

        foreach ($data['categoryIds'] as $id) {
            if (!is_int($id) || $id <= 0) {
                throw new ValidationException(
                    message: "Invalid argument: all category IDs must be positive integers",
                    context: ['field' => 'categoryIds', 'invalid_id' => $id]
                );
            }
        }

        return ['success' => true];
    }
    public static function validateSearchTerm(string $searchTerm): void {
        $searchTerm = trim($searchTerm);
        $minLength = 3; 

        if (empty($searchTerm)) {
            throw new ValidationException("Search term cannot be empty.", code:400, context:['field' => 'search_term']);
        }

        if (strlen($searchTerm) < $minLength) {
            throw new ValidationException("Search term must be at least **{$minLength}** characters long.", code:400, context:['field' => 'search_term', 'min_length' => $minLength]);
        }
        
        // Optional: Check max length to prevent DOS attacks via massive query strings
        $maxLength = 100;
        if (strlen($searchTerm) > $maxLength) {
            throw new ValidationException("Search term cannot exceed **{$maxLength}** characters.", code:400, context:['field' => 'search_term', 'max_length' => $maxLength]);
        }
        
        // Optional: Sanitization/Filtering (e.g., regex to restrict characters) can be added here
    }
}
?>