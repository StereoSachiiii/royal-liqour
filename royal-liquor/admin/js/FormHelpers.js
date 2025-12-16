import { escapeHtml } from './utils.js';
import { API_ROUTES } from './dashboard.routes.js';

/**
 * Upload image to server
 * @param {File} file - Image file to upload
 * @param {string} entity - Entity type (products, users, etc.)
 * @returns {Promise<string>} Image URL
 */
export async function uploadImage(file, entity) {
    const formData = new FormData();
    formData.append('image', file);  // Backend expects 'image' key
    formData.append('entity', entity);

    const response = await fetch(API_ROUTES.IMAGES.UPLOAD, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    });

    const result = await response.json();

    if (!result.success) {
        throw new Error(result.message || 'Image upload failed');
    }

    return result.data.url;
}

/**
 * Render image input field with preview
 * @param {Object} options - Configuration options
 * @returns {string} HTML for image input
 */
export function renderImageInput(options = {}) {
    const {
        currentUrl = '',
        label = 'Image',
        required = false,
        name = 'image_url',
        id = 'image-input'
    } = options;

    return `
        <div class="form-group">
            <label class="form-label ${required ? 'required' : ''}">${escapeHtml(label)}</label>
            <input 
                type="file" 
                id="${id}-file" 
                class="form-file image-upload-input" 
                accept="image/*" 
            />
            <input 
                type="hidden" 
                id="${id}-url" 
                name="${name}" 
                class="image-url-input" 
                value="${escapeHtml(currentUrl)}" 
            />
            <div id="${id}-preview" class="file-preview ${currentUrl ? 'active' : ''}">
                ${currentUrl ? `<img src="${escapeHtml(currentUrl)}" alt="Preview" class="w-full h-auto rounded" />` : ''}
            </div>
        </div>
    `;
}

/**
 * Initialize image upload handlers
 * @param {HTMLElement} container - Container element (form or modal)
 * @param {string} entity - Entity type for upload
 * @param {string} inputId - ID prefix for image input elements
 */
export function initImageUpload(container, entity, inputId = 'image-input') {
    const fileInput = container.querySelector(`#${inputId}-file`);
    const urlInput = container.querySelector(`#${inputId}-url`);
    const preview = container.querySelector(`#${inputId}-preview`);

    if (!fileInput || !urlInput || !preview) {
        console.warn('[FormHelpers] Image upload elements not found');
        return;
    }

    fileInput.addEventListener('change', async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        try {
            // Show loading state
            preview.innerHTML = '<div class="p-4 text-center text-muted">Uploading...</div>';
            preview.classList.add('active');

            // Upload image
            const url = await uploadImage(file, entity);
            urlInput.value = url;

            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-auto rounded" />`;
            };
            reader.readAsDataURL(file);

        } catch (error) {
            console.error('[FormHelpers] Image upload error:', error);
            preview.innerHTML = `<div class="p-4 text-center text-danger">Upload failed: ${escapeHtml(error.message)}</div>`;
        }
    });
}

/**
 * Render dropdown select field
 * @param {Object} options - Configuration options
 * @returns {string} HTML for select field
 */
export function renderSelect(options = {}) {
    const {
        label = '',
        name = '',
        required = false,
        items = [],
        value = '',
        placeholder = 'Select an option',
        valueKey = 'id',
        labelKey = 'name'
    } = options;

    const optionsHtml = items.map(item => {
        const itemValue = item[valueKey];
        const itemLabel = item[labelKey];
        const selected = String(itemValue) === String(value) ? 'selected' : '';
        return `<option value="${escapeHtml(String(itemValue))}" ${selected}>${escapeHtml(itemLabel)}</option>`;
    }).join('');

    return `
        <div class="form-group">
            <label class="form-label ${required ? 'required' : ''}">${escapeHtml(label)}</label>
            <select name="${escapeHtml(name)}" class="form-input" ${required ? 'required' : ''}>
                <option value="">${escapeHtml(placeholder)}</option>
                ${optionsHtml}
            </select>
        </div>
    `;
}

