<?php
declare(strict_types=1);

/**
 * Auto-loader for API modules
 * Scans directories and automatically requires all PHP files
 */

function loadApiModules(Router $router): void
{
    $baseDir = __DIR__;
    
    // Load all route files
    $routeFiles = [
        'users.php',
        'products.php', 
        'categories.php',
        'orders.php',
        'order-items.php',
        'stock.php',
        'cart.php',
        'cart-items.php',
        'payments.php',
        'warehouses.php',
        'suppliers.php',
        'addresses.php',
        'flavor-profile.php',
        'feedback.php',
        'product-recognition.php',
        'cocktail-recipes.php',
        'recipe-ingredients.php',
        'user-preferences.php',
        'admin-views.php'
    ];
    
    foreach ($routeFiles as $file) {
        $path = $baseDir . '/' . $file;
        if (file_exists($path)) {
            require $path; // Use require instead of require_once to pass router variable
        }
    }
}

function loadDependencies(): void
{
    // Project root (e.g. C:/xampp/htdocs/royal-liquor)
    $projectRoot = realpath(__DIR__ . '/../..');
    // Admin directory (e.g. C:/xampp/htdocs/royal-liquor/admin)
    $adminDir    = $projectRoot . '/admin';
    
    // Core dependencies (live at project root)
    $coreFiles = [
        'core/Request.php',
        'core/Session.php',
        'middleware/JsonMiddleware.php',
        'exceptions/BaseException.php'
    ];
    
    foreach ($coreFiles as $file) {
        $path = $projectRoot . '/' . $file;
        if (file_exists($path)) {
            require_once $path;
        }
    }

    // Admin router (global Router class used by admin API route files)
    $adminRouterPath = $adminDir . '/core/Router.php';
    if (file_exists($adminRouterPath)) {
        require_once $adminRouterPath;
    }
    
    // Auto-load all admin repositories, services, controllers
    $directories = ['repositories', 'services', 'controllers'];
    
    foreach ($directories as $dir) {
        $fullDir = $adminDir . '/' . $dir;
        if (is_dir($fullDir)) {
            $files = glob($fullDir . '/*.php');
            foreach ($files as $file) {
                require_once $file;
            }
        }
    }
}
