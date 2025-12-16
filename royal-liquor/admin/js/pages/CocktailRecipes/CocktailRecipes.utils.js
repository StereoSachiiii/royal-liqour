import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest } from '../../utils.js';

/**
 * Fetch cocktail recipes from API with proper error handling
 * @param {number} limit - Number of cocktail recipes to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Array|Object>} Array of cocktail recipes or error object
 */
export async function fetchCocktailRecipes(limit = 50, offset = 0, query = '') {
    try {
        const params = { limit, offset };

        // Use search endpoint when query is provided
        let url;
        if (query && query.trim()) {
            params.search = query.trim();
            url = API_ROUTES.COCKTAIL_RECIPES.SEARCH + buildQueryString(params);
        } else {
            url = API_ROUTES.COCKTAIL_RECIPES.LIST + buildQueryString(params);
        }

        console.log('[CocktailRecipes] Fetching:', url);
        const response = await apiRequest(url);

        // Handle data.items structure (from getAll)
        const data = response.data?.items || response.data || [];
        return data;
    } catch (error) {
        console.error('Error fetching cocktail recipes:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single cocktail recipe details
 * @param {number} id - Recipe ID
 * @returns {Promise<Object>}
 */
export async function fetchModalDetails(id) {
    try {
        const response = await apiRequest(API_ROUTES.COCKTAIL_RECIPES.GET(id));
        return { success: true, cocktail_recipe: response.data };
    } catch (error) {
        console.error('Error fetching cocktail recipe:', error);
        return { error: error.message };
    }
}

/**
 * Create a new cocktail recipe
 * @param {Object} data - Recipe data
 * @returns {Promise<Object>}
 */
export async function createCocktailRecipe(data) {
    return apiRequest(API_ROUTES.COCKTAIL_RECIPES.CREATE, {
        method: 'POST',
        body: data
    });
}

/**
 * Update a cocktail recipe
 * @param {number} id - Recipe ID
 * @param {Object} data - Updated data
 * @returns {Promise<Object>}
 */
export async function updateCocktailRecipe(id, data) {
    return apiRequest(API_ROUTES.COCKTAIL_RECIPES.UPDATE(id), {
        method: 'PUT',
        body: data
    });
}

/**
 * Delete a cocktail recipe
 * @param {number} id - Recipe ID
 * @returns {Promise<Object>}
 */
export async function deleteCocktailRecipe(id) {
    return apiRequest(API_ROUTES.COCKTAIL_RECIPES.DELETE(id), {
        method: 'DELETE'
    });
}