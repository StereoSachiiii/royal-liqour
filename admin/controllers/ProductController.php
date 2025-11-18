<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../validators/ProductValidator.php';

/**
 * ProductController manages all product operations.
 *
 * All methods return a standardized JSON-serializable array:
 * ```php
 * [
 *     'success'  => bool,
 *     'message'  => string,
 *     'data'     => array|null,
 *     'code'     => int,
 *     'context'  => array
 * ]
 * ```
 *
 * @uses ProductRepository
 * @uses Session
 */
class ProductController
{
    private ProductRepository $productRepository;
    private Session $session;

    /**
     * @param ProductRepository $productRepository
     * @param Session           $session
     */
    public function __construct(ProductRepository $productRepository, Session $session)
    {
        $this->productRepository = $productRepository;
        $this->session = $session;
    }

    /**
     * Retrieve all active products with pagination.
     *
     * @param int $limit  Maximum number of products to return (default: 50).
     * @param int $offset Pagination offset (default: 0).
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function getAllProducts(int $limit = 50, int $offset = 0): array
    {
        try {
            $products = $this->productRepository->getAllProducts($limit, $offset);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $productData,
                'code' => 200,
                'context' => []
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['error' => $e->getMessage()]
            ];
        }
    }

    /**
     * Retrieve all products including inactive (admin view).
     *
     * @param int $limit  Maximum number of products to return (default: 50).
     * @param int $offset Pagination offset (default: 0).
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function getAllProductsIncludingInactive(int $limit = 50, int $offset = 0): array
    {
        try {
            $products = $this->productRepository->getAllProductsIncludingInactive($limit, $offset);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'All products retrieved successfully',
                'data' => $productData,
                'code' => 200,
                'context' => []
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['error' => $e->getMessage()]
            ];
        }
    }

    /**
     * Retrieve a single active product by ID.
     *
     * @param int $productId The product ID.
     *
     * @return array{success: bool, message: string, data: ?array, code: int, context: array}
     */
    public function getProductById(int $productId): array
    {
        try {
            $product = $this->productRepository->getProductById($productId);

            return [
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $product->toArray(),
                'code' => 200,
                'context' => []
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch product: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId]
            ];
        }
    }

    /**
     * Retrieve a single product by ID including inactive (admin).
     *
     * @param int $productId The product ID.
     *
     * @return array{success: bool, message: string, data: ?array, code: int, context: array}
     */
    public function getProductByIdAdmin(int $productId): array
    {
        try {
            $product = $this->productRepository->getProductByIdAdmin($productId);

            return [
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $product->toArray(),
                'code' => 200,
                'context' => []
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch product: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId]
            ];
        }
    }

    /**
     * Search products by name or description.
     *
     * @param string $query  Search query string.
     * @param int    $limit  Maximum results (default: 50).
     * @param int    $offset Pagination offset (default: 0).
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function searchProducts(string $query, int $limit = 50, int $offset = 0): array
    {
        try {
            if (empty(trim($query))) {
                return [
                    'success' => false,
                    'message' => 'Search query cannot be empty',
                    'data' => null,
                    'code' => 400,
                    'context' => []
                ];
            }

            $products = $this->productRepository->searchProducts($query, $limit, $offset);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => $productData,
                'code' => 200,
                'context' => ['query' => $query, 'results_count' => count($productData)]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['query' => $query]
            ];
        }
    }

    /**
     * Count search results.
     *
     * @param string $query Search query string.
     *
     * @return array{success: bool, message: string, data: ?int, code: int, context: array}
     */
    public function countSearchResults(string $query): array
    {
        try {
            if (empty(trim($query))) {
                return [
                    'success' => false,
                    'message' => 'Search query cannot be empty',
                    'data' => null,
                    'code' => 400,
                    'context' => []
                ];
            }

            $count = $this->productRepository->countSearchResults($query);

            return [
                'success' => true,
                'message' => 'Search count retrieved',
                'data' => $count,
                'code' => 200,
                'context' => ['query' => $query]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to count results: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get products by category.
     *
     * @param int $categoryId Category ID.
     * @param int $limit      Maximum results (default: 50).
     * @param int $offset     Pagination offset (default: 0).
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function getProductsByCategory(int $categoryId, int $limit = 50, int $offset = 0): array
    {
        try {
            $products = $this->productRepository->getProductsByCategory($categoryId, $limit, $offset);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'Category products retrieved successfully',
                'data' => $productData,
                'code' => 200,
                'context' => ['categoryId' => $categoryId, 'count' => count($productData)]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch category products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['categoryId' => $categoryId]
            ];
        }
    }

    /**
     * Get products by supplier.
     *
     * @param int $supplierId Supplier ID.
     * @param int $limit      Maximum results (default: 50).
     * @param int $offset     Pagination offset (default: 0).
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function getProductsBySupplier(int $supplierId, int $limit = 50, int $offset = 0): array
    {
        try {
            $products = $this->productRepository->getProductsBySupplier($supplierId, $limit, $offset);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'Supplier products retrieved successfully',
                'data' => $productData,
                'code' => 200,
                'context' => ['supplierId' => $supplierId, 'count' => count($productData)]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch supplier products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['supplierId' => $supplierId]
            ];
        }
    }

    /**
     * Get products within price range.
     *
     * @param float $minPrice Minimum price.
     * @param float $maxPrice Maximum price.
     * @param int   $limit    Maximum results (default: 50).
     * @param int   $offset   Pagination offset (default: 0).
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function getProductsByPriceRange(float $minPrice, float $maxPrice, int $limit = 50, int $offset = 0): array
    {
        try {
            if ($minPrice < 0 || $maxPrice < 0 || $minPrice > $maxPrice) {
                return [
                    'success' => false,
                    'message' => 'Invalid price range',
                    'data' => null,
                    'code' => 400,
                    'context' => ['minPrice' => $minPrice, 'maxPrice' => $maxPrice]
                ];
            }

            $products = $this->productRepository->getProductsByPriceRange($minPrice, $maxPrice, $limit, $offset);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'Price range products retrieved successfully',
                'data' => $productData,
                'code' => 200,
                'context' => ['minPrice' => $minPrice, 'maxPrice' => $maxPrice, 'count' => count($productData)]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch price range products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get products sorted by date.
     *
     * @param string $order  'DESC' for newest, 'ASC' for oldest (default: DESC).
     * @param int    $limit  Maximum results (default: 50).
     * @param int    $offset Pagination offset (default: 0).
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function getProductsByDate(string $order = 'DESC', int $limit = 50, int $offset = 0): array
    {
        try {
            $products = $this->productRepository->getProductsByDate($order, $limit, $offset);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'Products sorted by date retrieved successfully',
                'data' => $productData,
                'code' => 200,
                'context' => ['order' => $order, 'count' => count($productData)]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get products sorted by price.
     *
     * @param string $order  'ASC' for lowest to highest, 'DESC' for highest to lowest (default: ASC).
     * @param int    $limit  Maximum results (default: 50).
     * @param int    $offset Pagination offset (default: 0).
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function getProductsByPrice(string $order = 'ASC', int $limit = 50, int $offset = 0): array
    {
        try {
            $products = $this->productRepository->getProductsByPrice($order, $limit, $offset);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'Products sorted by price retrieved successfully',
                'data' => $productData,
                'code' => 200,
                'context' => ['order' => $order, 'count' => count($productData)]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get products by multiple IDs (for cart/order operations).
     *
     * @param array $ids Array of product IDs.
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function getProductsByIds(array $ids): array
    {
        try {
            if (empty($ids)) {
                return [
                    'success' => false,
                    'message' => 'Product IDs array cannot be empty',
                    'data' => null,
                    'code' => 400,
                    'context' => []
                ];
            }

            $products = $this->productRepository->getProductsByIds($ids);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $productData,
                'code' => 200,
                'context' => ['requested_count' => count($ids), 'found_count' => count($productData)]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get low stock products.
     *
     * @param int $threshold Stock threshold (default: 10).
     * @param int $limit     Maximum results (default: 50).
     * @param int $offset    Pagination offset (default: 0).
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function getLowStockProducts(int $threshold = 10, int $limit = 50, int $offset = 0): array
    {
        try {
            $products = $this->productRepository->getLowStockProducts($threshold, $limit, $offset);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'Low stock products retrieved successfully',
                'data' => $productData,
                'code' => 200,
                'context' => ['threshold' => $threshold, 'count' => count($productData)]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch low stock products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get out of stock products.
     *
     * @param int $limit  Maximum results (default: 50).
     * @param int $offset Pagination offset (default: 0).
     *
     * @return array{success: bool, message: string, data: array, code: int, context: array}
     */
    public function getOutOfStockProducts(int $limit = 50, int $offset = 0): array
    {
        try {
            $products = $this->productRepository->getOutOfStockProducts($limit, $offset);
            $productData = array_map(fn(Product $product): array => $product->toArray(), $products);

            return [
                'success' => true,
                'message' => 'Out of stock products retrieved successfully',
                'data' => $productData,
                'code' => 200,
                'context' => ['count' => count($productData)]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch out of stock products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get total count of active products.
     *
     * @return array{success: bool, message: string, data: ?int, code: int, context: array}
     */
    public function countProducts(): array
    {
        try {
            $count = $this->productRepository->countProducts();

            return [
                'success' => true,
                'message' => 'Product count retrieved',
                'data' => $count,
                'code' => 200,
                'context' => []
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to count products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get total count of all products including inactive.
     *
     * @return array{success: bool, message: string, data: ?int, code: int, context: array}
     */
    public function countAllProducts(): array
    {
        try {
            $count = $this->productRepository->countAllProducts();

            return [
                'success' => true,
                'message' => 'Total product count retrieved',
                'data' => $count,
                'code' => 200,
                'context' => []
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to count products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get product count by category.
     *
     * @param int $categoryId Category ID.
     *
     * @return array{success: bool, message: string, data: ?int, code: int, context: array}
     */
    public function countProductsByCategory(int $categoryId): array
    {
        try {
            $count = $this->productRepository->countProductsByCategory($categoryId);

            return [
                'success' => true,
                'message' => 'Category product count retrieved',
                'data' => $count,
                'code' => 200,
                'context' => ['categoryId' => $categoryId]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to count category products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['categoryId' => $categoryId]
            ];
        }
    }

    /**
     * Get product count by supplier.
     *
     * @param int $supplierId Supplier ID.
     *
     * @return array{success: bool, message: string, data: ?int, code: int, context: array}
     */
    public function countProductsBySupplier(int $supplierId): array
    {
        try {
            $count = $this->productRepository->countProductsBySupplier($supplierId);

            return [
                'success' => true,
                'message' => 'Supplier product count retrieved',
                'data' => $count,
                'code' => 200,
                'context' => ['supplierId' => $supplierId]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to count supplier products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['supplierId' => $supplierId]
            ];
        }
    }

    /**
     * Get low stock product count.
     *
     * @param int $threshold Stock threshold (default: 10).
     *
     * @return array{success: bool, message: string, data: ?int, code: int, context: array}
     */
    public function countLowStockProducts(int $threshold = 10): array
    {
        try {
            $count = $this->productRepository->countLowStockProducts($threshold);

            return [
                'success' => true,
                'message' => 'Low stock count retrieved',
                'data' => $count,
                'code' => 200,
                'context' => ['threshold' => $threshold]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to count low stock products: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => []
            ];
        }
    }

    /**
     * Get product stock quantity by ID.
     *
     * @param int $productId Product ID.
     *
     * @return array{success: bool, message: string, data: ?int, code: int, context: array}
     */
    public function getProductStock(int $productId): array
    {
        try {
            $stock = $this->productRepository->getProductStock($productId);

            return [
                'success' => true,
                'message' => 'Product stock retrieved',
                'data' => $stock,
                'code' => 200,
                'context' => ['productId' => $productId]
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch stock: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId]
            ];
        }
    }

    /**
     * Check if product exists.
     *
     * @param int $productId Product ID.
     *
     * @return array{success: bool, message: string, data: ?bool, code: int, context: array}
     */
    public function productExists(int $productId): array
    {
        try {
            $exists = $this->productRepository->productExists($productId);

            return [
                'success' => true,
                'message' => 'Product existence check completed',
                'data' => $exists,
                'code' => 200,
                'context' => ['productId' => $productId, 'exists' => $exists]
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to check product existence: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId]
            ];
        }
    }

    /**
     * Create a new product.
     *
     * @param array $data Product creation data.
     *
     * @return array{success: bool, message: string, data: ?array, code: int, context: array}
     */
    public function createProduct(array $data): array
    {
        try {
            ProductValidator::validateCreate($data);

            $product = $this->productRepository->createProduct($data);

            if (!$product) {
                throw new RuntimeException('Product creation failed in repository.');
            }

            return [
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->toArray(),
                'code' => 201,
                'context' => []
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (DatabaseException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['input' => $data]
            ];
        }
    }

    /**
     * Update an existing product (all fields).
     *
     * @param int   $productId Product ID to update.
     * @param array $data      Product data to update.
     *
     * @return array{success: bool, message: string, data: ?array, code: int, context: array}
     */
    public function updateProduct(int $productId, array $data): array
    {
        try {
            $existingProduct = $this->productRepository->getProductByIdAdmin($productId);

            if (!$existingProduct) {
                throw new NotFoundException('Product not found.', context: ['productId' => $productId]);
            }

            $updatedProduct = $this->productRepository->updateProduct($productId, $data);

            if (!$updatedProduct) {
                throw new DatabaseException('No changes applied or update failed.');
            }

            return [
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $updatedProduct->toArray(),
                'code' => 200,
                'context' => []
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (DatabaseException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId, 'input' => $data]
            ];
        }
    }

    /**
     * Partial update product (only provided fields).
     *
     * @param int   $productId Product ID to update.
     * @param array $data      Partial product data.
     *
     * @return array{success: bool, message: string, data: ?array, code: int, context: array}
     */
    public function partialUpdateProduct(int $productId, array $data): array
    {
        try {
            $existingProduct = $this->productRepository->getProductByIdAdmin($productId);

            if (!$existingProduct) {
                throw new NotFoundException('Product not found.', context: ['productId' => $productId]);
            }

            $updatedProduct = $this->productRepository->partialUpdateProduct($productId, $data);

            if (!$updatedProduct) {
                throw new DatabaseException('No changes applied or update failed.');
            }

            return [
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $updatedProduct->toArray(),
                'code' => 200,
                'context' => []
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (DatabaseException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId, 'input' => $data]
            ];
        }
    }

    /**
     * Update product stock to specific quantity.
     *
     * @param int $productId Product ID.
     * @param int $quantity  New stock quantity.
     *
     * @return array{success: bool, message: string, data: null, code: int, context: array}
     */
    public function updateStock(int $productId, int $quantity): array
    {
        try {
            if ($quantity < 0) {
                return [
                    'success' => false,
                    'message' => 'Stock quantity cannot be negative',
                    'data' => null,
                    'code' => 400,
                    'context' => []
                ];
            }

            $existingProduct = $this->productRepository->getProductByIdAdmin($productId);

            if (!$existingProduct) {
                throw new NotFoundException('Product not found.', context: ['productId' => $productId]);
            }

            $this->productRepository->updateStock($productId, $quantity);

            return [
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => null,
                'code' => 200,
                'context' => ['productId' => $productId, 'quantity' => $quantity]
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (DatabaseException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to update stock: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId]
            ];
        }
    }

    /**
     * Decrement product stock (for order operations).
     *
     * @param int $productId Product ID.
     * @param int $quantity  Quantity to decrement.
     *
     * @return array{success: bool, message: string, data: null, code: int, context: array}
     */
    public function decrementStock(int $productId, int $quantity): array
    {
        try {
            if ($quantity <= 0) {
                return [
                    'success' => false,
                    'message' => 'Quantity must be greater than zero',
                    'data' => null,
                    'code' => 400,
                    'context' => []
                ];
            }

            $this->productRepository->decrementStock($productId, $quantity);

            return [
                'success' => true,
                'message' => 'Stock decremented successfully',
                'data' => null,
                'code' => 200,
                'context' => ['productId' => $productId, 'quantity' => $quantity]
            ];
        } catch (DatabaseException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to decrement stock: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId, 'quantity' => $quantity]
            ];
        }
    }

    /**
     * Increment product stock (for returns/restocking).
     *
     * @param int $productId Product ID.
     * @param int $quantity  Quantity to increment.
     *
     * @return array{success: bool, message: string, data: null, code: int, context: array}
     */
    public function incrementStock(int $productId, int $quantity): array
    {
        try {
            if ($quantity <= 0) {
                return [
                    'success' => false,
                    'message' => 'Quantity must be greater than zero',
                    'data' => null,
                    'code' => 400,
                    'context' => []
                ];
            }

            $this->productRepository->incrementStock($productId, $quantity);

            return [
                'success' => true,
                'message' => 'Stock incremented successfully',
                'data' => null,
                'code' => 200,
                'context' => ['productId' => $productId, 'quantity' => $quantity]
            ];
        } catch (DatabaseException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to increment stock: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId, 'quantity' => $quantity]
            ];
        }
    }

    /**
     * Soft delete a product (mark as inactive).
     *
     * @param int $productId Product ID to delete.
     *
     * @return array{success: bool, message: string, data: null, code: int, context: array}
     */
    public function deleteProduct(int $productId): array
    {
        try {
            $existingProduct = $this->productRepository->getProductByIdAdmin($productId);

            if (!$existingProduct) {
                throw new NotFoundException('Product not found.', context: ['productId' => $productId]);
            }

            $deleted = $this->productRepository->deleteProduct($productId);

            if (!$deleted) {
                throw new DatabaseException('Product not found or already deleted.', context: ['productId' => $productId]);
            }

            return [
                'success' => true,
                'message' => 'Product deleted successfully',
                'data' => null,
                'code' => 200,
                'context' => []
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (DatabaseException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId]
            ];
        }
    }

    /**
     * Restore a soft-deleted product.
     *
     * @param int $productId Product ID to restore.
     *
     * @return array{success: bool, message: string, data: null, code: int, context: array}
     */
    public function restoreProduct(int $productId): array
    {
        try {
            $existingProduct = $this->productRepository->getProductByIdAdmin($productId);

            if (!$existingProduct) {
                throw new NotFoundException('Product not found.', context: ['productId' => $productId]);
            }

            $restored = $this->productRepository->restoreProduct($productId);

            if (!$restored) {
                throw new DatabaseException('Product restoration failed.', context: ['productId' => $productId]);
            }

            return [
                'success' => true,
                'message' => 'Product restored successfully',
                'data' => null,
                'code' => 200,
                'context' => []
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (DatabaseException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to restore product: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId]
            ];
        }
    }

    /**
     * Permanently delete a product from database.
     *
     * @param int $productId Product ID to permanently delete.
     *
     * @return array{success: bool, message: string, data: null, code: int, context: array}
     */
    public function hardDeleteProduct(int $productId): array
    {
        try {
            $existingProduct = $this->productRepository->getProductByIdAdmin($productId);

            if (!$existingProduct) {
                throw new NotFoundException('Product not found.', context: ['productId' => $productId]);
            }

            $deleted = $this->productRepository->hardDeleteProduct($productId);

            if (!$deleted) {
                throw new DatabaseException('Product not found or already deleted.', context: ['productId' => $productId]);
            }

            return [
                'success' => true,
                'message' => 'Product permanently deleted successfully',
                'data' => null,
                'code' => 200,
                'context' => []
            ];
        } catch (NotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (DatabaseException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => 'Failed to permanently delete product: ' . $e->getMessage(),
                'data' => null,
                'code' => 500,
                'context' => ['productId' => $productId]
            ];
        }
    }
}
?>