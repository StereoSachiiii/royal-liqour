import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest } from '../../utils.js';

/**
 * Fetch product recognition from API with proper error handling
 * @param {number} limit - Number of recognitions to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Array|Object>} Array of recognitions or error object
 */
export async function fetchProductRecognition(limit = 50, offset = 0, query = '') {
    try {
        const params = { limit, offset };

        // Use search endpoint when query is provided
        let url;
        if (query && query.trim()) {
            params.search = query.trim();
            url = API_ROUTES.PRODUCT_RECOGNITION.SEARCH + buildQueryString(params);
        } else {
            url = API_ROUTES.PRODUCT_RECOGNITION.LIST + buildQueryString(params);
        }

        console.log('[ProductRecognition] Fetching:', url);
        const response = await apiRequest(url);
        return response.data || [];
    } catch (error) {
        console.error('Error fetching product recognition:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single product recognition details
 * @param {number} id - Recognition ID
 * @returns {Promise<Object>}
 */
export async function fetchModalDetails(id) {
    try {
        const response = await apiRequest(API_ROUTES.PRODUCT_RECOGNITION.GET(id));
        return { success: true, product_recognition: response.data };
    } catch (error) {
        console.error('Error fetching product recognition:', error);
        return { error: error.message };
    }
}

/**
 * Create a new product recognition
 * @param {Object} data - Recognition data
 * @returns {Promise<Object>}
 */
export async function createProductRecognition(data) {
    return apiRequest(API_ROUTES.PRODUCT_RECOGNITION.CREATE, {
        method: 'POST',
        body: data
    });
}

/**
 * Update a product recognition
 * @param {number} id - Recognition ID
 * @param {Object} data - Updated data
 * @returns {Promise<Object>}
 */
export async function updateProductRecognition(id, data) {
    return apiRequest(API_ROUTES.PRODUCT_RECOGNITION.UPDATE(id), {
        method: 'PUT',
        body: data
    });
}

/**
 * Delete a product recognition
 * @param {number} id - Recognition ID
 * @returns {Promise<Object>}
 */
export async function deleteProductRecognition(id) {
    return apiRequest(API_ROUTES.PRODUCT_RECOGNITION.DELETE(id), {
        method: 'DELETE'
    });
}