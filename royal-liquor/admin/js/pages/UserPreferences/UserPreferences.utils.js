import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch user preferences from API with proper error handling
 * @param {number} limit - Number of user preferences to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Object>} Object with user_preferences array or error
 */
export async function fetchUserPreferences(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.USER_PREFERENCES.LIST + buildQueryString({
            limit,
            offset,
            search: query || undefined
        });

        console.log('[UserPreferences API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[UserPreferences API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch user preferences');
        }

        // Return unwrapped data array
        return Array.isArray(response.data) ? response.data : [];

    } catch (error) {
        console.error('[UserPreferences API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch user preference details for modal
 * @param {number} prefId 
 * @returns {Promise<Object>} User preference data or error object
 */
export async function fetchModalDetails(prefId) {
    try {
        const url = API_ROUTES.USER_PREFERENCES.GET(prefId);
        console.log('[UserPreferences API] Fetching details:', url);

        const response = await apiRequest(url);
        console.log('[UserPreferences API] Details response:', { success: response.success, hasData: !!response.data });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch user preference details');
        }

        // Validate data format
        const pref = response.data;
        if (!pref || typeof pref !== 'object' || !pref.id) {
            throw new Error('Invalid user preference data format from API');
        }

        return { success: true, user_preference: pref };

    } catch (error) {
        console.error('[UserPreferences API] Error fetching preference:', error);
        return { error: error.message };
    }
}