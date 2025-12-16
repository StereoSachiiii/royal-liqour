import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderTextarea,
    renderSelect,
    renderImageInput,
    initImageUpload,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render product recognition edit form with full schema
 * @param {number} recognitionId - Recognition ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderProductRecognitionEdit(recognitionId) {
    try {
        // Fetch recognition, products, and users
        const [recognitionRes, productsRes, usersRes] = await Promise.all([
            apiRequest(API_ROUTES.PRODUCT_RECOGNITION.GET(recognitionId)),
            apiRequest(API_ROUTES.PRODUCTS.LIST + buildQueryString({ limit: 200 })),
            apiRequest(API_ROUTES.USERS.LIST + buildQueryString({ limit: 200 }))
        ]);

        const recognition = recognitionRes.data || {};
        const products = productsRes.data?.items || productsRes.data || [];
        const users = usersRes.data?.items || usersRes.data || [];

        console.log('[ProductRecognitionEdit] Loaded recognition:', recognition);

        const apiProviderOptions = [
            { id: '', name: 'Select Provider' },
            { id: 'google_vision', name: 'Google Vision' },
            { id: 'clarifai', name: 'Clarifai' },
            { id: 'imagga', name: 'Imagga' }
        ];

        // Convert labels array to comma-separated string
        const labelsText = Array.isArray(recognition.recognized_labels)
            ? recognition.recognized_labels.join(', ')
            : '';

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit Product Recognition</h2>
                    <p class="text-sm text-gray-500 mt-1">Update recognition data</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="product-recognition-edit-form" class="p-6" data-recognition-id="${recognitionId}">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                <div class="products_field">
                                    <strong>ID</strong>
                                    <span>${recognition.id}</span>
                                </div>
                                
                                <div class="products_field">
                                    <strong>Session ID</strong>
                                    <span>${escapeHtml(recognition.session_id || '-')}</span>
                                </div>
                                
                                <div class="products_field">
                                    <strong>Created At</strong>
                                    <span>${recognition.created_at || '-'}</span>
                                </div>
                                
                                ${renderImageInput({
            label: 'Image',
            name: 'image_url',
            value: recognition.image_data || recognition.image_url || '',
            id: 'recognition-image-edit'
        })}
                                
                                ${renderSelect({
            label: 'User',
            name: 'user_id',
            value: recognition.user_id || '',
            items: users.map(u => ({ id: u.id, name: u.name || u.email })),
            placeholder: 'Select user (optional)'
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Matched Product',
            name: 'matched_product_id',
            value: recognition.recognized_product_id || recognition.matched_product_id || '',
            items: products.map(p => ({ id: p.id, name: p.name })),
            placeholder: 'Select matched product'
        })}
                                
                                ${renderTextInput({
            label: 'Confidence Score (%)',
            name: 'confidence_score',
            type: 'number',
            value: recognition.confidence_score || '',
            placeholder: '0 to 100',
            step: '0.01',
            min: 0,
            max: 100
        })}
                                
                                ${renderSelect({
            label: 'API Provider',
            name: 'api_provider',
            value: recognition.api_provider || '',
            items: apiProviderOptions
        })}
                                
                                ${renderTextarea({
            label: 'Recognized Text',
            name: 'recognized_text',
            value: recognition.recognized_text || '',
            placeholder: 'Text recognized from the image...',
            rows: 3
        })}
                                
                                ${renderTextarea({
            label: 'Recognized Labels (comma-separated)',
            name: 'recognized_labels_text',
            value: labelsText,
            placeholder: 'label1, label2, label3',
            rows: 2
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-recognition">
                                🗑️ Delete
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
        console.error('[ProductRecognitionEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Recognition</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize product recognition edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} recognitionId - Recognition ID being edited
 * @param {Function} onSuccess - Callback after successful update/delete
 */
export function initProductRecognitionEditHandlers(container, recognitionId, onSuccess) {
    const form = container.querySelector('#product-recognition-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-recognition');

    if (!form) {
        console.error('[ProductRecognitionEdit] Form not found');
        return;
    }

    console.log('[ProductRecognitionEdit] Initializing handlers for recognition:', recognitionId);

    // Initialize image upload
    initImageUpload(container, 'products', 'recognition-image-edit');

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
                deleteBtn.textContent = '⚠️ Click again';
                deleteBtn.classList.add('btn-danger');
                deleteTimeout = setTimeout(() => {
                    deleteClickCount = 0;
                    deleteBtn.innerHTML = '🗑️ Delete';
                    deleteBtn.classList.remove('btn-danger');
                }, 3000);
            } else if (deleteClickCount === 2) {
                clearTimeout(deleteTimeout);
                deleteBtn.disabled = true;
                deleteBtn.textContent = 'Deleting...';

                try {
                    const response = await apiRequest(API_ROUTES.PRODUCT_RECOGNITION.DELETE(recognitionId), {
                        method: 'DELETE'
                    });

                    if (response.success) {
                        console.log('[ProductRecognitionEdit] Recognition deleted');
                        closeModal();
                        if (onSuccess) onSuccess(null, 'deleted');
                    } else {
                        throw new Error(response.message || 'Failed to delete');
                    }
                } catch (error) {
                    console.error('[ProductRecognitionEdit] Delete error:', error);
                    deleteClickCount = 0;
                    deleteBtn.innerHTML = '🗑️ Delete';
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

            const formDataObj = getFormData(form);

            // Parse labels from comma-separated text
            const labelsText = formDataObj.recognized_labels_text || '';
            const labels = labelsText.split(',').map(l => l.trim()).filter(l => l);

            const payload = {
                image_url: formDataObj.image_url || undefined,
                user_id: formDataObj.user_id ? parseInt(formDataObj.user_id) : null,
                matched_product_id: formDataObj.matched_product_id ? parseInt(formDataObj.matched_product_id) : null,
                confidence_score: formDataObj.confidence_score ? parseFloat(formDataObj.confidence_score) : null,
                api_provider: formDataObj.api_provider || null,
                recognized_text: formDataObj.recognized_text || null,
                recognized_labels: labels.length > 0 ? labels : null
            };

            console.log('[ProductRecognitionEdit] Updating recognition:', payload);

            const response = await apiRequest(API_ROUTES.PRODUCT_RECOGNITION.UPDATE(recognitionId), {
                method: 'PUT',
                body: payload
            });

            if (response.success) {
                console.log('[ProductRecognitionEdit] Recognition updated:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to update');
            }

        } catch (error) {
            console.error('[ProductRecognitionEdit] Error:', error);
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
