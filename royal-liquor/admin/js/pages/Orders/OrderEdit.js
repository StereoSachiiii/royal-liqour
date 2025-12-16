import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderTextarea,
    renderSelect,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

// Order status options
const ORDER_STATUSES = [
    { id: 'pending', name: 'Pending' },
    { id: 'processing', name: 'Processing' },
    { id: 'shipped', name: 'Shipped' },
    { id: 'delivered', name: 'Delivered' },
    { id: 'cancelled', name: 'Cancelled' },
    { id: 'refunded', name: 'Refunded' }
];

/**
 * Render order edit form with Material Design styling
 * @param {number} orderId - Order ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderOrderEdit(orderId) {
    try {
        // Fetch order data and users
        const [orderResponse, usersResponse] = await Promise.all([
            apiRequest(API_ROUTES.ORDERS.GET(orderId)),
            apiRequest(API_ROUTES.USERS.LIST + buildQueryString({ limit: 100 }))
        ]);

        const order = orderResponse.data || {};
        const users = usersResponse.data || [];

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Order</h2>
                    <p class="text-sm text-gray-500 mt-1">Order #${escapeHtml(order.order_number || order.id)}</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="order-edit-form" class="p-6" data-order-id="${orderId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Customer',
            name: 'user_id',
            required: true,
            value: order.user_id || '',
            items: users.map(u => ({ id: u.id, name: `${u.name || u.username} (${u.email})` })),
            placeholder: 'Select a customer'
        })}
                                
                                ${renderSelect({
            label: 'Status',
            name: 'status',
            required: true,
            value: order.status || 'pending',
            items: ORDER_STATUSES,
            placeholder: 'Select status'
        })}
                                
                                ${renderTextInput({
            label: 'Order Number',
            name: 'order_number',
            value: order.order_number || '',
            required: true,
            placeholder: 'ORD-XXXXX'
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Total (Cents)',
            name: 'total_cents',
            type: 'number',
            value: order.total_cents || '',
            required: true,
            min: 0,
            placeholder: '10000'
        })}
                                
                                ${renderTextarea({
            label: 'Notes',
            name: 'notes',
            value: order.notes || '',
            placeholder: 'Order notes (optional)',
            rows: 4
        })}
                                
                                <!-- Order Info (Read Only) -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Order Info</h4>
                                    <div class="text-sm text-gray-500">
                                        <div>Created: ${order.created_at || '-'}</div>
                                        <div>Paid At: ${order.paid_at || 'Not paid'}</div>
                                        <div>Items: ${order.item_count || 0}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-order">
                                🗑️ Delete Order
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
        console.error('[OrderEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Order</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize order edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} orderId - Order ID being edited
 * @param {Function} onSuccess - Callback after successful update
 */
export function initOrderEditHandlers(container, orderId, onSuccess) {
    const form = container.querySelector('#order-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-order');

    if (!form) {
        console.error('[OrderEdit] Form not found');
        return;
    }

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
                        deleteBtn.innerHTML = '🗑️ Delete Order';
                        deleteBtn.classList.remove('btn-warning');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.ORDERS.DELETE(orderId), {
                    method: 'DELETE'
                });

                if (response.success) {
                    console.log('[OrderEdit] Order deleted successfully');
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Failed to delete order');
                }
            } catch (error) {
                console.error('[OrderEdit] Delete error:', error);
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
                deleteBtn.innerHTML = '🗑️ Delete Order';
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
                user_id: parseInt(formDataObj.user_id),
                status: formDataObj.status,
                order_number: formDataObj.order_number,
                total_cents: parseInt(formDataObj.total_cents),
                notes: formDataObj.notes || null
            };

            console.log('[OrderEdit] Updating order:', payload);

            const response = await apiRequest(API_ROUTES.ORDERS.UPDATE(orderId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                console.log('[OrderEdit] Order updated successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update order');
            }

        } catch (error) {
            console.error('[OrderEdit] Error:', error);
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
