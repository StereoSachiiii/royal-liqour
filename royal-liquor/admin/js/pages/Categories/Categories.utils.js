import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch categories from API with proper error handling
 * @param {number} limit 
 * @param {number} offset 
 * @param {string} query 
 * @returns {Promise<Array|Object>} Array of categories or error object
 */
export async function fetchAllCategories(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.CATEGORIES.LIST + buildQueryString({
            limit,
            offset,
            enriched: 'true',
            search: query || undefined
        });

        console.log('[Categories API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[Categories API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch categories');
        }
        return response.data || [];
    } catch (error) {
        console.error('[Categories API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch category details for modal
 * @param {number} categoryId 
 * @returns {Promise<Object>} Category data or error object
 */
export async function fetchModalDetails(categoryId) {
    try {
        const url = API_ROUTES.CATEGORIES.GET(categoryId) + buildQueryString({ enriched: 'true' });
        console.log('[Categories API] Fetching details:', url);

        const response = await apiRequest(url);
        console.log('[Categories API] Details response:', { success: response.success, hasData: !!response.data });

        if (response.success && response.data) {
            return { success: true, category: response.data };
        }

        return { success: false, error: response.message || 'Failed to fetch category details' };
    } catch (error) {
        console.error('[Categories API] Error fetching category:', error);
        return { success: false, error: error.message };
    }
}