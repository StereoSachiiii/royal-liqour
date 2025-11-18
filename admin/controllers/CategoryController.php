<?php

// Ensure all necessary dependencies are included
require_once __DIR__.'/../../core/Database.php'; 
require_once __DIR__.'/../../core/Session.php';
require_once __DIR__.'/../models/Category.php';
require_once __DIR__.'/../repositories/CategoryRepository.php';
require_once __DIR__.'/../exceptions/DatabaseException.php';
require_once __DIR__.'/../exceptions/NotFoundException.php';
require_once __DIR__.'/../exceptions/ValidationException.php';
require_once __DIR__.'/../validators/CategoryValidator.php'; 

class CategoryController {

    private ?CategoryRepository $categoryRepository;
    private ?CategoryValidator $categoryValidator;
    private const MAX_LIMIT = 100; // Prevent abuse
    private const DEFAULT_LIMIT = 50;
    
    /**
     * CategoryController constructor. 
     * Uses Dependency Injection for repository and validator.
     */
    public function __construct(
        CategoryRepository $categoryRepository=null , 
        CategoryValidator $categoryValidator =null
    ) {
        $this->categoryRepository = $categoryRepository ?? new CategoryRepository();
        $this->categoryValidator = $categoryValidator ?? new CategoryValidator();
    }

    /**
     * Validates pagination input ensuring it's a non-negative integer within limits.
     * 
     * @param int $input
     * @param string $name
     * @param int|null $max Maximum allowed value
     * @return int
     * @throws ValidationException
     */
private function validatePaginationInput($input, string $name, ?int $max = null): int
{
    // Ensure the value is actually an integer
    if (filter_var($input, FILTER_VALIDATE_INT) === false) {
        throw new ValidationException("{$name} must be an integer.", code: 400);
    }

    // Cast it safely
    $input = (int) $input;

    // Check negative
    if ($input < 0) {
        throw new ValidationException("{$name} cannot be negative.", code: 400);
    }

    // Check max
    if ($max !== null && $input > $max) {
        throw new ValidationException("{$name} cannot exceed {$max}.", code: 400);
    }

    return $input;
}


