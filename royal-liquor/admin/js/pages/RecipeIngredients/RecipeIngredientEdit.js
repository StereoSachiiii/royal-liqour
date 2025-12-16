import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import { renderTextInput, renderSelect, renderCheckbox, getFormData } from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render recipe ingredient edit form (matching Products pattern exactly)
 * @param {number} ingredientId - Ingredient ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderRecipeIngredientEdit(ingredientId) {
    try {
        // Fetch ingredient data and products in parallel
        const [ingredientRes, productsRes] = await Promise.all([
            apiRequest(API_ROUTES.RECIPE_INGREDIENTS.GET(ingredientId)),
            apiRequest(API_ROUTES.PRODUCTS.LIST + buildQueryString({ limit: 200 }))
        ]);

        const ingredient = ingredientRes.data || {};
        const products = productsRes.data || [];

        // Format product items
        const productItems = products.map(p => ({
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
                    <h2 class="text-xl font-semibold text-gray-900">Edit Recipe Ingredient</h2>
                    <p class="text-sm text-gray-500 mt-1">ID: ${ingredient.id} · Recipe: ${escapeHtml(ingredient.recipe_name || 'Unknown')}</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="recipe-ingredient-edit-form" class="p-6" data-id="${ingredientId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Product',
            name: 'product_id',
            value: ingredient.product_id,
            items: productItems,
            required: true,
            placeholder: 'Select a product'
        })}
                                
                                ${renderTextInput({
            label: 'Quantity',
            name: 'quantity',
            type: 'number',
            value: ingredient.quantity || 1,
            required: true,
            placeholder: 'e.g. 1.5'
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Unit',
            name: 'unit',
            value: ingredient.unit || 'oz',
            items: unitOptions,
            required: true
        })}
                                
                                ${renderCheckbox({
            label: 'Optional Ingredient',
            name: 'is_optional',
            checked: ingredient.is_optional || false,
            helpText: 'Can be omitted when making the cocktail'
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-ingredient">
                                🗑️ Delete Ingredient
                            </button>
                            <div class="d-flex gap-3">
                                <button type="button" class="btn btn-outline" id="cancel-edit">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <span class="btn-text">Save Changes</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[RecipeIngredientEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Ingredient</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize handlers for the edit form
 */
export function initRecipeIngredientEditHandlers(container, ingredientId, onSuccess) {
    const form = container.querySelector('#recipe-ingredient-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-ingredient');

    if (!form) {
        console.error('[RecipeIngredientEdit] Form not found');
        return;
    }

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => closeModal());
    }

    // Delete button with confirmation
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            if (!deleteBtn.dataset.confirmed) {
                deleteBtn.dataset.confirmed = 'pending';
                deleteBtn.innerHTML = '⚠️ Click again to confirm';
                deleteBtn.classList.add('btn-warning');
                setTimeout(() => {
                    if (deleteBtn.dataset.confirmed === 'pending') {
                        deleteBtn.dataset.confirmed = '';
                        deleteBtn.innerHTML = '🗑️ Delete Ingredient';
                        deleteBtn.classList.remove('btn-warning');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.RECIPE_INGREDIENTS.DELETE(ingredientId), {
                    method: 'DELETE'
                });

                if (response.success) {
                    console.log('[RecipeIngredientEdit] Ingredient deleted successfully');
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Failed to delete ingredient');
                }
            } catch (error) {
                console.error('[RecipeIngredientEdit] Delete error:', error);
                showFormError(form, error.message);
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '🗑️ Delete Ingredient';
                deleteBtn.dataset.confirmed = '';
            }
        });
    }

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Saving...';

            // Get form data
            const formDataObj = getFormData(form);

            // Convert types
            const payload = {
                product_id: parseInt(formDataObj.product_id),
                quantity: parseFloat(formDataObj.quantity),
                unit: formDataObj.unit,
                is_optional: formDataObj.is_optional || false
            };

            console.log('[RecipeIngredientEdit] Updating ingredient:', payload);

            // Submit to API
            const response = await apiRequest(API_ROUTES.RECIPE_INGREDIENTS.UPDATE(ingredientId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                console.log('[RecipeIngredientEdit] Ingredient updated successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update ingredient');
            }

        } catch (error) {
            console.error('[RecipeIngredientEdit] Error:', error);
            showFormError(form, error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

function showFormError(form, message) {
    let errorEl = form.querySelector('.form-error-banner');
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'form-error-banner';
        form.prepend(errorEl);
    }
    errorEl.textContent = `Error: ${message}`;
    errorEl.style.display = 'block';
}
