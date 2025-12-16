import { API_ROUTES } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderTextarea,
    renderCheckbox,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render supplier create form with Material Design styling
 * @returns {Promise<string>} Form HTML
 */
export async function renderSupplierCreate() {
    return `
        <div class="admin-modal admin-modal--lg">
            <!-- Header -->
            <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                <h2 class="text-xl font-semibold text-gray-900">Create Supplier</h2>
                <p class="text-sm text-gray-500 mt-1">Add a new product supplier</p>
            </div>
            
            <!-- Body -->
            <div class="admin-modal__body bg-gray-50">
                <form id="supplier-create-form" class="p-6">
                    <!-- Two Column Grid -->
                    <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                        <!-- Left Column -->
                        <div class="d-flex flex-col gap-4">
                            ${renderTextInput({
        label: 'Supplier Name',
        name: 'name',
        required: true,
        placeholder: 'e.g. Premium Spirits Inc.'
    })}
                            
                            ${renderTextInput({
        label: 'Email',
        name: 'email',
        type: 'email',
        placeholder: 'contact@supplier.com'
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
                            ${renderTextarea({
        label: 'Address',
        name: 'address',
        rows: 3,
        placeholder: '123 Supply Chain Rd, City, State 12345'
    })}
                            
                            ${renderCheckbox({
        label: 'Active',
        name: 'is_active',
        checked: true,
        helpText: 'Allow ordering from this supplier'
    })}
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                        <button type="button" class="btn btn-outline" id="cancel-create">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="btn-text">Create Supplier</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

/**
 * Initialize supplier create form handlers
 * @param {HTMLElement} container - Modal container
 * @param {Function} onSuccess - Callback after successful creation
 */
export function initSupplierCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#supplier-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[SupplierCreate] Form not found');
        return;
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
                email: formDataObj.email || null,
                phone: formDataObj.phone || null,
                address: formDataObj.address || null,
                is_active: formDataObj.is_active === 'on' || formDataObj.is_active === true
            };

            console.log('[SupplierCreate] Creating supplier:', payload);

            const response = await apiRequest(API_ROUTES.SUPPLIERS.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[SupplierCreate] Supplier created successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data);
            } else {
                throw new Error(response.message || 'Failed to create supplier');
            }

        } catch (error) {
            console.error('[SupplierCreate] Error:', error);
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
