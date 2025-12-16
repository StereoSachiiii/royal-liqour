import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch stock entries from API with proper error handling
 * @param {number} limit 
 * @param {number} offset 
 * @param {string} query 
 * @returns {Promise<Object>} Object with stocks array or error
 */
export async function fetchStock(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.STOCK.LIST + buildQueryString({
            limit,
            offset,
            search: query || undefined
        });

        console.log('[Stock API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[Stock API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch stock');
        }

        // Handle both array and object with items
        const stocks = response.data?.items || response.data || [];
        return { success: true, stocks };
    } catch (error) {
        console.error('[Stock API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch stock details for modal
 * @param {number} stockId 
 * @returns {Promise<Object>} Stock data or error object
 */
export async function fetchModalDetails(stockId) {
    try {
        const url = API_ROUTES.STOCK.GET(stockId);
        console.log('[Stock API] Fetching details:', url);

        const response = await apiRequest(url);
        console.log('[Stock API] Details response:', { success: response.success, hasData: !!response.data });

        if (response.success && response.data) {
            // API now returns enriched data directly
            return { success: true, stock: response.data };
        }

        return { error: response.message || 'Failed to fetch stock details' };
    } catch (error) {
        console.error('[Stock API] Error fetching stock:', error);
        return { error: error.message };
    }
}