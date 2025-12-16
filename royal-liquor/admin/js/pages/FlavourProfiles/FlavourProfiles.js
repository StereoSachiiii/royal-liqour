import { fetchFlavorProfiles, fetchModalDetails } from "./FlavourProfiles.utils.js";
import { renderFlavorProfileEdit, initFlavorProfileEditHandlers } from "./FlavorProfileEdit.js";
import { renderFlavorProfileCreate, initFlavorProfileCreateHandlers } from "./FlavorProfileCreate.js";
import { escapeHtml, openStandardModal, debounce, saveState, getState } from "../../utils.js";

const DEFAULT_LIMIT = 20;
let currentOffset = 0;
let currentQuery = getState('admin:flavor_profiles:query', '');

async function loadMoreFlavorProfiles() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const profiles = await fetchFlavorProfiles(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (profiles.error) {
            return `<tr><td colspan="7" class="admin-entity__error-cell">Error: ${escapeHtml(profiles.error)}</td></tr>`;
        }

        if (profiles.length === 0) {
            return `<tr><td colspan="7" class="admin-entity__no-data-cell">No more flavor profiles to load</td></tr>`;
        }

        return profiles.map(profile => renderFlavorProfileRow(profile)).join('');
    } catch (error) {
        console.error('Error loading more flavor profiles:', error);
        return `<tr><td colspan="7" class="admin-entity__error-cell">Failed to load flavor profiles</td></tr>`;
    }
}

function renderFlavorProfileRow(profile) {
    // Determine status badge if needed, or just display basic info
    const productName = profile.product_name || `Product #${profile.product_id}`;

    return `
        <tr data-id="${profile.product_id}">
            <td>${profile.product_id}</td>
            <td>
                <div style="display:flex; align-items:center; gap:10px;">
                    ${profile.product_image_url ? `<img src="${profile.product_image_url}" alt="" style="width:30px; height:30px; object-fit:cover; border-radius:4px;">` : ''}
                    <div>
                        <div style="font-weight:500;">${escapeHtml(productName)}</div>
                        <div style="font-size:0.85em; color:#888;">${escapeHtml(profile.product_slug || '')}</div>
                    </div>
                </div>
            </td>
            <td><div class="rating-pill">${profile.sweetness ?? '-'}</div></td>
            <td><div class="rating-pill">${profile.bitterness ?? '-'}</div></td>
            <td><div class="rating-pill">${profile.strength ?? '-'}</div></td>
            <td><div class="rating-pill">${profile.fruitiness ?? '-'}</div></td>
            <td><div class="rating-pill">${profile.spiciness ?? '-'}</div></td>
            <td>
                <button class="btn btn-outline btn-sm flavor-profile-view" data-id="${profile.product_id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm flavor-profile-edit" data-id="${profile.product_id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

export const FlavourProfiles = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const profiles = await fetchFlavorProfiles(DEFAULT_LIMIT, currentOffset, currentQuery);

        const hasData = Array.isArray(profiles) && profiles.length > 0;
        const tableRows = hasData
            ? profiles.map(renderFlavorProfileRow).join('')
            : `<tr><td colspan="7" class="admin-entity__empty">${profiles.error ? escapeHtml(profiles.error) : 'No flavor profiles found'}</td></tr>`;

        const countLabel = hasData ? `${profiles.length}${profiles.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        return `
            <div class="admin-entity">
                <div class="admin-entity__header">
                     <h2 class="admin-entity__title">Flavor Profiles Management (${countLabel})</h2>
                     <div class="admin-entity__actions">
                        <input id="flavor_profiles-search-input" class="admin-entity__search" type="search" placeholder="Search product name..." value="${escapeHtml(currentQuery)}" aria-label="Search flavor profiles" />
                        <button id="flavor_profiles-create-btn" class="btn btn-primary">Add New Profile</button>
                        <button id="flavor_profiles_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product</th>
                                <th>Sweet</th>
                                <th>Bitter</th>
                                <th>Strength</th>
                                <th>Fruity</th>
                                <th>Spicy</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                         <tbody id="flavor_profiles-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="flavor_profiles_load-more-wrapper" style="text-align:center;">
                    ${hasData && profiles.length === DEFAULT_LIMIT ? `
                        <button id="flavor_profiles_load-more-btn" class="btn btn-outline btn-sm">
                            Load More Flavor Profiles
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering flavor profiles table:', error);
        return `
            <div class="admin-entity">
                <div class="admin-entity__empty">
                    <strong>Error:</strong> Failed to load flavor profiles table
                </div>
            </div>
        `;
    }
};

