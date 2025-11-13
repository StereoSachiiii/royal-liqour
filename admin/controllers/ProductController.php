<?php
declare(strict_types=1);


require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../../core/Session.php'; // Fixed path: was missing '/'
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../validators/ProductValidator.php';
/**
 * ProductController manages CRUD operations for products.
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
     * Retrieve all products with pagination.
     *
     * @param int $limit  Maximum number of products to return (default: 50).
     * @param int $offset Pagination offset (default: 0).
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data: array<array{id: int, name: string, price: float, description: ?string, stock: int, created_at: string, updated_at: ?string}>,
     *     code: int,
     *     context: array
     * }
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
     * Retrieve a single product by its ID.
     *
     * @param int $productId The primary key of the product.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array{id: int, name: string, price: float, description: ?string, stock: int, created_at: string, updated_at: ?string},
     *     code: int,
     *     context: array
     * }
     */
    public function getProductById(int $productId): array
    {
        try {
            $product = $this->productRepository->getProductById($productId);

            if (!$product) {
                throw new NotFoundException('Product not found.', context: ['productId' => $productId]);
            }

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
     * Create a new product.
     *
     * @param array{
     *     name: string,
     *     price: float,
     *     description?: ?string,
     *     stock?: ?int
     * } $data Product creation data.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array{id: int, name: string, price: float, description: ?string, stock: int, created_at: string, updated_at: ?string},
     *     code: int,
     *     context: array
     * }
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
        }catch(DatabaseException $e){
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
     * Update an existing product.
     *
     * @param int $productId The product ID to update.
     * @param array{
     *     name?: ?string,
     *     price?: ?float,
     *     description?: ?string,
     *     stock?: ?int
     * } $data Fields to update (all optional).
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data?: array{id: int, name: string, price: float, description: ?string, stock: int, created_at: string, updated_at: ?string},
     *     code: int,
     *     context: array
     * }
     */
    public function updateProduct(int $productId, array $data): array
    {
        try {
            $existingProduct = $this->productRepository->getProductById($productId);
            if (!$existingProduct) {
                throw new NotFoundException('Product not found.', context: ['productId' => $productId]);
            }

            $updatedProduct = $this->productRepository->updateProduct(
                $productId,
                $data
            );

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
        }catch (DatabaseException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        }
         catch (RuntimeException $e) {
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
     * Soft or hard delete a product by ID.
     * @param int $productId The product ID to delete.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     data: null,
     *     code: int,
     *     context: array
     * }
     */
    public function deleteProduct(int $productId): array
    {
        try {
            $existingProduct = $this->productRepository->getProductById($productId);
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
        }catch(DatabaseException $e){
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        }
         catch (RuntimeException $e) {
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
     * Summary of hardDeleteProduct
     * @param int $productId
     * @throws \NotFoundException
     * @throws \DatabaseException
     * @return array{code: int, context: array, data: null, message: string, success: bool|array{code: int, context: array{productId: int}, data: null, message: string, success: bool}}
     */
    public function hardDeleteProduct(int $productId): array
    {
        try {
            $existingProduct = $this->productRepository->getProductById($productId);
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
        }catch(DatabaseException $e){
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
                'code' => $e->getStatusCode(),
                'context' => $e->getContext()
            ];
        }
         catch (RuntimeException $e) {
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