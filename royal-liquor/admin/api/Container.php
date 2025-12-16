<?php
declare(strict_types=1);

/**
 * Simple Dependency Injection Container
 * Manages service instances and their dependencies
 */

class Container
{
    private array $instances = [];
    private array $bindings = [];

    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = function () use ($abstract, $concrete) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $concrete($this);
            }
            return $this->instances[$abstract];
        };
    }

    public function make(string $abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]($this);
        }

        if (class_exists($abstract)) {
            return new $abstract();
        }

        throw new Exception("No binding found for {$abstract}");
    }

    public function get(string $abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        return $this->make($abstract);
    }
}

/**
 * Service Provider for registering all dependencies
 */
class ApiServiceProvider
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        $this->registerSingletons();
        $this->registerRepositories();
        $this->registerServices();
        $this->registerControllers();
    }

    private function registerSingletons(): void
    {
        $this->container->singleton(Session::class, fn() => Session::getInstance());
    }

    private function registerRepositories(): void
    {
        $repositories = [
            ProductRepository::class,
            StockRepository::class,
            CategoryRepository::class,
            CartRepository::class,
            CartItemRepository::class,
            OrderRepository::class,
            OrderItemRepository::class,
            PaymentRepository::class,
            WarehouseRepository::class,
            SupplierRepository::class,
            AddressRepository::class,
            FeedbackRepository::class,
            FlavorProfileRepository::class,
            CocktailRecipeRepository::class,
            ProductRecognitionRepository::class,
            RecipeIngredientRepository::class,
            UserPreferenceRepository::class,
            AdminViewRepository::class
        ];

        foreach ($repositories as $repo) {
            $this->container->bind($repo, fn() => new $repo());
        }
    }

    private function registerServices(): void
    {
        $this->container->bind(ProductService::class, fn($c) => new ProductService($c->make(ProductRepository::class)));
        $this->container->bind(StockService::class, fn($c) => new StockService($c->make(StockRepository::class)));
        $this->container->bind(CategoryService::class, fn($c) => new CategoryService($c->make(CategoryRepository::class)));
        $this->container->bind(CartService::class, fn($c) => new CartService($c->make(CartRepository::class)));
        $this->container->bind(CartItemService::class, fn($c) => new CartItemService($c->make(CartItemRepository::class)));
        $this->container->bind(OrderService::class, fn($c) => new OrderService($c->make(OrderRepository::class)));
        $this->container->bind(OrderItemService::class, fn($c) => new OrderItemService($c->make(OrderItemRepository::class), $c->make(StockRepository::class)));
        $this->container->bind(PaymentService::class, fn($c) => new PaymentService($c->make(PaymentRepository::class)));
        $this->container->bind(WarehouseService::class, fn($c) => new WarehouseService($c->make(WarehouseRepository::class)));
        $this->container->bind(SupplierService::class, fn($c) => new SupplierService($c->make(SupplierRepository::class)));
        $this->container->bind(AddressService::class, fn($c) => new AddressService($c->make(AddressRepository::class)));
        $this->container->bind(FeedbackService::class, fn($c) => new FeedbackService($c->make(FeedbackRepository::class)));
        $this->container->bind(FlavorProfileService::class, fn($c) => new FlavorProfileService($c->make(FlavorProfileRepository::class)));
        $this->container->bind(CocktailRecipeService::class, fn($c) => new CocktailRecipeService($c->make(CocktailRecipeRepository::class)));
        $this->container->bind(ProductRecognitionService::class, fn($c) => new ProductRecognitionService($c->make(ProductRecognitionRepository::class)));
        $this->container->bind(RecipeIngredientService::class, fn($c) => new RecipeIngredientService($c->make(RecipeIngredientRepository::class)));
        $this->container->bind(UserPreferenceService::class, fn($c) => new UserPreferenceService($c->make(UserPreferenceRepository::class)));
        $this->container->bind(AdminViewService::class, fn($c) => new AdminViewService($c->make(AdminViewRepository::class)));
    }

    private function registerControllers(): void
    {
        $this->container->bind(ProductController::class, fn($c) => new ProductController($c->make(ProductService::class)));
        $this->container->bind(StockController::class, fn($c) => new StockController($c->make(StockService::class), $c->get(Session::class)));
        $this->container->bind(CategoryController::class, fn($c) => new CategoryController($c->make(CategoryService::class)));
        $this->container->bind(CartController::class, fn($c) => new CartController($c->make(CartService::class)));
        $this->container->bind(CartItemController::class, fn($c) => new CartItemController($c->make(CartItemService::class)));
        $this->container->bind(OrderController::class, fn($c) => new OrderController($c->make(OrderService::class), $c->get(Session::class)));
        $this->container->bind(OrderItemController::class, fn($c) => new OrderItemController($c->make(OrderItemService::class)));
        $this->container->bind(PaymentController::class, fn($c) => new PaymentController($c->make(PaymentService::class)));
        $this->container->bind(WarehouseController::class, fn($c) => new WarehouseController($c->make(WarehouseService::class)));
        $this->container->bind(SupplierController::class, fn($c) => new SupplierController($c->make(SupplierService::class)));
        $this->container->bind(AddressController::class, fn($c) => new AddressController($c->make(AddressService::class)));
        $this->container->bind(FeedbackController::class, fn($c) => new FeedbackController($c->make(FeedbackService::class)));
        $this->container->bind(FlavorProfileController::class, fn($c) => new FlavorProfileController($c->make(FlavorProfileService::class)));
        $this->container->bind(CocktailRecipeController::class, fn($c) => new CocktailRecipeController($c->make(CocktailRecipeService::class)));
        $this->container->bind(ProductRecognitionController::class, fn($c) => new ProductRecognitionController($c->make(ProductRecognitionService::class)));
        $this->container->bind(RecipeIngredientController::class, fn($c) => new RecipeIngredientController($c->make(RecipeIngredientService::class)));
        $this->container->bind(UserPreferenceController::class, fn($c) => new UserPreferenceController($c->make(UserPreferenceService::class)));
        $this->container->bind(AdminViewController::class, fn($c) => new AdminViewController($c->make(AdminViewService::class)));
    }
}