const detailsHtml = (profile) => {
    // Helper to render bars for flavor attributes
    const renderBar = (label, value, colorVar = 'primary') => {
        const pct = (value / 10) * 100;
        const color = colorVar === 'primary' ? 'var(--accent)' : `var(--${colorVar})`;

        return `
            <div style="margin-bottom: var(--space-4);">
                <div style="display:flex; justify-content:space-between; margin-bottom: 4px; font-size: 0.9rem;">
                    <strong style="color: var(--text);">${escapeHtml(label)}</strong>
                    <span style="font-weight: 700; color: ${color};">${value}/10</span>
                </div>
                <div style="height: 6px; background: var(--border); border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: ${pct}%; background: ${color}; transition: width 0.3s ease;"></div>
                </div>
            </div>
        `;
    };

    const imageUrl = profile.product_image_url || '';

    return `
        <div class="flavor-profile-view-content">
            
            <!-- Product Info Header -->
            <div class="d-flex align-items-center mb-4 pb-4 border-bottom" style="border-color: var(--border);">
                 ${imageUrl ? `
                    <img src="${escapeHtml(imageUrl)}" style="width: 64px; height: 64px; object-fit: cover; border-radius: var(--radius-md, 0.5rem); border: 1px solid var(--border); margin-right: var(--space-4);" />
                ` : `
                    <div style="width: 64px; height: 64px; background: var(--bg-secondary); border-radius: var(--radius-md, 0.5rem); border: 1px solid var(--border); margin-right: var(--space-4); display: flex; align-items: center; justify-content: center; color: var(--text-light);">📷</div>
                `}
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text); margin: 0 0 var(--space-1) 0;">${escapeHtml(profile.product_name || 'Unknown Product')}</h3>
                    <div style="font-size: 0.875rem; color: var(--text-light); font-family: monospace;">ID: ${profile.product_id} • ${escapeHtml(profile.product_slug || '')}</div>
                </div>
            </div>

            <!-- Flavor Attributes -->
            <div style="background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-lg, 0.5rem); padding: var(--space-4); margin-bottom: var(--space-6);">
                <h4 style="font-size: 1rem; font-weight: 700; color: var(--text); margin-bottom: var(--space-4); border-bottom: 1px solid var(--border); padding-bottom: var(--space-2);">
                    Flavor Matrix
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--space-4);">
                    ${renderBar('Strength', profile.strength || 0, 'danger')}
                    ${renderBar('Sweetness', profile.sweetness || 0, 'warning')}
                    ${renderBar('Bitterness', profile.bitterness || 0, 'primary')}
                    ${renderBar('Smokiness', profile.smokiness || 0, 'secondary')}
                    ${renderBar('Fruitiness', profile.fruitiness || 0, 'success')}
                    ${renderBar('Spiciness', profile.spiciness || 0, 'danger')}
                </div>
            </div>

            <!-- Tags -->
            <div class="mb-2">
                <h4 style="font-size: 1rem; font-weight: 700; color: var(--text); margin-bottom: var(--space-2);">Tags</h4>
                <div>
                    ${profile.tags && profile.tags.length
            ? profile.tags.map(t => `<span class="badge" style="background: var(--bg-secondary); color: var(--text); border: 1px solid var(--border); margin-right: 4px; padding: 4px 8px; border-radius: 4px;">${escapeHtml(t)}</span>`).join('')
            : '<span style="color: var(--text-light); font-style: italic;">No tags</span>'}
                </div>
            </div>

             <div class="text-end border-top pt-3 mt-4" style="border-color: var(--border);">
                <button class="btn btn-primary flavor-profile-edit" data-id="${profile.product_id}" style="background: var(--accent); border-color: var(--accent); color: white;">Edit Profile</button>
            </div>
        </div>
    `;
};

