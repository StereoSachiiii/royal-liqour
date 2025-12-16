import { API_ROUTES } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderCheckbox,
    renderImageInput,
    initImageUpload,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal, formatDate } from '../../utils.js';

/**
 * Render warehouse edit form with Material Design styling
 * @param {number} warehouseId - Warehouse ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderWarehouseEdit(warehouseId) {
    try {
        const response = await apiRequest(API_ROUTES.WAREHOUSES.GET(warehouseId));
        const warehouse = response.data || {};

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Warehouse</h2>
                    <p class="text-sm text-gray-500 mt-1">${escapeHtml(warehouse.name || '')}</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="warehouse-edit-form" class="p-6" data-warehouse-id="${warehouseId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Warehouse Name',
            name: 'name',
            value: warehouse.name || '',
            required: true,
            placeholder: 'e.g. Main Distribution Center'
        })}
                                
                                ${renderTextInput({
            label: 'Address',
            name: 'address',
            value: warehouse.address || '',
            placeholder: '123 Warehouse Dr, City, State 12345'
        })}
                                
                                ${renderTextInput({
            label: 'Phone',
            name: 'phone',
            type: 'tel',
            value: warehouse.phone || '',
            placeholder: '+1 (555) 123-4567'
        })}
                                
                                <!-- Stats -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Statistics</h4>
                                    <div class="text-sm text-gray-500">
                                        <div>Stock Entries: ${warehouse.total_stock_entries ?? 0}</div>
                                        <div>Products: ${warehouse.unique_products ?? 0}</div>
                                        <div>Created: ${warehouse.created_at ? formatDate(warehouse.created_at) : '-'}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderImageInput({
            label: 'Warehouse Image',
            name: 'image_url',
            id: 'warehouse-image',
            currentUrl: warehouse.image_url || ''
        })}
                                
                                ${renderCheckbox({
            label: 'Active',
            name: 'is_active',
            checked: warehouse.is_active !== false,
            helpText: 'Accept inventory at this location'
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-warehouse">
                                🗑️ Delete Warehouse
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
        console.error('[WarehouseEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Warehouse</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize warehouse edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} warehouseId - Warehouse ID being edited
 * @param {Function} onSuccess - Callback after successful update
 */
export function initWarehouseEditHandlers(container, warehouseId, onSuccess) {
    const form = container.querySelector('#warehouse-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-warehouse');

    if (!form) {
        console.error('[WarehouseEdit] Form not found');
        return;
    }

    // Initialize image upload with entity for storage path
    initImageUpload(container, 'warehouses', 'warehouse-image');

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
                deleteBtn.classList.remove('btn-outline');
                setTimeout(() => {
                    if (deleteBtn.dataset.confirmed === 'pending') {
                        deleteBtn.dataset.confirmed = '';
                        deleteBtn.innerHTML = '🗑️ Delete Warehouse';
                        deleteBtn.classList.remove('btn-warning');
                        deleteBtn.classList.add('btn-outline');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.WAREHOUSES.DELETE(warehouseId), {
                    method: 'DELETE'
                });

                if (response.success) {
                    console.log('[WarehouseEdit] Warehouse deleted successfully');
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Failed to delete warehouse');
                }
            } catch (error) {
                console.error('[WarehouseEdit] Delete error:', error);
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
                deleteBtn.innerHTML = '🗑️ Delete Warehouse';
                deleteBtn.dataset.confirmed = '';
                deleteBtn.classList.remove('btn-warning');
                deleteBtn.classList.add('btn-outline');
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
                address: formDataObj.address || null,
                phone: formDataObj.phone || null,
                image_url: formDataObj.image_url || null,
                is_active: formDataObj.is_active === 'on' || formDataObj.is_active === true
            };

            console.log('[WarehouseEdit] Updating warehouse:', payload);

            const response = await apiRequest(API_ROUTES.WAREHOUSES.UPDATE(warehouseId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                console.log('[WarehouseEdit] Warehouse updated successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update warehouse');
            }

        } catch (error) {
            console.error('[WarehouseEdit] Error:', error);
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
