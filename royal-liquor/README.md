# 🥃 Royal Liquor - Premium E-Commerce Platform

A **production-grade e-commerce application** built strictly with **Vanilla PHP** and **Pure JavaScript** — demonstrating mastery of web fundamentals through custom-built architecture.

> 🚫 **No Frameworks**. No Laravel. No React. No jQuery. Just pure, foundational engineering.

---

## ⚡ Why This Project Matters

This isn't a tutorial project. It's a **full-featured e-commerce system** that implements the same design patterns used by enterprise frameworks — but built from scratch to prove deep understanding of:

- **MVC Architecture** with Service & Repository layers
- **Dependency Injection Container** using PHP's Reflection API
- **Custom Validation Engine** with composable validation hooks
- **AJAX-First Frontend** with centralized API management
- **Real-time Features**: Cart sync, stock validation, toast notifications

---

## 🔧 Core Design Patterns

### 🔁 Singleton Pattern — Database Connection

```php
// core/Database.php
class Database {
    private static ?PDO $pdo = null;  // Single instance

    public static function getPdo(): PDO {
        if (self::$pdo === null) {
            // Lazy initialization — only connect when needed
            $config = require __DIR__ . '/../config/config.php';
            $dsn = "pgsql:host={$host};port={$port};dbname={$db};";
            
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,  // Native prepared statements
            ]);
        }
        return self::$pdo;
    }
}
```

**Benefits:**
- ✅ **One connection per request** — no accidental connection leaks
- ✅ **Lazy loading** — connection only opened when first query runs
- ✅ **Global access** — any class can call `Database::getPdo()`

---

### 🔗 Middleware Pipeline Pattern

Custom middleware chain implementing the **Chain of Responsibility** pattern:

```php
// core/MiddlewareStack.php
class MiddlewareStack {
    private array $middleware = [];

    public function add(MiddlewareInterface $middleware): void {
        $this->middleware[] = $middleware;
    }

    public function process(Request $request, callable $finalHandler): Response {
        $next = $this->createNext($this->middleware, $finalHandler);
        return $next($request);  // Start the chain
    }

    private function createNext(array $middleware, callable $finalHandler): callable {
        if (empty($middleware)) return $finalHandler;

        $current = array_shift($middleware);
        return fn(Request $request) => $current->handle(
            $request,
            $this->createNext($middleware, $finalHandler)
        );
    }
}
```

**Middleware Inventory:**

| Middleware | Purpose |
|------------|---------|
| `AuthMiddleware` | Validates session & user permissions |
| `CSRFMiddleware` | Token validation on state-changing requests |
| `JsonMiddleware` | Sets `Content-Type: application/json` |
| `CorsMiddleware` | Cross-origin resource sharing headers |
| `RateLimitMiddleware` | Request throttling per session |

---

### ⏱️ Rate Limiting

Session-based sliding window rate limiter:

```php
// admin/middleware/RateLimitMiddleware.php
class RateLimitMiddleware {
    private static int $maxRequests = 3;
    private static int $timeWindow = 60;  // seconds

    public static function check(string $key): void {
        $session = Session::getInstance();
        $now = time();
        
        $rateData = $session->get("rate_limit:{$key}");
        $elapsed = $now - ($rateData['start_time'] ?? $now);
        
        if ($elapsed < self::$timeWindow) {
            if ($rateData['count'] >= self::$maxRequests) {
                self::emitLimitExceededResponse();  // 429 Too Many Requests
            }
            $rateData['count']++;
        } else {
            // Window expired — reset
            $rateData = ['count' => 1, 'start_time' => $now];
        }
        
        $session->set("rate_limit:{$key}", $rateData);
    }
}
```

**Features:**
- Per-endpoint rate limiting with custom keys
- Configurable limits (`configure($maxRequests, $timeWindow)`)
- Clean 429 JSON response with retry guidance
- Session-based (no external Redis/Memcached needed)

---

### 🔐 CSRF Protection

Session-based token generation with timing-safe validation:

```php
// core/CSRF.php
class CSRF {
    private int $csrfTokenLength = 32;  // bytes

    public function generateToken(): string {
        $token = bin2hex(random_bytes($this->csrfTokenLength));
        $this->session->set('csrf_token', $token);
        return $token;
    }

    public function getToken(): string {
        $token = $this->session->get('csrf_token');
        if (!$token) $token = $this->generateToken();
        return $token;
    }

    public function validateToken(string $token): bool {
        $currentToken = $this->getToken();
        // Timing-safe comparison to prevent timing attacks
        return hash_equals($currentToken, $token);
    }
}
```

