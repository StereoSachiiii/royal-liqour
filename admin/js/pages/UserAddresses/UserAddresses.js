import { fetchUserAddresses, fetchModalDetails } from "./UserAddresses.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreUserAddresses() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const addresses = await fetchUserAddresses(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (addresses.error) {
            return `<tr><td colspan="8" class="user_addresses_error-cell">Error: ${escapeHtml(addresses.error)}</td></tr>`;
        }

        if (addresses.length === 0) {
            return `<tr><td colspan="8" class="user_addresses_no-data-cell">No more user addresses to load</td></tr>`;
        }

        return addresses.map(address => renderUserAddressRow(address)).join('');
    } catch (error) {
        console.error('Error loading more user addresses:', error);
        return `<tr><td colspan="8" class="user_addresses_error-cell">Failed to load user addresses</td></tr>`;
    }
}

function renderUserAddressRow(address) {
    return `
        <tr class="user_addresses_row user-address-row" data-address-id="${address.id}">
            <td class="user_addresses_cell">${address.id}</td>
            <td class="user_addresses_cell">${address.user_id}</td>
            <td class="user_addresses_cell">${escapeHtml(address.user_name)}</td>
            <td class="user_addresses_cell">${escapeHtml(address.user_email)}</td>
            <td class="user_addresses_cell">${address.address_type}</td>
            <td class="user_addresses_cell">${escapeHtml(address.city)}</td>
            <td class="user_addresses_cell">${escapeHtml(address.country)}</td>
            <td class="user_addresses_cell">${address.is_default ? 'Yes' : 'No'}</td>
            <td class="user_addresses_cell">${formatDate(address.created_at)}</td>
            <td class="user_addresses_cell user_addresses_actions">
                <button class="user_addresses_btn-view" data-id="${address.id}" title="View Details">👁️ View</button>
                <a href="manage/user_addresses/update.php?id=${address.id}" class="user_addresses_btn-edit btn-edit" title="Edit User Address">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const UserAddresses = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const addresses = await fetchUserAddresses(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (addresses.error) {
            return `
                <div class="user_addresses_table user-addresses-table">
                    <div class="user_addresses_error-box">
                        <strong>Error:</strong> ${escapeHtml(addresses.error)}
                    </div>
                </div>
            `;
        }

        if (addresses.length === 0) {
            return `
                <div class="user_addresses_table user-addresses-table">
                    <div class="user_addresses_no-data-box">
                        <p>📭 No user addresses found.</p>
                    </div>
                </div>
            `;
        }

        const tableRows = addresses.map(address => renderUserAddressRow(address)).join('');

        return `
            <div class="user_addresses_table user-addresses-table">
                <div class="user_addresses_header table-header">
                    <h2>User Addresses Management (${addresses.length}${addresses.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="user_addresses_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="user_addresses-search-input" class="user_addresses_search-input" type="search" placeholder="Search user name, email or city" aria-label="Search user addresses" />
                         <a href="manage/user_addresses/create.php" class="user_addresses_btn-primary btn-primary">
            Create
        </a>
                        <button id="user_addresses_refresh-btn" class="user_addresses_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="user_addresses_wrapper table-wrapper">
                    <table class="user_addresses_data-table user-addresses-data-table">
                        <thead>
                            <tr class="user_addresses_header-row table-header-row">
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
                        <tbody id="user_addresses-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="user_addresses_load-more-wrapper" class="user_addresses_load-more-wrapper" style="text-align:center;">
                    ${addresses.length === DEFAULT_LIMIT ? `
                        <button id="user_addresses_load-more-btn" class="user_addresses_btn-load-more btn-load-more">
                            Load More User Addresses
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering user addresses table:', error);
        return `
            <div class="user_addresses_table user-addresses-table">
                <div class="user_addresses_error-box">
                    <strong>Error:</strong> Failed to load user addresses table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (address) => {
    return `
<div class="user_addresses_card user-address-card">
    <div class="user_addresses_card-header user-address-card-header">
        <span>User Address Details</span>
        <span class="user_addresses_badge ${address.is_default ? 'user_addresses_status_paid' : 'user_addresses_status_pending'}">
            ${address.is_default ? 'Default' : 'Not Default'}
        </span>
        <button class="user_addresses_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="user_addresses_section-title user-address-section-title">Basic Info</div>
    <div class="user_addresses_data-grid user-address-data-grid">
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">ID</strong>
            <span class="user_addresses_value data-value">${address.id || 'N/A'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">User Name</strong>
            <span class="user_addresses_value data-value">${address.user_name ? escapeHtml(address.user_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">User Email</strong>
            <span class="user_addresses_value data-value">${address.user_email ? escapeHtml(address.user_email) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Type</strong>
            <span class="user_addresses_value data-value">${address.address_type}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Recipient Name</strong>
            <span class="user_addresses_value data-value">${address.recipient_name ? escapeHtml(address.recipient_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Phone</strong>
            <span class="user_addresses_value data-value">${address.phone ? escapeHtml(address.phone) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Address Line 1</strong>
            <span class="user_addresses_value data-value">${address.address_line1 ? escapeHtml(address.address_line1) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Address Line 2</strong>
            <span class="user_addresses_value data-value">${address.address_line2 ? escapeHtml(address.address_line2) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">City</strong>
            <span class="user_addresses_value data-value">${address.city ? escapeHtml(address.city) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">State</strong>
            <span class="user_addresses_value data-value">${address.state ? escapeHtml(address.state) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Postal Code</strong>
            <span class="user_addresses_value data-value">${address.postal_code ? escapeHtml(address.postal_code) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Country</strong>
            <span class="user_addresses_value data-value">${address.country ? escapeHtml(address.country) : '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="user_addresses_section-title user-address-section-title">Timeline</div>
    <div class="user_addresses_data-grid user-address-data-grid">
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Created At</strong>
            <span class="user_addresses_value data-value">${address.created_at ? formatDate(address.created_at) : 'N/A'}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Updated At</strong>
            <span class="user_addresses_value data-value">${address.updated_at ? formatDate(address.updated_at) : 'N/A'}</span>
        </div>
    </div>

    <div class="user_addresses_section-title user-address-section-title">Usage</div>
    <div class="user_addresses_data-grid user-address-data-grid">
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Used as Shipping</strong>
            <span class="user_addresses_value data-value">${address.used_as_shipping || 0}</span>
        </div>
        <div class="user_addresses_field data-field">
            <strong class="user_addresses_label data-label">Used as Billing</strong>
            <span class="user_addresses_value data-value">${address.used_as_billing || 0}</span>
        </div>
    </div>

    <div class="user_addresses_footer card-footer">
        <a href="manage/user_addresses/update.php?id=${address.id}" class="user_addresses_btn-primary btn-primary">
            Edit User Address
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch user address details');
        }

        const address = result.user_address;

        if (!address || typeof address !== 'object' || !address.id) {
            throw new Error('Invalid user address data format');
        }

        return detailsHtml(address);

    } catch (error) {
        throw new Error(error.message || 'Failed to load user address details');
    }
};


export const userAddressesListeners = async () => {


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
            const results = await fetchUserAddresses(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('user_addresses-table-body');
            const loadMoreWrapper = document.getElementById('user_addresses_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="8" class="user_addresses_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="user_addresses_no-data-cell">No user addresses found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderUserAddressRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="user_addresses_load-more-btn" class="user_addresses_btn-load-more btn-load-more">Load More User Addresses</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.user_addresses_btn-view') || e.target.closest('.user_addresses_btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.user_addresses_btn-view') ? e.target : e.target.closest('.user_addresses_btn-view');
            const addressId = button.dataset.id;

            if (!addressId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading user address details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(addressId));
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
                        <h3 class="modal-error-title">Error Loading User Address</h3>
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
        if (e.target.id === 'user_addresses_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreUserAddresses();
                document.getElementById('user_addresses-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more user addresses to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more user addresses to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More User Addresses';
                }
            } catch (error) {
                console.error('Error loading more user addresses:', error);
                button.disabled = false;
                button.textContent = 'Load More User Addresses';
            }
        }

        // refresh
        if (e.target.id === 'user_addresses_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await UserAddresses();
                document.querySelector('.user-addresses-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing user addresses:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'user_addresses-search-input') {
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
;
}

window.loadMoreUserAddresses = loadMoreUserAddresses;
window.fetchUserAddresses = fetchUserAddresses;