import { API_ROUTES } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderTextarea,
    renderCheckbox,
    renderImageInput,
    initImageUpload,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render category create form with Material Design styling
 * @returns {Promise<string>} Form HTML
 */
export async function renderCategoryCreate() {
    return `
        <div class="admin-modal admin-modal--lg">
            <!-- Header -->
            <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                <h2 class="text-xl font-semibold text-gray-900">Create Category</h2>
                <p class="text-sm text-gray-500 mt-1">Add a new product category</p>
            </div>
            
            <!-- Body -->
            <div class="admin-modal__body bg-gray-50">
                <form id="category-create-form" class="p-6">
                    <!-- Two Column Grid -->
                    <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                        <!-- Left Column -->
                        <div class="d-flex flex-col gap-4">
                            ${renderTextInput({
        label: 'Category Name',
        name: 'name',
        required: true,
        placeholder: 'e.g. Whiskey'
    })}
                            
                            ${renderTextInput({
        label: 'Slug',
        name: 'slug',
        placeholder: 'e.g. whiskey (auto-generated if blank)'
    })}
                            
                            ${renderTextarea({
        label: 'Description',
        name: 'description',
        rows: 4,
        placeholder: 'Category description...'
    })}
                        </div>
                        
                        <!-- Right Column -->
                        <div class="d-flex flex-col gap-4">
                            ${renderImageInput({
        label: 'Category Image',
        name: 'image_url',
        existingUrl: ''
    })}
                            
                            ${renderCheckbox({
        label: 'Active',
        name: 'is_active',
        checked: true,
        helpText: 'Show this category on the storefront'
    })}
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                        <button type="button" class="btn btn-outline" id="cancel-create">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="btn-text">Create Category</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

/**
 * Initialize category create form handlers
 * @param {HTMLElement} container - Modal container
 * @param {Function} onSuccess - Callback after successful creation
 */
export function initCategoryCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#category-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[CategoryCreate] Form not found');
        return;
    }

    // Initialize image upload
    initImageUpload(container);

    // Auto-generate slug from name
    const nameInput = form.querySelector('[name="name"]');
    const slugInput = form.querySelector('[name="slug"]');
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', () => {
            if (!slugInput.value || slugInput.dataset.autoGen === 'true') {
                slugInput.value = nameInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
                slugInput.dataset.autoGen = 'true';
            }
        });
        slugInput.addEventListener('input', () => {
            slugInput.dataset.autoGen = 'false';
        });
    }

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

            const formDataObj = getFormData(form);

            const payload = {
                name: formDataObj.name,
                slug: formDataObj.slug || undefined,
                description: formDataObj.description || '',
                image_url: formDataObj.image_url || null,
                is_active: formDataObj.is_active === 'on' || formDataObj.is_active === true
            };

            console.log('[CategoryCreate] Creating category:', payload);

            const response = await apiRequest(API_ROUTES.CATEGORIES.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[CategoryCreate] Category created successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data);
            } else {
                throw new Error(response.message || 'Failed to create category');
            }

        } catch (error) {
            console.error('[CategoryCreate] Error:', error);
            // Show inline error instead of alert
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
