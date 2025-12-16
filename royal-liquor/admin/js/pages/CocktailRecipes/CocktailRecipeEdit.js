import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderTextarea,
    renderSelect,
    renderCheckbox,
    renderImageInput,
    initImageUpload,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render cocktail recipe edit form with Material Design styling
 * @param {number} recipeId - Recipe ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderCocktailRecipeEdit(recipeId) {
    try {
        // Fetch recipe data
        const response = await apiRequest(API_ROUTES.COCKTAIL_RECIPES.GET(recipeId));
        const recipe = response.data || {};

        console.log('[CocktailRecipeEdit] Loaded recipe:', recipe);

        const difficultyOptions = [
            { id: 'easy', name: 'Easy' },
            { id: 'medium', name: 'Medium' },
            { id: 'hard', name: 'Hard' }
        ];

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Cocktail Recipe</h2>
                    <p class="text-sm text-gray-500 mt-1">Update recipe information</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="cocktail-recipe-edit-form" class="p-6" data-recipe-id="${recipeId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Recipe Name',
            name: 'name',
            value: recipe.name || '',
            required: true,
            placeholder: 'Enter recipe name'
        })}
                                
                                ${renderTextarea({
            label: 'Description',
            name: 'description',
            value: recipe.description || '',
            placeholder: 'Brief description of the cocktail',
            rows: 3
        })}
                                
                                ${renderTextarea({
            label: 'Instructions',
            name: 'instructions',
            value: recipe.instructions || '',
            placeholder: 'Step by step preparation instructions',
            rows: 5
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Difficulty',
            name: 'difficulty',
            value: recipe.difficulty || 'easy',
            items: difficultyOptions,
            required: true
        })}
                                
                                ${renderTextInput({
            label: 'Preparation Time (minutes)',
            name: 'preparation_time',
            type: 'number',
            value: recipe.preparation_time || '',
            placeholder: 'e.g. 5'
        })}
                                
                                ${renderTextInput({
            label: 'Serves',
            name: 'serves',
            type: 'number',
            value: recipe.serves || 1,
            min: 1,
            placeholder: '1'
        })}
                                
                                ${renderImageInput({
            label: 'Recipe Image',
            name: 'image_url',
            currentUrl: recipe.image_url || '',
            required: false,
            id: 'recipe-image'
        })}
                                
                                ${renderCheckbox({
            label: 'Is Active',
            name: 'is_active',
            checked: recipe.is_active !== false
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-recipe">
                                🗑️ Delete Recipe
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
        console.error('[CocktailRecipeEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Recipe</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize cocktail recipe edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} recipeId - Recipe ID being edited
 * @param {Function} onSuccess - Callback after successful update/delete
 */
export function initCocktailRecipeEditHandlers(container, recipeId, onSuccess) {
    const form = container.querySelector('#cocktail-recipe-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-recipe');

    if (!form) {
        console.error('[CocktailRecipeEdit] Form not found');
        return;
    }

    console.log('[CocktailRecipeEdit] Initializing handlers for recipe:', recipeId);

    // Initialize image upload
    initImageUpload(container, 'cocktail-recipes', 'recipe-image');

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => closeModal());
    }

    // Delete button (double-click)
    if (deleteBtn) {
        let deleteClickCount = 0;
        let deleteTimeout = null;

        deleteBtn.addEventListener('click', async () => {
            deleteClickCount++;

            if (deleteClickCount === 1) {
                deleteBtn.textContent = '⚠️ Click again to confirm delete';
                deleteBtn.classList.add('btn-danger');
                deleteTimeout = setTimeout(() => {
                    deleteClickCount = 0;
                    deleteBtn.innerHTML = '🗑️ Delete Recipe';
                    deleteBtn.classList.remove('btn-danger');
                }, 3000);
            } else if (deleteClickCount === 2) {
                clearTimeout(deleteTimeout);
                deleteBtn.disabled = true;
                deleteBtn.textContent = 'Deleting...';

                try {
                    const response = await apiRequest(API_ROUTES.COCKTAIL_RECIPES.DELETE(recipeId), {
                        method: 'DELETE'
                    });

                    if (response.success) {
                        console.log('[CocktailRecipeEdit] Recipe deleted');
                        closeModal();
                        if (onSuccess) onSuccess(null, 'deleted');
                    } else {
                        throw new Error(response.message || 'Failed to delete recipe');
                    }
                } catch (error) {
                    console.error('[CocktailRecipeEdit] Delete error:', error);
                    deleteClickCount = 0;
                    deleteBtn.innerHTML = '🗑️ Delete Recipe';
                    deleteBtn.disabled = false;

                    let errorEl = form.querySelector('.form-error-banner');
                    if (!errorEl) {
                        errorEl = document.createElement('div');
                        errorEl.className = 'form-error-banner';
                        form.prepend(errorEl);
                    }
                    errorEl.textContent = `Delete failed: ${error.message}`;
                    errorEl.style.display = 'block';
                }
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

            // Build payload
            const payload = {
                name: formDataObj.name,
                description: formDataObj.description || null,
                instructions: formDataObj.instructions || null,
                difficulty: formDataObj.difficulty || 'easy',
                preparation_time: formDataObj.preparation_time ? parseInt(formDataObj.preparation_time) : null,
                serves: formDataObj.serves ? parseInt(formDataObj.serves) : 1,
                image_url: formDataObj.image_url || null,
                is_active: formDataObj.is_active || false
            };

            console.log('[CocktailRecipeEdit] Updating recipe:', payload);

            // Submit to API
            const response = await apiRequest(API_ROUTES.COCKTAIL_RECIPES.UPDATE(recipeId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                console.log('[CocktailRecipeEdit] Recipe updated successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update recipe');
            }

        } catch (error) {
            console.error('[CocktailRecipeEdit] Error:', error);
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
