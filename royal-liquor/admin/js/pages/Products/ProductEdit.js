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
 * Render product edit form with Material Design styling
 * @param {number} productId - Product ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderProductEdit(productId) {
    try {
        // Fetch product data, categories, and suppliers
        const [productResponse, categoriesResponse, suppliersResponse] = await Promise.all([
            apiRequest(API_ROUTES.PRODUCTS.GET(productId)),
            apiRequest(API_ROUTES.CATEGORIES.LIST + buildQueryString({ limit: 100 })),
            apiRequest(API_ROUTES.SUPPLIERS.LIST + buildQueryString({ limit: 100 }))
        ]);

        const product = productResponse.data || {};
        const categories = categoriesResponse.data || [];
        const suppliers = suppliersResponse.data || [];

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Product</h2>
                    <p class="text-sm text-gray-500 mt-1">Update product information</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="product-edit-form" class="p-6" data-product-id="${productId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Product Name',
            name: 'name',
            value: product.name || '',
            required: true,
            placeholder: 'Enter product name'
        })}
                                
                                ${renderTextInput({
            label: 'Slug',
            name: 'slug',
            value: product.slug || '',
            required: true,
            placeholder: 'product-slug'
        })}
                                
                                ${renderTextInput({
            label: 'Price (Cents)',
            name: 'price_cents',
            type: 'number',
            value: product.price_cents || '',
            required: true,
            min: 1,
            placeholder: '1000'
        })}
                                
                                ${renderSelect({
            label: 'Category',
            name: 'category_id',
            value: product.category_id || '',
            required: true,
            items: categories,
            placeholder: 'Select a category'
        })}
                                
                                ${renderSelect({
            label: 'Supplier',
            name: 'supplier_id',
            value: product.supplier_id || '',
            items: suppliers,
            placeholder: 'Select a supplier (optional)'
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextarea({
            label: 'Description',
            name: 'description',
            value: product.description || '',
            placeholder: 'Enter product description',
            rows: 4
        })}
                                
                                ${renderImageInput({
            label: 'Product Image',
            name: 'image_url',
            currentUrl: product.image_url || '',
            required: false,
            id: 'product-image'
        })}
                                
                                ${renderCheckbox({
            label: 'Is Active',
            name: 'is_active',
            checked: product.is_active || false
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-product">
                                🗑️ Delete Product
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
        console.error('[ProductEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Product</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize product edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} productId - Product ID being edited
 * @param {Function} onSuccess - Callback after successful update
 */
export function initProductEditHandlers(container, productId, onSuccess) {
    const form = container.querySelector('#product-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-product');

    if (!form) {
        console.error('[ProductEdit] Form not found');
        return;
    }

    // Initialize image upload
    initImageUpload(container, 'products', 'product-image');

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeModal();
        });
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
                        deleteBtn.innerHTML = '🗑️ Delete Product';
                        deleteBtn.classList.remove('btn-warning');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.PRODUCTS.DELETE(productId), {
                    method: 'DELETE'
                });

                if (response.success) {
                    console.log('[ProductEdit] Product deleted successfully');
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Failed to delete product');
                }
            } catch (error) {
                console.error('[ProductEdit] Delete error:', error);
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
                deleteBtn.innerHTML = '🗑️ Delete Product';
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
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Saving...';

            // Get form data
            const formDataObj = getFormData(form);

            // Convert types
            const payload = {
                name: formDataObj.name,
                slug: formDataObj.slug,
                description: formDataObj.description || null,
                price_cents: parseInt(formDataObj.price_cents),
                category_id: parseInt(formDataObj.category_id),
                supplier_id: formDataObj.supplier_id ? parseInt(formDataObj.supplier_id) : null,
                image_url: formDataObj.image_url || null,
                is_active: formDataObj.is_active || false
            };

            console.log('[ProductEdit] Updating product:', payload);

            // Submit to API
            const response = await apiRequest(API_ROUTES.PRODUCTS.UPDATE(productId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                console.log('[ProductEdit] Product updated successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update product');
            }

        } catch (error) {
            console.error('[ProductEdit] Error:', error);
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
