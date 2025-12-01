 import {  DETAIL_VIEW_API_URL } from "../config.js";
 
 /**
 * Fetch Dashboard from API with proper error handling

 * @returns {Promise<Array|Object>} Array of products or error object
 */
export async function fetchDashboard() {
    try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}?dashboard`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            const errorData = await response.text().catch(() => ({}));
            
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
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to fetch stats');
        }
        
        return data.data || [];
        
    } catch (error) {
        console.error('Error fetching stats:', error);
        return { error: error.message };
    }
}

