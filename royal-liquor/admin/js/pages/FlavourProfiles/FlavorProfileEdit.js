import { API_ROUTES } from '../../dashboard.routes.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

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
 * Render flavor profile edit form
 * @param {number} productId
 * @returns {Promise<string>}
 */
export async function renderFlavorProfileEdit(productId) {
    try {
        const response = await apiRequest(API_ROUTES.FLAVOR_PROFILES.GET(productId));
        const profile = response.data || {};

        console.log('[FlavorProfileEdit] Profile data:', profile);

        let tagsValue = '';
        if (Array.isArray(profile.tags)) {
            tagsValue = profile.tags.join(', ');
        } else if (typeof profile.tags === 'string' && profile.tags.startsWith('{')) {
            tagsValue = profile.tags.replace(/[{}"\\]/g, '');
        }

        const imageUrl = profile.product_image_url || '';

        // NOTE: We do NOT return <div class="admin-modal"> wrapper because openStandardModal adds it.
        // We return the content that goes INSIDE admin-modal__body.

        return `
            <div class="flavor-profile-edit-container">
                
                <!-- Product Info Header (In-body) -->
                <div class="d-flex align-items-center mb-4 pb-4 border-bottom" style="border-color: var(--border);">
                     ${imageUrl ? `
                        <img src="${escapeHtml(imageUrl)}" style="width: 64px; height: 64px; object-fit: cover; border-radius: var(--radius-md, 0.5rem); border: 1px solid var(--border); margin-right: var(--space-4);" />
                    ` : `
                        <div style="width: 64px; height: 64px; background: var(--bg-secondary); border-radius: var(--radius-md, 0.5rem); border: 1px solid var(--border); margin-right: var(--space-4); display: flex; align-items: center; justify-content: center; color: var(--text-light);">📷</div>
                    `}
                    <div>
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text); margin: 0 0 var(--space-1) 0;">${escapeHtml(profile.product_name || 'Unknown Product')}</h3>
                        <div style="font-size: 0.875rem; color: var(--text-light); font-family: monospace;">ID: ${productId} • ${escapeHtml(profile.product_slug || '')}</div>
                    </div>
                </div>

                <form id="flavor-profile-edit-form" data-product-id="${productId}">
                    
                    <!-- Flavor Matrix -->
                    <div style="background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-lg, 0.5rem); padding: var(--space-4); margin-bottom: var(--space-6);">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--space-4);">
                            ${renderSlider('Strength / Intensity', 'strength', profile.strength ?? 5, 'danger')}
                            ${renderSlider('Sweetness', 'sweetness', profile.sweetness ?? 5, 'warning')}
                            ${renderSlider('Bitterness', 'bitterness', profile.bitterness ?? 5, 'primary')}
                            ${renderSlider('Smokiness', 'smokiness', profile.smokiness ?? 5, 'secondary')}
                            ${renderSlider('Fruitiness', 'fruitiness', profile.fruitiness ?? 5, 'success')}
                            ${renderSlider('Spiciness', 'spiciness', profile.spiciness ?? 5, 'danger')}
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 700; color: var(--text);">Flavor Tags</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--bg-secondary); color: var(--text-light); border-right: 0;">#</span>
                            <input type="text" name="tags" class="form-control" 
                                   style="border-left: 0;"
                                   value="${escapeHtml(tagsValue)}" 
                                   placeholder="e.g. vanilla, oak, cinnamon"/>
                        </div>
                    </div>

                    <div class="form-error-banner alert alert-danger hidden mb-4" style="display:none;"></div>

                    <!-- Actions -->
                    <div class="d-flex justify-between align-items-center pt-2 border-top mt-4" style="border-color: var(--border);">
                         <button type="button" class="btn btn-outline-danger" id="delete-flavor-profile" style="border: 1px solid var(--danger); color: var(--danger);">
                            🗑️ Delete
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline" onclick="closeModal()" style="color: var(--text);">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="background: var(--accent); border-color: var(--accent); color: white;">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        `;
    } catch (error) {
        console.error('[FlavorProfileEdit] Error:', error);
        return `<div class="text-center text-danger p-4">Error: ${escapeHtml(error.message)}</div>`;
    }
}

/**
 * Initialize handlers
 */
export function initFlavorProfileEditHandlers(container, productId, onSuccess) {
    const form = container.querySelector('#flavor-profile-edit-form');
    const deleteBtn = container.querySelector('#delete-flavor-profile');
    if (!form) return;

    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            if (!deleteBtn.dataset.confirmed) {
                deleteBtn.dataset.confirmed = 'pending';
                deleteBtn.innerHTML = '⚠️ Confirm?';
                deleteBtn.style.backgroundColor = 'var(--danger)';
                deleteBtn.style.color = 'white';
                setTimeout(() => {
                    if (deleteBtn.dataset.confirmed === 'pending') {
                        deleteBtn.dataset.confirmed = '';
                        deleteBtn.innerHTML = '🗑️ Delete';
                        deleteBtn.style.backgroundColor = 'transparent';
                        deleteBtn.style.color = 'var(--danger)';
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                const response = await apiRequest(API_ROUTES.FLAVOR_PROFILES.DELETE(productId), { method: 'DELETE' });
                if (response.success) {
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Delete failed');
                }
            } catch (error) {
                console.error('[FlavorProfileEdit] Delete error:', error);
                showFormError(form, error.message);
                deleteBtn.disabled = false;
                deleteBtn.dataset.confirmed = '';
                deleteBtn.innerHTML = '🗑️ Delete';
                deleteBtn.style.backgroundColor = 'transparent';
                deleteBtn.style.color = 'var(--danger)';
            }
        });
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const errorEl = form.querySelector('.form-error-banner');
        const originalText = submitBtn.innerHTML;

        try {
            errorEl.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';

            const tagsInput = form.querySelector('[name="tags"]').value;
            const tags = tagsInput ? tagsInput.split(',').map(t => t.trim()).filter(t => t) : [];

            const payload = {
                sweetness: getIntVal(form, 'sweetness'),
                bitterness: getIntVal(form, 'bitterness'),
                strength: getIntVal(form, 'strength'),
                smokiness: getIntVal(form, 'smokiness'),
                fruitiness: getIntVal(form, 'fruitiness'),
                spiciness: getIntVal(form, 'spiciness'),
                tags: tags
            };

            const response = await apiRequest(API_ROUTES.FLAVOR_PROFILES.UPDATE(productId), {
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
            console.error('[FlavorProfileEdit] Update error:', error);
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

function showFormError(form, message) {
    let errorEl = form.querySelector('.form-error-banner');
    if (errorEl) {
        errorEl.textContent = `Error: ${message}`;
        errorEl.style.display = 'block';
    } else {
        alert(message);
    }
}
