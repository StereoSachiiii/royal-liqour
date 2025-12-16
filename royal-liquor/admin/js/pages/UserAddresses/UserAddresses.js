import { fetchUserAddresses, fetchModalDetails } from "./UserAddresses.utils.js";
import { renderUserAddressEdit, initUserAddressEditHandlers } from "./UserAddressEdit.js";
import { renderUserAddressCreate, initUserAddressCreateHandlers } from "./UserAddressCreate.js";
import { escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal } from "../../utils.js";

const DEFAULT_LIMIT = 10;
let currentOffset = 0;
let currentQuery = getState('admin:addresses:query', '');
let lastResults = [];

function renderAddressRow(address) {
    if (!address || !address.id) return '';

    const userName = escapeHtml(address.user_name || '-');
    const userEmail = escapeHtml(address.user_email || '-');
    const city = escapeHtml(address.city || '-');
    const country = escapeHtml(address.country || '-');

    return `
        <tr data-address-id="${address.id}">
            <td>${address.id}</td>
            <td>${address.user_id}</td>
            <td>${userName}</td>
            <td>${userEmail}</td>
            <td>${escapeHtml(address.address_type || '-')}</td>
            <td>${city}</td>
            <td>${country}</td>
            <td><span class="badge ${address.is_default ? 'badge-active' : 'badge-inactive'}">${address.is_default ? 'Default' : 'Not Default'}</span></td>
            <td>${formatDate(address.created_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm user-address-view" data-id="${address.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm user-address-edit" data-id="${address.id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

async function loadMoreAddresses() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const addresses = await fetchUserAddresses(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (addresses.error) {
            return `<tr><td colspan="10" class="admin-entity__empty">Error: ${escapeHtml(addresses.error)}</td></tr>`;
        }

        if (!addresses.length) {
            return `<tr><td colspan="10" class="admin-entity__empty">No more addresses to load</td></tr>`;
        }

        return addresses.map(renderAddressRow).join('');
    } catch (error) {
        console.error('Error loading more addresses:', error);
        return `<tr><td colspan="10" class="admin-entity__empty">Failed to load addresses</td></tr>`;
    }
}

export const UserAddresses = async () => {
    try {
        currentOffset = 0;
        const addresses = await fetchUserAddresses(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (addresses.error) {
            lastResults = [];
        } else {
            lastResults = Array.isArray(addresses) ? addresses : [];
        }

        const hasData = lastResults && lastResults.length > 0;
        const tableRows = hasData
            ? lastResults.map(renderAddressRow).join('')
            : `<tr><td colspan="10" class="admin-entity__empty">${addresses && addresses.error ? escapeHtml(addresses.error) : 'No addresses found'}</td></tr>`;

        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        return `
            <div class="admin-entity">
                <div class="admin-entity__header">
                    <h2 class="admin-entity__title">User Addresses Management (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="addresses-search-input" class="admin-entity__search" type="search" placeholder="Search user or city..." value="${escapeHtml(currentQuery)}" />
                        <button id="user-address-create-btn" class="btn btn-primary">+ New Address</button>
                        <button id="addresses_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>User Name</th>
                                <th>User Email</th>
                                <th>Type</th>
                                <th>City</th>
                                <th>Country</th>
                                <th>Default</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="addresses_table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
                <div id="addresses_load-more-wrapper" style="text-align:center; margin-top: var(--space-4);">
                    ${hasData && lastResults.length === DEFAULT_LIMIT ? `<button id="addresses_load-more-btn" class="btn btn-outline btn-sm">Load More Addresses</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering addresses table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> Failed to load addresses table</div></div>`;
    }
};

const addressDetailsHtml = (address) => {
    if (!address) return '<div class="admin-entity__empty">No address data</div>';

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Address Details</span>
                <span class="badge ${address.is_default ? 'badge-active' : 'badge-inactive'}">${address.is_default ? 'Default' : 'Not Default'}</span>
            </div>
            <div class="products_section-title">User Info</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>User Name</strong><span>${escapeHtml(address.user_name || '-')}</span></div>
                <div class="products_field"><strong>User Email</strong><span>${escapeHtml(address.user_email || '-')}</span></div>
                <div class="products_field"><strong>Recipient Name</strong><span>${escapeHtml(address.recipient_name || '-')}</span></div>
                <div class="products_field"><strong>Phone</strong><span>${escapeHtml(address.phone || '-')}</span></div>
            </div>
            <div class="products_section-title">Address Details</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Type</strong><span>${escapeHtml(address.address_type || '-')}</span></div>
                <div class="products_field" style="grid-column: 1 / -1;"><strong>Address Line 1</strong><span>${escapeHtml(address.address_line1 || '-')}</span></div>
                <div class="products_field" style="grid-column: 1 / -1;"><strong>Address Line 2</strong><span>${escapeHtml(address.address_line2 || '-')}</span></div>
                <div class="products_field"><strong>City</strong><span>${escapeHtml(address.city || '-')}</span></div>
                <div class="products_field"><strong>State</strong><span>${escapeHtml(address.state || '-')}</span></div>
                <div class="products_field"><strong>Postal Code</strong><span>${escapeHtml(address.postal_code || '-')}</span></div>
                <div class="products_field"><strong>Country</strong><span>${escapeHtml(address.country || '-')}</span></div>
            </div>
            <div class="products_section-title">Usage Stats</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Used as Shipping</strong><span>${address.used_as_shipping ?? 0} times</span></div>
                <div class="products_field"><strong>Used as Billing</strong><span>${address.used_as_billing ?? 0} times</span></div>
            </div>
            <div class="products_section-title">Timeline</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Created At</strong><span>${formatDate(address.created_at)}</span></div>
                <div class="products_field"><strong>Updated At</strong><span>${address.updated_at ? formatDate(address.updated_at) : '-'}</span></div>
            </div>
            <div class="products_footer">
                <a href="manage/user_addresses/update.php?id=${address.id}" class="btn btn-primary">Edit Address</a>
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

        return addressDetailsHtml(result.user_address);
    } catch (error) {
        throw new Error(error.message || 'Failed to load address details');
    }
};

