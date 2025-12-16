import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderTextarea,
    renderSelect,
    renderCheckbox,
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
 * Render order create form with Material Design styling
 * @returns {Promise<string>} Form HTML
 */
export async function renderOrderCreate() {
    try {
        // Fetch users and carts for dropdowns
        const [usersResponse, cartsResponse] = await Promise.all([
            apiRequest(API_ROUTES.USERS.LIST + buildQueryString({ limit: 100 })),
            apiRequest(API_ROUTES.CARTS.LIST + buildQueryString({ limit: 100 }))
        ]);
        const users = usersResponse.data || [];
        const carts = cartsResponse.data || [];

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Create New Order</h2>
                    <p class="text-sm text-gray-500 mt-1">Fill in the order details</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <!-- Warning Disclosure -->
                    <div class="order-create-warning">
                        <span class="order-create-warning-icon">⚠️</span>
                        <div>
                            <strong>Important:</strong> Each cart can only be used for one order. 
                            If a cart already has an associated order, you must create a new cart first.
                        </div>
                    </div>
                    
                    <form id="order-create-form" class="p-6">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Customer',
            name: 'user_id',
            required: true,
            items: users.map(u => ({ id: u.id, name: `${u.name || u.username} (${u.email})` })),
            placeholder: 'Select a customer'
        })}
                                
                                ${renderSelect({
            label: 'Status',
            name: 'status',
            required: true,
            items: ORDER_STATUSES,
            value: 'pending',
            placeholder: 'Select status'
        })}
                                
                                ${renderTextInput({
            label: 'Order Number',
            name: 'order_number',
            required: true,
            placeholder: 'ORD-XXXXX'
        })}
                                
                                ${renderSelect({
            label: 'Cart',
            name: 'cart_id',
            required: true,
            items: carts.map(c => ({ id: c.id, name: `Cart #${c.id} - User: ${c.user_name || c.user_id}` })),
            placeholder: 'Select a cart'
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Total (Cents)',
            name: 'total_cents',
            type: 'number',
            required: true,
            min: 0,
            placeholder: '10000'
        })}
                                
                                ${renderTextarea({
            label: 'Notes',
            name: 'notes',
            placeholder: 'Order notes (optional)',
            rows: 4
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-outline" id="cancel-create">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="btn-text">Create Order</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[OrderCreate] Error rendering form:', error);
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
 * Initialize order create form handlers
 * @param {HTMLElement} container - Modal container
 * @param {Function} onSuccess - Callback after successful creation
 */
export function initOrderCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#order-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[OrderCreate] Form not found');
        return;
    }

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
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Creating...';

            const formDataObj = getFormData(form);

            const payload = {
                user_id: parseInt(formDataObj.user_id),
                cart_id: parseInt(formDataObj.cart_id),
                status: formDataObj.status,
                order_number: formDataObj.order_number,
                total_cents: parseInt(formDataObj.total_cents),
                notes: formDataObj.notes || null
            };

            console.log('[OrderCreate] Creating order:', payload);

            const response = await apiRequest(API_ROUTES.ORDERS.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[OrderCreate] Order created successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data);
            } else {
                throw new Error(response.message || 'Failed to create order');
            }

        } catch (error) {
            console.error('[OrderCreate] Error:', error);
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