    /**
     * Helper to standardize successful array response structure.
     * 
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @return array
     */
    private function successResponse(string $message, $data, int $code = 200): array {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
            'context' => []
        ];
    }

    /**
     * Helper to standardize error array response structure.
     * 
     * @param Exception $error
     * @return array
     */
    private function errorResponse(Exception $error): array {
        $statusCode = method_exists($error, 'getStatusCode') ? $error->getStatusCode() : 500;
        $context = method_exists($error, 'getContext') ? $error->getContext() : [];
        
        // Log the error
        $this->logError($error, $context);
        
        return [
            'success' => false,
            'message' => $error->getMessage(),
            'code'    => $statusCode,
            'context' => $context
        ];
    }

    /**
     * Centralized error logging.
     * 
     * @param Exception $error
     * @param array $context
     * @return void
     */
    private function logError(Exception $error, array $context = []): void {
        error_log(sprintf(
            "[%s] CategoryController Error: %s | File: %s | Line: %d | Context: %s",
            date('Y-m-d H:i:s'),
            $error->getMessage(),
            $error->getFile(),
            $error->getLine(),
            json_encode($context)
        ));
    }

    /**
     * Wraps operations with consistent error handling to reduce repetition.
     * 
     * @param callable $operation
     * @return array
     */
    private function handleRequest(callable $operation): array {
        try {
            return $operation();
        } catch (NotFoundException|ValidationException|DatabaseException $e) {
            return $this->errorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse(
                new DatabaseException("An unexpected error occurred: " . $e->getMessage(), code:500)
            );
        }
    }

    // --- READ OPERATIONS ---

    /**
     * Get all categories with pagination (used for Admin View by including inactive).
     *
     * @param int $limit
     * @param int $offset
     * @param bool $includeInactive
     * @return array
     */
    public function getAllCategories(string|int $limit = self::DEFAULT_LIMIT, string|int $offset = 0, bool $includeInactive = false): array {
        return $this->handleRequest(function() use ($limit, $offset, $includeInactive) {
            $limit = $this->validatePaginationInput($limit, 'Limit', self::MAX_LIMIT);
            $offset = $this->validatePaginationInput($offset, 'Offset');
            
            $categories = $this->categoryRepository->getAllCategories($limit, $offset, $includeInactive);
            $categoryData = array_map(fn(Category $category) => $category->toArray(), $categories);

            return $this->successResponse("Successfully fetched categories.", $categoryData);
        });
    }

    /**
     * Get category by ID.
     * 
     * @param int $id
     * @return array
     */
    public function getCategoryById(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $this->categoryValidator->validateCategoryId($id);
            
            $category = $this->categoryRepository->getCategoryById($id);
            
            if (!$category) {
                throw new NotFoundException("Category with ID {$id} not found.", []);
            }
            
            return $this->successResponse('Category fetched successfully.', $category->toArray());
        });
    }



    public function getCategoryByIdAdmin(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $this->categoryValidator->validateCategoryId($id);
            
            $category = $this->categoryRepository->getCategoryById($id);
            
            if (!$category) {
                throw new NotFoundException("Category with ID {$id} not found.", []);
            }
            
            return $this->successResponse('Category fetched successfully.', $category->toArray());
        });
    }
    /**
     * Get category by name.
     * 
     * @param string $name
     * @return array
     */
    public function getCategoryByName(string $name): array {
        return $this->handleRequest(function() use ($name) {
            $this->categoryValidator->validateCategoryName($name);
            
            $category = $this->categoryRepository->getCategoryByName($name);
            
            if (!$category) {
                throw new NotFoundException("Category '{$name}' not found.", []);
            }
            
            return $this->successResponse('Category fetched successfully by name.', $category->toArray());
        });
    }

    /**
     * Get only active categories (used for public-facing view).
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getActiveCategories(int|string $limit = self::DEFAULT_LIMIT, int|string $offset = 0): array {
        return $this->handleRequest(function() use ($limit, $offset) {
            $limit = $this->validatePaginationInput($limit, 'Limit', self::MAX_LIMIT);
            $offset = $this->validatePaginationInput($offset, 'Offset');
            
            $activeCategories = $this->categoryRepository->getActiveCategories($limit, $offset);
            
            $activeCategoryArray = array_map(
                fn(Category $item) => $item->toArray(),
                $activeCategories
            );

            return $this->successResponse('Successfully fetched all active categories.', $activeCategoryArray);
        });
    }

    /**
     * Search categories by name or description.
     * 
     * @param string $searchTerm
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function searchCategories(string $searchTerm, int|string $limit = self::DEFAULT_LIMIT, int|string $offset = 0): array {
        return $this->handleRequest(function() use ($searchTerm, $limit, $offset) {
            $this->categoryValidator->validateSearchTerm($searchTerm);
            $limit = intval($this->validatePaginationInput($limit, 'Limit', self::MAX_LIMIT));
            $offset = intval($this->validatePaginationInput($offset, 'Offset'));

            $categories = $this->categoryRepository->searchCategories($searchTerm, $limit, $offset);
            $categoryData = array_map(fn(Category $category) => $category->toArray(), $categories);
            
            return $this->successResponse("Successfully searched categories.", $categoryData);
        });
    }

    /**
     * Get categories with product count (useful for dashboards/navigation).
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getCategoriesWithProductCount(int|string $limit = self::DEFAULT_LIMIT, int|string $offset = 0): array {
        return $this->handleRequest(function() use ($limit, $offset) {
            $limit = $this->validatePaginationInput($limit, 'Limit', self::MAX_LIMIT);
            $offset = $this->validatePaginationInput($offset, 'Offset');
            
            $categoriesWithCount = $this->categoryRepository->getCategoriesWithProductCount($limit, $offset);

            return $this->successResponse(
                "Successfully fetched categories with product counts.",
                $categoriesWithCount
            );
        });
    }
    
    /**
     * Get total count of categories.
     * 
     * @param bool $activeOnly
     * @return array
     */
    public function getCategoryCountActive(): array {
        return $this->handleRequest(function()  {
            $count = $this->categoryRepository->getCategoryCount(true);
            return $this->successResponse('Category count (active) fetched successfully.', ['count' => $count]);
        });
    }

    public function getCatergoryCountAll():array{
        return $this->handleRequest(
            function():array{
                $count = $this->categoryRepository->getCategoryCount(false);
                return $this->successResponse(message:'Category count fetched succesfully.',data:['count' => $count]);
            }
        );
    }
    
    // --- WRITE OPERATIONS ---

    /**
     * Create a new category.
     * 
     * @param array $categoryData
     * @return array
     */
    public function createCategory(array $categoryData): array {
        return $this->handleRequest(function() use ($categoryData) {
            $this->categoryValidator->validateCreate($categoryData);

            // Check if name already exists before creation
            if ($this->categoryRepository->categoryNameExists($categoryData['name'])) {
                throw new ValidationException(
                    "Category name '{$categoryData['name']}' already exists.", 
                    code:409
                );
            }

            $category = $this->categoryRepository->createCategory($categoryData);

            return $this->successResponse(
                'Successfully created the category.',
                $category->toArray(),
                201
            );
        });
    }

    /**
     * Update an existing category.
     * 
     * @param array $data Must include 'id' key
     * @return array
     */
    public function updateCategory(array $data): array {
        return $this->handleRequest(function() use ($data) {
            $this->categoryValidator->validateUpdate($data);

            // Check for duplicate name, excluding the current category ID
            if (isset($data['name']) && $this->categoryRepository->categoryNameExists($data['name'], $data['id'])) {
                throw new ValidationException(
                    "Category name '{$data['name']}' already exists for another category.", 
                    code:409
                );
            }

            $category = $this->categoryRepository->updateCategory( $data);

            return $this->successResponse('Successfully updated the category.', $category->toArray());
        });
    }

    /**
     * Update category status (active/inactive).
     * 
     * @param int $id
     * @param bool $isActive
     * @return array
     */
    public function updateCategoryStatus(int $id, bool $isActive): array {
        return $this->handleRequest(function() use ($id, $isActive) {
            $this->categoryValidator->validateCategoryId($id);

            $category = $this->categoryRepository->updateCategoryStatus($id, $isActive);

            $status = $isActive ? 'active' : 'inactive';
            return $this->successResponse(
                "Category status updated to '{$status}' successfully.",
                $category->toArray()
            );
        });
    }

    /**
     * Soft delete a category.
     * 
     * @param int $id
     * @return array
     */
    public function softDeleteCategory(array $data): array {
        $id = $data['id'];
        return $this->handleRequest(function() use ($id) {
            $this->categoryValidator->validateCategoryId($id);

            $success = $this->categoryRepository->softDeleteCategory($id);

            if (!$success) {
                throw new DatabaseException(
                    "Soft delete failed for category ID {$id}, possibly already deleted.", 
                    code:500
                ); 
            }
            return $this->successResponse('Category soft deleted successfully.', ['id' => $id]);
        });
    }
    
    /**
     * Restore a soft-deleted category.
     * 
     * @param int $id
     * @return array
     */
    public function restoreCategory(array $data): array {
        $id = $data['id'];
        return $this->handleRequest(function() use ($id) {
            $this->categoryValidator->validateCategoryId($id);
            $category = $this->categoryRepository->restoreCategory($id);

            return $this->successResponse('Category restored successfully.', $category->toArray());
        });
    }

    /**
     * Bulk soft delete categories.
     * 
     * @param array $ids
     * @return array
     */
    public function bulkDeleteCategories(array $ids): array {
        return $this->handleRequest(function() use ($ids) {
            // Validate that we have a non-empty array of integers
            if (empty($ids)) {
                throw new ValidationException("Bulk delete requires a non-empty array of IDs.",code: 400);
            }
            
            // Check all elements are integers
            if (count($ids) !== count(array_filter($ids, 'is_int'))) {
                throw new ValidationException("All IDs must be integers.", code:400);
            }

            $deletedCount = $this->categoryRepository->bulkDeleteCategories($ids);

            return $this->successResponse(
                "Successfully soft deleted {$deletedCount} categories.",
                ['deleted_count' => $deletedCount]
            );
        });
    }
    
    /**
     * Hard delete a category (permanent removal).
     * 
     * @param int $id
     * @return array
     */
    public function deleteCategory(int $id): array {
        return $this->handleRequest(function() use ($id) {
            $this->categoryValidator->validateCategoryId($id);

            $success = $this->categoryRepository->deleteCategory($id);

            if (!$success) {
                throw new DatabaseException("Hard delete failed for category ID {$id}.", code:500);
            }

            return $this->successResponse('Category permanently deleted successfully.', ['id' => $id]);
        });
    }


    public function categoryNameExists(string $name): array {
        return $this->handleRequest(function() use ($name) {
            $this->categoryValidator->validateCategoryName($name);

            $exists = $this->categoryRepository->categoryNameExists($name);

            return $this->successResponse(
                "Category name existence check completed.",
                ['exists' => $exists]
            );
        });
    }

    public function hardDeleteCategory(array $data): array {
        $id = $data['id'];
        return $this->handleRequest(function() use ($id) {
            $this->categoryValidator->validateCategoryId($id);

            $success = $this->categoryRepository->deleteCategory($id);

            if (!$success) {
                throw new DatabaseException("Hard delete failed for category ID {$id}.", code:500);
            }

            return $this->successResponse('Category permanently deleted successfully.', ['id' => $id]);
        });
    }
}