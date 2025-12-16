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
 * Render category edit form with Material Design styling
 * @param {number} categoryId - Category ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderCategoryEdit(categoryId) {
    try {
        const response = await apiRequest(API_ROUTES.CATEGORIES.GET(categoryId));
        const category = response.data || {};

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Category</h2>
                    <p class="text-sm text-gray-500 mt-1">${escapeHtml(category.name || '')}</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="category-edit-form" class="p-6" data-category-id="${categoryId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Category Name',
            name: 'name',
            value: category.name || '',
            required: true,
            placeholder: 'e.g. Whiskey'
        })}
                                
                                ${renderTextInput({
            label: 'Slug',
            name: 'slug',
            value: category.slug || '',
            placeholder: 'e.g. whiskey'
        })}
                                
                                ${renderTextarea({
            label: 'Description',
            name: 'description',
            value: category.description || '',
            rows: 4,
            placeholder: 'Category description...'
        })}
                                
                                <!-- Stats -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Statistics</h4>
                                    <div class="text-sm text-gray-500">
                                        <div>Products: ${category.product_count || category.total_products || 0}</div>
                                        <div>Created: ${category.created_at || '-'}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderImageInput({
            label: 'Category Image',
            name: 'image_url',
            existingUrl: category.image_url || ''
        })}
                                
                                ${renderCheckbox({
            label: 'Active',
            name: 'is_active',
            checked: category.is_active !== false,
            helpText: 'Show this category on the storefront'
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-category">
                                🗑️ Delete Category
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
        console.error('[CategoryEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Category</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize category edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} categoryId - Category ID being edited
 * @param {Function} onSuccess - Callback after successful update
 */
export function initCategoryEditHandlers(container, categoryId, onSuccess) {
    const form = container.querySelector('#category-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-category');

    if (!form) {
        console.error('[CategoryEdit] Form not found');
        return;
    }

    // Initialize image upload
    initImageUpload(container);

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => closeModal());
    }

    // Delete button
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            // Show confirmation by changing button state
            if (!deleteBtn.dataset.confirmed) {
                deleteBtn.dataset.confirmed = 'pending';
                deleteBtn.innerHTML = '⚠️ Click again to confirm';
                deleteBtn.classList.add('btn-warning');
                setTimeout(() => {
                    if (deleteBtn.dataset.confirmed === 'pending') {
                        deleteBtn.dataset.confirmed = '';
                        deleteBtn.innerHTML = '🗑️ Delete Category';
                        deleteBtn.classList.remove('btn-warning');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.CATEGORIES.DELETE(categoryId), {
                    method: 'DELETE'
                });

                if (response.success) {
                    console.log('[CategoryEdit] Category deleted successfully');
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Failed to delete category');
                }
            } catch (error) {
                console.error('[CategoryEdit] Delete error:', error);
                // Show inline error instead of alert
                let errorEl = form.querySelector('.form-error-banner');
                if (!errorEl) {
                    errorEl = document.createElement('div');
                    errorEl.className = 'form-error-banner';
                    form.prepend(errorEl);
                }
                errorEl.textContent = `Delete failed: ${error.message}`;
                errorEl.style.display = 'block';
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '🗑️ Delete Category';
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

            const formDataObj = getFormData(form);

            const payload = {
                name: formDataObj.name,
                slug: formDataObj.slug || undefined,
                description: formDataObj.description || '',
                image_url: formDataObj.image_url || null,
                is_active: formDataObj.is_active === 'on' || formDataObj.is_active === true
            };

            console.log('[CategoryEdit] Updating category:', payload);

            const response = await apiRequest(API_ROUTES.CATEGORIES.UPDATE(categoryId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                console.log('[CategoryEdit] Category updated successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update category');
            }

        } catch (error) {
            console.error('[CategoryEdit] Error:', error);
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