/**
 * Render text input field
 * @param {Object} options - Configuration options
 * @returns {string} HTML for text input
 */
export function renderTextInput(options = {}) {
    const {
        label = '',
        name = '',
        type = 'text',
        value = '',
        required = false,
        placeholder = '',
        min = null,
        max = null
    } = options;

    const attrs = [];
    if (required) attrs.push('required');
    if (placeholder) attrs.push(`placeholder="${escapeHtml(placeholder)}"`);
    if (min !== null) attrs.push(`min="${min}"`);
    if (max !== null) attrs.push(`max="${max}"`);

    return `
        <div class="form-group">
            <label class="form-label ${required ? 'required' : ''}">${escapeHtml(label)}</label>
            <input 
                type="${type}" 
                name="${escapeHtml(name)}" 
                value="${escapeHtml(String(value))}" 
                class="form-input" 
                ${attrs.join(' ')}
            />
        </div>
    `;
}

/**
 * Render textarea field
 * @param {Object} options - Configuration options
 * @returns {string} HTML for textarea
 */
export function renderTextarea(options = {}) {
    const {
        label = '',
        name = '',
        value = '',
        required = false,
        placeholder = '',
        rows = 4
    } = options;

    return `
        <div class="form-group">
            <label class="form-label ${required ? 'required' : ''}">${escapeHtml(label)}</label>
            <textarea 
                name="${escapeHtml(name)}" 
                class="form-input" 
                rows="${rows}"
                ${required ? 'required' : ''}
                ${placeholder ? `placeholder="${escapeHtml(placeholder)}"` : ''}
            >${escapeHtml(String(value))}</textarea>
        </div>
    `;
}

/**
 * Render checkbox field
 * @param {Object} options - Configuration options
 * @returns {string} HTML for checkbox
 */
export function renderCheckbox(options = {}) {
    const {
        label = '',
        name = '',
        checked = false
    } = options;

    return `
        <div class="form-group">
            <div class="checkbox-wrapper">
                <input 
                    type="checkbox" 
                    id="${escapeHtml(name)}" 
                    name="${escapeHtml(name)}" 
                    class="form-checkbox" 
                    ${checked ? 'checked' : ''}
                />
                <label for="${escapeHtml(name)}">${escapeHtml(label)}</label>
            </div>
        </div>
    `;
}

/**
 * Get form data as object
 * @param {HTMLFormElement} form - Form element
 * @returns {Object} Form data as key-value pairs
 */
export function getFormData(form) {
    const formData = new FormData(form);
    const data = {};

    for (const [key, value] of formData.entries()) {
        // Handle checkboxes
        if (form.elements[key]?.type === 'checkbox') {
            data[key] = form.elements[key].checked;
        } else {
            data[key] = value;
        }
    }

    return data;
}

/**
 * Render searchable select field
 * @param {Object} options 
 * @returns {string} HTML
 */
export function renderSearchableSelect(options = {}) {
    const {
        label = '',
        name = '', // This will be the name of the hidden input
        required = false,
        placeholder = 'Search...',
        helpText = ''
    } = options;

    const id = `search-select-${name}-${Math.random().toString(36).substr(2, 9)}`;

    return `
        <div class="form-group searchable-select-container" id="${id}">
            <label class="form-label ${required ? 'required' : ''}">${escapeHtml(label)}</label>
            <div class="relative">
                <input 
                    type="text" 
                    class="form-input search-input" 
                    placeholder="${escapeHtml(placeholder)}" 
                    autocomplete="off"
                />
                <input 
                    type="hidden" 
                    name="${escapeHtml(name)}" 
                    class="value-input" 
                    ${required ? 'required' : ''}
                />
                <div class="search-results absolute w-full bg-white border rounded shadow-lg mt-1 hidden" style="z-index: 10; max-height: 200px; overflow-y: auto;">
                    <!-- Results injected here -->
                </div>
                <!-- Selection display -->
                <div class="selection-display mt-2 hidden p-2 bg-gray-50 border rounded flex justify-between items-center">
                    <span class="selected-text font-medium"></span>
                    <button type="button" class="btn btn-xs btn-outline clear-selection" title="Clear">✖</button>
                </div>
            </div>
            ${helpText ? `<p class="text-xs text-gray-500 mt-1">${escapeHtml(helpText)}</p>` : ''}
        </div>
    `;
}

