import { fetchUserPreferences, fetchModalDetails } from "./UserPreferences.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreUserPreferences() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const preferences = await fetchUserPreferences(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (preferences.error) {
            return `<tr><td colspan="5" class="user_preferences_error-cell">Error: ${escapeHtml(preferences.error)}</td></tr>`;
        }

        if (preferences.length === 0) {
            return `<tr><td colspan="5" class="user_preferences_no-data-cell">No more user preferences to load</td></tr>`;
        }

        return preferences.map(preference => renderUserPreferenceRow(preference)).join('');
    } catch (error) {
        console.error('Error loading more user preferences:', error);
        return `<tr><td colspan="5" class="user_preferences_error-cell">Failed to load user preferences</td></tr>`;
    }
}

function renderUserPreferenceRow(preference) {
    return `
        <tr class="user_preferences_row user-preference-row" data-preference-id="${preference.id}">
            <td class="user_preferences_cell">${preference.id}</td>
            <td class="user_preferences_cell">${preference.user_id}</td>
            <td class="user_preferences_cell">${escapeHtml(preference.user_name)}</td>
            <td class="user_preferences_cell">${escapeHtml(preference.user_email)}</td>
            <td class="user_preferences_cell">${formatDate(preference.created_at)}</td>
            <td class="user_preferences_cell">${formatDate(preference.updated_at)}</td>
            <td class="user_preferences_cell user_preferences_actions">
                <button class="user_preferences_btn-view" data-id="${preference.id}" title="View Details">👁️ View</button>
                <a href="manage/user_preference/update.php?id=${preference.id}" class="user_preferences_btn-edit btn-edit" title="Edit User Preference">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const UserPreferences = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const preferences = await fetchUserPreferences(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (preferences.error) {
            return `
                <div class="user_preferences_table user-preferences-table">
                    <div class="user_preferences_error-box">
                        <strong>Error:</strong> ${escapeHtml(preferences.error)}
                    </div>
                </div>
            `;
        }

        if (preferences.length === 0) {
            return `
                <div class="user_preferences_table user-preferences-table">
                    <div class="user_preferences_no-data-box">
                        <p>📭 No user preferences found.</p>
                    </div>
                </div>
            `;
        }

        const tableRows = preferences.map(preference => renderUserPreferenceRow(preference)).join('');

        return `
            <div class="user_preferences_table user-preferences-table">
                <div class="user_preferences_header table-header">
                    <h2>User Preferences Management (${preferences.length}${preferences.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="user_preferences_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="user_preferences-search-input" class="user_preferences_search-input" type="search" placeholder="Search user name or email" aria-label="Search user preferences" />
                                <a href="manage/user_preference/create.php?" class="user_preferences_btn-primary btn-primary">
            Create
        </a>
                        <button id="user_preferences_refresh-btn" class="user_preferences_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="user_preferences_wrapper table-wrapper">
                    <table class="user_preferences_data-table user-preferences-data-table">
                        <thead>
                            <tr class="user_preferences_header-row table-header-row">
                                <th>ID</th>
                                <th>User ID</th>
                                <th>User Name</th>
                                <th>User Email</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="user_preferences-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="user_preferences_load-more-wrapper" class="user_preferences_load-more-wrapper" style="text-align:center;">
                    ${preferences.length === DEFAULT_LIMIT ? `
                        <button id="user_preferences_load-more-btn" class="user_preferences_btn-load-more btn-load-more">
                            Load More User Preferences
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering user preferences table:', error);
        return `
            <div class="user_preferences_table user-preferences-table">
                <div class="user_preferences_error-box">
                    <strong>Error:</strong> Failed to load user preferences table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (preference) => {
    return `
<div class="user_preferences_card user-preference-card">
    <div class="user_preferences_card-header user-preference-card-header">
        <span>User Preference Details</span>
        <button class="user_preferences_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="user_preferences_section-title user-preference-section-title">Basic Info</div>
    <div class="user_preferences_data-grid user-preference-data-grid">
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">ID</strong>
            <span class="user_preferences_value data-value">${preference.id || 'N/A'}</span>
        </div>
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">User Name</strong>
            <span class="user_preferences_value data-value">${preference.user_name ? escapeHtml(preference.user_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">User Email</strong>
            <span class="user_preferences_value data-value">${preference.user_email ? escapeHtml(preference.user_email) : '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="user_preferences_section-title user-preference-section-title">Preferences</div>
    <div class="user_preferences_data-grid user-preference-data-grid">
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">Sweetness</strong>
            <span class="user_preferences_value data-value">${preference.preferred_sweetness || 5}</span>
        </div>
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">Bitterness</strong>
            <span class="user_preferences_value data-value">${preference.preferred_bitterness || 5}</span>
        </div>
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">Strength</strong>
            <span class="user_preferences_value data-value">${preference.preferred_strength || 5}</span>
        </div>
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">Smokiness</strong>
            <span class="user_preferences_value data-value">${preference.preferred_smokiness || 5}</span>
        </div>
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">Fruitiness</strong>
            <span class="user_preferences_value data-value">${preference.preferred_fruitiness || 5}</span>
        </div>
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">Spiciness</strong>
            <span class="user_preferences_value data-value">${preference.preferred_spiciness || 5}</span>
        </div>
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">Favorite Categories</strong>
            <span class="user_preferences_value data-value">${preference.favorite_category_names || '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="user_preferences_section-title user-preference-section-title">Timeline</div>
    <div class="user_preferences_data-grid user-preference-data-grid">
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">Created At</strong>
            <span class="user_preferences_value data-value">${preference.created_at ? formatDate(preference.created_at) : 'N/A'}</span>
        </div>
        <div class="user_preferences_field data-field">
            <strong class="user_preferences_label data-label">Updated At</strong>
            <span class="user_preferences_value data-value">${preference.updated_at ? formatDate(preference.updated_at) : 'N/A'}</span>
        </div>
    </div>

    <div class="user_preferences_footer card-footer">
        <a href="manage/user_preference/update.php?id=${preference.id}" class="user_preferences_btn-primary btn-primary">
            Edit User Preference
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch user preference details');
        }

        const preference = result.user_preference;

        if (!preference || typeof preference !== 'object' || !preference.id) {
            throw new Error('Invalid user preference data format');
        }

        return detailsHtml(preference);

    } catch (error) {
        throw new Error(error.message || 'Failed to load user preference details');
    }
};

export const userPreferencesListeners  = async () => {
    
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

    // search debounce helper
    function debounce(fn, wait = 300) {
        let t = null;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), wait);
        };
    }

    async function performSearch(query) {
        try {
            currentQuery = query || '';
            currentOffset = 0;
            const results = await fetchUserPreferences(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('user_preferences-table-body');
            const loadMoreWrapper = document.getElementById('user_preferences_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="5" class="user_preferences_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="user_preferences_no-data-cell">No user preferences found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderUserPreferenceRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="user_preferences_load-more-btn" class="user_preferences_btn-load-more btn-load-more">Load More User Preferences</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.user_preferences_btn-view') || e.target.closest('.user_preferences_btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.user_preferences_btn-view') ? e.target : e.target.closest('.user_preferences_btn-view');
            const preferenceId = button.dataset.id;
            

            if (!preferenceId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading user preference details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(preferenceId));
                modalBody.innerHTML = html;

                const closeBtn = modalBody.querySelector('.modal-close-btn');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        modal.classList.remove('active');
                    });
                }

            } catch (error) {
                modalBody.innerHTML = `
                    <div class="modal-error">
                        <div class="modal-error-icon">⚠️</div>
                        <h3 class="modal-error-title">Error Loading User Preference</h3>
                        <p class="modal-error-message">${escapeHtml(error.message)}</p>
                        <button class="modal-close-btn modal-error-btn">
                            Close
                        </button>
                    </div>
                `;

                const errorCloseBtn = modalBody.querySelector('.modal-close-btn');
                if (errorCloseBtn) {
                    errorCloseBtn.addEventListener('click', () => {
                        modal.classList.remove('active');
                    });
                }
            }
        }

        // load more
        if (e.target.id === 'user_preferences_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreUserPreferences();
                document.getElementById('user_preferences-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more user preferences to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more user preferences to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More User Preferences';
                }
            } catch (error) {
                console.error('Error loading more user preferences:', error);
                button.disabled = false;
                button.textContent = 'Load More User Preferences';
            }
        }

        // refresh
        if (e.target.id === 'user_preferences_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await UserPreferences();
                document.querySelector('.user-preferences-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing user preferences:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'user_preferences-search-input') {
            debouncedSearch(e);
        }
    });

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    if (modalClose) {
        modalClose.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }
}

window.loadMoreUserPreferences = loadMoreUserPreferences;
window.fetchUserPreferences = fetchUserPreferences;