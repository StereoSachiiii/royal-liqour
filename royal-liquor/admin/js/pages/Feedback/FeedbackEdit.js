import { API_ROUTES } from '../../dashboard.routes.js';
import { renderTextInput, renderSelect, renderCheckbox, renderTextarea } from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';
import { fetchUsersForDropdown, fetchProductsForDropdown } from './Feedback.utils.js';

const RATING_OPTIONS = [
    { id: 1, name: '1 - Poor' },
    { id: 2, name: '2 - Fair' },
    { id: 3, name: '3 - Good' },
    { id: 4, name: '4 - Very Good' },
    { id: 5, name: '5 - Excellent' }
];

/**
 * Render feedback edit form
 * @param {number} feedbackId
 * @returns {Promise<string>}
 */
export async function renderFeedbackEdit(feedbackId) {
    try {
        // Fetch feedback, users and products in parallel
        const [feedbackResponse, users, products] = await Promise.all([
            apiRequest(API_ROUTES.FEEDBACK.GET(feedbackId)),
            fetchUsersForDropdown(),
            fetchProductsForDropdown()
        ]);

        const feedback = feedbackResponse.data || {};

        // Format dropdown items
        const userItems = users.map(u => ({
            id: u.id,
            name: `${u.name} (${u.email})`
        }));

        const productItems = products.map(p => ({
            id: p.id,
            name: `${p.name} (${p.sku || 'N/A'})`
        }));

        return `
            <div class="admin-modal admin-modal--lg">
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Feedback</h2>
                    <p class="text-sm text-gray-500 mt-1">ID: ${feedback.id}</p>
                </div>
                
                <div class="admin-modal__body bg-gray-50 p-6">
                    <form id="feedback-edit-form" data-feedback-id="${feedbackId}">
                        <div class="products_section-title mb-3">User & Product</div>
                        <div class="d-grid gap-4" style="grid-template-columns: 1fr 1fr;">
                            ${renderSelect({
            label: 'User',
            name: 'user_id',
            value: feedback.user_id,
            items: userItems,
            required: true,
            placeholder: 'Select a user'
        })}
                            ${renderSelect({
            label: 'Product',
            name: 'product_id',
            value: feedback.product_id,
            items: productItems,
            required: true,
            placeholder: 'Select a product'
        })}
                        </div>

                        <div class="products_section-title mt-4 mb-3">Rating & Review</div>
                        ${renderSelect({
            label: 'Rating',
            name: 'rating',
            value: feedback.rating,
            items: RATING_OPTIONS,
            required: true
        })}
                        ${renderTextarea({
            label: 'Comment',
            name: 'comment',
            value: feedback.comment || '',
            placeholder: 'Write the review comment...',
            rows: 4
        })}

                        <div class="products_section-title mt-4 mb-3">Status</div>
                        <div class="d-flex gap-4">
                            ${renderCheckbox({
            label: 'Verified Purchase',
            name: 'is_verified_purchase',
            checked: feedback.is_verified_purchase || false,
            helpText: 'User has purchased this product'
        })}
                            ${renderCheckbox({
            label: 'Active',
            name: 'is_active',
            checked: feedback.is_active !== false,
            helpText: 'Feedback is visible to public'
        })}
                        </div>

                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-feedback">🗑️ Delete</button>
                            <div class="flex-spacer"></div>
                            <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[FeedbackEdit] Error:', error);
        return `<div class="admin-entity__empty">Error loading feedback: ${escapeHtml(error.message)}</div>`;
    }
}

/**
 * Initialize handlers
 */
export function initFeedbackEditHandlers(container, feedbackId, onSuccess) {
    const form = container.querySelector('#feedback-edit-form');
    const deleteBtn = container.querySelector('#delete-feedback');
    if (!form) return;

    // Delete handler
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            if (!deleteBtn.dataset.confirmed) {
                deleteBtn.dataset.confirmed = 'pending';
                deleteBtn.innerHTML = '⚠️ Click to Confirm';
                deleteBtn.classList.add('btn-warning');
                deleteBtn.classList.remove('btn-outline');
                setTimeout(() => {
                    if (deleteBtn.dataset.confirmed === 'pending') {
                        deleteBtn.dataset.confirmed = '';
                        deleteBtn.innerHTML = '🗑️ Delete';
                        deleteBtn.classList.remove('btn-warning');
                        deleteBtn.classList.add('btn-outline');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.FEEDBACK.DELETE(feedbackId), { method: 'DELETE' });
                if (response.success) {
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Delete failed');
                }
            } catch (error) {
                console.error('[FeedbackEdit] Delete error:', error);
                showFormError(form, error.message);
                deleteBtn.disabled = false;
                deleteBtn.dataset.confirmed = '';
                deleteBtn.innerHTML = '🗑️ Delete';
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

            const payload = {
                user_id: parseInt(form.querySelector('[name="user_id"]').value),
                product_id: parseInt(form.querySelector('[name="product_id"]').value),
                rating: parseInt(form.querySelector('[name="rating"]').value),
                comment: form.querySelector('[name="comment"]').value || null,
                is_verified_purchase: form.querySelector('[name="is_verified_purchase"]')?.checked ?? false,
                is_active: form.querySelector('[name="is_active"]')?.checked ?? true
            };

            console.log('[FeedbackEdit] Updating feedback:', payload);

            const response = await apiRequest(API_ROUTES.FEEDBACK.UPDATE(feedbackId), {
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
            console.error('[FeedbackEdit] Update error:', error);
            showFormError(form, error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

function showFormError(form, message) {
    let errorEl = form.querySelector('.form-error-banner');
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'form-error-banner';
        form.prepend(errorEl);
    }
    errorEl.textContent = `Error: ${message}`;
    errorEl.style.display = 'block';
}
