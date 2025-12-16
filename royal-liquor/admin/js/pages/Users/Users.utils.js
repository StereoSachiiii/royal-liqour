import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch users from API with proper error handling
 * @param {number} limit 
 * @param {number} offset 
 * @param {string} query 
 * @returns {Promise<Array|Object>} Array of users or error object
 */
export async function fetchUsers(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.USERS.LIST + buildQueryString({
            limit,
            offset,
            search: query || undefined
        });

        console.log('[Users API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[Users API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch users');
        }
        return response.data || [];
    } catch (error) {
        console.error('[Users API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch user details for modal
 * @param {number} userId 
 * @returns {Promise<Object>} User data or error object
 */
export async function fetchModalDetails(userId) {
    try {
        const url = API_ROUTES.USERS.GET(userId);
        console.log('[Users API] Fetching details:', url);

        const response = await apiRequest(url);
        console.log('[Users API] Details response:', { success: response.success, hasData: !!response.data });

        if (response.success && response.data) {
            return { success: true, user: response.data };
        }

        return { error: response.message || 'Failed to fetch user details' };
    } catch (error) {
        console.error('[Users API] Error fetching user:', error);
        return { error: error.message };
    }
}
