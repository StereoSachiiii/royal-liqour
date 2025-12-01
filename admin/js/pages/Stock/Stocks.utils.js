import { API_URL_STOCK, DETAIL_VIEW_API_URL } from "../config.js";

export const fetchStock = async (DEFAULT_LIMIT = 0 , offset = 0, query = '') => {
    try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}?entity=stock&limit=${DEFAULT_LIMIT}&offset=${offset}&search=${query}`, {
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
                return { error: 'Stock not found' };
            }
            
            throw new Error(errorData.message || `Failed to fetch stock`);
        }

        const data = await response.json();
        
        
        
        return { success: true, stocks: data.data.items };
        
    } catch (error) {
        console.error('Error fetching stock:', error);
        return { error: error.message };
    }
}
export const fetchModalDetails = async (stockId) => {
    try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}?entity=stock&id=${stockId}`, {
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
                return { error: 'Stock not found' };
            }
            
            throw new Error(errorData.message || `Failed to fetch stock`);
        }

        const data = await response.json();

        return { success: true, stocks: data.data.data };
        
    } catch (error) {
        console.error('Error fetching stock:', error);
        return { error: error.message };
    }
}