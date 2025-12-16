import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch carts from API with proper error handling
 * @param {number} limit - Number of carts to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Object>} Object with carts array or error
 */
export async function fetchCarts(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.CARTS.LIST + buildQueryString({
            limit,
            offset,
            search: query || undefined
        });

        console.log('[Carts API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[Carts API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch carts');
        }

        // Return unwrapped data array
        return Array.isArray(response.data) ? response.data : [];

    } catch (error) {
        console.error('[Carts API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch cart details for modal
 * @param {number} cartId 
 * @returns {Promise<Object>} Cart data or error object
 */
export async function fetchModalDetails(cartId) {
    try {
        const url = API_ROUTES.CARTS.GET(cartId);
        console.log('[Carts API] Fetching details:', url);

        const response = await apiRequest(url);
        console.log('[Carts API] Details response:', { success: response.success, hasData: !!response.data, data: response.data });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch cart details');
        }

        // Validate data format
        const cart = response.data;
        if (!cart || typeof cart !== 'object' || !cart.id) {
            throw new Error('Invalid cart data format from API');
        }

        return { success: true, cart };

    } catch (error) {
        console.error('[Carts API] Error fetching cart:', error);
        return { error: error.message };
    }
}