**Middleware Integration:**
```php
// admin/middleware/CSRFMiddleware.php
class CSRFMiddleware {
    public static function verifyCsrf(): void {
        $csrf = Session::getInstance()->getCsrfInstance();
        
        // Accept token from header OR POST body
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] 
              ?? $_POST['csrf_token'] 
              ?? null;

        if (!$token || !$csrf->validateToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }
    }
}
```

**Frontend Token Injection** (automatic for all POST/PUT/DELETE):
```javascript
// public/utils/api-helper.js
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content');
    
    // Fallback to cookie
    const match = document.cookie.match(/csrf_token=([^;]+)/);
    return match ? match[1] : '';
}

// Automatically added to mutating requests
if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
    headers['X-CSRF-Token'] = getCsrfToken();
}
```

---

### 📦 Standardized JSON Response

Every API response follows a consistent structure via the `Response` class:

```php
// core/Response.php
class Response {
    // Success response
    public static function success(string $message, mixed $data = null, int $code = 200): self {
        return (new self())->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    // Error response
    public static function error(string $message, array $errors = [], int $code = 400): self {
        return (new self())->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    // Paginated response
    public static function paginated(array $items, int $total, int $limit, int $offset): self {
        return (new self())->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'count' => count($items)
            ]
        ]);
    }
}
```

**Semantic Response Methods:**

| Method | HTTP Code | Use Case |
|--------|-----------|----------|
| `Response::success()` | 200 | Standard success |
| `Response::created()` | 201 | Resource created |
| `Response::noContent()` | 204 | Delete success |
| `Response::error()` | 400 | Bad request |
| `Response::unauthorized()` | 401 | Auth required |
| `Response::forbidden()` | 403 | Permission denied |
| `Response::notFound()` | 404 | Resource not found |
| `Response::validationError()` | 422 | Validation failed |
| `Response::conflict()` | 409 | Duplicate resource |
| `Response::tooManyRequests()` | 429 | Rate limited |
| `Response::serverError()` | 500 | Server error |

---

### 🌐 Centralized API Client

Single point of contact for all frontend-backend communication:

```javascript
// public/utils/api-helper.js
export const API = {
    baseUrl: '/royal-liquor/api/v1',

    // Product endpoints
    products: {
        list: (params) => apiRequest('/products' + buildQuery(params)),
        get: (id) => apiRequest('/products/' + id),
        search: (query) => apiRequest('/products/search?q=' + query),
    },

    // Cart endpoints
    cart: {
        get: (id) => apiRequest('/cart/' + id),
        addItem: (data) => apiRequest('/cart-items', { method: 'POST', body: data }),
        updateItem: (id, data) => apiRequest('/cart-items/' + id, { method: 'PUT', body: data }),
        removeItem: (id) => apiRequest('/cart-items/' + id, { method: 'DELETE' }),
    },

    // Orders, Users, Addresses, Recipes, Flavors, Recognition...
    // 15+ domain modules with full CRUD
};
```

**Automatic Features:**
- ✅ **CSRF Token Injection** — Added to all POST/PUT/DELETE
- ✅ **JSON Serialization** — Objects auto-serialized to JSON body
- ✅ **Error Handling** — Throws on non-success responses
- ✅ **Session Cookies** — `credentials: 'include'` for auth
- ✅ **Content-Type Headers** — `application/json` by default

**Usage Example:**
```javascript
// List products with filtering
const products = await API.products.list({ category_id: 5, limit: 20 });

// Add item to cart (CSRF token auto-injected)
await API.cart.addItem({ product_id: 123, quantity: 2 });

// Search recipes
const recipes = await API.recipes.search('margarita');
```

---

## 🛤️ Routing Architecture

### Backend: Custom Regex Router

```php
// core/Router.php
class Router {
    private array $routes = [];

    public function get(string $path, callable $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void {
        // Convert :param syntax to named regex groups
        // /products/:id → /products/(?P<id>[^/]+)
        $pattern = preg_replace_callback(
            '#:([a-zA-Z_][a-zA-Z0-9_]*)#',
            fn($m) => '(?P<' . $m[1] . '>[^/]+)',
            $path
        );
        
        $this->routes[$method][] = [
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): mixed {
        foreach ($this->routes[$request->getMethod()] as $route) {
            if (preg_match($route['pattern'], $request->getUri(), $matches)) {
                return $route['handler']($request, $matches);
            }
        }
        return null;  // 404
    }
}
```

**Route Registration:**
```php
$router->group('/api/v1', function($r) {
    $r->get('/products', fn($req) => $productController->getAll($req));
    $r->get('/products/:id', fn($req, $p) => $productController->getById((int)$p['id']));
    $r->post('/products', fn($req) => $productController->create($req));
    $r->put('/products/:id', fn($req, $p) => $productController->update((int)$p['id'], $req));
    $r->delete('/products/:id', fn($req, $p) => $productController->delete((int)$p['id']));
});
```

