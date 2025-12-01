import { API_URL, DETAIL_VIEW_API_URL } from "../config.js";

/**
 * Fetch user addresses from API with proper error handling
 * @param {number} limit - Number of user addresses to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Array|Object>} Array of user addresses or error object
 */
export async function fetchUserAddresses(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = `${DETAIL_VIEW_API_URL}?limit=${limit}&entity=user_addresses&offset=${offset}${query ? `&query=${encodeURIComponent(query)}` : ''}`;
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
        console.log(data.data.items);
        return data.data.items || [];
        
    } catch (error) {
        console.error('Error fetching user addresses:', error);
        return { error: error.message };
    }
}

export const fetchModalDetails = async (addressId) => {
    try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}?entity=user_addresses&id=${addressId}`, {
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
                return { error: 'User address not found' };
            }
            
            throw new Error(errorData.message || `Failed to fetch user address`);
        }

        const data = await response.json();

        return { success: true, user_address: data.data.data };
        
    } catch (error) {
        console.error('Error fetching user address:', error);
        return { error: error.message };
    }
}