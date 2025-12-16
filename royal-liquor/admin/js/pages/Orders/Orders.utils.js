import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch all orders with pagination and search
 * @param {number} limit 
 * @param {number} offset 
 * @param {string} search 
 */
export async function fetchAllOrders(limit = 50, offset = 0, search = "") {
    try {
        const url = API_ROUTES.ORDERS.LIST + buildQueryString({
            limit,
            offset,
            search: search || undefined
        });

        console.log('[Orders API] Fetching:', url);
        const result = await apiRequest(url);
        console.log('[Orders API] Response:', { success: result.success, dataCount: Array.isArray(result.data) ? result.data.length : 'N/A', message: result.message });

        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch orders');
        }
        return result.data || [];
    } catch (error) {
        console.error('[Orders API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single order details
 * @param {number} id 
 */
export async function fetchOrder(id) {
    try {
        const url = API_ROUTES.ORDERS.GET(id);
        const result = await apiRequest(url);
        if (!result.success) throw new Error(result.message);
        return { success: true, order: result.data };
    } catch (error) {
        return { error: error.message };
    }
}

/**
 * Fetch detailed order for modal (enriched with items, addresses)
 * @param {number} id 
 */
export async function fetchModalDetails(id) {
    try {
        // Use enriched endpoint for detailed order data
        const url = `${API_ROUTES.ORDERS.GET(id)}/enriched`;
        console.log('[Orders API] Fetching details:', url);

        const result = await apiRequest(url);
        console.log('[Orders API] Details response:', { success: result.success, hasData: !!result.data });

        if (!result.success) throw new Error(result.message);
        return { success: true, order: result.data };
    } catch (error) {
        console.error('[Orders API] fetchModalDetails error:', error);
        return { error: error.message };
    }
}