**Features:**
- Named parameter capture (`:id`, `:slug`)
- Route grouping with prefixes
- Reflection-based handler introspection
- Full HTTP method support (GET, POST, PUT, DELETE)

---

## ⚡ SPA-Like Experience Without React

The admin panel achieves **Single Page Application behavior** using **pure JavaScript** with hash-based routing:

### Frontend Router (`admin/js/router.js`)

```javascript
import { render } from "./render.js";
import { saveState, getState } from "./utils.js";

const pages = [
    { page: "overview", path: "overview" },
    { page: "Products", path: "products" },
    { page: "Orders", path: "orders" },
    { page: "Users", path: "users" },
    // ... 18+ pages
];

let currentPage = getState('admin:lastPage', 'overview');

const navigate = (pagePath) => {
    if (currentPage !== pagePath) {
        currentPage = pagePath;
        saveState('admin:lastPage', currentPage);  // Persist to localStorage
        updateBreadcrumb(currentPage);
        updateActiveLink(currentPage);
        render(currentPage, mainElement);          // Dynamic content swap
        history.pushState({}, '', `#${currentPage}`);
    }
};

// Event delegation for menu clicks
menuElement.addEventListener('click', (event) => {
    event.preventDefault();
    const link = event.target.closest("a");
    if (link) navigate(link.getAttribute('data-page'));
});
```

### How It Works

1. **Single HTML Shell**: `admin/index.php` loads once with sidebar, header, and empty `<main id="content">`
2. **Hash-Based Routing**: URLs like `#products`, `#orders` trigger page swaps
3. **Dynamic Content Loading**: `render()` function fetches and injects page HTML
4. **State Persistence**: Last visited page saved to `localStorage`
5. **No Full Page Reloads**: Smooth transitions, instant navigation

---

## 💾 State Management with localStorage

Custom "hooks-like" pattern for state persistence:

### Storage Utilities (`admin/js/utils.js`)

```javascript
// Save state to localStorage
export function saveState(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
}

// Get state with fallback default
export function getState(key, defaultValue) {
    const stored = localStorage.getItem(key);
    return stored ? JSON.parse(stored) : defaultValue;
}
```

### Cart Storage Module (`public/utils/cart-storage.js`)

```javascript
const CART_STORAGE_KEY = 'cart';

export function getCart() {
    const cartData = localStorage.getItem(CART_STORAGE_KEY);
    return cartData ? JSON.parse(cartData) : [];
}

export async function addItemToCart(productId, quantity = 1) {
    const cart = getCart();
    const existingItem = cart.find(item => Number(item.id) === Number(productId));
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        const product = await fetchProduct(productId);
        cart.push({ ...product, quantity });
    }
    
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    return true;
}

export function getCartItemCount() {
    return getCart().reduce((total, item) => total + item.quantity, 0);
}
```

### Storage Modules

| Module | Purpose |
|--------|---------|
| `cart-storage.js` | Shopping cart state |
| `wishlist-storage.js` | Saved products |
| `preferences-storage.js` | User preferences, taste profile |
| `utils.js` | Admin panel page state |

---

## 📦 FIFO Stock Deduction

Stock is deducted using **First-In-First-Out** logic across multiple warehouses:

### Warehouse Selection Algorithm

```php
// StockRepository.php — reserveStock()
public function reserveStock(int $orderId): void {
    $this->pdo->beginTransaction();
    
    foreach ($orderItems as $item) {
        // Find warehouse with MOST available stock (ensures FIFO rotation)
        $stockStmt = $this->pdo->prepare("
            SELECT id, warehouse_id FROM stock 
            WHERE product_id = :product_id 
            AND (quantity - reserved) >= :quantity
            ORDER BY (quantity - reserved) DESC  -- Prioritize oldest/fullest
            LIMIT 1
            FOR UPDATE  -- Lock row to prevent race conditions
        ");
        
        // Reserve the stock
        $this->pdo->prepare("
            UPDATE stock SET reserved = reserved + :quantity
            WHERE id = :stock_id
        ")->execute([...]);
        
        // Track which warehouse fulfills this item
        $this->pdo->prepare("
            UPDATE order_items SET warehouse_id = :warehouse_id
            WHERE order_id = :order_id AND product_id = :product_id
        ")->execute([...]);
    }
    
    $this->pdo->commit();
}
```

### Stock Lifecycle

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ Order       │────▶│ Reserve     │────▶│ Payment     │────▶│ Deduct      │
│ Created     │     │ Stock       │     │ Confirmed   │     │ Stock       │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
                          │                                       │
                          ▼                                       ▼
                    reserved++                          quantity -= reserved
                                                        reserved -= reserved
