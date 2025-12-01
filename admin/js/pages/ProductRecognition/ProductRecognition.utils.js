import { API_URL, DETAIL_VIEW_API_URL } from "../config.js";

/**
 * Fetch product recognition from API with proper error handling
 * @param {number} limit - Number of product recognition to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Array|Object>} Array of product recognition or error object
 */
export async function fetchProductRecognition(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = `${DETAIL_VIEW_API_URL}?entity=product_recognition&limit=${limit}&offset=${offset}${query ? `&search=${encodeURIComponent(query)}` : ''}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include'
        });

        if (!response.ok) {
            const errorData = await response.text().catch(() => ({}));
            
            console.log(errorData);
            if (response.status === 401) {
                window.location.href = '/royal-liquor/public/auth/auth.php';
                return { error: 'Please login to continue' };
            }
            
            if (response.status === 403) {
                return { error: 'Access denied. Admin privileges required.' };
            }
            
            throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        console.log(data.data);
        return data.data.items || [];
        
    } catch (error) {
        console.error('Error fetching product recognition:', error);
        return { error: error.message };
    }
}

export const fetchModalDetails = async (recognitionId) => {
    try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}?entity=product_recognition&id=${recognitionId}`, {
            method: 'GET',
            credentials: 'include'
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            
            if (response.status === 401) {
                window.location.href = '/login.php';
                return { error: 'Please login to continue' };
            }
            
            if (response.status === 403) {
                return { error: 'Access denied. Admin privileges required.' };
            }
            
            if (response.status === 404) {
                return { error: 'Product recognition not found' };
            }
            
            throw new Error(errorData.message || `Failed to fetch product recognition`);
        }

        const data = await response.json();

        return { success: true, product_recognition: data.data.data };
        
    } catch (error) {
        console.error('Error fetching product recognition:', error);
        return { error: error.message };
    }
}