# System Architecture

This project is an exploration of **building robust software without frameworks**. Instead of using Laravel or Symfony, we built the core architecture components from scratch.

## 🧱 Core Design Patterns

### 1. The Regex Router (`admin/core/Router.php`)
A custom routing engine that matches URL patterns to Controller actions using Regular Expressions.
*   **Pattern**: `^/api/users/(\d+)$` maps to `UserController::get($id)`
*   **Feature**: Supports capturing parameters (IDs, slugs) directly from the URL.
*   **Method**: `Router::add('GET', '/path', $callback)`

```php
// Example Route Definition
$router->get('/products/:id', function(Request $request, array $params) {
    return $controller->getById((int)$params['id']);
});
```

### 2. Dependency Injection Container (`admin/core/Container.php`)
We implemented a **Service Container** to handle object instantiation and dependency management.
*   **Why?** To avoid `new Class()` spaghetti and enable easy testing/mocking.
*   **How?** It uses **ReflectionAPI** to auto-wire dependencies in constructors.

**Example Wiring:**
`Controller` depends on `Service` which depends on `Repository` which depends on `Database`.
The Container resolves this chain automatically:
```php
$container->get(ProductController::class); // Automatically builds the entire dependency tree
```

### 3. Singleton Database (`admin/core/Database.php`)
The Database connection is a **Singleton**.
*   Ensures only ONE connection to PostgreSQL is opened per request.
*   Accessible globally via `Database::getInstance()` (though we inject it via DI mostly).

### 4. Separation of Concerns (MVC-SR)
Strict layer separation ensures code quality and maintainability.

*   **Controller**: "The Traffic Cop". Validates input, talks to Service, returns JSON. **NO Business Logic.**
*   **Service**: "The Brain". Business rules, calculations (e.g. `Total = Price * Qty`).
*   **Repository**: "The Librarian". SQL queries only. **NO Business Logic.**

### 5. Custom Exceptions
Instead of generic errors, we throw specific Typed Exceptions:
*   `ResourceNotFoundException` (404)
*   `ValidationException` (400)
*   `AuthenticationException` (401)
*   The `ErrorHandler` catches these and Formats consistent JSON responses automatically.

## 🚧 WIP / Advanced Frontiers

### Validation Engine
A declarative validation system (inspired by Laravel's `$request->validate()`) is being built to replace manual `if/else` checks.

### Product Recognition (AI)
Integrating Computer Vision to allow users to snap a photo of a bottle and find it in our catalog.
*   **Status**: Database schema ready (`product_recognition` table). Endpoints scaffolded.
*   **Next Step**: Integration with Vision API (Google/AWS).

## 🔄 Frontend Architecture
*   **ES Modules**: Native Javascript modules (`import`/`export`). No Webpack/Babel required for modern browsers.
*   **Component-Based**: UI logic is split into `utils/*.js` files (Cart, Auth, API).
*   **Centralized API**: `api-helper.js` acts as the SDK for our backend.
