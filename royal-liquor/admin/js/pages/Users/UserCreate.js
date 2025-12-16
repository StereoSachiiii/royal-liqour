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
 * Render user create form with Material Design styling
 * @returns {Promise<string>} Form HTML
 */
export async function renderUserCreate() {
    return `
        <div class="admin-modal admin-modal--lg">
            <!-- Header -->
            <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                <h2 class="text-xl font-semibold text-gray-900">Create New User</h2>
                <p class="text-sm text-gray-500 mt-1">Fill in the user details</p>
            </div>
            
            <!-- Body -->
            <div class="admin-modal__body bg-gray-50">
                <form id="user-create-form" class="p-6">
                    <!-- Two Column Grid -->
                    <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                        <!-- Left Column -->
                        <div class="d-flex flex-col gap-4">
                            ${renderTextInput({
        label: 'Full Name',
        name: 'name',
        required: true,
        placeholder: 'Enter full name'
    })}
                            
                            ${renderTextInput({
        label: 'Email',
        name: 'email',
        type: 'email',
        required: true,
        placeholder: 'user@example.com'
    })}
                            
                            ${renderTextInput({
        label: 'Phone',
        name: 'phone',
        type: 'tel',
        placeholder: '+1 234 567 8900'
    })}
                        </div>
                        
                        <!-- Right Column -->
                        <div class="d-flex flex-col gap-4">
                            ${renderTextInput({
        label: 'Password',
        name: 'password',
        type: 'password',
        required: true,
        placeholder: 'Minimum 8 characters'
    })}
                            
                            ${renderImageInput({
        label: 'Profile Image',
        name: 'profile_image_url',
        required: false,
        id: 'user-image'
    })}
                            
                            <div class="d-flex gap-4">
                                ${renderCheckbox({
        label: 'Is Active',
        name: 'is_active',
        checked: true
    })}
                                
                                ${renderCheckbox({
        label: 'Is Admin',
        name: 'is_admin',
        checked: false
    })}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                        <button type="button" class="btn btn-outline" id="cancel-create">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="btn-text">Create User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

/**
 * Initialize user create form handlers
 * @param {HTMLElement} container - Modal container
 * @param {Function} onSuccess - Callback after successful creation
 */
export function initUserCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#user-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[UserCreate] Form not found');
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

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Creating...';

            const formDataObj = getFormData(form);

            const payload = {
                name: formDataObj.name,
                email: formDataObj.email,
                phone: formDataObj.phone || null,
                password: formDataObj.password,
                profile_image_url: formDataObj.profile_image_url || null,
                is_active: formDataObj.is_active || false,
                is_admin: formDataObj.is_admin || false
            };

            console.log('[UserCreate] Creating user:', payload);

            const response = await apiRequest(API_ROUTES.USERS.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[UserCreate] User created successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data);
            } else {
                throw new Error(response.message || 'Failed to create user');
            }

        } catch (error) {
            console.error('[UserCreate] Error:', error);
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
