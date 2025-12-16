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
 * Render cocktail recipe create form with Material Design styling
 * @returns {Promise<string>} Form HTML
 */
export async function renderCocktailRecipeCreate() {
    try {
        const difficultyOptions = [
            { id: 'easy', name: 'Easy' },
            { id: 'medium', name: 'Medium' },
            { id: 'hard', name: 'Hard' }
        ];

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Create Cocktail Recipe</h2>
                    <p class="text-sm text-gray-500 mt-1">Add a new cocktail recipe to the system</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="cocktail-recipe-create-form" class="p-6">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Recipe Name',
            name: 'name',
            required: true,
            placeholder: 'Enter recipe name'
        })}
                                
                                ${renderTextarea({
            label: 'Description',
            name: 'description',
            placeholder: 'Brief description of the cocktail',
            rows: 3
        })}
                                
                                ${renderTextarea({
            label: 'Instructions',
            name: 'instructions',
            placeholder: 'Step by step preparation instructions',
            rows: 5
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Difficulty',
            name: 'difficulty',
            value: 'easy',
            items: difficultyOptions,
            required: true
        })}
                                
                                ${renderTextInput({
            label: 'Preparation Time (minutes)',
            name: 'preparation_time',
            type: 'number',
            placeholder: 'e.g. 5'
        })}
                                
                                ${renderTextInput({
            label: 'Serves',
            name: 'serves',
            type: 'number',
            value: 1,
            min: 1,
            placeholder: '1'
        })}
                                
                                ${renderImageInput({
            label: 'Recipe Image',
            name: 'image_url',
            required: false,
            id: 'recipe-image'
        })}
                                
                                ${renderCheckbox({
            label: 'Is Active',
            name: 'is_active',
            checked: true
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-outline" id="cancel-create">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="btn-text">Create Recipe</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[CocktailRecipeCreate] Error rendering form:', error);
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
 * Initialize cocktail recipe create form handlers
 * @param {HTMLElement} container - Modal container
 * @param {Function} onSuccess - Callback after successful creation
 */
export function initCocktailRecipeCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#cocktail-recipe-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[CocktailRecipeCreate] Form not found');
        return;
    }

    console.log('[CocktailRecipeCreate] Initializing handlers');

    // Initialize image upload
    initImageUpload(container, 'cocktail-recipes', 'recipe-image');

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

            if (!payload.name) throw new Error('Recipe name is required');

            console.log('[CocktailRecipeCreate] Creating recipe:', payload);

            // Submit to API
            const response = await apiRequest(API_ROUTES.COCKTAIL_RECIPES.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[CocktailRecipeCreate] Recipe created successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'created');
            } else {
                throw new Error(response.message || 'Failed to create recipe');
            }

        } catch (error) {
            console.error('[CocktailRecipeCreate] Error:', error);
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
