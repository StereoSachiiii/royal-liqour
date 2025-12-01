import { API_URL, DETAIL_VIEW_API_URL } from "../config.js";

/**
 * Fetch cocktail recipes from API with proper error handling
 * @param {number} limit - Number of cocktail recipes to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Array|Object>} Array of cocktail recipes or error object
 */
export async function fetchCocktailRecipes(limit = DEFAULT_LIMIT, offset = 0, query = '') {
    try {
        const url = `${DETAIL_VIEW_API_URL}?entity=cocktail_recipes&limit=${limit}&offset=${offset}${query ? `&search=${encodeURIComponent(query)}` : ''}`;
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
        console.error('Error fetching cocktail recipes:', error);
        return { error: error.message };
    }
}

export const fetchModalDetails = async (recipeId) => {
    try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}?entity=cocktail_recipes&id=${recipeId}`, {
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
                return { error: 'Cocktail recipe not found' };
            }
            
            throw new Error(errorData.message || `Failed to fetch cocktail recipe`);
        }

        const data = await response.json();

        return { success: true, cocktail_recipe: data.data.data };
        
    } catch (error) {
        console.error('Error fetching cocktail recipe:', error);
        return { error: error.message };
    }
}