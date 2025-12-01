import { API_URL, DETAIL_VIEW_API_URL } from "../config.js";

/**
 * Fetch users from API with proper error handling
 * @param {number} limit - Number of users to fetch
 * @param {number} offset - Offset for pagination
 * @returns {Promise<Array|Object>} Array of users or error object
 */
export async function fetchUsers(limit = DEFAULT_LIMIT, offset = 0,search='') {
    try {
        console.log('Fetching URL:', `${API_URL}?limit=${limit}&offset=${offset}&search=${search}`);
        const response = await fetch(`${API_URL}?limit=${limit}&offset=${offset}&search=${search}`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include'
        });

        // Handle HTTP errors
        if (!response.ok) {
            const errorData = await response.text().catch(() => ({}));
            
            console.log(errorData);
            if (response.status === 401) {
                
                window.location.href = '/royal-liquor/public/auth/auth.php';
                return { error: 'Please login to continue' };
            }
            
            if (response.status === 403) {
                // Forbidden - not admin
                return { error: 'Access denied. Admin privileges required.' };
            }
            
            throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        return data.data || [];
        
    } catch (error) {
        console.error('Error fetching users:', error);
        return { error: error.message };
    }
}



export const fetchModalDetails = async (userId) => {
        try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}?entity=users&id=${userId}`, {
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
                return { error: 'User not found' };
            }
            
            throw new Error(errorData.message || `Failed to fetch user`);
        }

        const data = await response.json();

        return { success: true, user: data.data.data };
        
    } catch (error) {
        console.error('Error fetching user:', error);
        return { error: error.message };
    }
}
