import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderTextarea,
    renderSelect,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render payment create form with Material Design styling
 * @returns {Promise<string>} Form HTML
 */
export async function renderPaymentCreate() {
    try {
        // Fetch orders for dropdown
        const ordersResponse = await apiRequest(API_ROUTES.ORDERS.LIST + buildQueryString({ limit: 100 }));
        const orders = ordersResponse.data?.items || ordersResponse.data || [];

        const statusOptions = [
            { id: 'pending', name: 'Pending' },
            { id: 'captured', name: 'Captured' },
            { id: 'failed', name: 'Failed' }
        ];

        const gatewayOptions = [
            { id: 'stripe', name: 'Stripe' },
            { id: 'paypal', name: 'PayPal' },
            { id: 'manual', name: 'Manual' }
        ];

        const currencyOptions = [
            { id: 'USD', name: 'USD' },
            { id: 'LKR', name: 'LKR' },
            { id: 'EUR', name: 'EUR' }
        ];

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Create Payment</h2>
                    <p class="text-sm text-gray-500 mt-1">Add a new payment record</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="payment-create-form" class="p-6">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Order',
            name: 'order_id',
            value: '',
            items: orders.map(o => ({ id: o.id, name: `#${o.order_number || o.id} - $${(o.total_cents / 100).toFixed(2)}` })),
            required: true,
            placeholder: 'Select an order'
        })}
                                
                                ${renderTextInput({
            label: 'Amount (Cents)',
            name: 'amount_cents',
            type: 'number',
            required: true,
            placeholder: 'e.g. 1000 for $10.00',
            min: 1
        })}
                                
                                ${renderSelect({
            label: 'Currency',
            name: 'currency',
            value: 'LKR',
            items: currencyOptions,
            required: true
        })}
                                
                                ${renderSelect({
            label: 'Gateway',
            name: 'gateway',
            value: 'manual',
            items: gatewayOptions,
            required: true
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Status',
            name: 'status',
            value: 'pending',
            items: statusOptions,
            required: true
        })}
                                
                                ${renderTextInput({
            label: 'Gateway Order ID',
            name: 'gateway_order_id',
            placeholder: 'External gateway order ID'
        })}
                                
                                ${renderTextInput({
            label: 'Transaction ID',
            name: 'transaction_id',
            placeholder: 'Transaction reference'
        })}
                                
                                ${renderTextarea({
            label: 'Payload (JSON)',
            name: 'payload',
            placeholder: '{"key": "value"}',
            rows: 3
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-outline" id="cancel-create">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="btn-text">Create Payment</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[PaymentCreate] Error rendering form:', error);
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
 * Initialize payment create form handlers
 * @param {HTMLElement} container - Modal container
 * @param {Function} onSuccess - Callback after successful creation
 */
export function initPaymentCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#payment-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[PaymentCreate] Form not found');
        return;
    }

    console.log('[PaymentCreate] Initializing handlers');

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

            // Get form data
            const formDataObj = getFormData(form);

            // Build payload object
            const payload = {
                order_id: parseInt(formDataObj.order_id),
                amount_cents: parseInt(formDataObj.amount_cents),
                currency: formDataObj.currency || 'LKR',
                gateway: formDataObj.gateway || 'manual',
                gateway_order_id: formDataObj.gateway_order_id || null,
                transaction_id: formDataObj.transaction_id || null,
                status: formDataObj.status || 'pending'
            };

            // Build metadata payload - always structured
            const metadataPayload = {
                created_via: 'admin_panel',
                created_at: new Date().toISOString(),
                gateway: formDataObj.gateway || 'manual'
            };

            // If user provided custom JSON, merge it
            if (formDataObj.payload && formDataObj.payload.trim()) {
                try {
                    const customPayload = JSON.parse(formDataObj.payload);
                    payload.payload = { ...metadataPayload, ...customPayload };
                } catch (jsonErr) {
                    throw new Error('Invalid JSON in payload field');
                }
            } else {
                // Use default metadata
                payload.payload = metadataPayload;
            }

            if (!payload.order_id) throw new Error('Order is required');
            if (!payload.amount_cents || payload.amount_cents < 1) throw new Error('Valid amount is required');

            console.log('[PaymentCreate] Creating payment:', payload);

            // Submit to API
            const response = await apiRequest(API_ROUTES.PAYMENTS.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[PaymentCreate] Payment created successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'created');
            } else {
                throw new Error(response.message || 'Failed to create payment');
            }

        } catch (error) {
            console.error('[PaymentCreate] Error:', error);
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
