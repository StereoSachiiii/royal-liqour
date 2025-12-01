import { fetchUsers, fetchModalDetails } from "./Users.utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreUsers() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const users = await fetchUsers(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (users.error) {
            return `<tr><td colspan="9" class="users_error-cell">Error: ${escapeHtml(users.error)}</td></tr>`;
        }

        if (users.length === 0) {
            return `<tr><td colspan="9" class="users_no-data-cell">No more users to load</td></tr>`;
        }

        return users.map(user => renderUserRow(user)).join('');
    } catch (error) {
        console.error('Error loading more users:', error);
        return `<tr><td colspan="9" class="users_error-cell">Failed to load users</td></tr>`;
    }
}

function renderUserRow(user) {
    return `
        <tr class="users_row user-row" data-user-id="${user.id}">
            <td class="users_cell">${user.id}</td>
            <td class="users_cell">${escapeHtml(user.name)}</td>
            <td class="users_cell">${escapeHtml(user.email)}</td>
            <td class="users_cell">${user.phone ? escapeHtml(user.phone) : '-'}</td>
            <td class="users_cell">
                <span class="users_badge ${user.is_active ? 'users_status_paid' : 'users_status_cancelled'}">
                    ${user.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="users_cell">
                <span class="users_badge ${user.is_admin ? 'users_status_paid' : 'users_status_pending'}">
                    ${user.is_admin ? 'Admin' : 'User'}
                </span>
            </td>
            <td class="users_cell">${formatDate(user.created_at)}</td>
            <td class="users_cell">${user.last_login_at ? formatDate(user.last_login_at) : '-'}</td>
            <td class="users_cell users_actions">
                <button class="users_btn-view btn-view" data-id="${user.id}" title="View Details">👁️ View</button>
                <a href="manage/user/update.php?id=${user.id}" class="users_btn-edit btn-edit" title="Edit User">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const Users = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const users = await fetchUsers(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (users.error) {
            return `
                <div class="users_table users-table">
                    <div class="users_error-box">
                        <strong>Error:</strong> ${escapeHtml(users.error)}
                    </div>
                </div>
            `;
        }

        if (users.length === 0) {
            return `
                <div class="users_table users-table">
                    <div class="users_no-data-box">
                        <p>📭 No users found.</p>
                    </div>
                </div>
            `;
        }

        const tableRows = users.map(user => renderUserRow(user)).join('');

        return `
            <div class="users_table users-table">
                <div class="users_header table-header">
                    <h2>Users Management (${users.length}${users.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="users_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="users-search-input" class="users_search-input" type="search" placeholder="Search name, email or phone" aria-label="Search users" />
                        <a href="manage/user/create.php"> make a new user </a>
                        <button id="users_refresh-btn" class="users_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="users_wrapper table-wrapper">
                    <table class="users_data-table users-data-table">
                        <thead>
                            <tr class="users_header-row table-header-row">
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="users_load-more-wrapper" class="users_load-more-wrapper" style="text-align:center;">
                    ${users.length === DEFAULT_LIMIT ? `
                        <button id="users_load-more-btn" class="users_btn-load-more btn-load-more">
                            Load More Users
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering users table:', error);
        return `
            <div class="users_table users-table">
                <div class="users_error-box">
                    <strong>Error:</strong> Failed to load users table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (user) => {
    const lifetimeValue = parseFloat(user.lifetime_value_cents) || 0;
    const avgOrderValue = parseFloat(user.avg_order_value_cents) || 0;

    return `
<div class="users_card user-card">
    <div class="users_card-header user-card-header">
        <span>User Details</span>
        <span class="users_badge ${user.is_anonymized ? 'users_status_cancelled' : (user.is_active ? 'users_status_paid' : 'users_status_pending')}">
            ${user.is_anonymized ? 'Anonymized' : (user.is_active ? 'Active' : 'Inactive')}
        </span>
        <button class="users_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="users_section-title user-section-title">Identity & Contact</div>
    <div class="users_data-grid user-data-grid">
        <div class="users_field data-field">
            <strong class="users_label data-label">ID</strong>
            <span class="users_value data-value">${user.id || 'N/A'}</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Name</strong>
            <span class="users_value data-value">${user.name ? escapeHtml(user.name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Email</strong>
            <span class="users_value data-value">${user.email ? escapeHtml(user.email) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Phone</strong>
            <span class="users_value data-value">${user.phone ? escapeHtml(user.phone) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Role</strong>
            <div>
                <span class="users_badge badge-role ${user.is_admin ? 'users_status_paid' : 'users_status_pending'}">
                    ${user.is_admin ? 'ADMIN' : 'USER'}
                </span>
            </div>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Profile Image</strong>
            <span class="users_value data-value">
                ${user.profile_image_url ? `<a href="${escapeHtml(user.profile_image_url)}" target="_blank" class="link-primary">View Image</a>` : '<span class="data-empty">No Image</span>'}
            </span>
        </div>
    </div>

    <div class="users_section-title user-section-title">Timeline & Activity</div>
    <div class="users_data-grid user-data-grid">
        <div class="users_field data-field">
            <strong class="users_label data-label">Created At</strong>
            <span class="users_value data-value">${user.created_at ? formatDate(user.created_at) : 'N/A'}</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Last Login</strong>
            <span class="users_value data-value">${user.last_login_at ? formatDate(user.last_login_at) : '<span class="data-empty">Never</span>'}</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Updated</strong>
            <span class="users_value data-value">${user.updated_at ? formatDate(user.updated_at) : 'N/A'}</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Addresses</strong>
            <span class="users_value data-value">${user.address_count || '0'} saved</span>
        </div>
    </div>

    <div class="users_section-title user-section-title">Orders & Finance</div>
    <div class="users_data-grid user-data-grid">
        <div class="users_field data-field">
            <strong class="users_label data-label">Lifetime Value</strong>
            <span class="users_value data-value text-success">$${(lifetimeValue / 100).toFixed(2)}</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Avg Order Value</strong>
            <span class="users_value data-value">$${(avgOrderValue / 100).toFixed(2)}</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Total Orders</strong>
            <span class="users_value data-value">${user.total_orders || '0'} <span class="data-empty">(${user.completed_orders || '0'} completed)</span></span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Pending Orders</strong>
            <span class="users_value data-value ${(user.pending_orders || 0) > 0 ? 'text-warning' : ''}">${user.pending_orders || '0'} orders</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Cancelled Orders</strong>
            <span class="users_value data-value">${user.cancelled_orders || '0'}</span>
        </div>
        <div class="users_field data-field">
            <strong class="users_label data-label">Active Carts</strong>
            <span class="users_value data-value">${user.active_carts || '0'}</span>
        </div>
    </div>

    <div class="users_section-title user-section-title">Recent Orders</div>
    <div class="users_items recent-orders-container">
        ${user.recent_orders && Array.isArray(user.recent_orders) && user.recent_orders.length > 0 ?
            user.recent_orders.map(order => `
                <div class="users_item-row order-row">
                    <div class="users_item-name order-id">${order.order_number || 'N/A'}</div>
                    <div class="users_item-qty order-date">${order.created_at ? formatOrderDate(order.created_at) : 'N/A'}</div>
                    <div class="users_item-price">
                        <span class="users_badge ${order.status === 'delivered' || order.status === 'paid' ? 'users_status_paid' : (order.status === 'cancelled' ? 'users_status_cancelled' : 'users_status_processing')}">
                            ${(order.status || 'unknown').toUpperCase()}
                        </span>
                        <div class="order-total">$${order.total_cents ? (order.total_cents / 100).toFixed(2) : '0.00'}</div>
                    </div>
                </div>
            `).join('')
            : '<div class="empty-state">No recent orders found.</div>'
        }
    </div>

    <div class="users_footer card-footer">
        <a href="manage/user/update.php?id=${user.id}" class="users_btn-primary btn-primary">
            Edit User Profile
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch user details');
        }

        const user = result.user;

        if (!user || typeof user !== 'object' || !user.id) {
            throw new Error('Invalid user data format');
        }

        return detailsHtml(user);

    } catch (error) {
        throw new Error(error.message || 'Failed to load user details');
    }
};

document.addEventListener('DOMContentLoaded', () => {
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
            const results = await fetchUsers(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('users-table-body');
            const loadMoreWrapper = document.getElementById('users_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="9" class="users_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="9" class="users_no-data-cell">No users found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }
            console.log(results);
            tbody.innerHTML = results.map(renderUserRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="users_load-more-btn" class="users_btn-load-more btn-load-more">Load More Users</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal (unchanged)
        if (e.target.matches('.btn-view') || e.target.closest('.btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.btn-view') ? e.target : e.target.closest('.btn-view');
            const userId = button.dataset.id;

            if (!userId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading user details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(userId));
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
                        <h3 class="modal-error-title">Error Loading User</h3>
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
        if (e.target.id === 'users_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreUsers();
                document.getElementById('users-table-body').insertAdjacentHTML('beforeend', html);

                // if returned "no more" row then disable button
                if (html.includes('No more users to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more users to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Users';
                }
            } catch (error) {
                console.error('Error loading more users:', error);
                button.disabled = false;
                button.textContent = 'Load More Users';
            }
        }

        // refresh - reset search too
        if (e.target.id === 'users_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await Users();
                document.querySelector('.users-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing users:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'users-search-input') {
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
});

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
    }
}

function formatOrderDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        console.error('Error formatting order date:', error);
        return dateString;
    }
}

window.loadMoreUsers = loadMoreUsers;
window.fetchUsers = fetchUsers;
