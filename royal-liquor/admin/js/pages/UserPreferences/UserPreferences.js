import { fetchUserPreferences, fetchModalDetails } from "./UserPreferences.utils.js";
import { renderUserPreferenceEdit, initUserPreferenceEditHandlers } from "./UserPreferenceEdit.js";
import { escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal } from "../../utils.js";

const DEFAULT_LIMIT = 10;
let currentOffset = 0;
let currentQuery = getState('admin:user_preferences:query', '');
let lastResults = [];

function renderUserPreferenceRow(pref) {
    if (!pref || !pref.id) return '';

    const userName = escapeHtml(pref.user_name || '-');
    const sweet = pref.preferred_sweetness ?? '-';
    const bitter = pref.preferred_bitterness ?? '-';
    const strength = pref.preferred_strength ?? '-';
    const smoke = pref.preferred_smokiness ?? '-';

    // Format favorites (Postgres array string or actual array)
    let favorites = '-';
    if (pref.favorite_categories) {
        if (typeof pref.favorite_categories === 'string') {
            favorites = pref.favorite_categories.replace(/[{}]/g, '').split(',').length + ' cats';
        } else if (Array.isArray(pref.favorite_categories)) {
            favorites = pref.favorite_categories.length + ' cats';
        }
    }

    return `
        <tr data-pref-id="${pref.id}">
            <td>${pref.id}</td>
            <td>${userName}</td>
            <td>
                <span title="Sweetness: ${sweet}" class="badge">${sweet}</span>
                <span title="Bitterness: ${bitter}" class="badge">${bitter}</span>
                <span title="Strength: ${strength}" class="badge">${strength}</span>
                <span title="Smokiness: ${smoke}" class="badge">${smoke}</span>
            </td>
            <td>${favorites}</td>
            <td>${formatDate(pref.created_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm user-preference-view" data-id="${pref.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm user-preference-edit" data-id="${pref.id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

async function loadMoreUserPreferences() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const prefs = await fetchUserPreferences(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (prefs.error) {
            return `<tr><td colspan="6" class="admin-entity__empty">Error: ${escapeHtml(prefs.error)}</td></tr>`;
        }

        if (!prefs.length) {
            return `<tr><td colspan="6" class="admin-entity__empty">No more user preferences to load</td></tr>`;
        }

        return prefs.map(renderUserPreferenceRow).join('');
    } catch (error) {
        console.error('Error loading more user preferences:', error);
        return `<tr><td colspan="6" class="admin-entity__empty">Failed to load user preferences</td></tr>`;
    }
}

export const UserPreferences = async () => {
    try {
        currentOffset = 0;
        const prefs = await fetchUserPreferences(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (prefs.error) {
            lastResults = [];
        } else {
            lastResults = Array.isArray(prefs) ? prefs : [];
        }

        const hasData = lastResults && lastResults.length > 0;
        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        const tableRows = hasData
            ? lastResults.map(renderUserPreferenceRow).join('')
            : `<tr><td colspan="6" class="admin-entity__empty">${prefs && prefs.error ? escapeHtml(prefs.error) : 'No user preferences found'}</td></tr>`;

        return `
            <div class="admin-entity">
                <style>
                    .badge {
                        display: inline-block;
                        padding: 2px 6px;
                        background: #eee;
                        border-radius: 4px;
                        font-size: 0.85em;
                        margin-right: 4px;
                    }
                </style>
                <div class="admin-entity__header">
                    <h2 class="admin-entity__title">User Preferences Management (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="user_prefs-search-input" class="admin-entity__search" type="search" placeholder="Search..." value="${escapeHtml(currentQuery)}" />
                        <button id="user_prefs_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Flavor Profile (Sw/Bi/Str/Sm)</th>
                                <th>Favorites</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="user_prefs-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
                <div id="user_prefs_load-more-wrapper" style="text-align:center; margin-top: var(--space-4);">
                    ${hasData && lastResults.length === DEFAULT_LIMIT ? `<button id="user_prefs_load-more-btn" class="btn btn-outline btn-sm">Load More User Preferences</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering user preferences table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> ${escapeHtml(error.toString())} <br> <small>${escapeHtml(error.stack)}</small></div></div>`;
    }
};

const userPreferenceDetailsHtml = (pref) => {
    if (!pref) return '<div class="admin-entity__empty">No user preference data</div>';

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Preference Details</span>
            </div>
            <div class="products_section-title">Flavor Profile (0-10)</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Sweetness</strong><span>${pref.preferred_sweetness ?? '-'}</span></div>
                <div class="products_field"><strong>Bitterness</strong><span>${pref.preferred_bitterness ?? '-'}</span></div>
                <div class="products_field"><strong>Strength</strong><span>${pref.preferred_strength ?? '-'}</span></div>
                <div class="products_field"><strong>Smokiness</strong><span>${pref.preferred_smokiness ?? '-'}</span></div>
                <div class="products_field"><strong>Fruitiness</strong><span>${pref.preferred_fruitiness ?? '-'}</span></div>
                <div class="products_field"><strong>Spiciness</strong><span>${pref.preferred_spiciness ?? '-'}</span></div>
            </div>
            <div class="products_section-title">User Information</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>User ID</strong><span>${pref.user_id}</span></div>
                <div class="products_field"><strong>User Name</strong><span>${escapeHtml(pref.user_name || '-')}</span></div>
                <div class="products_field"><strong>User Email</strong><span>${escapeHtml(pref.user_email || '-')}</span></div>
            </div>
            <div class="products_section-title">Other</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Favorite Categories</strong><span>${escapeHtml(String(pref.favorite_categories || '-'))}</span></div>
                <div class="products_field"><strong>Created At</strong><span>${formatDate(pref.created_at)}</span></div>
            </div>
            <div class="products_footer">
                <button class="btn btn-primary js-edit-pref" data-id="${pref.id}">Edit Preferences</button>
            </div>
        </div>
    `;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(Number(id));

        if (result.error) {
            throw new Error(result.error);
        }

        return userPreferenceDetailsHtml(result.user_preference);
    } catch (error) {
        throw new Error(error.message || 'Failed to load user preference details');
    }
};

// Attach delegated handlers
(() => {
    async function performSearch(query) {
        try {
            currentQuery = query || '';
            saveState('admin:user_preferences:query', currentQuery);
            currentOffset = 0;
            const results = await fetchUserPreferences(DEFAULT_LIMIT, 0, currentQuery);

            lastResults = results.error ? [] : (Array.isArray(results) ? results : []);
            const hasData = lastResults.length > 0;
            const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

            const titleEl = document.querySelector('.admin-entity__title');
            if (titleEl) titleEl.textContent = `User Preferences Management (${countLabel})`;

            const tbody = document.getElementById('user_prefs-table-body');
            if (tbody) {
                tbody.innerHTML = hasData
                    ? lastResults.map(renderUserPreferenceRow).join('')
                    : `<tr><td colspan="6" class="admin-entity__empty">${results.error ? escapeHtml(results.error) : 'No user preferences found'}</td></tr>`;
            }

            const loadMoreWrapper = document.getElementById('user_prefs_load-more-wrapper');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = hasData && lastResults.length === DEFAULT_LIMIT
                    ? `<button id="user_prefs_load-more-btn" class="btn btn-outline btn-sm">Load More User Preferences</button>`
                    : '';
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('input', (e) => {
        if (e.target.id === 'user_prefs-search-input') {
            debouncedSearch(e);
        }
    });

    document.addEventListener('click', async (e) => {
        // View button - user-preference-view class only
        if (e.target.matches('.user-preference-view') || e.target.closest('.user-preference-view')) {
            const btn = e.target.closest('.user-preference-view') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const html = await renderModal(id);
                openStandardModal({
                    title: 'Preference Details',
                    bodyHtml: html,
                    size: 'xl'
                });
            } catch (err) {
                openStandardModal({
                    title: 'Error loading details',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(err.message)}</div>`,
                    size: 'xl'
                });
            }
        }

        // Edit button - user-preference-edit class only
        if (e.target.matches('.user-preference-edit') || e.target.closest('.user-preference-edit')) {
            const btn = e.target.closest('.user-preference-edit') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const formHtml = await renderUserPreferenceEdit(parseInt(id));
                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initUserPreferenceEditHandlers(modalBody, parseInt(id), (data, action) => {
                    if (action === 'updated' || action === 'deleted') {
                        performSearch(currentQuery);
                    }
                });
            } catch (error) {
                console.error('Error opening edit preference:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Edit from within modal
        if (e.target.matches('.js-edit-pref') || e.target.closest('.js-edit-pref')) {
            const btn = e.target.closest('.js-edit-pref') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            const standardModal = document.getElementById('standard-modal');
            if (standardModal) standardModal.classList.remove('active');

            try {
                const formHtml = await renderUserPreferenceEdit(parseInt(id));
                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initUserPreferenceEditHandlers(modalBody, parseInt(id), (data, action) => {
                    if (action === 'updated' || action === 'deleted') {
                        performSearch(currentQuery);
                    }
                });
            } catch (error) {
                console.error('Error opening edit preference:', error);
            }
        }

        // Load more
        if (e.target.id === 'user_prefs_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';
            try {
                const html = await loadMoreUserPreferences();
                document.getElementById('user_prefs-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more user preferences') || html.includes('Error')) {
                    button.remove();
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More User Preferences';
                }
            } catch {
                button.disabled = false;
                button.textContent = 'Load More User Preferences';
            }
        }

        // Refresh
        if (e.target.id === 'user_prefs_refresh-btn') {
            performSearch(currentQuery);
        }
    });
})();

// Export for backwards compatibility
export const userPreferencesListeners = async () => {
    console.log('userPreferencesListeners called - listeners are now auto-attached');
};

window.loadMoreUserPreferences = loadMoreUserPreferences;