```

### Order State Handlers

| Method | Action |
|--------|--------|
| `reserveStock($orderId)` | Mark stock as reserved (pending orders) |
| `confirmPayment($orderId)` | Deduct reserved stock from quantity |
| `cancelOrder($orderId)` | Release reserved stock back to available |
| `refundOrder($orderId)` | Restore quantity (returned items) |

---

## 🛡️ Dropdown-Based Data Integrity

Instead of free-text input for foreign keys, we use **server-populated dropdowns** to prevent invalid data:

### Pattern

```javascript
// When creating a product, category_id comes from dropdown
async function loadCategoryDropdown(selectElement) {
    const categories = await API.get('/api/categories');
    selectElement.innerHTML = categories.map(c => 
        `<option value="${c.id}">${c.name}</option>`
    ).join('');
}

// Form submission sends valid category_id
const productData = {
    name: formData.get('name'),
    category_id: parseInt(formData.get('category_id')),  // Always valid FK
    supplier_id: parseInt(formData.get('supplier_id')),  // Always valid FK
};
```

### Benefits

| Problem | Solution |
|---------|----------|
| Invalid `category_id` | Dropdown only shows existing categories |
| Typos in supplier name | Select from existing suppliers |
| Race conditions | Fresh data loaded on form open |
| Foreign key violations | Impossible — only valid IDs selectable |

### Where Used

- **Products**: Category, Supplier selection
- **Stock**: Product, Warehouse selection
- **Order Items**: Product selection
- **Recipe Ingredients**: Recipe, Product selection
- **Addresses**: User selection

---

## 🏗️ Architecture: MVC + Service + Repository

We follow a **strict 4-layer separation** to ensure clean, maintainable code:

```
┌─────────────────────────────────────────────────────┐
│                   CONTROLLERS                        │
│   (admin/controllers/*.php)                          │
│   • Receives HTTP request & validates input          │
│   • Delegates to Services                            │
│   • Returns JSON responses                           │
│   • ❌ NO business logic                             │
└─────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────┐
│                    SERVICES                          │
│   (admin/services/*.php)                             │
│   • Contains ALL business logic                      │
│   • Orchestrates Repositories                        │
│   • Calls Validators before operations               │
│   • Examples: CartService, FlavorProfileService      │
└─────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────┐
│                  REPOSITORIES                        │
│   (admin/repositories/*.php)                         │
│   • Pure SQL queries (PostgreSQL-optimized)          │
│   • Returns Domain Models                            │
│   • ❌ NO business logic                             │
│   • Examples: ProductRepository, OrderRepository     │
└─────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────┐
│                    DATABASE                          │
│   PostgreSQL (JSONB, Foreign Keys, Complex Joins)   │
└─────────────────────────────────────────────────────┘
```

### 📁 Layer Statistics
| Layer | Count | Location |
|-------|-------|----------|
| **Controllers** | 25 | `admin/controllers/` |
| **Services** | 26+ | `admin/services/` |
| **Repositories** | 21 | `admin/repositories/` |
| **Validators** | 21 | `admin/validators/` |
| **Models** | 23 | `admin/models/` |

---

## ✅ Validation Engine (WIP)

Instead of scattered `if/else` checks, we built a **declarative validation system** inspired by Laravel's validation:

### Core Components

```php
// admin/validators/Validator.php
interface ValidatorInterface {
    public static function validateCreate(array $data): void;
    public static function validateUpdate(array $data): void;
}

final class ValidationRunner {
    // Runs multiple validation hooks and aggregates errors
    public static function run(array $hooks, array $data, string $message): void;
}
```

### Example: Address Validation

```php
// admin/validators/AddressValidator.php
class AddressValidator implements ValidatorInterface {
    public static function validateCreate(array $data): void {
        ValidationRunner::run([
            fn($d) => self::validateStreet($d),
            fn($d) => self::validateCity($d),
            fn($d) => self::validateState($d),
            fn($d) => self::validateZipCode($d),
        ], $data, 'Address validation failed');
    }
}
```

### Validation Coverage

| Domain | Validators |
|--------|-----------|
| **Users & Auth** | `UserValidator`, `AuthValidator` |
| **Products** | `ProductValidator`, `FlavorProfileValidator` |
| **Orders** | `OrderValidator`, `OrderItemValidator`, `PaymentValidator` |
| **Cart** | `CartValidator`, `CartItemValidator` |
| **Content** | `CategoryValidator`, `CocktailRecipeValidator`, `FeedbackValidator` |
| **Inventory** | `StockValidator`, `WarehouseValidator`, `SupplierValidator` |

---

## �️ Domain Models

Typed PHP 8.2 models with strict constructor promotion and serialization:

### Model Design Pattern

```php
// admin/models/ProductModel.php
class ProductModel
{
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?string $slug = null,
        private ?string $description = null,
        private ?int $price_cents = null,      // Money stored in cents
        private ?string $image_url = null,
        private ?int $category_id = null,
        private ?int $supplier_id = null,
        private bool $is_active = true,
        private ?string $created_at = null,
        private ?string $updated_at = null,
        private ?string $deleted_at = null     // Soft delete support
    ) {}

    // Typed getters for each property
    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    // ...

    // Array serialization for API responses
    public function toArray(): array { /* ... */ }
}
```

### Model Inventory (23 Models)

| Domain | Models |
|--------|--------|
| **Users & Auth** | `UserModel`, `UserPreferenceModel`, `AddressModel` |
| **Products** | `ProductModel`, `CategoryModel`, `FlavorProfileModel` |
| **Orders** | `OrderModel`, `OrderItemModel`, `PaymentModel` |
| **Cart** | `CartModel`, `CartItemModel` |
| **Inventory** | `StockModel`, `WarehouseModel`, `SupplierModel` |
| **Content** | `CocktailRecipeModel`, `RecipeIngredientModel`, `FeedbackModel` |
| **AI/ML** | `ProductRecognitionModel` |
| **System** | `AuditLogModel`, `BaseModel` |

### Key Model Features

- **Money in Cents**: All monetary values stored as integers (`price_cents`, `total_cents`) to avoid floating-point errors
- **Soft Deletes**: `deleted_at` column for reversible deletions
- **Timestamps**: `created_at`, `updated_at` on all tables
- **Nullable Types**: Proper PHP 8 nullable type hints (`?int`, `?string`)

---

## 📊 Database Indexing Strategy

Carefully optimized indexes for query performance on PostgreSQL:

### Index Types & Patterns

```sql
-- Partial indexes (filter by active/deleted)
CREATE INDEX idx_users_email_active ON users(email) 
    WHERE is_active = TRUE AND deleted_at IS NULL;

