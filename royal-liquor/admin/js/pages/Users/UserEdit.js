import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderCheckbox,
    renderImageInput,
    initImageUpload,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render user edit form with Material Design styling
 * @param {number} userId - User ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderUserEdit(userId) {
    try {
        const userResponse = await apiRequest(API_ROUTES.USERS.GET(userId));
        const user = userResponse.data || {};

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit User</h2>
                    <p class="text-sm text-gray-500 mt-1">${escapeHtml(user.name || user.email)}</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="user-edit-form" class="p-6" data-user-id="${userId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Full Name',
            name: 'name',
            value: user.name || '',
            required: true,
            placeholder: 'Enter full name'
        })}
                                
                                ${renderTextInput({
            label: 'Email',
            name: 'email',
            type: 'email',
            value: user.email || '',
            required: true,
            placeholder: 'user@example.com'
        })}
                                
                                ${renderTextInput({
            label: 'Phone',
            name: 'phone',
            type: 'tel',
            value: user.phone || '',
            placeholder: '+1 234 567 8900'
        })}
                                
                                ${renderTextInput({
            label: 'New Password',
            name: 'password',
            type: 'password',
            placeholder: 'Leave blank to keep current'
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderImageInput({
            label: 'Profile Image',
            name: 'profile_image_url',
            currentUrl: user.profile_image_url || '',
            required: false,
            id: 'user-image'
        })}
                                
                                <div class="d-flex gap-4">
                                    ${renderCheckbox({
            label: 'Is Active',
            name: 'is_active',
            checked: user.is_active || false
        })}
                                    
                                    ${renderCheckbox({
            label: 'Is Admin',
            name: 'is_admin',
            checked: user.is_admin || false
        })}
                                </div>
                                
                                <!-- User Info (Read Only) -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">User Info</h4>
                                    <div class="text-sm text-gray-500">
                                        <div>Created: ${user.created_at || '-'}</div>
                                        <div>Last Login: ${user.last_login_at || 'Never'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-user">
                                🗑️ Delete User
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
        console.error('[UserEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load User</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize user edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} userId - User ID being edited
 * @param {Function} onSuccess - Callback after successful update
 */
export function initUserEditHandlers(container, userId, onSuccess) {
    const form = container.querySelector('#user-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-user');

    if (!form) {
        console.error('[UserEdit] Form not found');
        return;
    }

    // Initialize image upload
    initImageUpload(container, 'users', 'user-image');

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeModal();
        });
    }

    // Delete button
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            // Show confirmation by changing button state
            if (!deleteBtn.dataset.confirmed) {
                deleteBtn.dataset.confirmed = 'pending';
                deleteBtn.innerHTML = '⚠️ Click again to confirm';
                deleteBtn.classList.add('btn-warning');
                setTimeout(() => {
                    if (deleteBtn.dataset.confirmed === 'pending') {
                        deleteBtn.dataset.confirmed = '';
                        deleteBtn.innerHTML = '🗑️ Delete User';
                        deleteBtn.classList.remove('btn-warning');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.USERS.DELETE(userId), {
                    method: 'DELETE'
                });

                if (response.success) {
                    console.log('[UserEdit] User deleted successfully');
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Failed to delete user');
                }
            } catch (error) {
                console.error('[UserEdit] Delete error:', error);
                // Show inline error instead of alert
                let errorEl = form.querySelector('.form-error-banner');
                if (!errorEl) {
                    errorEl = document.createElement('div');
                    errorEl.className = 'form-error-banner';
                    form.prepend(errorEl);
                }
                errorEl.textContent = `Delete failed: ${error.message}`;
                errorEl.style.display = 'block';
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '🗑️ Delete User';
                deleteBtn.dataset.confirmed = '';
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

            const formDataObj = getFormData(form);

            const payload = {
                name: formDataObj.name,
                email: formDataObj.email,
                phone: formDataObj.phone || null,
                profile_image_url: formDataObj.profile_image_url || null,
                is_active: formDataObj.is_active || false,
                is_admin: formDataObj.is_admin || false
            };

            // Only include password if provided
            if (formDataObj.password) {
                payload.password = formDataObj.password;
            }

            console.log('[UserEdit] Updating user:', payload);

            const response = await apiRequest(API_ROUTES.USERS.UPDATE(userId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                console.log('[UserEdit] User updated successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update user');
            }

        } catch (error) {
            console.error('[UserEdit] Error:', error);
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
