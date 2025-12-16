import { API_ROUTES } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderCheckbox,
    renderImageInput,
    initImageUpload,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render warehouse create form with Material Design styling
 * @returns {Promise<string>} Form HTML
 */
export async function renderWarehouseCreate() {
    return `
        <div class="admin-modal admin-modal--lg">
            <!-- Header -->
            <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                <h2 class="text-xl font-semibold text-gray-900">Create Warehouse</h2>
                <p class="text-sm text-gray-500 mt-1">Add a new storage location</p>
            </div>
            
            <!-- Body -->
            <div class="admin-modal__body bg-gray-50">
                <form id="warehouse-create-form" class="p-6">
                    <!-- Two Column Grid -->
                    <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                        <!-- Left Column -->
                        <div class="d-flex flex-col gap-4">
                            ${renderTextInput({
        label: 'Warehouse Name',
        name: 'name',
        required: true,
        placeholder: 'e.g. Main Distribution Center'
    })}
                            
                            ${renderTextInput({
        label: 'Address',
        name: 'address',
        placeholder: '123 Warehouse Dr, City, State 12345'
    })}
                            
                            ${renderTextInput({
        label: 'Phone',
        name: 'phone',
        type: 'tel',
        placeholder: '+1 (555) 123-4567'
    })}
                        </div>
                        
                        <!-- Right Column -->
                        <div class="d-flex flex-col gap-4">
                            ${renderImageInput({
        label: 'Warehouse Image',
        name: 'image_url',
        id: 'warehouse-image',
        currentUrl: ''
    })}
                            
                            ${renderCheckbox({
        label: 'Active',
        name: 'is_active',
        checked: true,
        helpText: 'Accept inventory at this location'
    })}
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                        <button type="button" class="btn btn-outline" id="cancel-create">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="btn-text">Create Warehouse</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

/**
 * Initialize warehouse create form handlers
 * @param {HTMLElement} container - Modal container
 * @param {Function} onSuccess - Callback after successful creation
 */
export function initWarehouseCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#warehouse-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[WarehouseCreate] Form not found');
        return;
    }

    // Initialize image upload with entity for storage path
    initImageUpload(container, 'warehouses', 'warehouse-image');

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
                address: formDataObj.address || null,
                phone: formDataObj.phone || null,
                image_url: formDataObj.image_url || null,
                is_active: formDataObj.is_active === 'on' || formDataObj.is_active === true
            };

            console.log('[WarehouseCreate] Creating warehouse:', payload);

            const response = await apiRequest(API_ROUTES.WAREHOUSES.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[WarehouseCreate] Warehouse created successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data);
            } else {
                throw new Error(response.message || 'Failed to create warehouse');
            }

        } catch (error) {
            console.error('[WarehouseCreate] Error:', error);
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
