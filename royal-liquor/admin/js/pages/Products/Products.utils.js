import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

const DEFAULT_LIMIT = 5;

/**
 * Fetch products from API with proper error handling and response tracking
 * @param {number} limit - Number of products to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} currentQuery - Search query
 * @returns {Promise<Array|Object>} Array of products or error object
 */
export async function fetchAllProducts(limit = DEFAULT_LIMIT, offset = 0, currentQuery = '') {
    try {
        const url = API_ROUTES.PRODUCTS.LIST + buildQueryString({
            limit,
            offset,
            search: currentQuery
        });
        const response = await apiRequest(url, { method: 'GET' });

        // API response tracking: log response structure for debugging
        console.log('[Products API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A', message: response.message });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch products');
        }

        // Return unwrapped data array
        return Array.isArray(response.data) ? response.data : [];

    } catch (error) {
        console.error('[Products API] Error:', error);
        return { error: error.message };
    }
}



/**
 * Fetch single product by ID with proper API response tracking
 * @param {number} productId - Product ID
 * @returns {Promise<Object>} Product data with success flag or error
 */
export async function fetchModalDetails(productId) {
    try {
        // Use the RESTful path pattern: /admin/views/products/{id}
        const url = API_ROUTES.ADMIN_VIEWS.DETAIL('products', productId);
        const response = await apiRequest(url, { method: 'GET' });

        // API response tracking: log response structure
        console.log('[Product Details API] Response:', { success: response.success, hasProduct: !!response.data, message: response.message, productId });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch product details');
        }

        // Extract product from response.data (AdminViewController returns { success, message, data: product })
        const product = response.data;
        if (!product || typeof product !== 'object' || !product.id) {
            throw new Error('Invalid product data format from API');
        }

        return { success: true, product };

    } catch (error) {
        console.error('[Product Details API] Error:', error);
        return { error: error.message };
    }
}