CREATE INDEX idx_products_active ON products(id) 
    WHERE is_active = TRUE AND deleted_at IS NULL;

-- Composite indexes for common queries
CREATE INDEX idx_orders_user_date ON orders(user_id, created_at DESC);
CREATE INDEX idx_orders_status_date ON orders(status, created_at DESC);

-- Unique partial indexes (only one active cart per user/session)
CREATE UNIQUE INDEX idx_carts_active_user ON carts(user_id) 
    WHERE status = 'active';
CREATE UNIQUE INDEX idx_carts_active_session ON carts(session_id) 
    WHERE status = 'active';

-- Low stock alerting index
CREATE INDEX idx_stock_low ON stock(quantity ASC) 
    WHERE quantity < 50;

-- Flavor profile indexes for recommendation queries
CREATE INDEX idx_flavor_sweetness ON flavor_profiles(sweetness);
CREATE INDEX idx_flavor_strength ON flavor_profiles(strength);
```

### Index Summary

| Category | Indexes | Purpose |
|----------|---------|---------|
| **Users** | `idx_users_email_active` | Fast login lookups |
| **Products** | `idx_products_active`, `idx_products_category`, `idx_products_price`, `idx_products_slug_active`, `idx_products_name` | Catalog queries |
| **Stock** | `idx_stock_available`, `idx_stock_low` | Inventory management |
| **Carts** | `idx_carts_active_user`, `idx_carts_active_session` | Cart uniqueness |
| **Orders** | `idx_orders_user_date`, `idx_orders_status_date` | Order history |
| **Feedback** | `idx_feedback_product_active` | Product reviews |
| **Flavors** | `idx_flavor_sweetness`, `idx_flavor_strength` | Recommendations |

---

## 🪞 Denormalized Admin Views

**36 precomputed PostgreSQL views** for optimized admin dashboard queries:

### View Pattern: List vs Detail

Each entity has two views:
- **`admin_list_*`**: Lightweight, paginated data for tables
- **`admin_detail_*`**: Rich, denormalized data for modals/detail pages

```sql
-- List view: Fast, minimal columns
CREATE VIEW admin_list_products AS
SELECT 
    p.id, p.name, p.slug, p.price_cents,
    cat.name as category_name,
    sup.name as supplier_name,
    p.is_active, p.created_at,
    (SELECT COALESCE(SUM(s.quantity - s.reserved), 0) 
     FROM stock s WHERE s.product_id = p.id) as available_stock,
    (SELECT AVG(rating) FROM feedback f 
     WHERE f.product_id = p.id AND f.is_active = TRUE) as avg_rating
