import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch suppliers from API with proper error handling
 * @param {number} limit 
 * @param {number} offset 
 * @param {string} query 
 * @returns {Promise<Array|Object>} Array of suppliers or error object
 */
export async function fetchAllSuppliers(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.SUPPLIERS.LIST + buildQueryString({
            limit,
            offset,
            search: query || undefined
        });

        console.log('[Suppliers API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[Suppliers API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch suppliers');
        }
        return response.data || [];
    } catch (error) {
        console.error('[Suppliers API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch supplier details for modal
 * @param {number} supplierId 
 * @returns {Promise<Object>} Supplier data or error object
 */
export async function fetchSupplierDetails(supplierId) {
    try {
        const url = API_ROUTES.SUPPLIERS.GET(supplierId) + buildQueryString({ enriched: 'true' });
        console.log('[Suppliers API] Fetching details:', url);

        const response = await apiRequest(url);
        console.log('[Suppliers API] Details response:', { success: response.success, hasData: !!response.data });

        if (response.success && response.data) {
            return { success: true, supplier: response.data };
        }

        return { success: false, error: response.message || 'Failed to fetch supplier details' };
    } catch (error) {
        console.error('[Suppliers API] Error fetching supplier:', error);
        return { success: false, error: error.message };
    }
}
