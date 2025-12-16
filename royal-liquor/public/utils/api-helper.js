/**
 * Royal Liquor - Centralized API Helper
 * All public frontend API calls should use this module
 */

const API_BASE_URL = '/royal-liquor/api/v1';

/**
 * Build query string from params object
 */
function buildQuery(params = {}) {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            query.append(key, value);
        }
    });
    const str = query.toString();
    return str ? '?' + str : '';
}

/**
 * Get CSRF token from meta tag or cookie
 */
function getCsrfToken() {
    // Check meta tag first
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content');

    // Check cookie as fallback
    const match = document.cookie.match(/csrf_token=([^;]+)/);
    return match ? match[1] : '';
}

/**
 * Centralized API request function
 * @param {string} endpoint - API endpoint (without base URL)
 * @param {object} options - Fetch options
 * @returns {Promise<object>} API response
 */
async function apiRequest(endpoint, options = {}) {
    const url = API_BASE_URL + endpoint;

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers
    };

    // Add CSRF token for mutating requests
    if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(options.method?.toUpperCase())) {
        headers['X-CSRF-Token'] = getCsrfToken();
    }

    const config = {
        method: options.method || 'GET',
        headers,
        credentials: 'include', // Include cookies for session auth
        ...options
    };

    if (options.body && typeof options.body === 'object') {
        config.body = JSON.stringify(options.body);
    }

    try {
        const response = await fetch(url, config);

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error('Server returned non-JSON response');
        }

        const data = await response.json();

        if (!data.success && data.message) {
            throw new Error(data.message);
        }

        return data;
    } catch (error) {
        console.error(`[API] ${options.method || 'GET'} ${endpoint} failed:`, error);
        throw error;
    }
}

/**
 * Centralized API module
 */
export const API = {
    baseUrl: API_BASE_URL,
    request: apiRequest,
    buildQuery,

    // ==================== PRODUCTS ====================
    products: {
        list: (params = {}) => apiRequest('/products' + buildQuery(params)),
        search: (query, params = {}) => apiRequest('/products/search' + buildQuery({ search: query, ...params })),
        get: (id) => apiRequest('/products/' + id),
        getByCategory: (categoryId, params = {}) => apiRequest('/products' + buildQuery({ category_id: categoryId, ...params })),
        featured: (params = {}) => apiRequest('/products' + buildQuery({ featured: true, ...params }))
    },

    // ==================== CATEGORIES ====================
    categories: {
        list: (params = {}) => apiRequest('/categories' + buildQuery(params)),
        get: (id) => apiRequest('/categories/' + id),
        getWithProducts: (id) => apiRequest('/categories/' + id + '/products')
    },

    // ==================== CART ====================
    cart: {
        get: (cartId) => apiRequest('/cart/' + cartId),
        create: (data) => apiRequest('/cart', { method: 'POST', body: data }),
        update: (id, data) => apiRequest('/cart/' + id, { method: 'PUT', body: data }),
        delete: (id) => apiRequest('/cart/' + id, { method: 'DELETE' }),

        // Cart Items
        getItems: (cartId) => apiRequest('/cart-items' + buildQuery({ cart_id: cartId })),
        addItem: (data) => apiRequest('/cart-items', { method: 'POST', body: data }),
        updateItem: (id, data) => apiRequest('/cart-items/' + id, { method: 'PUT', body: data }),
        removeItem: (id) => apiRequest('/cart-items/' + id, { method: 'DELETE' })
    },

    // ==================== ORDERS ====================
    orders: {
        list: (params = {}) => apiRequest('/orders' + buildQuery(params)),
        get: (id) => apiRequest('/orders/' + id),
        create: (data) => apiRequest('/orders', { method: 'POST', body: data }),
        getByUser: (userId, params = {}) => apiRequest('/orders' + buildQuery({ user_id: userId, ...params })),

        // Order Items
        getItems: (orderId) => apiRequest('/order-items' + buildQuery({ order_id: orderId }))
    },

    // ==================== PAYMENTS ====================
    payments: {
        create: (data) => apiRequest('/payments', { method: 'POST', body: data }),
        get: (id) => apiRequest('/payments/' + id),
        getByOrder: (orderId) => apiRequest('/payments' + buildQuery({ order_id: orderId }))
    },

    // ==================== USERS ====================
    users: {
        get: (id) => apiRequest('/users/' + id),
        update: (id, data) => apiRequest('/users/' + id, { method: 'PUT', body: data }),
        getCurrentUser: () => apiRequest('/users/me'),

        // Auth
        login: (credentials) => apiRequest('/auth/login', { method: 'POST', body: credentials }),
        register: (data) => apiRequest('/auth/register', { method: 'POST', body: data }),
        logout: () => apiRequest('/auth/logout', { method: 'POST' })
    },

    // ==================== ADDRESSES ====================
    addresses: {
        list: (userId) => apiRequest('/addresses' + buildQuery({ user_id: userId })),
        get: (id) => apiRequest('/addresses/' + id),
        create: (data) => apiRequest('/addresses', { method: 'POST', body: data }),
        update: (id, data) => apiRequest('/addresses/' + id, { method: 'PUT', body: data }),
        delete: (id) => apiRequest('/addresses/' + id, { method: 'DELETE' }),
        setDefault: (id) => apiRequest('/addresses/' + id + '/default', { method: 'PUT' })
    },

    // ==================== WISHLIST ====================
    wishlist: {
        get: (userId) => apiRequest('/wishlist' + buildQuery({ user_id: userId })),
        add: (data) => apiRequest('/wishlist', { method: 'POST', body: data }),
        remove: (id) => apiRequest('/wishlist/' + id, { method: 'DELETE' })
    },

    // ==================== FEEDBACK ====================
    feedback: {
        submit: (data) => apiRequest('/feedback', { method: 'POST', body: data }),
        getByProduct: (productId) => apiRequest('/feedback' + buildQuery({ product_id: productId }))
    },

    // ==================== RECIPES ====================
    recipes: {
        list: (params = {}) => apiRequest('/cocktail-recipes' + buildQuery(params)),
        search: (query, params = {}) => apiRequest('/cocktail-recipes/search' + buildQuery({ search: query, ...params })),
        get: (id) => apiRequest('/cocktail-recipes/' + id),
        getIngredients: (recipeId) => apiRequest('/recipe-ingredients' + buildQuery({ recipe_id: recipeId }))
    },

    // ==================== FLAVOR PROFILES ====================
    flavorProfiles: {
        list: () => apiRequest('/flavour-profiles'),
        get: (id) => apiRequest('/flavour-profiles/' + id)
    },

    // ==================== USER PREFERENCES ====================
    preferences: {
        get: (userId) => apiRequest('/user-preferences' + buildQuery({ user_id: userId })),
        update: (id, data) => apiRequest('/user-preferences/' + id, { method: 'PUT', body: data }),
        create: (data) => apiRequest('/user-preferences', { method: 'POST', body: data })
    },

    // ==================== PRODUCT RECOGNITION ====================
    recognition: {
        create: (data) => apiRequest('/product-recognition', { method: 'POST', body: data }),
        get: (id) => apiRequest('/product-recognition/' + id)
    },

    // ==================== STOCK ====================
    stock: {
        getByProduct: (productId) => apiRequest('/stock' + buildQuery({ product_id: productId })),
        checkAvailability: (productId, quantity) => apiRequest('/stock/check' + buildQuery({ product_id: productId, quantity }))
    }
};

// Export for ES modules
export default API;

// Also attach to window for non-module usage
if (typeof window !== 'undefined') {
    window.RoyalAPI = API;
}
