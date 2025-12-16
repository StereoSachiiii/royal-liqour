import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest } from '../../utils.js';

/**
 * Fetch payments from API with proper error handling
 * @param {number} limit - Number of payments to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Array|Object>} Array of payments or error object
 */
export async function fetchPayments(limit = 50, offset = 0, query = '') {
    try {
        const params = { limit, offset };

        // Use search endpoint when query is provided
        let url;
        if (query && query.trim()) {
            params.search = query.trim();
            url = API_ROUTES.PAYMENTS.SEARCH + buildQueryString(params);
        } else {
            url = API_ROUTES.PAYMENTS.LIST + buildQueryString(params);
        }

        console.log('[Payments] Fetching:', url);
        const response = await apiRequest(url);
        return response.data || [];
    } catch (error) {
        console.error('Error fetching payments:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single payment details
 * @param {number} id - Payment ID
 * @returns {Promise<Object>}
 */
export async function fetchModalDetails(id) {
    try {
        const response = await apiRequest(API_ROUTES.PAYMENTS.GET(id));
        return { success: true, payment: response.data };
    } catch (error) {
        console.error('Error fetching payment:', error);
        return { error: error.message };
    }
}

/**
 * Create a new payment
 * @param {Object} data - Payment data
 * @returns {Promise<Object>}
 */
export async function createPayment(data) {
    return apiRequest(API_ROUTES.PAYMENTS.CREATE, {
        method: 'POST',
        body: data
    });
}

/**
 * Update a payment
 * @param {number} id - Payment ID
 * @param {Object} data - Updated data
 * @returns {Promise<Object>}
 */
export async function updatePayment(id, data) {
    return apiRequest(API_ROUTES.PAYMENTS.UPDATE(id), {
        method: 'PUT',
        body: data
    });
}

/**
 * Delete a payment
 * @param {number} id - Payment ID
 * @returns {Promise<Object>}
 */
export async function deletePayment(id) {
    return apiRequest(API_ROUTES.PAYMENTS.DELETE(id), {
        method: 'DELETE'
    });
}