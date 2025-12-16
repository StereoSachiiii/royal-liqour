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
 * Render product recognition create form with full schema
 * @returns {Promise<string>} Form HTML
 */
export async function renderProductRecognitionCreate() {
    try {
        // Fetch products and users for dropdowns
        const [productsRes, usersRes] = await Promise.all([
            apiRequest(API_ROUTES.PRODUCTS.LIST + buildQueryString({ limit: 200 })),
            apiRequest(API_ROUTES.USERS.LIST + buildQueryString({ limit: 200 }))
        ]);

        const products = productsRes.data?.items || productsRes.data || [];
        const users = usersRes.data?.items || usersRes.data || [];

        const apiProviderOptions = [
            { id: '', name: 'Select Provider' },
            { id: 'google_vision', name: 'Google Vision' },
            { id: 'clarifai', name: 'Clarifai' },
            { id: 'imagga', name: 'Imagga' }
        ];

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Create Product Recognition</h2>
                    <p class="text-sm text-gray-500 mt-1">Add a new recognition record</p>
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="product-recognition-create-form" class="p-6">
                        <!-- Two Column Grid -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Session ID',
            name: 'session_id',
            required: true,
            placeholder: 'Enter session identifier',
            value: 'session-' + Date.now()
        })}
                                
                                ${renderImageInput({
            label: 'Image',
            name: 'image_url',
            required: true,
            id: 'recognition-image'
        })}
                                
                                ${renderSelect({
            label: 'User',
            name: 'user_id',
            value: '',
            items: users.map(u => ({ id: u.id, name: u.name || u.email })),
            placeholder: 'Select user (optional)'
        })}
                                
                                ${renderSelect({
            label: 'Matched Product',
            name: 'matched_product_id',
            value: '',
            items: products.map(p => ({ id: p.id, name: p.name })),
            placeholder: 'Select matched product (optional)'
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderTextInput({
            label: 'Confidence Score (%)',
            name: 'confidence_score',
            type: 'number',
            placeholder: '0 to 100',
            step: '0.01',
            min: 0,
            max: 100
        })}
                                
                                ${renderSelect({
            label: 'API Provider',
            name: 'api_provider',
            value: '',
            items: apiProviderOptions
        })}
                                
                                ${renderTextarea({
            label: 'Recognized Text',
            name: 'recognized_text',
            placeholder: 'Text recognized from the image...',
            rows: 3
        })}
                                
                                ${renderTextarea({
            label: 'Recognized Labels (comma-separated)',
            name: 'recognized_labels_text',
            placeholder: 'label1, label2, label3',
            rows: 2
        })}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-outline" id="cancel-create">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="btn-text">Create Recognition</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[ProductRecognitionCreate] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Form</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize product recognition create form handlers
 * @param {HTMLElement} container - Modal container
 * @param {Function} onSuccess - Callback after successful creation
 */
export function initProductRecognitionCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#product-recognition-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[ProductRecognitionCreate] Form not found');
        return;
    }

    console.log('[ProductRecognitionCreate] Initializing handlers');

    // Initialize image upload
    initImageUpload(container, 'products', 'recognition-image');

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => closeModal());
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

            // Parse labels from comma-separated text
            const labelsText = formDataObj.recognized_labels_text || '';
            const labels = labelsText.split(',').map(l => l.trim()).filter(l => l);

            const payload = {
                session_id: formDataObj.session_id,
                image_url: formDataObj.image_url,
                user_id: formDataObj.user_id ? parseInt(formDataObj.user_id) : null,
                matched_product_id: formDataObj.matched_product_id ? parseInt(formDataObj.matched_product_id) : null,
                confidence_score: formDataObj.confidence_score ? parseFloat(formDataObj.confidence_score) : null,
                api_provider: formDataObj.api_provider || null,
                recognized_text: formDataObj.recognized_text || null,
                recognized_labels: labels.length > 0 ? labels : null
            };

            if (!payload.session_id) throw new Error('Session ID is required');
            if (!payload.image_url) throw new Error('Image is required');

            console.log('[ProductRecognitionCreate] Creating recognition:', payload);

            const response = await apiRequest(API_ROUTES.PRODUCT_RECOGNITION.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[ProductRecognitionCreate] Recognition created:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'created');
            } else {
                throw new Error(response.message || 'Failed to create recognition');
            }

        } catch (error) {
            console.error('[ProductRecognitionCreate] Error:', error);
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
