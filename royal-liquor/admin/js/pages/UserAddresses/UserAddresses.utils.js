import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch user addresses from API with proper error handling
 * @param {number} limit - Number of user addresses to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Object>} Object with addresses array or error
 */
export async function fetchUserAddresses(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.USER_ADDRESSES.LIST + buildQueryString({
            limit,
            offset,
            search: query || undefined
        });

        console.log('[UserAddresses API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[UserAddresses API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch addresses');
        }

        // Return unwrapped data array
        return Array.isArray(response.data) ? response.data : [];

    } catch (error) {
        console.error('[UserAddresses API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single address by ID for modal
 * @param {number} addressId 
 * @returns {Promise<Object>} Address data or error object
 */
export async function fetchModalDetails(addressId) {
    try {
        const url = API_ROUTES.USER_ADDRESSES.GET(addressId);
        console.log('[UserAddresses API] Fetching details:', url);

        const response = await apiRequest(url);
        console.log('[UserAddresses API] Details response:', { success: response.success, hasData: !!response.data });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch address details');
        }

        // Validate data format
        const address = response.data;
        if (!address || typeof address !== 'object' || !address.id) {
            throw new Error('Invalid address data format from API');
        }

        return { success: true, user_address: address };

    } catch (error) {
        console.error('[UserAddresses API] Error fetching address:', error);
        return { error: error.message };
    }
}

/**
 * Fetch all users for searchable dropdown
 * @returns {Promise<Array>} Array of users
 */
export async function fetchUsersForDropdown() {
    try {
        const response = await apiRequest(API_ROUTES.USERS.LIST + '?limit=100');
        return response.success ? response.data : [];
    } catch (error) {
        console.error('[UserAddresses API] Error fetching users:', error);
        return [];
    }
}