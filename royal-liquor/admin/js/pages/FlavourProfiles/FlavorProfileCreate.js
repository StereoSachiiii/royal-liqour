import { API_ROUTES } from '../../dashboard.routes.js';
import { renderSelect } from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';
import { fetchProductsForDropdown } from './FlavourProfiles.utils.js';

/**
 * Render slider for flavor attribute (0-10) with visual feedback
 */
function renderSlider(label, name, value = 5, colorClass = 'primary') {
    const accentColor = colorClass === 'primary' ? 'var(--accent)' : `var(--${colorClass})`;

    return `
        <div class="flavor-slider-group" style="background: var(--bg); padding: var(--space-4); border: 1px solid var(--border); border-radius: var(--radius-md, 0.375rem);">
            <div class="d-flex justify-between align-items-center mb-2">
                <label class="form-label mb-0 fw-bold" style="color: var(--text); font-size: 0.9rem;">${escapeHtml(label)}</label>
                <span class="badge" style="background: ${accentColor}; color: white; width: 28px; text-align: center;">${value}</span>
            </div>
            <input 
                type="range" 
                name="${escapeHtml(name)}" 
                min="0" max="10" 
                value="${value}" 
                class="form-range w-100"
                style="height: 6px; accent-color: ${accentColor}; cursor: pointer;"
                oninput="this.previousElementSibling.querySelector('.badge').textContent = this.value"
            />
            <div class="d-flex justify-between mt-1" style="font-size: 0.75rem; color: var(--text-light);">
                <span>Low</span>
                <span>High</span>
            </div>
        </div>
    `;
}

/**
 * Render flavor profile create form
 * @returns {Promise<string>}
 */
export async function renderFlavorProfileCreate() {
    try {
        const products = await fetchProductsForDropdown();

        const productItems = products.map(p => ({
            id: p.id,
            name: `${p.name} (${p.slug || 'N/A'})`
        }));

        // Returned content is INJECTED into admin-modal__body. 
        // No outer wrappers.
        const formHtml = `
            <div class="flavor-profile-create-content">
                <form id="flavor-profile-create-form">
                    
                    <!-- Section 1: Product Selection -->
                    <div style="background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-lg, 0.5rem); padding: var(--space-4); margin-bottom: var(--space-6);">
                        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text); margin-bottom: var(--space-4); border-bottom: 1px solid var(--border); padding-bottom: var(--space-2);">
                            1. Select Product
                        </h3>
                        ${renderSelect({
            label: 'Product Target',
            name: 'product_id',
            value: '',
            items: productItems,
            required: true,
            placeholder: 'Search and select a product...',
            searchable: true
        })}
                    </div>

                    <!-- Section 2: Flavor Matrix -->
                    <div style="background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-lg, 0.5rem); padding: var(--space-4); margin-bottom: var(--space-6);">
                        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text); margin-bottom: var(--space-4); border-bottom: 1px solid var(--border); padding-bottom: var(--space-2);">
                            2. Flavor Matrix
                        </h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--space-4);">
                            ${renderSlider('Strength / Intensity', 'strength', 5, 'danger')}
                            ${renderSlider('Sweetness', 'sweetness', 5, 'warning')}
                            ${renderSlider('Bitterness', 'bitterness', 5, 'primary')}
                            ${renderSlider('Smokiness', 'smokiness', 5, 'secondary')}
                            ${renderSlider('Fruitiness', 'fruitiness', 5, 'success')}
                            ${renderSlider('Spiciness', 'spiciness', 5, 'danger')}
                        </div>
                    </div>

                    <!-- Section 3: Metadata -->
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 700; color: var(--text);">3. Flavor Tags</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--bg-secondary); color: var(--text-light); border-right: 0;">#</span>
                            <input type="text" name="tags" class="form-control" 
                                   style="border-left: 0;"
                                   placeholder="e.g. vanilla, oak, cinnamon (comma separated)"/>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-light); margin-top: var(--space-1);">
                            Add descriptive keywords.
                        </div>
                    </div>

                    <div class="form-error-banner alert alert-danger hidden mb-4" style="display:none;"></div>

                    <div class="d-flex gap-2 justify-end pt-2 border-top mt-4" style="border-color: var(--border);">
                        <button type="button" class="btn btn-outline" onclick="closeModal()" style="color: var(--text);">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background: var(--accent); border-color: var(--accent); color: white;">
                            Create Profile
                        </button>
                    </div>
                </form>
            </div>
        `;
        return formHtml;
    } catch (error) {
        console.error('[FlavorProfileCreate] Error:', error);
        return `<div class="text-center text-danger p-4">Error loading form: ${escapeHtml(error.message)}</div>`;
    }
}

/**
 * Initialize handlers
 */
export function initFlavorProfileCreateHandlers(container, onSuccess) {
    const form = container.querySelector('#flavor-profile-create-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const errorEl = form.querySelector('.form-error-banner');
        const originalText = submitBtn.innerHTML;

        try {
            errorEl.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating...';

            const productIdStr = form.querySelector('[name="product_id"]').value;
            if (!productIdStr) throw new Error('Please select a product');
            const productId = parseInt(productIdStr);

            const tagsInput = form.querySelector('[name="tags"]').value;
            const tags = tagsInput ? tagsInput.split(',').map(t => t.trim()).filter(t => t) : [];

            const payload = {
                product_id: productId,
                sweetness: getIntVal(form, 'sweetness'),
                bitterness: getIntVal(form, 'bitterness'),
                strength: getIntVal(form, 'strength'),
                smokiness: getIntVal(form, 'smokiness'),
                fruitiness: getIntVal(form, 'fruitiness'),
                spiciness: getIntVal(form, 'spiciness'),
                tags: tags
            };

            const response = await apiRequest(API_ROUTES.FLAVOR_PROFILES.CREATE, {
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
            console.error('[FlavorProfileCreate] Submit Error:', error);
            errorEl.textContent = `Error: ${error.message}`;
            errorEl.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

function getIntVal(form, name) {
    const val = form.querySelector(`[name="${name}"]`).value;
    return parseInt(val) || 0;
}