// Attach delegated handlers
(() => {
    async function performSearch(query) {
        try {
            currentQuery = query || '';
            saveState('admin:addresses:query', currentQuery);
            currentOffset = 0;
            const results = await fetchUserAddresses(DEFAULT_LIMIT, 0, currentQuery);

            lastResults = results.error ? [] : (Array.isArray(results) ? results : []);
            const hasData = lastResults.length > 0;
            const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

            const titleEl = document.querySelector('.admin-entity__title');
            if (titleEl) titleEl.textContent = `User Addresses Management (${countLabel})`;

            const tbody = document.getElementById('addresses_table-body');
            if (tbody) {
                tbody.innerHTML = hasData
                    ? lastResults.map(renderAddressRow).join('')
                    : `<tr><td colspan="10" class="admin-entity__empty">${results.error ? escapeHtml(results.error) : 'No addresses found'}</td></tr>`;
            }

            const loadMoreWrapper = document.getElementById('addresses_load-more-wrapper');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = hasData && lastResults.length === DEFAULT_LIMIT
                    ? `<button id="addresses_load-more-btn" class="btn btn-outline btn-sm">Load More Addresses</button>`
                    : '';
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('input', (e) => {
        if (e.target.id === 'addresses-search-input') {
            debouncedSearch(e);
        }
    });

    document.addEventListener('click', async (e) => {
        // View button
        // View button - user-address-view class only
        if (e.target.matches('.user-address-view') || e.target.closest('.user-address-view')) {
            const btn = e.target.closest('.user-address-view') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const html = await renderModal(id);
                openStandardModal({
                    title: 'Address Details',
                    bodyHtml: html,
                    size: 'xl'
                });
            } catch (err) {
                openStandardModal({
                    title: 'Error loading address',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(err.message)}</div>`,
                    size: 'xl'
                });
            }
        }

        // Edit button - user-address-edit class only
        if (e.target.matches('.user-address-edit') || e.target.closest('.user-address-edit')) {
            const btn = e.target.closest('.user-address-edit') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const formHtml = await renderUserAddressEdit(parseInt(id));
                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initUserAddressEditHandlers(modalBody, parseInt(id), (data, action) => {
                    if (action === 'updated' || action === 'deleted') {
                        performSearch(currentQuery);
                    }
                });
            } catch (error) {
                console.error('Error opening edit address:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Create button
        if (e.target.id === 'user-address-create-btn') {
            try {
                const formHtml = await renderUserAddressCreate();
                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initUserAddressCreateHandlers(modalBody, (data, action) => {
                    if (action === 'created') {
                        performSearch(currentQuery);
                    }
                });
            } catch (error) {
                console.error('Error opening create address:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Load more
        if (e.target.id === 'addresses_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';
            try {
                const html = await loadMoreAddresses();
                document.getElementById('addresses_table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more addresses') || html.includes('Error')) {
                    button.remove();
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Addresses';
                }
            } catch {
                button.disabled = false;
                button.textContent = 'Load More Addresses';
            }
        }

        // Refresh
        if (e.target.id === 'addresses_refresh-btn') {
            performSearch(currentQuery);
        }
    });
})();

// Export for backwards compatibility
export const userAddressesListeners = async () => {
    console.log('userAddressesListeners called - listeners are now auto-attached');
};

window.loadMoreAddresses = loadMoreAddresses;