import { API_ROUTES } from '../../dashboard.routes.js';
import { renderSelect } from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

// Generate number options 0-10 for flavor sliders
const flavorItems = Array.from({ length: 11 }, (_, i) => ({ id: i, name: i.toString() }));

/**
 * Fetch all categories for the multiselect
 */
async function fetchCategories() {
    try {
        const response = await apiRequest(API_ROUTES.CATEGORIES.LIST + '?limit=100');
        return response.success ? response.data : [];
    } catch (error) {
        console.error('[UserPreferenceEdit] Error fetching categories:', error);
        return [];
    }
}

/**
 * Parse PostgreSQL array format {1,2,3} to JS array
 */
function parsePgArray(value) {
    if (!value) return [];
    if (Array.isArray(value)) return value;
    if (typeof value === 'string') {
        return value.replace(/[{}]/g, '').split(',').filter(s => s).map(Number);
    }
    return [];
}

/**
 * Render user preference edit form
 * @param {number} prefId
 * @returns {Promise<string>}
 */
export async function renderUserPreferenceEdit(prefId) {
    try {
        // Fetch preference and categories in parallel
        const [prefResponse, categories] = await Promise.all([
            apiRequest(API_ROUTES.USER_PREFERENCES.GET(prefId)),
            fetchCategories()
        ]);

        const pref = prefResponse.data || {};
        const selectedCats = parsePgArray(pref.favorite_categories);

        // Build category checkboxes
        const categoryCheckboxes = categories.map(cat => {
            const checked = selectedCats.includes(cat.id) ? 'checked' : '';
            return `
                <label class="category-checkbox">
                    <input type="checkbox" name="favorite_categories" value="${cat.id}" ${checked}>
                    <span>${escapeHtml(cat.name)}</span>
                </label>
            `;
        }).join('');

        return `
            <div class="admin-modal admin-modal--lg">
                <style>
                    .flavor-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
                    .category-list { display: flex; flex-wrap: wrap; gap: 0.5rem; max-height: 150px; overflow-y: auto; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); }
                    .category-checkbox { display: flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: var(--bg-muted); border-radius: var(--radius-sm); cursor: pointer; }
                    .category-checkbox:hover { background: var(--bg-hover); }
                    .category-checkbox input[type="checkbox"] { margin: 0; }
                </style>

                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Edit User Preferences</h2>
                    <p class="text-sm text-gray-500 mt-1">User: ${escapeHtml(pref.user_name || 'Unknown')} (ID: ${pref.user_id})</p>
                </div>
                
                <div class="admin-modal__body bg-gray-50 p-6">
                    <form id="user-pref-edit-form" data-pref-id="${prefId}">
                        <div class="products_section-title mb-3">Flavor Profile (0-10 scale)</div>
                        <div class="flavor-grid">
                            ${renderSelect({
            label: 'Sweetness',
            name: 'preferred_sweetness',
            value: pref.preferred_sweetness ?? 5,
            items: flavorItems,
            valueKey: 'id',
            labelKey: 'name'
        })}
                            ${renderSelect({
            label: 'Bitterness',
            name: 'preferred_bitterness',
            value: pref.preferred_bitterness ?? 5,
            items: flavorItems,
            valueKey: 'id',
            labelKey: 'name'
        })}
                            ${renderSelect({
            label: 'Strength',
            name: 'preferred_strength',
            value: pref.preferred_strength ?? 5,
            items: flavorItems,
            valueKey: 'id',
            labelKey: 'name'
        })}
                            ${renderSelect({
            label: 'Smokiness',
            name: 'preferred_smokiness',
            value: pref.preferred_smokiness ?? 5,
            items: flavorItems,
            valueKey: 'id',
            labelKey: 'name'
        })}
                            ${renderSelect({
            label: 'Fruitiness',
            name: 'preferred_fruitiness',
            value: pref.preferred_fruitiness ?? 5,
            items: flavorItems,
            valueKey: 'id',
            labelKey: 'name'
        })}
                            ${renderSelect({
            label: 'Spiciness',
            name: 'preferred_spiciness',
            value: pref.preferred_spiciness ?? 5,
            items: flavorItems,
            valueKey: 'id',
            labelKey: 'name'
        })}
                        </div>
                        
                        <div class="products_section-title mt-4 mb-2">Favorite Categories</div>
                        <div class="category-list">
                            ${categoryCheckboxes || '<span class="text-muted">No categories available</span>'}
                        </div>

                        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-pref">🗑️ Delete</button>
                            <div class="flex-spacer"></div>
                            <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[UserPreferenceEdit] Error:', error);
        return `<div class="admin-entity__empty">Error loading preferences: ${escapeHtml(error.message)}</div>`;
    }
}

/**
 * Initialize handlers
 * @param {HTMLElement} container 
 * @param {number} prefId 
 * @param {Function} onSuccess 
 */
export function initUserPreferenceEditHandlers(container, prefId, onSuccess) {
    const form = container.querySelector('#user-pref-edit-form');
    const deleteBtn = container.querySelector('#delete-pref');
    if (!form) return;

    // Delete handler with double-click confirmation
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

                const response = await apiRequest(API_ROUTES.USER_PREFERENCES.DELETE(prefId), { method: 'DELETE' });
                if (response.success) {
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Delete failed');
                }
            } catch (error) {
                console.error('[UserPreferenceEdit] Delete error:', error);
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

            // Get selected categories from checkboxes
            const selectedCats = Array.from(form.querySelectorAll('input[name="favorite_categories"]:checked'))
                .map(cb => parseInt(cb.value));

            const payload = {
                preferred_sweetness: parseInt(form.querySelector('[name="preferred_sweetness"]').value),
                preferred_bitterness: parseInt(form.querySelector('[name="preferred_bitterness"]').value),
                preferred_strength: parseInt(form.querySelector('[name="preferred_strength"]').value),
                preferred_smokiness: parseInt(form.querySelector('[name="preferred_smokiness"]').value),
                preferred_fruitiness: parseInt(form.querySelector('[name="preferred_fruitiness"]').value),
                preferred_spiciness: parseInt(form.querySelector('[name="preferred_spiciness"]').value),
                favorite_categories: selectedCats
            };

            console.log('[UserPreferenceEdit] Updating preference:', payload);

            const response = await apiRequest(API_ROUTES.USER_PREFERENCES.UPDATE(prefId), {
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
            console.error('[UserPreferenceEdit] Update error:', error);
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
