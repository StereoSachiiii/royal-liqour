import { API_ROUTES } from '../../dashboard.routes.js';
import { renderTextInput, getFormData } from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal, formatCurrency, formatDate } from '../../utils.js';

/**
 * Render order item edit form with Material Design styling
 * @param {number} orderItemId - Order item ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderOrderItemEdit(orderItemId) {
    try {
        const response = await apiRequest(API_ROUTES.ORDER_ITEMS.GET(orderItemId));
        const item = response.data || {};

        const subtotal = formatCurrency((item.price_cents || 0) * (item.quantity || 0));
        const unitPrice = formatCurrency(item.price_cents || 0);

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl d-flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Edit Order Item</h2>
                        <p class="text-sm text-gray-500 mt-1">Order #${item.order_id} • ${escapeHtml(item.product_name || 'Unknown Product')}</p>
                    </div>
                    <span class="badge badge-${item.order_status || 'pending'}">${escapeHtml(item.order_status || 'N/A')}</span>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <!-- Warning Banner -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mx-6 mt-4" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-color: #f59e0b;">
                        <div class="d-flex gap-3">
                            <span class="text-xl">⚠️</span>
                            <div>
                                <h4 class="font-medium text-amber-800" style="color: #92400e; margin: 0 0 4px 0;">Caution: Editing Order Items</h4>
                                <p class="text-sm text-amber-700" style="color: #a16207; margin: 0;">Editing order items can affect inventory and order totals. Use only for emergency corrections.</p>
                            </div>
                        </div>
                    </div>
                    
                    <form id="order-item-edit-form" class="p-6" data-order-item-id="${orderItemId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column: Current Details -->
                            <div class="d-flex flex-col gap-4">
                                <div class="bg-white p-4 rounded-lg border">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Current Item Details</h4>
                                    <div class="d-grid gap-2">
                                        <div class="d-flex justify-between"><span class="text-gray-500">Product</span><span class="font-medium">${escapeHtml(item.product_name || '-')}</span></div>
                                        <div class="d-flex justify-between"><span class="text-gray-500">Unit Price</span><span>${unitPrice}</span></div>
                                        <div class="d-flex justify-between"><span class="text-gray-500">Current Quantity</span><span class="font-medium">${item.quantity || 0}</span></div>
                                        <div class="d-flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-medium">${subtotal}</span></div>
                                    </div>
                                </div>
                                
                                <div class="bg-white p-4 rounded-lg border">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Order Information</h4>
                                    <div class="d-grid gap-2">
                                        <div class="d-flex justify-between"><span class="text-gray-500">Order ID</span><span>#${item.order_id}</span></div>
                                        <div class="d-flex justify-between"><span class="text-gray-500">Customer</span><span>${escapeHtml(item.user_name || '-')}</span></div>
                                        <div class="d-flex justify-between"><span class="text-gray-500">Order Status</span><span class="badge badge-${item.order_status || 'pending'}">${escapeHtml(item.order_status || 'N/A')}</span></div>
                                        <div class="d-flex justify-between"><span class="text-gray-500">Created</span><span>${item.created_at ? formatDate(item.created_at) : '-'}</span></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: Edit Form -->
                            <div class="d-flex flex-col gap-4">
                                <div class="bg-white p-4 rounded-lg border">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Update Fields</h4>
                                    
                                    ${renderTextInput({
            label: 'Quantity',
            name: 'quantity',
            type: 'number',
            value: item.quantity || 1,
            required: true,
            min: 1,
            placeholder: 'Enter quantity'
        })}
                                    <p class="text-xs text-gray-400 mt-1">Changing quantity may affect inventory and order total</p>
                                    
                                    <div style="margin-top: 1rem;">
                                        ${renderTextInput({
            label: 'Warehouse ID (Optional)',
            name: 'warehouse_id',
            type: 'number',
            value: item.warehouse_id || '',
            min: 1,
            placeholder: 'Leave empty if not applicable'
        })}
                                        <p class="text-xs text-gray-400 mt-1">Only change to reassign fulfillment warehouse</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-outline" id="cancel-edit">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="btn-text">⚠️ Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[OrderItemEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Order Item</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize order item edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} orderItemId - Order item ID being edited
 * @param {Function} onSuccess - Callback after successful update
 */
export function initOrderItemEditHandlers(container, orderItemId, onSuccess) {
    const form = container.querySelector('#order-item-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');

    if (!form) {
        console.error('[OrderItemEdit] Form not found');
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

        // Double-click confirmation for critical edits
        if (!submitBtn.dataset.confirmed) {
            submitBtn.dataset.confirmed = 'pending';
            submitBtn.innerHTML = '⚠️ Confirm Changes?';
            submitBtn.classList.add('btn-warning');
            submitBtn.classList.remove('btn-primary');
            setTimeout(() => {
                if (submitBtn.dataset.confirmed === 'pending') {
                    submitBtn.dataset.confirmed = '';
                    submitBtn.innerHTML = originalText; // Restore original innerHTML (with icon)
                    submitBtn.classList.remove('btn-warning');
                    submitBtn.classList.add('btn-primary');
                }
            }, 3000);
            return;
        }

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Saving...';

            const formDataObj = getFormData(form);

            const payload = {
                quantity: parseInt(formDataObj.quantity)
            };

            // Only include warehouse_id if provided
            if (formDataObj.warehouse_id && formDataObj.warehouse_id.trim() !== '') {
                payload.warehouse_id = parseInt(formDataObj.warehouse_id);
            }

            console.log('[OrderItemEdit] Updating order item:', payload);

            const response = await apiRequest(API_ROUTES.ORDER_ITEMS.UPDATE(orderItemId), {
                method: 'PUT',
                body: payload // apiRequest handles encoding
            });

            if (response.success) {
                console.log('[OrderItemEdit] Order item updated successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update order item');
            }

        } catch (error) {
            console.error('[OrderItemEdit] Error:', error);
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
            submitBtn.dataset.confirmed = '';
            submitBtn.innerHTML = originalText;
            submitBtn.classList.remove('btn-warning');
            submitBtn.classList.add('btn-primary');
        }
    });
}
