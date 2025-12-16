import { API_URL_ORDER_ITEMS } from "../config.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch order items from API with proper error handling
 * @param {number} limit - Number of order items to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query (currently unused, for future)
 * @returns {Promise<Array|Object>} Array of order items or error object
 */
export async function fetchOrderItems(limit = 50, offset = 0, query = '') {
    try {
        const params = new URLSearchParams({
            limit: limit.toString(),
            offset: offset.toString()
        });
        // Note: API doesn't support search yet, but keeping param for future
        // if (query) params.set('search', query);

        const url = `${API_URL_ORDER_ITEMS}?${params.toString()}`;
        const response = await apiRequest(url);

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch order items');
        }

        return response.data || [];
    } catch (error) {
        console.error('Error fetching order items:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single order item by ID
 * @param {number} itemId - Order item ID
 * @returns {Promise<Object>} Order item data or error
 */
export async function fetchModalDetails(itemId) {
    try {
        const url = `${API_URL_ORDER_ITEMS}?id=${itemId}`;
        const response = await apiRequest(url);

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch order item');
        }

        return { success: true, order_item: response.data };
    } catch (error) {
        console.error('Error fetching order item:', error);
        return { error: error.message };
    }
}