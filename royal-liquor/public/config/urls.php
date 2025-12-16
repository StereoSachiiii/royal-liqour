<?php
/**
 * Centralized URL Configuration
 * All URLs should be defined here for easy maintenance
 */

// Base URL - Update this for different environments
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . '://' . $host . '/royal-liquor/public/');
}

// API Base URL - Update this for different environments
define('API_BASE_URL', 'http://localhost/royal-liquor/admin/api');

// API Endpoints
define('API_ENDPOINTS', [
    'products' => API_BASE_URL . '/products.php',
    'categories' => API_BASE_URL . '/categories.php',
    'cart' => API_BASE_URL . '/cart.php',
    'cart_items' => API_BASE_URL . '/cart-items.php',
    'orders' => API_BASE_URL . '/orders.php',
    'order_items' => API_BASE_URL . '/order-items.php',
    'addresses' => API_BASE_URL . '/addresses.php',
    'users' => API_BASE_URL . '/users.php',
    'wishlist' => API_BASE_URL . '/wishlist.php',
    'feedback' => API_BASE_URL . '/feedback.php',
    'payments' => API_BASE_URL . '/payments.php',
    'stock' => API_BASE_URL . '/stock.php',
    'suppliers' => API_BASE_URL . '/suppliers.php',
    'flavor_profile' => API_BASE_URL . '/flavor-profile.php',
]);

// Page URLs (for redirects and navigation)
define('PAGE_URLS', [
    // Main pages
    'home' => BASE_URL,
    'shop' => BASE_URL . 'shop.php',
    'product' => BASE_URL . 'product.php',
    'checkout' => BASE_URL . 'checkout.php',
    'search' => BASE_URL . 'search.php',
    'cart' => BASE_URL . 'cart.php',
    'category' => BASE_URL . 'category.php',
    'feedback' => BASE_URL . 'feedback.php',
    
    // Static pages
    'about' => BASE_URL . 'about.php',
    'contact' => BASE_URL . 'contact.php',
    'faq' => BASE_URL . 'faq.php',
    
    // MyAccount pages (clean .php routes)
    'account' => BASE_URL . 'myaccount/',
    'orders' => BASE_URL . 'myaccount/orders.php',
    'wishlist' => BASE_URL . 'myaccount/wishlist.php',
    'addresses' => BASE_URL . 'myaccount/addresses.php',
    'settings' => BASE_URL . 'myaccount/settings.php',
    'logout' => BASE_URL . 'myaccount/logout.php',
    
    // Auth
    'login' => BASE_URL . 'auth/auth.php',
]);

/**
 * Helper function to get API endpoint URL
 * @param string $endpoint - Endpoint name from API_ENDPOINTS
 * @return string - Full API URL
 */
function getApiUrl($endpoint) {
    if (!isset(API_ENDPOINTS[$endpoint])) {
        throw new Exception("Unknown API endpoint: $endpoint");
    }
    return API_ENDPOINTS[$endpoint];
}

/**
 * Helper function to get page URL
 * @param string $page - Page name from PAGE_URLS
 * @return string - Full page URL
 */
function getPageUrl($page) {
    if (!isset(PAGE_URLS[$page])) {
        throw new Exception("Unknown page: $page");
    }
    return PAGE_URLS[$page];
}
