import { API_URL, DETAIL_VIEW_API_URL } from "../config.js";

/**
 * Fetch recipe ingredients from API with proper error handling
 * @param {number} limit - Number of recipe ingredients to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Array|Object>} Array of recipe ingredients or error object
 */
export async function fetchRecipeIngredients(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = `${DETAIL_VIEW_API_URL}?entity=recipe_ingredients&limit=${limit}&offset=${offset}${query ? `&search=${encodeURIComponent(query)}` : ''}`;
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
        return data.data.items || [];
        
    } catch (error) {
        console.error('Error fetching recipe ingredients:', error);
        return { error: error.message };
    }
}

export const fetchModalDetails = async (ingredientId) => {
    try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}?entity=recipe_ingredients&id=${ingredientId}`, {
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
                return { error: 'Recipe ingredient not found' };
            }
            
            throw new Error(errorData.message || `Failed to fetch recipe ingredient`);
        }

        const data = await response.json();

        return { success: true, recipe_ingredient: data.data.data };
        
    } catch (error) {
        console.error('Error fetching recipe ingredient:', error);
        return { error: error.message };
    }
}