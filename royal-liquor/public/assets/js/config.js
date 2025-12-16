/**
 * JavaScript Configuration
 * Centralized configuration for API endpoints and application settings
 */

// Determine base URL dynamically
const getBaseUrl = () => {
    const { protocol, hostname, port } = window.location;
    const portStr = port ? `:${port}` : '';
    return `${protocol}//${hostname}${portStr}/royal-liquor/admin/api`;
};

// API Configuration
export const API_CONFIG = {
    baseUrl: getBaseUrl(),
    endpoints: {
        products: '/products.php',
        categories: '/categories.php',
        cart: '/cart.php',
        cartItems: '/cart-items.php',
        orders: '/orders.php',
        orderItems: '/order-items.php',
        addresses: '/addresses.php',
        users: '/users.php',
        wishlist: '/wishlist.php',
        feedback: '/feedback.php',
        payments: '/payments.php',
        stock: '/stock.php',
        suppliers: '/suppliers.php',
        flavorProfile: '/flavor-profile.php',
    }
};

/**
 * Build full API URL with query parameters
 * @param {string} endpoint - Endpoint name from API_CONFIG.endpoints
 * @param {object} params - Query parameters as key-value pairs
 * @returns {string} - Full URL with query string
 */
export function getApiUrl(endpoint, params = {}) {
    if (!API_CONFIG.endpoints[endpoint]) {
        throw new Error(`Unknown API endpoint: ${endpoint}`);
    }

    const url = new URL(API_CONFIG.baseUrl + API_CONFIG.endpoints[endpoint]);

    // Add query parameters
    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined) {
            url.searchParams.append(key, params[key]);
        }
    });

    return url.toString();
}

/**
 * Application settings
 */
export const APP_CONFIG = {
    // Pagination
    defaultPageSize: 24,
    maxPageSize: 100,

    // Timeouts (in milliseconds)
    apiTimeout: 30000,
    debounceDelay: 300,

    // Cache settings
    cacheEnabled: true,
    cacheDuration: 5 * 60 * 1000, // 5 minutes

    // UI settings
    toastDuration: 3000,
    modalAnimationDuration: 300,
};

/**
 * Get environment (development, production, etc.)
 */
export function getEnvironment() {
    const hostname = window.location.hostname;
    if (hostname === 'localhost' || hostname === '127.0.0.1') {
        return 'development';
    }
    return 'production';
}

/**
 * Check if in development mode
 */
export function isDevelopment() {
    return getEnvironment() === 'development';
}
