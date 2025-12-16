import { API_ROUTES, buildQueryString } from "../../dashboard.routes.js";
import { apiRequest } from "../../utils.js";

/**
 * Fetch feedback from API with proper error handling
 * @param {number} limit - Number of feedback to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Object>} Object with feedback array or error
 */
export async function fetchFeedback(limit = 20, offset = 0, query = '') {
    try {
        const url = API_ROUTES.FEEDBACK.LIST + buildQueryString({
            limit,
            offset,
            search: query || undefined,
            details: true  // Request enriched data
        });

        console.log('[Feedback API] Fetching:', url);
        const response = await apiRequest(url);
        console.log('[Feedback API] Response:', { success: response.success, dataCount: Array.isArray(response.data) ? response.data.length : 'N/A' });

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch feedback');
        }

        return Array.isArray(response.data) ? response.data : [];

    } catch (error) {
        console.error('[Feedback API] Error:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single feedback by ID for modal
 * @param {number} feedbackId 
 * @returns {Promise<Object>} Feedback data or error object
 */
export async function fetchModalDetails(feedbackId) {
    try {
        if (!feedbackId || isNaN(feedbackId)) {
            throw new Error('Invalid feedback ID');
        }

        const url = API_ROUTES.FEEDBACK.GET(feedbackId);
        console.log('[Feedback API] Fetching details from:', url);

        const response = await apiRequest(url);
        console.log('[Feedback API] Full response:', JSON.stringify(response, null, 2));

        if (!response.success) {
            throw new Error(response.message || 'Failed to fetch feedback details');
        }

        const feedback = response.data;
        console.log('[Feedback API] Extracted feedback:', feedback);

        if (!feedback || typeof feedback !== 'object') {
            throw new Error('Invalid feedback data format from API');
        }

        // Ensure we have at least an ID
        if (!feedback.id) {
            console.warn('[Feedback API] Feedback object has no ID:', feedback);
        }

        return { success: true, feedback };

    } catch (error) {
        console.error('[Feedback API] Error fetching feedback:', error);
        return { error: error.message };
    }
}

/**
 * Fetch all users for dropdown
 */
export async function fetchUsersForDropdown() {
    try {
        const response = await apiRequest(API_ROUTES.USERS.LIST + '?limit=100');
        return response.success ? response.data : [];
    } catch (error) {
        console.error('[Feedback API] Error fetching users:', error);
        return [];
    }
}

/**
 * Fetch all products for dropdown
 */
export async function fetchProductsForDropdown() {
    try {
        const response = await apiRequest(API_ROUTES.PRODUCTS.LIST + '?limit=100');
        return response.success ? response.data : [];
    } catch (error) {
        console.error('[Feedback API] Error fetching products:', error);
        return [];
    }
}