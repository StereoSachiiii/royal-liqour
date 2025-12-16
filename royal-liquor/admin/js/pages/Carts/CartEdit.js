import { API_ROUTES } from '../../dashboard.routes.js';
import { renderSelect, getFormData } from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render cart edit form
 * @param {number} cartId
 * @returns {Promise<string>}
 */
export async function renderCartEdit(cartId) {
    try {
        const response = await apiRequest(API_ROUTES.CARTS.GET(cartId));
        const cart = response.data || {};

        return `
            <div class="admin-modal admin-modal--md">
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Cart Status</h2>
                    <p class="text-sm text-gray-500 mt-1">Cart #${cart.id} • Session: ${escapeHtml(cart.session_id || '').substring(0, 8)}...</p>
                </div>
                
                <div class="admin-modal__body bg-gray-50 p-6">
                    <form id="cart-edit-form" data-cart-id="${cartId}">
                        <div class="bg-white p-4 rounded-lg border mb-4">
                            ${renderSelect({
            label: 'Cart Status',
            name: 'status',
            value: cart.status,
            options: [
                { value: 'active', label: 'Active' },
                { value: 'converted', label: 'Converted' },
                { value: 'abandoned', label: 'Abandoned' },
                { value: 'expired', label: 'Expired' }
            ],
            required: true,
            helpText: 'Changing status may affect user access to this cart.'
        })}
                        </div>

                        <div class="d-flex gap-3 justify-end pt-2">
                             <button type="button" class="btn btn-danger btn-outline" id="delete-cart-btn">🗑️ Delete Cart</button>
                             <div class="flex-spacer"></div>
                             <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                             <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[CartEdit] Error:', error);
        return `<div class="admin-entity__empty">Error loading cart: ${escapeHtml(error.message)}</div>`;
    }
}

/**
 * Initialize cart edit handlers
 * @param {HTMLElement} container 
 * @param {number} cartId 
 * @param {Function} onSuccess 
 */
export function initCartEditHandlers(container, cartId, onSuccess) {
    const form = container.querySelector('#cart-edit-form');
    const deleteBtn = container.querySelector('#delete-cart-btn');
    if (!form) return;

    // Delete handler with double-click confirmation
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            if (!deleteBtn.dataset.confirmed) {
                deleteBtn.dataset.confirmed = 'pending';
                deleteBtn.innerHTML = '⚠️ Click to Confirm Delete';
                deleteBtn.classList.add('btn-warning');
                deleteBtn.classList.remove('btn-outline');
                setTimeout(() => {
                    if (deleteBtn.dataset.confirmed === 'pending') {
                        deleteBtn.dataset.confirmed = '';
                        deleteBtn.innerHTML = '🗑️ Delete Cart';
                        deleteBtn.classList.remove('btn-warning');
                        deleteBtn.classList.add('btn-outline');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.CARTS.DELETE(cartId), { method: 'DELETE' });
                if (response.success) {
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Delete failed');
                }
            } catch (error) {
                console.error('[CartEdit] Delete error:', error);
                let errorEl = form.querySelector('.form-error-banner');
                if (!errorEl) {
                    errorEl = document.createElement('div');
                    errorEl.className = 'form-error-banner';
                    form.prepend(errorEl);
                }

                // Check for FK constraint error
                const errorMsg = error.message || '';
                if (errorMsg.includes('foreign key') || errorMsg.includes('still referenced')) {
                    errorEl.textContent = '⚠️ Cannot delete: This cart is linked to existing orders. Remove the orders first or mark the cart as abandoned instead.';
                } else {
                    errorEl.textContent = `Error: ${errorMsg}`;
                }
                errorEl.style.display = 'block';

                deleteBtn.disabled = false;
                deleteBtn.dataset.confirmed = '';
                deleteBtn.innerHTML = '🗑️ Delete Cart';
                deleteBtn.classList.remove('btn-warning');
                deleteBtn.classList.add('btn-outline');
            }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Saving...';

            const formData = getFormData(form);
            const payload = { status: formData.status };

            console.log('[CartEdit] Updating cart:', payload);

            const response = await apiRequest(API_ROUTES.CARTS.UPDATE(cartId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Update failed');
            }
        } catch (error) {
            console.error('[CartEdit] Update error:', error);
            // Show inline error
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
