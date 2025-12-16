import { API_ROUTES } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderTextarea,
    renderCheckbox,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal, formatDate } from '../../utils.js';

/**
 * Render supplier edit form with Material Design styling
 * @param {number} supplierId - Supplier ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderSupplierEdit(supplierId) {
    try {
        const response = await apiRequest(API_ROUTES.SUPPLIERS.GET(supplierId));
        const supplier = response.data || {};

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Supplier</h2>
                    <p class="text-sm text-gray-500 mt-1">${escapeHtml(supplier.name || '')}</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="supplier-edit-form" class="p-6" data-supplier-id="${supplierId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Supplier Name',
            name: 'name',
            value: supplier.name || '',
            required: true,
            placeholder: 'e.g. Premium Spirits Inc.'
        })}
                                
                                ${renderTextInput({
            label: 'Email',
            name: 'email',
            type: 'email',
            value: supplier.email || '',
            placeholder: 'contact@supplier.com'
        })}
                                
                                ${renderTextInput({
            label: 'Phone',
            name: 'phone',
            type: 'tel',
            value: supplier.phone || '',
            placeholder: '+1 (555) 123-4567'
        })}
                                
                                <!-- Stats -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Statistics</h4>
                                    <div class="text-sm text-gray-500">
                                        <div>Products: ${supplier.total_products ?? supplier.product_count ?? 0}</div>
                                        <div>Created: ${supplier.created_at ? formatDate(supplier.created_at) : '-'}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextarea({
            label: 'Address',
            name: 'address',
            value: supplier.address || '',
            rows: 3,
            placeholder: '123 Supply Chain Rd, City, State 12345'
        })}
                                
                                ${renderCheckbox({
            label: 'Active',
            name: 'is_active',
            checked: supplier.is_active !== false,
            helpText: 'Allow ordering from this supplier'
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-supplier">
                                🗑️ Delete Supplier
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
        console.error('[SupplierEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Supplier</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize supplier edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} supplierId - Supplier ID being edited
 * @param {Function} onSuccess - Callback after successful update
 */
export function initSupplierEditHandlers(container, supplierId, onSuccess) {
    const form = container.querySelector('#supplier-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-supplier');

    if (!form) {
        console.error('[SupplierEdit] Form not found');
        return;
    }

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
                        deleteBtn.innerHTML = '🗑️ Delete Supplier';
                        deleteBtn.classList.remove('btn-warning');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.SUPPLIERS.DELETE(supplierId), {
                    method: 'DELETE'
                });

                if (response.success) {
                    console.log('[SupplierEdit] Supplier deleted successfully');
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Failed to delete supplier');
                }
            } catch (error) {
                console.error('[SupplierEdit] Delete error:', error);
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
                deleteBtn.innerHTML = '🗑️ Delete Supplier';
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
                email: formDataObj.email || null,
                phone: formDataObj.phone || null,
                address: formDataObj.address || null,
                is_active: formDataObj.is_active === 'on' || formDataObj.is_active === true
            };

            console.log('[SupplierEdit] Updating supplier:', payload);

            const response = await apiRequest(API_ROUTES.SUPPLIERS.UPDATE(supplierId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                console.log('[SupplierEdit] Supplier updated successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update supplier');
            }

        } catch (error) {
            console.error('[SupplierEdit] Error:', error);
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
