import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch flavor profiles from API with proper error handling
 * @param {number} limit - Number of flavor profiles to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Array|Object>} Array of flavor profiles or error object
 */
export async function fetchFlavorProfiles(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.FLAVOR_PROFILES.LIST + buildQueryString({
            limit,
            offset,
            search: query || undefined
        });

        console.log('[FlavorProfiles API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[FlavorProfiles API] Response:', { success: response.success, count: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch flavor profiles');
        }

        return Array.isArray(response.data) ? response.data : [];

    } catch (error) {
        console.error('[FlavorProfiles API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single flavor profile by product ID for modal
 * @param {number} productId 
 * @returns {Promise<Object>} Flavor profile data or error object
 */
export async function fetchModalDetails(productId) {
    try {
        if (!productId || isNaN(productId)) {
            throw new Error('Invalid product ID');
        }

        const url = API_ROUTES.FLAVOR_PROFILES.GET(productId);
        console.log('[FlavorProfiles API] Fetching details from:', url);

        const response = await apiRequest(url);
        console.log('[FlavorProfiles API] Details response:', response);

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch flavor profile');
        }

        const flavorProfile = response.data;
        if (!flavorProfile || typeof flavorProfile !== 'object') {
            throw new Error('Invalid flavor profile data from API');
        }

        return { success: true, flavor_profile: flavorProfile };

    } catch (error) {
        console.error('[FlavorProfiles API] Error fetching profile:', error);
        return { error: error.message };
    }
}

/**
 * Fetch products for dropdown (products without flavor profiles or a specific one)
 */
export async function fetchProductsForDropdown() {
    try {
        const response = await apiRequest(API_ROUTES.PRODUCTS.LIST + '?limit=200');
        return response.success ? response.data : [];
    } catch (error) {
        console.error('[FlavorProfiles API] Error fetching products:', error);
        return [];
    }
}
