import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { renderTextInput, renderSelect, renderCheckbox, getFormData } from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render recipe ingredient create form (matching Products pattern exactly)
 * @returns {Promise<string>} Form HTML
 */
export async function renderRecipeIngredientCreate() {
    try {
        // Fetch recipes and products in parallel
        const [recipesRes, productsRes] = await Promise.all([
            apiRequest(API_ROUTES.COCKTAIL_RECIPES.LIST + buildQueryString({ limit: 200 })),
            apiRequest(API_ROUTES.PRODUCTS.LIST + buildQueryString({ limit: 200 }))
        ]);

        console.log('[RecipeIngredientCreate] Raw API responses:', { recipesRes, productsRes });

        // Handle different response structures with explicit checks
        let recipes = [];
        let products = [];

        // Try to extract recipes array from various response formats
        if (Array.isArray(recipesRes)) {
            recipes = recipesRes;
        } else if (recipesRes && recipesRes.data && Array.isArray(recipesRes.data.items)) {
            // Cocktail recipes format: {success: true, data: {items: [...], total: 3}}
            recipes = recipesRes.data.items;
        } else if (recipesRes && Array.isArray(recipesRes.data)) {
            recipes = recipesRes.data;
        }

        // Try to extract products array
        if (Array.isArray(productsRes)) {
            products = productsRes;
        } else if (productsRes && Array.isArray(productsRes.data)) {
            products = productsRes.data;
        }

        console.log('[RecipeIngredientCreate] Parsed data:', { recipes, products });
        console.log('[RecipeIngredientCreate] Loaded', recipes.length, 'recipes,', products.length, 'products');

        // Format items for dropdowns (with safety checks)
        const recipeItems = (recipes || []).map(r => ({
            id: r.id,
            name: r.name || `Recipe #${r.id}`
        }));

        const productItems = (products || []).map(p => ({
            id: p.id,
            name: `${p.name} (${p.sku || 'No SKU'})`
        }));

        const unitOptions = [
            { id: 'oz', name: 'oz (ounce)' },
            { id: 'ml', name: 'ml (milliliter)' },
            { id: 'dash', name: 'dash' },
            { id: 'drop', name: 'drop' },
            { id: 'tsp', name: 'tsp (teaspoon)' },
            { id: 'tbsp', name: 'tbsp (tablespoon)' },
            { id: 'cup', name: 'cup' },
            { id: 'piece', name: 'piece' },
            { id: 'slice', name: 'slice' },
            { id: 'whole', name: 'whole' },
            { id: 'shot', name: 'shot' },
            { id: 'splash', name: 'splash' }
        ];

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Add Recipe Ingredient</h2>
                    <p class="text-sm text-gray-500 mt-1">Add a product as an ingredient to a cocktail recipe</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="recipe-ingredient-create-form" class="p-6">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Recipe',
            name: 'recipe_id',
            items: recipeItems,
            required: true,
            placeholder: 'Select a recipe'
        })}
                                
                                ${renderSelect({
            label: 'Product',
            name: 'product_id',
            items: productItems,
            required: true,
            placeholder: 'Select a product'
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Quantity',
            name: 'quantity',
            type: 'number',
            value: 1,
            required: true,
            placeholder: 'e.g. 1.5'
        })}
                                
                                ${renderSelect({
            label: 'Unit',
            name: 'unit',
            value: 'oz',
            items: unitOptions,
            required: true
        })}
                                
                                ${renderCheckbox({
            label: 'Optional Ingredient',
            name: 'is_optional',
            helpText: 'Can be omitted when making the cocktail'
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-outline" id="cancel-create">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="btn-text">Add Ingredient</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[RecipeIngredientCreate] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Form</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize handlers for the create form
 */
export function initRecipeIngredientCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#recipe-ingredient-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[RecipeIngredientCreate] Form not found in container:', container);
        console.error('[RecipeIngredientCreate] Container HTML:', container.innerHTML.substring(0, 500));
        return;
    }

    console.log('[RecipeIngredientCreate] Form found, initializing handlers');

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => closeModal());
    }

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Creating...';

            // Get form data
            const formDataObj = getFormData(form);

            // Convert types
            const payload = {
                recipe_id: parseInt(formDataObj.recipe_id),
                product_id: parseInt(formDataObj.product_id),
                quantity: parseFloat(formDataObj.quantity),
                unit: formDataObj.unit,
                is_optional: formDataObj.is_optional || false
            };

            if (!payload.recipe_id) throw new Error('Please select a recipe');
            if (!payload.product_id) throw new Error('Please select a product');

            console.log('[RecipeIngredientCreate] Creating ingredient:', payload);

            // Submit to API
            const response = await apiRequest(API_ROUTES.RECIPE_INGREDIENTS.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[RecipeIngredientCreate] Ingredient created successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'created');
            } else {
                throw new Error(response.message || 'Failed to create ingredient');
            }

        } catch (error) {
            console.error('[RecipeIngredientCreate] Error:', error);
            let errorEl = form.querySelector('.form-error-banner');
            if (!errorEl) {
                errorEl = document.createElement('div');
                errorEl.className = 'form-error-banner';
                form.prepend(errorEl);
            }
            errorEl.textContent = `Error: ${error.message}`;
            errorEl.style.display = 'block';

            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}
