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
 * Render product create form with Material Design styling
 * @returns {Promise<string>} Form HTML
 */
export async function renderProductCreate() {
    try {
        // Fetch categories and suppliers for dropdowns
        const [categoriesResponse, suppliersResponse] = await Promise.all([
            apiRequest(API_ROUTES.CATEGORIES.LIST + buildQueryString({ limit: 100 })),
            apiRequest(API_ROUTES.SUPPLIERS.LIST + buildQueryString({ limit: 100 }))
        ]);

        const categories = categoriesResponse.data || [];
        const suppliers = suppliersResponse.data || [];

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Create New Product</h2>
                    <p class="text-sm text-gray-500 mt-1">Fill in the details to add a new product</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="product-create-form" class="p-6">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Product Name',
            name: 'name',
            required: true,
            placeholder: 'Enter product name'
        })}
                                
                                ${renderTextInput({
            label: 'Slug',
            name: 'slug',
            required: true,
            placeholder: 'product-slug'
        })}
                                
                                ${renderTextInput({
            label: 'Price (Cents)',
            name: 'price_cents',
            type: 'number',
            required: true,
            min: 1,
            placeholder: '1000'
        })}
                                
                                ${renderSelect({
            label: 'Category',
            name: 'category_id',
            required: true,
            items: categories,
            placeholder: 'Select a category'
        })}
                                
                                ${renderSelect({
            label: 'Supplier',
            name: 'supplier_id',
            items: suppliers,
            placeholder: 'Select a supplier (optional)'
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextarea({
            label: 'Description',
            name: 'description',
            placeholder: 'Enter product description',
            rows: 4
        })}
                                
                                ${renderImageInput({
            label: 'Product Image',
            name: 'image_url',
            required: false,
            id: 'product-image'
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
                                <span class="btn-text">Create Product</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[ProductCreate] Error rendering form:', error);
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
 * Initialize product create form handlers
 * @param {HTMLElement} container - Modal container
 * @param {Function} onSuccess - Callback after successful creation
 */
export function initProductCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#product-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[ProductCreate] Form not found');
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

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Creating...';

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

            console.log('[ProductCreate] Creating product:', payload);

            // Submit to API
            const response = await apiRequest(API_ROUTES.PRODUCTS.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[ProductCreate] Product created successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data);
            } else {
                throw new Error(response.message || 'Failed to create product');
            }

        } catch (error) {
            console.error('[ProductCreate] Error:', error);
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
