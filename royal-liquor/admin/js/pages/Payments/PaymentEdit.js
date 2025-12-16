import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderTextarea,
    renderSelect,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render payment edit form with Material Design styling
 * @param {number} paymentId - Payment ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderPaymentEdit(paymentId) {
    try {
        // Fetch payment data
        const response = await apiRequest(API_ROUTES.PAYMENTS.GET(paymentId));
        const payment = response.data || {};

        console.log('[PaymentEdit] Loaded payment:', payment);

        const statusOptions = [
            { id: 'pending', name: 'Pending' },
            { id: 'captured', name: 'Captured' },
            { id: 'failed', name: 'Failed' },
            { id: 'refunded', name: 'Refunded' },
            { id: 'voided', name: 'Voided' }
        ];

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Payment</h2>
                    <p class="text-sm text-gray-500 mt-1">Update payment information</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="payment-edit-form" class="p-6" data-payment-id="${paymentId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                <div class="products_field">
                                    <strong>Payment ID</strong>
                                    <span>${payment.id}</span>
                                </div>
                                
                                <div class="products_field">
                                    <strong>Order ID</strong>
                                    <span>${payment.order_id}</span>
                                </div>
                                
                                <div class="products_field">
                                    <strong>Amount</strong>
                                    <span>$${(payment.amount_cents / 100).toFixed(2)} ${payment.currency || 'USD'}</span>
                                </div>
                                
                                <div class="products_field">
                                    <strong>Gateway</strong>
                                    <span>${escapeHtml(payment.gateway || '-')}</span>
                                </div>
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Status',
            name: 'status',
            value: payment.status || 'pending',
            items: statusOptions,
            required: true
        })}
                                
                                ${renderTextInput({
            label: 'Transaction ID',
            name: 'transaction_id',
            value: payment.transaction_id || '',
            placeholder: 'Enter transaction ID'
        })}
                                
                                ${renderTextarea({
            label: 'Payload (JSON)',
            name: 'payload',
            value: payment.payload ? JSON.stringify(payment.payload, null, 2) : '',
            placeholder: '{"key": "value"}',
            rows: 4
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-payment">
                                🗑️ Delete Payment
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
        console.error('[PaymentEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Payment</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize payment edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} paymentId - Payment ID being edited
 * @param {Function} onSuccess - Callback after successful update/delete
 */
export function initPaymentEditHandlers(container, paymentId, onSuccess) {
    const form = container.querySelector('#payment-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-payment');

    if (!form) {
        console.error('[PaymentEdit] Form not found');
        return;
    }

    console.log('[PaymentEdit] Initializing handlers for payment:', paymentId);

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => closeModal());
    }

    // Delete button (double-click)
    if (deleteBtn) {
        let deleteClickCount = 0;
        let deleteTimeout = null;

        deleteBtn.addEventListener('click', async () => {
            deleteClickCount++;

            if (deleteClickCount === 1) {
                deleteBtn.textContent = '⚠️ Click again to confirm delete';
                deleteBtn.classList.add('btn-danger');
                deleteTimeout = setTimeout(() => {
                    deleteClickCount = 0;
                    deleteBtn.innerHTML = '🗑️ Delete Payment';
                    deleteBtn.classList.remove('btn-danger');
                }, 3000);
            } else if (deleteClickCount === 2) {
                clearTimeout(deleteTimeout);
                deleteBtn.disabled = true;
                deleteBtn.textContent = 'Deleting...';

                try {
                    const response = await apiRequest(API_ROUTES.PAYMENTS.DELETE(paymentId), {
                        method: 'DELETE'
                    });

                    if (response.success) {
                        console.log('[PaymentEdit] Payment deleted');
                        closeModal();
                        if (onSuccess) onSuccess(null, 'deleted');
                    } else {
                        throw new Error(response.message || 'Failed to delete payment');
                    }
                } catch (error) {
                    console.error('[PaymentEdit] Delete error:', error);
                    deleteClickCount = 0;
                    deleteBtn.innerHTML = '🗑️ Delete Payment';
                    deleteBtn.disabled = false;

                    let errorEl = form.querySelector('.form-error-banner');
                    if (!errorEl) {
                        errorEl = document.createElement('div');
                        errorEl.className = 'form-error-banner';
                        form.prepend(errorEl);
                    }
                    errorEl.textContent = `Delete failed: ${error.message}`;
                    errorEl.style.display = 'block';
                }
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

            // Get form data
            const formDataObj = getFormData(form);

            // Build payload
            const payload = {
                status: formDataObj.status,
                transaction_id: formDataObj.transaction_id || null
            };

            // Parse JSON payload if provided
            if (formDataObj.payload && formDataObj.payload.trim()) {
                try {
                    payload.payload = JSON.parse(formDataObj.payload);
                } catch (jsonErr) {
                    throw new Error('Invalid JSON in payload field');
                }
            }

            console.log('[PaymentEdit] Updating payment:', payload);

            // Submit to API
            const response = await apiRequest(API_ROUTES.PAYMENTS.UPDATE(paymentId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                console.log('[PaymentEdit] Payment updated successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update payment');
            }

        } catch (error) {
            console.error('[PaymentEdit] Error:', error);
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
