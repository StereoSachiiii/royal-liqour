<?php
declare(strict_types=1);

require_once __DIR__.'/../repositories/ProductRepository.php';
require_once __DIR__.'/../../core/session.php';
require_once __DIR__.'/../models/ProductModel.php';

class ProductController {
    private ProductRepository $productRepository;
    private Session $session;

    public function __construct(ProductRepository $productRepository, Session $session) {
        $this->productRepository = $productRepository;
        $this->session = $session;
    }

    public function getAllProducts(int $limit, int $offset): array {
        if (!$this->session->get('is_admin')) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        try {
            $products = $this->productRepository->getAllProducts($limit, $offset);
            $productData = array_map(fn($product) => $product->toArray(), $products);

            return ['success' => true, 'data' => $productData];
        } catch (Exception $e) {
            error_log("Error retrieving products: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while fetching products.'];
        }
    }
}