FROM products p
LEFT JOIN categories cat ON p.category_id = cat.id
LEFT JOIN suppliers sup ON p.supplier_id = sup.id
WHERE p.deleted_at IS NULL;
```

```sql
-- Detail view: Rich nested data as JSON
CREATE VIEW admin_detail_products AS
SELECT 
    p.*, cat.name as category_name, sup.name as supplier_name,
    
    -- Stock by warehouse as JSON array
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT w.name as warehouse_name, s.quantity, s.reserved
        FROM stock s JOIN warehouses w ON s.warehouse_id = w.id
        WHERE s.product_id = p.id
    ) t) as stock_by_warehouse,
    
    -- Flavor profile as JSON object
    (SELECT row_to_json(fp) FROM flavor_profiles fp 
     WHERE fp.product_id = p.id) as flavor_profile,
    
    -- Recent orders as JSON array
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT o.order_number, oi.quantity, o.created_at
        FROM order_items oi JOIN orders o ON oi.order_id = o.id
        WHERE oi.product_id = p.id
        ORDER BY o.created_at DESC LIMIT 10
    ) t) as recent_orders
FROM products p...;
```

### Complete View Inventory

| Entity | List View | Detail View |
|--------|-----------|-------------|
| **Users** | `admin_list_users` | `admin_detail_users` |
| **Products** | `admin_list_products` | `admin_detail_products` |
| **Categories** | `admin_list_categories` | `admin_detail_categories` |
| **Orders** | `admin_list_orders` | `admin_detail_orders` |
| **Order Items** | `admin_list_order_items` | `admin_detail_order_items` |
| **Carts** | `admin_list_carts` | `admin_detail_carts` |
| **Cart Items** | `admin_list_cart_items` | `admin_detail_cart_items` |
| **Payments** | `admin_list_payments` | `admin_detail_payments` |
| **Stock** | `admin_list_stock` | `admin_detail_stock` |
| **Warehouses** | `admin_list_warehouses` | `admin_detail_warehouses` |
| **Suppliers** | `admin_list_suppliers` | `admin_detail_suppliers` |
| **Addresses** | `admin_list_user_addresses` | `admin_detail_user_addresses` |
| **Feedback** | `admin_list_feedback` | `admin_detail_feedback` |
| **Recipes** | `admin_list_cocktail_recipes` | `admin_detail_cocktail_recipes` |
| **Ingredients** | `admin_list_recipe_ingredients` | `admin_detail_recipe_ingredients` |
| **Preferences** | `admin_list_user_preferences` | `admin_detail_user_preferences` |
| **Flavor Profiles** | `admin_list_flavor_profiles` | `admin_detail_flavor_profiles` |
| **Recognition** | `admin_list_product_recognition` | `admin_detail_product_recognition` |

### Key Denormalization Techniques

- **Precomputed Aggregates**: `order_count`, `lifetime_value_cents`, `avg_rating`
- **Nested JSON**: Complex related data embedded as JSONB for single-query retrieval
- **Computed Fields**: `available_stock = quantity - reserved`
- **Historical Snapshots**: `price_at_add_cents` in cart items preserves original prices

---

## 📜 Audit Logging & Soft Deletes

### Soft Delete Pattern

All major entities support soft deletes:

```sql
-- Tables with deleted_at column
users             -- deleted_at TIMESTAMPTZ
user_addresses    -- deleted_at TIMESTAMPTZ
categories        -- deleted_at TIMESTAMPTZ
products          -- deleted_at TIMESTAMPTZ
suppliers         -- deleted_at TIMESTAMPTZ
warehouses        -- deleted_at TIMESTAMPTZ
feedback          -- deleted_at TIMESTAMPTZ
cocktail_recipes  -- deleted_at TIMESTAMPTZ
```

### Temporal Columns

Orders track complete lifecycle:

```sql
CREATE TABLE orders (
    -- ...
    created_at    TIMESTAMPTZ DEFAULT NOW(),
    updated_at    TIMESTAMPTZ DEFAULT NOW(),
    paid_at       TIMESTAMPTZ,    -- When payment captured
    shipped_at    TIMESTAMPTZ,    -- When shipped
    delivered_at  TIMESTAMPTZ,    -- When delivered
    cancelled_at  TIMESTAMPTZ     -- If cancelled
);

CREATE TABLE carts (
    -- ...
    created_at    TIMESTAMPTZ DEFAULT NOW(),
    updated_at    TIMESTAMPTZ DEFAULT NOW(),
    converted_at  TIMESTAMPTZ,    -- When converted to order
    abandoned_at  TIMESTAMPTZ     -- When marked abandoned
);
```

### User Activity Tracking

```sql
-- User login tracking
users.last_login_at TIMESTAMPTZ

-- User anonymization (GDPR compliance)
users.is_anonymized BOOLEAN DEFAULT FALSE
users.anonymized_at TIMESTAMPTZ
```

### Audit Service (Planned)

```php
// admin/services/AuditService.php (WIP)
class AuditService {
    // Log all admin actions
    public function logAction(string $action, array $context): void;
    
