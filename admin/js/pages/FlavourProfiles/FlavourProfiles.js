import { fetchFlavorProfiles, fetchModalDetails } from "./FlavourProfiles.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreFlavorProfiles() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const profiles = await fetchFlavorProfiles(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (profiles.error) {
            return `<tr><td colspan="5" class="flavor_profiles_error-cell">Error: ${escapeHtml(profiles.error)}</td></tr>`;
        }

        if (profiles.length === 0) {
            return `<tr><td colspan="5" class="flavor_profiles_no-data-cell">No more flavor profiles to load</td></tr>`;
        }

        return profiles.map(profile => renderFlavorProfileRow(profile)).join('');
    } catch (error) {
        console.error('Error loading more flavor profiles:', error);
        return `<tr><td colspan="5" class="flavor_profiles_error-cell">Failed to load flavor profiles</td></tr>`;
    }
}

function renderFlavorProfileRow(profile) {
    return `
        <tr class="flavor_profiles_row flavor-profile-row" data-profile-id="${profile.product_id}">
            <td class="flavor_profiles_cell">${profile.product_id}</td>
            <td class="flavor_profiles_cell">${escapeHtml(profile.product_name)}</td>
            <td class="flavor_profiles_cell">${escapeHtml(profile.product_slug)}</td>
            <td class="flavor_profiles_cell">${profile.sweetness || 5}</td>
            <td class="flavor_profiles_cell">${profile.bitterness || 5}</td>
            <td class="flavor_profiles_cell">${profile.strength || 5}</td>
            <td class="flavor_profiles_cell flavor_profiles_actions">
                <button class="flavor_profiles_btn-view" data-id="${profile.product_id}" title="View Details">👁️ View</button>
                <a href="manage/flavor_profile/update.php?id=${profile.product_id}" class="flavor_profiles_btn-edit btn-edit" title="Edit Flavor Profile">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const FlavourProfiles = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const profiles = await fetchFlavorProfiles(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (profiles.error) {
            return `
                <div class="flavor_profiles_table flavor-profiles-table">
                    <div class="flavor_profiles_error-box">
                        <strong>Error:</strong> ${escapeHtml(profiles.error)}
                    </div>
                </div>
            `;
        }

        if (profiles.length === 0) {
            return `
                <div class="flavor_profiles_table flavor-profiles-table">
                    <div class="flavor_profiles_no-data-box">
                        <p>📭 No flavor profiles found.</p>
                    </div>
                </div>
            `;
        }

        const tableRows = profiles.map(profile => renderFlavorProfileRow(profile)).join('');

        return `
            <div class="flavor_profiles_table flavor-profiles-table">
                <div class="flavor_profiles_header table-header">
                    <h2>Flavor Profiles Management (${profiles.length}${profiles.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="flavor_profiles_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="flavor_profiles-search-input" class="flavor_profiles_search-input" type="search" placeholder="Search product name or slug" aria-label="Search flavor profiles" />
                                <a href="manage/flavor_profile/create.php" class="flavor_profiles_btn-primary btn-primary">
            User
        </a>
                        <button id="flavor_profiles_refresh-btn" class="flavor_profiles_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="flavor_profiles_wrapper table-wrapper">
                    <table class="flavor_profiles_data-table flavor-profiles-data-table">
                        <thead>
                            <tr class="flavor_profiles_header-row table-header-row">
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Product Slug</th>
                                <th>Sweetness</th>
                                <th>Bitterness</th>
                                <th>Strength</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="flavor_profiles-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="flavor_profiles_load-more-wrapper" class="flavor_profiles_load-more-wrapper" style="text-align:center;">
                    ${profiles.length === DEFAULT_LIMIT ? `
                        <button id="flavor_profiles_load-more-btn" class="flavor_profiles_btn-load-more btn-load-more">
                            Load More Flavor Profiles
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering flavor profiles table:', error);
        return `
            <div class="flavor_profiles_table flavor-profiles-table">
                <div class="flavor_profiles_error-box">
                    <strong>Error:</strong> Failed to load flavor profiles table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (profile) => {
    return `
<div class="flavor_profiles_card flavor-profile-card">
    <div class="flavor_profiles_card-header flavor-profile-card-header">
        <span>Flavor Profile Details</span>
        <button class="flavor_profiles_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="flavor_profiles_section-title flavor-profile-section-title">Basic Info</div>
    <div class="flavor_profiles_data-grid flavor-profile-data-grid">
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Product ID</strong>
            <span class="flavor_profiles_value data-value">${profile.product_id || 'N/A'}</span>
        </div>
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Product Name</strong>
            <span class="flavor_profiles_value data-value">${profile.product_name ? escapeHtml(profile.product_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Product Slug</strong>
            <span class="flavor_profiles_value data-value">${profile.product_slug ? escapeHtml(profile.product_slug) : '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="flavor_profiles_section-title flavor-profile-section-title">Flavor Attributes</div>
    <div class="flavor_profiles_data-grid flavor-profile-data-grid">
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Sweetness</strong>
            <span class="flavor_profiles_value data-value">${profile.sweetness || 5}</span>
        </div>
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Bitterness</strong>
            <span class="flavor_profiles_value data-value">${profile.bitterness || 5}</span>
        </div>
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Strength</strong>
            <span class="flavor_profiles_value data-value">${profile.strength || 5}</span>
        </div>
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Smokiness</strong>
            <span class="flavor_profiles_value data-value">${profile.smokiness || 5}</span>
        </div>
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Fruitiness</strong>
            <span class="flavor_profiles_value data-value">${profile.fruitiness || 5}</span>
        </div>
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Spiciness</strong>
            <span class="flavor_profiles_value data-value">${profile.spiciness || 5}</span>
        </div>
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Tags</strong>
            <span class="flavor_profiles_value data-value">${profile.tags ? profile.tags.join(', ') : '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="flavor_profiles_section-title flavor-profile-section-title">Feedback</div>
    <div class="flavor_profiles_data-grid flavor-profile-data-grid">
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Avg Rating</strong>
            <span class="flavor_profiles_value data-value">${profile.avg_rating ? profile.avg_rating.toFixed(1) : '-'}</span>
        </div>
        <div class="flavor_profiles_field data-field">
            <strong class="flavor_profiles_label data-label">Feedback Count</strong>
            <span class="flavor_profiles_value data-value">${profile.feedback_count || 0}</span>
        </div>
    </div>

    <div class="flavor_profiles_footer card-footer">
        <a href="manage/flavor_profile/update.php?id=${profile.product_id}" class="flavor_profiles_btn-primary btn-primary">
            Edit Flavor Profile
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch flavor profile details');
        }

        const profile = result.flavor_profile;

        if (!profile || typeof profile !== 'object' || !profile.product_id) {
            throw new Error('Invalid flavor profile data format');
        }

        return detailsHtml(profile);

    } catch (error) {
        throw new Error(error.message || 'Failed to load flavor profile details');
    }
};


export const flavourProfilesListener = async () => {
    
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
            const results = await fetchFlavorProfiles(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('flavor_profiles-table-body');
            const loadMoreWrapper = document.getElementById('flavor_profiles_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="5" class="flavor_profiles_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="flavor_profiles_no-data-cell">No flavor profiles found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderFlavorProfileRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="flavor_profiles_load-more-btn" class="flavor_profiles_btn-load-more btn-load-more">Load More Flavor Profiles</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.flavor_profiles_btn-view') || e.target.closest('.flavor_profiles_btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.flavor_profiles_btn-view') ? e.target : e.target.closest('.flavor_profiles_btn-view');
            const profileId = button.dataset.id;

            if (!profileId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading flavor profile details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(profileId));
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
                        <h3 class="modal-error-title">Error Loading Flavor Profile</h3>
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
        if (e.target.id === 'flavor_profiles_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreFlavorProfiles();
                document.getElementById('flavor_profiles-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more flavor profiles to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more flavor profiles to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Flavor Profiles';
                }
            } catch (error) {
                console.error('Error loading more flavor profiles:', error);
                button.disabled = false;
                button.textContent = 'Load More Flavor Profiles';
            }
        }

        // refresh
        if (e.target.id === 'flavor_profiles_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await FlavorProfiles();
                document.querySelector('.flavor-profiles-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing flavor profiles:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'flavor_profiles-search-input') {
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

window.loadMoreFlavorProfiles = loadMoreFlavorProfiles;
window.fetchFlavorProfiles = fetchFlavorProfiles;