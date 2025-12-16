import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch cart items from API with proper error handling
 * @param {number} limit - Number of items to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Object>} Object with cart_items array or error
 */
export async function fetchCartItems(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.CART_ITEMS.LIST + buildQueryString({
            limit,
            offset,
            search: query || undefined
        });

        console.log('[CartItems API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[CartItems API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch cart items');
        }

        // Return unwrapped data array
        return Array.isArray(response.data) ? response.data : [];

    } catch (error) {
        console.error('[CartItems API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch cart item details for modal
 * @param {number} itemId 
 * @returns {Promise<Object>} Cart item data or error object
 */
export async function fetchModalDetails(itemId) {
    try {
        const url = API_ROUTES.CART_ITEMS.GET(itemId);
        console.log('[CartItems API] Fetching details:', url);

        const response = await apiRequest(url);
        console.log('[CartItems API] Details response:', { success: response.success, hasData: !!response.data, data: response.data });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch cart item details');
        }

        // Validate data format
        const item = response.data;
        if (!item || typeof item !== 'object' || !item.id) {
            throw new Error('Invalid cart item data format from API');
        }

        return { success: true, cart_item: item };

    } catch (error) {
        console.error('[CartItems API] Error fetching item:', error);
        return { error: error.message };
    }
}