// Event Delegation
(() => {
    async function performSearch(query) {
        try {
            currentQuery = query || '';
            currentOffset = 0;
            const results = await fetchFlavorProfiles(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('flavor_profiles-table-body');
            const loadMoreWrapper = document.getElementById('flavor_profiles_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="7" class="admin-entity__error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="7" class="admin-entity__no-data-cell">No flavor profiles found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderFlavorProfileRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="flavor_profiles_load-more-btn" class="btn btn-outline btn-sm">Load More Flavor Profiles</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // View - flavor-profile-view class only
        if (e.target.matches('.flavor-profile-view') || e.target.closest('.flavor-profile-view')) {
            const btn = e.target.closest('.flavor-profile-view');
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const result = await fetchModalDetails(id);
                if (result.success) {
                    openStandardModal({
                        title: `Flavor Profile: ${result.flavor_profile.product_name || 'Details'}`,
                        bodyHtml: detailsHtml(result.flavor_profile),
                        size: 'lg'
                    });
                } else {
                    throw new Error(result.error);
                }
            } catch (error) {
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="error-message">${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Edit - flavor-profile-edit class only
        if (e.target.matches('.flavor-profile-edit') || e.target.closest('.flavor-profile-edit')) {
            const btn = e.target.closest('.flavor-profile-edit');
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const html = await renderFlavorProfileEdit(Number(id));
                openStandardModal({
                    title: 'Edit Flavor Profile',
                    bodyHtml: html,
                    size: 'lg',
                    onMount: (container) => {
                        initFlavorProfileEditHandlers(container, Number(id), async () => {
                            // Refresh list
                            currentOffset = 0;
                            const refreshedHtml = await FlavourProfiles();
                            const adminEntity = document.querySelector('.admin-entity');
                            if (adminEntity) adminEntity.outerHTML = refreshedHtml;
                        });
                    }
                });
            } catch (error) {
                console.error('[FlavorProfiles] Edit modal error:', error);
            }
        }

        // Create
        if (e.target.id === 'flavor_profiles-create-btn') {
            try {
                const html = await renderFlavorProfileCreate();
                openStandardModal({
                    title: 'Create Flavor Profile',
                    bodyHtml: html,
                    size: 'lg',
                    onMount: (container) => {
                        initFlavorProfileCreateHandlers(container, async () => {
                            // Refresh list
                            currentOffset = 0;
                            const refreshedHtml = await FlavourProfiles();
                            const adminEntity = document.querySelector('.admin-entity');
                            if (adminEntity) adminEntity.outerHTML = refreshedHtml;
                        });
                    }
                });
            } catch (error) {
                console.error('[FlavorProfiles] Create modal error:', error);
            }
        }

        // Load More
        if (e.target.id === 'flavor_profiles_load-more-btn') {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Loading...';
            const html = await loadMoreFlavorProfiles();
            document.getElementById('flavor_profiles-table-body').insertAdjacentHTML('beforeend', html);
            if (html.includes('No more') || html.includes('Error')) {
                btn.disabled = true;
                btn.textContent = 'No more items';
            } else {
                btn.disabled = false;
                btn.textContent = 'Load More Flavor Profiles';
            }
        }

        // Refresh
        if (e.target.id === 'flavor_profiles_refresh-btn') {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Refreshing...';
            currentOffset = 0;
            currentQuery = '';
            const html = await FlavourProfiles();
            const container = document.querySelector('.admin-entity');
            if (container) container.outerHTML = html;
        }
    });

    document.addEventListener('input', (e) => {
        if (e.target.id === 'flavor_profiles-search-input') {
            debouncedSearch(e);
        }
    });

})();

window.loadMoreFlavorProfiles = loadMoreFlavorProfiles;
window.fetchFlavorProfiles = fetchFlavorProfiles;