/**
 * Initialize searchable select
 * @param {HTMLElement} container - The container of the rendered select
 * @param {Object} options - { searchUrlBuilder: (query) => string, itemRenderer: (item) => string, onSelect: (item) => void }
 */
export function initSearchableSelect(container, options = {}) {
    // Find the specific wrapper if container is the whole form, or use container if it IS the wrapper
    const wrappers = container.querySelectorAll('.searchable-select-container');
    // If container itself is the wrapper
    if (container.classList.contains('searchable-select-container')) {
        _initSingleSearchableSelect(container, options);
    } else {
        wrappers.forEach(wrapper => _initSingleSearchableSelect(wrapper, options));
    }
}

function _initSingleSearchableSelect(wrapper, options) {
    const { searchUrlBuilder, itemRenderer, onSelect } = options;
    if (!searchUrlBuilder) return;

    const searchInput = wrapper.querySelector('.search-input');
    const valueInput = wrapper.querySelector('.value-input');
    const resultsContainer = wrapper.querySelector('.search-results');
    const selectionDisplay = wrapper.querySelector('.selection-display');
    const selectedText = wrapper.querySelector('.selected-text');
    const clearBtn = wrapper.querySelector('.clear-selection');

    if (!searchInput || !valueInput) return;

    let debounceTimer;

    // Search Handler
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            resultsContainer.classList.add('hidden');
            resultsContainer.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(async () => {
            try {
                resultsContainer.innerHTML = '<div class="p-2 text-gray-500">Searching...</div>';
                resultsContainer.classList.remove('hidden');

                const response = await fetch(searchUrlBuilder(query));
                const data = await response.json();

                // Expecting data.data or data to be array
                const items = data.data || (Array.isArray(data) ? data : []);

                if (items.length === 0) {
                    resultsContainer.innerHTML = '<div class="p-2 text-gray-500">No results found</div>';
                    return;
                }

                resultsContainer.innerHTML = '';
                items.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'p-2 hover:bg-gray-100 cursor-pointer border-b last:border-b-0';
                    div.innerHTML = itemRenderer ? itemRenderer(item) : (item.name || item.id);
                    div.addEventListener('click', () => {
                        selectItem(item);
                    });
                    resultsContainer.appendChild(div);
                });

            } catch (error) {
                console.error('Search error:', error);
                resultsContainer.innerHTML = `<div class="p-2 text-red-500">Error: ${error.message}</div>`;
            }
        }, 300);
    });

    // Select Item Logic
    function selectItem(item) {
        valueInput.value = item.id;
        selectedText.textContent = itemRenderer ? itemRenderer(item).replace(/<[^>]*>/g, '') : (item.name || item.id); // Simple text extraction

        selectionDisplay.classList.remove('hidden');
        searchInput.classList.add('hidden');
        resultsContainer.classList.add('hidden');

        if (onSelect) onSelect(item);
    }

    // Clear Selection
    clearBtn.addEventListener('click', () => {
        valueInput.value = '';
        selectedText.textContent = '';
        searchInput.value = '';

        selectionDisplay.classList.add('hidden');
        searchInput.classList.remove('hidden');
        searchInput.focus();
    });

    // Close results on outside click
    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            resultsContainer.classList.add('hidden');
        }
    });
}
