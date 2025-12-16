import { API_ROUTES } from '../../dashboard.routes.js';
import { renderTextInput, getFormData } from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal, formatCurrency } from '../../utils.js';

/**
 * Render cart item edit form
 * @param {number} itemId
 * @returns {Promise<string>}
 */
export async function renderCartItemEdit(itemId) {
    try {
        const response = await apiRequest(API_ROUTES.CART_ITEMS.GET(itemId));
        const item = response.data || {};

        return `
            <div class="admin-modal admin-modal--md">
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Cart Item</h2>
                    <p class="text-sm text-gray-500 mt-1">Item #${item.id} • Cart #${item.cart_id}</p>
                </div>
                
                <div class="admin-modal__body bg-gray-50 p-6">
                    <form id="cart-item-edit-form" data-item-id="${itemId}">
                        <div class="bg-white p-4 rounded-lg border mb-4">
                            <div class="d-flex items-center gap-3 mb-4 pb-4 border-b">
                                ${item.product_image ? `<img src="${item.product_image}" class="w-12 h-12 rounded object-cover">` : ''}
                                <div>
                                    <div class="font-medium">${escapeHtml(item.product_name || 'Unknown Product')}</div>
                                    <div class="text-sm text-gray-500">${formatCurrency(item.price_at_add_cents)} each</div>
                                </div>
                            </div>

                            ${renderTextInput({
            label: 'Quantity',
            name: 'quantity',
            type: 'number',
            value: item.quantity,
            required: true,
            min: 1,
            helpText: 'Update the quantity for this item.'
        })}
                        </div>

                        <div class="d-flex gap-3 justify-end pt-2">
                             <button type="button" class="btn btn-danger btn-outline" id="delete-cart-item">🗑️ Remove Item</button>
                             <div class="flex-spacer"></div>
                             <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                             <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[CartItemEdit] Error:', error);
        return `<div class="admin-entity__empty">Error loading item: ${escapeHtml(error.message)}</div>`;
    }
}

/**
 * Initialize handlers
 * @param {HTMLElement} container 
 * @param {number} itemId 
 * @param {Function} onSuccess 
 */
export function initCartItemEditHandlers(container, itemId, onSuccess) {
    const form = container.querySelector('#cart-item-edit-form');
    const deleteBtn = container.querySelector('#delete-cart-item');
    if (!form) return;

    // Delete handler
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            // Double-click confirmation
            if (!deleteBtn.dataset.confirmed) {
                deleteBtn.dataset.confirmed = 'pending';
                deleteBtn.innerHTML = '⚠️ Click to Confirm';
                deleteBtn.classList.add('btn-warning');
                deleteBtn.classList.remove('btn-outline');
                setTimeout(() => {
                    if (deleteBtn.dataset.confirmed === 'pending') {
                        deleteBtn.dataset.confirmed = '';
                        deleteBtn.innerHTML = '🗑️ Remove Item';
                        deleteBtn.classList.remove('btn-warning');
                        deleteBtn.classList.add('btn-outline');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.CART_ITEMS.DELETE(itemId), { method: 'DELETE' });
                if (response.success) {
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message);
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert(`Delete failed: ${error.message}`); // Fallback
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '🗑️ Remove Item';
            }
        });
    }

    // Submit handler
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Saving...';

            const formData = getFormData(form);
            const payload = { quantity: parseInt(formData.quantity) };

            console.log('[CartItemEdit] Updating item:', payload);

            const response = await apiRequest(API_ROUTES.CART_ITEMS.UPDATE(itemId), {
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
            console.error('[CartItemEdit] Update error:', error);
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