    // Track data changes
    public function logChange(string $entity, int $id, array $before, array $after): void;
}
```

---

## �📡 AJAX-First Architecture

The frontend is built with **ES Modules** and communicates with the backend entirely via AJAX:

### Centralized API Client (`public/utils/api-helper.js`)

```javascript
// Single point of contact for all API calls
import { API } from './api-helper.js';

// Automatic CSRF token injection
// Automatic error handling & toast notifications
// Consistent response parsing

const products = await API.get('/api/products', { category: 'whiskey' });
await API.post('/api/cart/items', { product_id: 123, quantity: 2 });
```

### Frontend Utility Modules

| Module | Purpose |
|--------|---------|
| `api-helper.js` | Centralized API client with error handling |
| `cart.js` / `cart-storage.js` | Cart state management & persistence |
| `cart-slide-in.js` | Slide-in cart panel with animations |
| `wishlist.js` / `wishlist-storage.js` | Wishlist functionality |
| `toast.js` | Toast notification system |
| `quick-view-modal.js` | Product quick-view overlay |
| `search.js` | Real-time search with debouncing |
| `ui.js` | Shared UI utilities (16KB of UI logic) |
| `preferences-storage.js` | User preference persistence |

---

## 🖼️ Image Handling System

Robust server-side image processing with security validations:

### ImageService (`admin/services/ImageService.php`)

```php
class ImageService {
    // Secure file uploads with:
    // ✓ MIME type validation (jpeg, png, webp, gif)
    // ✓ File size limits (configurable, default 5MB)
    // ✓ Unique filename generation (random hashes)
    // ✓ Organized storage by entity type
    
    public function upload(string $entity, array $file): array {
        // Returns: url, original_name, mime, size
    }
}
```

### Features
- **Entity-based organization**: `/storage/user/images/`, `/storage/product/images/`
- **Secure naming**: `product_a1b2c3d4e5f6...hash.jpg` (prevents collisions & guessing)
- **Error mapping**: PHP upload errors translated to user-friendly messages

---

## 👤 User Profiles & Account Management

Complete user account system at `/public/myaccount/`:

| Page | Features |
|------|----------|
| **Dashboard** (`index.php`) | Overview, recent orders, quick stats |
| **Orders** (`orders.php`) | Order history, status tracking, reorder |
| **Addresses** (`addresses.php`) | Address book, default address management |
| **Wishlist** (`wishlist.php`) | Saved products, quick add-to-cart |
| **Taste Profile** (`taste-profile.php`) | Flavor preferences, recommendations |
| **Activity** (`activity.php`) | Account activity log |
| **Profile** (`profile.php`) | Personal info, avatar upload |

### Layout System
- `_layout.php` / `_layout_end.php` — Shared navigation and footer
- Consistent UI across all account pages

---

## 🎯 Recommendations & Flavor Profiles

### Flavor Profile System

Products have rich flavor metadata stored in PostgreSQL:

```php
// FlavorProfileService.php
class FlavorProfileService {
    public function getByProductId(int $productId): array;
    public function search(string $query): array;
}
```

### Features
- **Radar Chart Visualization**: Sweetness, smokiness, body, etc.
- **Similar Product Matching**: Find products with similar flavor profiles
- **User Preference Learning**: Track what users like for personalization
- **"Products You Might Like"**: Recommendation engine based on purchase history

### Supporting Services
| Service | Purpose |
|---------|---------|
| `FlavorProfileService` | Manage product flavor data |
| `UserPreferenceService` | Track user taste preferences |
| `ProductRecognitionService` | AI-powered product identification (WIP) |

---

## 🍸 Cocktail Recipe System

Interactive cocktail recipes with smart features:

| Feature | Description |
|---------|-------------|
| **Recipe Browser** | Browse cocktails by category, difficulty, ingredients |
| **"I Can Make This"** | Filter recipes by ingredients user has purchased |
| **One-Click Cart** | Add all missing ingredients to cart |
| **Ingredient Mapping** | Links recipes to products in inventory |

### Services
- `CocktailRecipeService` — Recipe CRUD & search
- `RecipeIngredientService` — Ingredient-product mapping

---

## 🤖 AI Product Recognition (WIP)

Upload a photo of a bottle and find it in our catalog:

```php
// ProductRecognitionService.php
class ProductRecognitionService {
    // Database schema ready
    // API endpoints scaffolded
    // Next: Integration with Vision API (Google/AWS)
}
```

---

## 🔒 Security Implementation

| Feature | Implementation |
|---------|----------------|
| **CSRF Protection** | Token-based validation on all state-changing requests |
| **Password Hashing** | BCrypt via `password_hash()` |
| **Session Hardening** | Regeneration, HttpOnly cookies, secure flags |
| **Input Validation** | Server-side validation on all endpoints |
| **SQL Injection** | Prepared statements everywhere |
| **XSS Prevention** | Output escaping, Content-Security-Policy headers |

### Custom Exceptions
```php
// Typed exceptions for consistent error handling
ResourceNotFoundException::class  // 404
ValidationException::class        // 400 + field errors
AuthenticationException::class    // 401
DuplicateException::class         // 409
DatabaseException::class          // 500
```

---

## 🔧 Core Infrastructure

### Dependency Injection Container (`core/Container.php`)

```php
// Auto-wiring using Reflection API
$container->get(ProductController::class);
// Automatically resolves: Controller → Service → Repository → Database
```

### Custom Router (`core/Router.php`)

```php
// Regex-based routing with parameter capture
$router->get('/products/:id', fn(Request $r, array $p) => 
    $controller->getById((int)$p['id'])
);

