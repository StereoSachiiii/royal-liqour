 import { API_URL_SUPPLIERS, DETAIL_VIEW_API_URL } from "../config.js";
 
 /**
 * Fetch products from API with proper error handling
 * @param {number} limit - Number of products to fetch
 * @param {number} offset - Offset for pagination
 * @returns {Promise<Array|Object>} Array of products or error object
 */
export async function fetchAllSuppliers(limit = DEFAULT_LIMIT, offset = 0,search='') {
    try {
        const response = await fetch(`${API_URL_SUPPLIERS}?&limit=${limit}&offset=${offset}&search=${search}`, {
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
            throw new Error(data.message || 'Failed to fetch products');
        }

        return data.data || [];
        
    } catch (error) {
        console.error('Error fetching products:', error);
        return { error: error.message };
    }
}



/**
 * Fetch single product by ID
 * @param {number} productId - Product ID
 * @returns {Promise<Object>} Product data or error
 */
export async function fetchSupplierDetails(supplierId) {
    try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}?entity=suppliers&id=${supplierId}`, {
            method: 'GET',
            credentials: 'include'
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            
            if (response.status === 401) {
                window.location.href = '/royal-liquor/public/auth/auth.php';
                return { error: 'Please login to continue' };
            }
            
            if (response.status === 403) {
                return { error: 'Access denied. Admin privileges required.' };
            }
            
            if (response.status === 404) {
                return { error: 'Product not found' };
            }
            
            throw new Error(errorData.message || 'Failed to fetch product');
        }

        const data = await response.json();
        return { success: true, supplier: data.data.data };
        
    } catch (error) {
        console.error('Error fetching product:', error);
        return { error: error.message };
    }
}
