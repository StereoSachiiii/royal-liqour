import { API_ROUTES } from '../../dashboard.routes.js';
import { renderSearchableSelect, initSearchableSelect, getFormData } from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render cart creation form
 * @returns {string} HTML
 */
export function renderCartCreate() {
    return `
        <div class="admin-modal admin-modal--md">
             <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                <h2 class="text-xl font-semibold text-gray-900">Create New Cart</h2>
                <p class="text-sm text-gray-500 mt-1">Manually create a cart for a user.</p>
            </div>
            
            <div class="admin-modal__body bg-gray-50 p-6">
                <form id="cart-create-form">
                    <div class="bg-white p-4 rounded-lg border mb-4">
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Select User</h4>
                            <!-- Searchable User Select -->
                            ${renderSearchableSelect({
        label: 'User Search',
        name: 'user_id',
        required: true,
        placeholder: 'Type name or email to search...',
        helpText: 'Search for an existing user to assign this cart to.'
    })}
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-end pt-2">
                         <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                         <button type="submit" class="btn btn-primary">Create Cart</button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

/**
 * Initialize handlers for cart creation
 * @param {HTMLElement} container 
 * @param {Function} onSuccess 
 */
export function initCartCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#cart-create-form');
    if (!form) return;

    // Initialize User Search
    // We need to target the specific container for user_id if we had multiple
    // But since initSearchableSelect handles all .searchable-select-container inside, we can just pass form
    // provided we correctly identify the search strategy via options.
    // Wait... FormHelpers.initSearchableSelect assumes uniform options if passed to container?
    // Actually my implementation iterates wrappers. 
    // BUT I can't pass different options for different fields easily with current helper unless I select specifically.

    const userSelectWrapper = form.querySelector('.searchable-select-container');
    if (userSelectWrapper) {
        initSearchableSelect(userSelectWrapper, {
            searchUrlBuilder: (query) => `${API_ROUTES.USERS.SEARCH}?search=${encodeURIComponent(query)}&limit=5`,
            itemRenderer: (user) => `
                <div class="flex items-center gap-2">
                    <div class="font-medium">${escapeHtml(user.name || 'Unknown')}</div>
                    <div class="text-gray-500 text-sm">&lt;${escapeHtml(user.email)}&gt;</div>
                </div>
            `,
            onSelect: (user) => console.log('Selected user:', user)
        });
    }

    // Submit Handler
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Creating...';

            const formData = getFormData(form);

            if (!formData.user_id) {
                throw new Error('Please select a user');
            }

            const payload = {
                user_id: parseInt(formData.user_id),
                status: 'active' // Default status
            };

            console.log('[CartCreate] Creating cart:', payload);

            const response = await apiRequest(API_ROUTES.CARTS.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                closeModal();
                if (onSuccess) onSuccess(response.data, 'created');
            } else {
                throw new Error(response.message || 'Creation failed');
            }
        } catch (error) {
            console.error('[CartCreate] Error:', error);

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
