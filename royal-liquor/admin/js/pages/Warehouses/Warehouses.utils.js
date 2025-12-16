import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch warehouses from API with proper error handling
 * @param {number} limit 
 * @param {number} offset 
 * @param {string} query 
 * @returns {Promise<Array|Object>} Array of warehouses or error object
 */
export async function fetchAllWarehouses(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.WAREHOUSES.LIST + buildQueryString({
            limit,
            offset,
            search: query || undefined
        });

        console.log('[Warehouses API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[Warehouses API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch warehouses');
        }
        return response.data || [];
    } catch (error) {
        console.error('[Warehouses API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch warehouse details for modal
 * @param {number} warehouseId 
 * @returns {Promise<Object>} Warehouse data or error object
 */
export async function fetchWarehouseDetails(warehouseId) {
    try {
        const url = API_ROUTES.WAREHOUSES.GET(warehouseId) + buildQueryString({ enriched: 'true' });
        console.log('[Warehouses API] Fetching details:', url);

        const response = await apiRequest(url);
        console.log('[Warehouses API] Details response:', { success: response.success, hasData: !!response.data });

        if (response.success && response.data) {
            return { success: true, warehouse: response.data };
        }

        return { success: false, error: response.message || 'Failed to fetch warehouse details' };
    } catch (error) {
        console.error('[Warehouses API] Error fetching warehouse:', error);
        return { success: false, error: error.message };
    }
}
