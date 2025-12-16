import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { apiRequest } from '../../utils.js';

/**
 * Fetch recipe ingredients from API with proper error handling
 * @param {number} limit - Number of recipe ingredients to fetch
 * @param {number} offset - Offset for pagination
 * @param {string} query - Search query
 * @returns {Promise<Array|Object>} Array of recipe ingredients or error object
 */
export async function fetchRecipeIngredients(limit = 50, offset = 0, query = '') {
    try {
        const params = { limit, offset };

        // Use search endpoint when query is provided
        let url;
        if (query && query.trim()) {
            params.search = query.trim();
            url = API_ROUTES.RECIPE_INGREDIENTS.SEARCH + buildQueryString(params);
        } else {
            url = API_ROUTES.RECIPE_INGREDIENTS.LIST + buildQueryString(params);
        }

        console.log('[RecipeIngredients] Fetching:', url);
        const response = await apiRequest(url);
        return response.data || [];
    } catch (error) {
        console.error('Error fetching recipe ingredients:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single recipe ingredient details
 * @param {number} id - Ingredient ID
 * @returns {Promise<Object>}
 */
export async function fetchModalDetails(id) {
    try {
        const response = await apiRequest(API_ROUTES.RECIPE_INGREDIENTS.GET(id));
        return { success: true, recipe_ingredient: response.data };
    } catch (error) {
        console.error('Error fetching recipe ingredient:', error);
        return { error: error.message };
    }
}

/**
 * Fetch recipes for dropdown selection
 * @returns {Promise<Array>}
 */
export async function fetchRecipesForDropdown() {
    try {
        // Using cocktail-recipes endpoint
        const response = await apiRequest('/royal-liquor/api/v1/cocktail-recipes?limit=200');
        return response.data || [];
    } catch (error) {
        console.error('Error fetching recipes:', error);
        return [];
    }
}

/**
 * Create a new recipe ingredient
 * @param {Object} data - Ingredient data
 * @returns {Promise<Object>}
 */
export async function createRecipeIngredient(data) {
    return apiRequest(API_ROUTES.RECIPE_INGREDIENTS.CREATE, {
        method: 'POST',
        body: data
    });
}

/**
 * Update a recipe ingredient
 * @param {number} id - Ingredient ID
 * @param {Object} data - Updated data
 * @returns {Promise<Object>}
 */
export async function updateRecipeIngredient(id, data) {
    return apiRequest(API_ROUTES.RECIPE_INGREDIENTS.UPDATE(id), {
        method: 'PUT',
        body: data
    });
}

/**
 * Delete a recipe ingredient
 * @param {number} id - Ingredient ID
 * @returns {Promise<Object>}
 */
export async function deleteRecipeIngredient(id) {
    return apiRequest(API_ROUTES.RECIPE_INGREDIENTS.DELETE(id), {
        method: 'DELETE'
    });
}