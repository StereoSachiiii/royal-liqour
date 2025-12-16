import { API_ROUTES } from '../../dashboard.routes.js';
import { renderSelect, renderCheckbox, renderTextarea } from '../../FormHelpers.js';
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
 * Render feedback create form
 * @returns {Promise<string>}
 */
export async function renderFeedbackCreate() {
    try {
        const [users, products] = await Promise.all([
            fetchUsersForDropdown(),
            fetchProductsForDropdown()
        ]);

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
                    <h2 class="text-xl font-semibold text-gray-900">Add New Feedback</h2>
                </div>
                
                <div class="admin-modal__body bg-gray-50 p-6">
                    <form id="feedback-create-form">
                        <div class="products_section-title mb-3">User & Product</div>
                        <div class="d-grid gap-4" style="grid-template-columns: 1fr 1fr;">
                            ${renderSelect({
            label: 'User',
            name: 'user_id',
            items: userItems,
            required: true,
            placeholder: 'Select a user'
        })}
                            ${renderSelect({
            label: 'Product',
            name: 'product_id',
            items: productItems,
            required: true,
            placeholder: 'Select a product'
        })}
                        </div>

                        <div class="products_section-title mt-4 mb-3">Rating & Review</div>
                        ${renderSelect({
            label: 'Rating',
            name: 'rating',
            value: 5,
            items: RATING_OPTIONS,
            required: true
        })}
                        ${renderTextarea({
            label: 'Comment',
            name: 'comment',
            placeholder: 'Write the review comment...',
            rows: 4
        })}

                        <div class="products_section-title mt-4 mb-3">Status</div>
                        <div class="d-flex gap-4">
                            ${renderCheckbox({
            label: 'Verified Purchase',
            name: 'is_verified_purchase',
            helpText: 'User has purchased this product'
        })}
                            ${renderCheckbox({
            label: 'Active',
            name: 'is_active',
            checked: true,
            helpText: 'Feedback is visible to public'
        })}
                        </div>

                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Feedback</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[FeedbackCreate] Error:', error);
        return `<div class="admin-entity__empty">Error loading form: ${escapeHtml(error.message)}</div>`;
    }
}

/**
 * Initialize handlers
 */
export function initFeedbackCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#feedback-create-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Creating...';

            const payload = {
                user_id: parseInt(form.querySelector('[name="user_id"]').value),
                product_id: parseInt(form.querySelector('[name="product_id"]').value),
                rating: parseInt(form.querySelector('[name="rating"]').value),
                comment: form.querySelector('[name="comment"]').value || null,
                is_verified_purchase: form.querySelector('[name="is_verified_purchase"]')?.checked ?? false,
                is_active: form.querySelector('[name="is_active"]')?.checked ?? true
            };

            console.log('[FeedbackCreate] Creating feedback:', payload);

            const response = await apiRequest(API_ROUTES.FEEDBACK.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                closeModal();
                if (onSuccess) onSuccess(response.data, 'created');
            } else {
                throw new Error(response.message || 'Create failed');
            }
        } catch (error) {
            console.error('[FeedbackCreate] Create error:', error);
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