$router->post('/api/cart/items', fn(Request $r) => 
    $cartController->addItem($r)
);
```

### Middleware Stack (`core/MiddlewareStack.php`)
- Authentication checks
- CSRF validation
- Request logging
- Error handling

---

## 📁 Project Structure

```
royal-liquor/
├── admin/                    # Backend API & Admin Panel
│   ├── api/                  # JSON API endpoints (23 files)
│   ├── controllers/          # Request handlers (25 files)
│   ├── services/             # Business logic (26+ files)
│   ├── repositories/         # Data access (21 files)
│   ├── validators/           # Input validation (21 files)
│   ├── models/               # Domain models (23 files)
│   ├── middleware/           # Request pipeline (5 files)
│   ├── exceptions/           # Typed exceptions (9 files)
│   ├── manage/               # Admin UI screens (39 files)
│   └── js/                   # Admin JavaScript (79 files)
│
├── public/                   # Customer-facing storefront
│   ├── utils/                # Frontend JS modules (20 files)
│   ├── components/           # Reusable UI components
│   ├── myaccount/            # User account pages
│   ├── auth/                 # Login/Register
│   ├── css/                  # Stylesheets
│   └── *.php                 # Store pages
│
├── core/                     # Framework-like utilities
│   ├── Container.php         # DI Container
│   ├── Router.php            # Regex Router
│   ├── Request.php           # HTTP Request wrapper
│   ├── Response.php          # HTTP Response builder
│   ├── Session.php           # Session management
│   ├── Database.php          # PostgreSQL singleton
│   └── MiddlewareStack.php   # Request pipeline
│
├── storage/                  # Uploaded files (images, etc.)
├── config/                   # Environment configuration
├── schema.sql                # Complete database schema
└── ARCHITECTURE.md           # Technical deep-dive
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.2+ (Strict Types) |
| **Database** | PostgreSQL (JSONB, Foreign Keys, Complex Joins) |
| **Frontend** | ES Modules (ES6+), Vanilla JavaScript |
| **Styling** | CSS Variables, Grid/Flexbox, Custom Components |
| **Server** | Apache with mod_rewrite (`.htaccess`) |

---

## 🚀 Setup & Installation

### 1. Database Setup
```bash
# Create PostgreSQL database
createdb royal_liquor

# Import schema
psql royal_liquor < schema.sql
```

### 2. Environment Configuration
```bash
cp .env.example .env
# Edit .env with your database credentials
```

### 3. Server Configuration
Point Apache/Nginx to the project root. Ensure `mod_rewrite` is enabled.

### 4. Access the Application
- **Store**: `http://localhost/royal-liquor/public/`
- **Admin**: `http://localhost/royal-liquor/admin/`

---

## 📊 Feature Status

| Feature | Status |
|---------|--------|
| Product Catalog & Search | ✅ Complete |
| Shopping Cart (AJAX) | ✅ Complete |
| Checkout Flow | ✅ Complete |
| User Authentication | ✅ Complete |
| Order Management | ✅ Complete |
| Address Book | ✅ Complete |
| Wishlist | ✅ Complete |
| Cocktail Recipes | ✅ Complete |
| Flavor Profiles | ✅ Complete |
| Admin Panel | ✅ Complete |
| Validation Engine | 🚧 WIP (Expanding) |
| AI Product Recognition | 🚧 WIP (Schema Ready) |
| User Recommendations | 🚧 WIP (Service Built) |

---

## 🎓 Learning Outcomes

Building this project demonstrates proficiency in:

- **Design Patterns**: MVC, Repository, Service Layer, Dependency Injection, Singleton
- **Database Design**: Normalization, indexing, JSONB optimization, complex queries
- **API Design**: RESTful conventions, consistent error responses, pagination
- **Security**: CSRF, XSS, SQL injection prevention, secure session management
- **Frontend Architecture**: Modular JavaScript, state management, AJAX patterns
- **PHP Mastery**: Strict types, interfaces, exceptions, Reflection API

---

<p align="center">
  <em>Built as an advanced learning project for full-stack web development patterns. took me 3 months and 3 refractors :)</em>
</p>
