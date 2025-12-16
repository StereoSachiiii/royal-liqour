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
 * Render user address edit form
 * @param {number} addressId
 * @returns {Promise<string>}
 */
export async function renderUserAddressEdit(addressId) {
    try {
        // Fetch address and users in parallel
        const [addressResponse, users] = await Promise.all([
            apiRequest(API_ROUTES.USER_ADDRESSES.GET(addressId)),
            fetchUsersForDropdown()
        ]);

        const address = addressResponse.data || {};

        // Format user items for dropdown
        const userItems = users.map(u => ({
            id: u.id,
            name: `${u.name} (${u.email})`
        }));

        return `
            <div class="admin-modal admin-modal--lg">
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit User Address</h2>
                    <p class="text-sm text-gray-500 mt-1">ID: ${address.id}</p>
                </div>
                
                <div class="admin-modal__body bg-gray-50 p-6">
                    <form id="user-address-edit-form" data-address-id="${addressId}">
                        <div class="products_section-title mb-3">User & Type</div>
                        <div class="d-grid gap-4" style="grid-template-columns: 1fr 1fr;">
                            ${renderSelect({
            label: 'User',
            name: 'user_id',
            value: address.user_id,
            items: userItems,
            required: true,
            placeholder: 'Select a user'
        })}
                            ${renderSelect({
            label: 'Address Type',
            name: 'address_type',
            value: address.address_type || 'both',
            items: ADDRESS_TYPES
        })}
                        </div>

                        <div class="products_section-title mt-4 mb-3">Recipient Info</div>
                        <div class="d-grid gap-4" style="grid-template-columns: 1fr 1fr;">
                            ${renderTextInput({
            label: 'Recipient Name',
            name: 'recipient_name',
            value: address.recipient_name || '',
            placeholder: 'Full name of recipient'
        })}
                            ${renderTextInput({
            label: 'Phone',
            name: 'phone',
            value: address.phone || '',
            type: 'tel',
            placeholder: '+94 77 123 4567'
        })}
                        </div>

                        <div class="products_section-title mt-4 mb-3">Address Details</div>
                        ${renderTextInput({
            label: 'Address Line 1',
            name: 'address_line1',
            value: address.address_line1 || '',
            required: true,
            placeholder: 'Street address'
        })}
                        ${renderTextInput({
            label: 'Address Line 2',
            name: 'address_line2',
            value: address.address_line2 || '',
            placeholder: 'Apartment, suite, unit, etc.'
        })}
                        <div class="d-grid gap-4" style="grid-template-columns: 1fr 1fr 1fr;">
                            ${renderTextInput({
            label: 'City',
            name: 'city',
            value: address.city || '',
            required: true
        })}
                            ${renderTextInput({
            label: 'State',
            name: 'state',
            value: address.state || ''
        })}
                            ${renderTextInput({
            label: 'Postal Code',
            name: 'postal_code',
            value: address.postal_code || '',
            required: true
        })}
                        </div>
                        ${renderTextInput({
            label: 'Country',
            name: 'country',
            value: address.country || 'Sri Lanka'
        })}
                        
                        ${renderCheckbox({
            label: 'Set as Default',
            name: 'is_default',
            checked: address.is_default || false,
            helpText: 'This will be the default address for this type'
        })}

                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-address">🗑️ Delete</button>
                            <div class="flex-spacer"></div>
                            <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[UserAddressEdit] Error:', error);
        return `<div class="admin-entity__empty">Error loading address: ${escapeHtml(error.message)}</div>`;
    }
}

/**
 * Initialize handlers
 * @param {HTMLElement} container 
 * @param {number} addressId 
 * @param {Function} onSuccess 
 */
export function initUserAddressEditHandlers(container, addressId, onSuccess) {
    const form = container.querySelector('#user-address-edit-form');
    const deleteBtn = container.querySelector('#delete-address');
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

                const response = await apiRequest(API_ROUTES.USER_ADDRESSES.DELETE(addressId), { method: 'DELETE' });
                if (response.success) {
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Delete failed');
                }
            } catch (error) {
                console.error('[UserAddressEdit] Delete error:', error);
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

            console.log('[UserAddressEdit] Updating address:', payload);

            const response = await apiRequest(API_ROUTES.USER_ADDRESSES.UPDATE(addressId), {
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
            console.error('[UserAddressEdit] Update error:', error);
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
