import { API_ROUTES } from '../../dashboard.routes.js';
import { renderTextInput, renderSelect, renderCheckbox } from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';
import { fetchUsersForDropdown } from './UserAddresses.utils.js';

const ADDRESS_TYPES = [
    { id: 'billing', name: 'Billing' },
    { id: 'shipping', name: 'Shipping' },
    { id: 'both', name: 'Both' }
];

/**
 * Render user address create form
 * @returns {Promise<string>}
 */
export async function renderUserAddressCreate() {
    try {
        const users = await fetchUsersForDropdown();

        // Format user items for dropdown
        const userItems = users.map(u => ({
            id: u.id,
            name: `${u.name} (${u.email})`
        }));

        return `
            <div class="admin-modal admin-modal--lg">
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Add New User Address</h2>
                </div>
                
                <div class="admin-modal__body bg-gray-50 p-6">
                    <form id="user-address-create-form">
                        <div class="products_section-title mb-3">User & Type</div>
                        <div class="d-grid gap-4" style="grid-template-columns: 1fr 1fr;">
                            ${renderSelect({
            label: 'User',
            name: 'user_id',
            items: userItems,
            required: true,
            placeholder: 'Select a user'
        })}
                            ${renderSelect({
            label: 'Address Type',
            name: 'address_type',
            value: 'both',
            items: ADDRESS_TYPES
        })}
                        </div>

                        <div class="products_section-title mt-4 mb-3">Recipient Info</div>
                        <div class="d-grid gap-4" style="grid-template-columns: 1fr 1fr;">
                            ${renderTextInput({
            label: 'Recipient Name',
            name: 'recipient_name',
            placeholder: 'Full name of recipient'
        })}
                            ${renderTextInput({
            label: 'Phone',
            name: 'phone',
            type: 'tel',
            placeholder: '+94 77 123 4567'
        })}
                        </div>

                        <div class="products_section-title mt-4 mb-3">Address Details</div>
                        ${renderTextInput({
            label: 'Address Line 1',
            name: 'address_line1',
            required: true,
            placeholder: 'Street address'
        })}
                        ${renderTextInput({
            label: 'Address Line 2',
            name: 'address_line2',
            placeholder: 'Apartment, suite, unit, etc.'
        })}
                        <div class="d-grid gap-4" style="grid-template-columns: 1fr 1fr 1fr;">
                            ${renderTextInput({
            label: 'City',
            name: 'city',
            required: true
        })}
                            ${renderTextInput({
            label: 'State',
            name: 'state'
        })}
                            ${renderTextInput({
            label: 'Postal Code',
            name: 'postal_code',
            required: true
        })}
                        </div>
                        ${renderTextInput({
            label: 'Country',
            name: 'country',
            value: 'Sri Lanka'
        })}
                        
                        ${renderCheckbox({
            label: 'Set as Default',
            name: 'is_default',
            helpText: 'This will be the default address for this type'
        })}

                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Address</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[UserAddressCreate] Error:', error);
        return `<div class="admin-entity__empty">Error loading form: ${escapeHtml(error.message)}</div>`;
    }
}

/**
 * Initialize handlers
 * @param {HTMLElement} container 
 * @param {Function} onSuccess 
 */
export function initUserAddressCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#user-address-create-form');
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
                address_type: form.querySelector('[name="address_type"]').value,
                recipient_name: form.querySelector('[name="recipient_name"]').value || null,
                phone: form.querySelector('[name="phone"]').value || null,
                address_line1: form.querySelector('[name="address_line1"]').value,
                address_line2: form.querySelector('[name="address_line2"]').value || null,
                city: form.querySelector('[name="city"]').value,
                state: form.querySelector('[name="state"]').value || null,
                postal_code: form.querySelector('[name="postal_code"]').value,
                country: form.querySelector('[name="country"]').value || 'Sri Lanka',
                is_default: form.querySelector('[name="is_default"]')?.checked ?? false
            };

            console.log('[UserAddressCreate] Creating address:', payload);

            const response = await apiRequest(API_ROUTES.USER_ADDRESSES.CREATE, {
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
            console.error('[UserAddressCreate] Create error:', error);
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
