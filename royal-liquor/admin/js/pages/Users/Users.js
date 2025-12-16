import { fetchUsers, fetchModalDetails } from "./Users.utils.js";
import { renderUserCreate, initUserCreateHandlers } from "./UserCreate.js";
import { renderUserEdit, initUserEditHandlers } from "./UserEdit.js";
import { API_ROUTES } from "../../dashboard.routes.js";
import {
    escapeHtml,
    formatDate,
    debounce,
    openStandardModal,
    closeModal
} from "../../utils.js";

const DEFAULT_LIMIT = 20;
let currentOffset = 0;
let currentQuery = '';

// ---------- ROW RENDER ---------- //
function renderUserRow(user) {
    return `
        <tr data-user-id="${user.id}">
            <td>${user.id}</td>
            <td>${escapeHtml(user.name || '-')}</td>
            <td>${escapeHtml(user.email || '-')}</td>
            <td>${user.phone ? escapeHtml(user.phone) : '-'}</td>
            <td>
                <span class="badge badge-status-${user.is_active ? 'active' : 'inactive'}">
                    ${user.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <span class="badge badge-status-${user.is_admin ? 'admin' : 'user'}">
                    ${user.is_admin ? 'Admin' : 'User'}
                </span>
            </td>
            <td>${formatDate(user.created_at)}</td>
            <td>${user.last_login_at ? formatDate(user.last_login_at) : '-'}</td>
            <td>
                <button class="btn btn-outline btn-sm js-admin-view" data-entity="user" data-id="${user.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm js-admin-edit" data-entity="user" data-id="${user.id}" title="Edit User">✏️ Edit</button>
            </td>
        </tr>
    `;
}

// ---------- MODAL HTML ---------- //
const userDetailsHtml = (user) => {
    if (!user) return '<div class="admin-entity__empty">No user data</div>';

    const lifetimeValue = parseFloat(user.lifetime_value_cents) || 0;
    const avgOrderValue = parseFloat(user.avg_order_value_cents) || 0;
    const avatarUrl = user.profile_image_url || '';

    return `
<div class="admin-modal admin-modal--lg">
    <div class="bg-white border-b px-6 py-4 rounded-t-xl d-flex justify-between items-center">
        <div class="d-flex items-center gap-4">
            <!-- Avatar Preview -->
            ${avatarUrl ? `
            <img src="${escapeHtml(avatarUrl)}" alt="${escapeHtml(user.name)}" class="user-avatar-preview" />
            ` : `
            <div class="user-avatar-placeholder">
                <span>👤</span>
            </div>
            `}
            <div>
                <h2 class="text-xl font-semibold text-gray-900">User Details</h2>
                <p class="text-sm text-gray-500">${escapeHtml(user.email)}</p>
            </div>
        </div>
        <span class="badge badge-status-${user.is_active ? 'active' : 'inactive'}">${user.is_active ? 'Active' : 'Inactive'}</span>
    </div>
    
    <div class="admin-modal__body bg-gray-50 p-6">
        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
            <!-- Left Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Identity & Contact</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">ID</span><span>${user.id}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Name</span><span>${escapeHtml(user.name || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Email</span><span>${escapeHtml(user.email || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Phone</span><span>${escapeHtml(user.phone || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Role</span><span class="badge badge-status-${user.is_admin ? 'admin' : 'user'}">${user.is_admin ? 'ADMIN' : 'USER'}</span></div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Timeline</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Created</span><span>${user.created_at ? formatDate(user.created_at) : '-'}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Last Login</span><span>${user.last_login_at ? formatDate(user.last_login_at) : 'Never'}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Addresses</span><span>${user.address_count || 0} saved</span></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Orders & Finance</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Lifetime Value</span><span class="text-success font-medium">$${(lifetimeValue / 100).toFixed(2)}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Avg Order Value</span><span>$${(avgOrderValue / 100).toFixed(2)}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Total Orders</span><span>${user.total_orders || 0} (${user.completed_orders || 0} completed)</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Pending</span><span>${user.pending_orders || 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Cancelled</span><span>${user.cancelled_orders || 0}</span></div>
                    </div>
                </div>
                
                ${user.recent_orders && Array.isArray(user.recent_orders) && user.recent_orders.length > 0 ? `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Recent Orders (${user.recent_orders.length})</h4>
                    <div class="d-flex flex-col gap-2">
                        ${user.recent_orders.map(order => `
                            <div class="d-flex justify-between items-center py-1 border-b">
                                <span>${escapeHtml(order.order_number || 'N/A')}</span>
                                <span class="badge badge-status-${order.status}">${order.status}</span>
                                <span class="font-medium">$${((order.total_cents || 0) / 100).toFixed(2)}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
        
        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
            <button class="btn btn-outline" onclick="closeModal()">Close</button>
            <button class="btn btn-primary js-admin-edit" data-entity="user" data-id="${user.id}">Edit User</button>
        </div>
    </div>
</div>`;
};

// ---------- LOAD LOGIC ---------- //
async function loadMoreUsers() {
    currentOffset += DEFAULT_LIMIT;
    const users = await fetchUsers(DEFAULT_LIMIT, currentOffset, currentQuery);

    if (users.error) {
        return `<tr><td colspan="9" class="admin-entity__empty">Error: ${escapeHtml(users.error)}</td></tr>`;
    }

    if (Array.isArray(users)) {
        if (users.length === 0) {
            return `<tr><td colspan="9" class="admin-entity__empty">No more users to load</td></tr>`;
        }
        return users.map(renderUserRow).join("");
    }
    return `<tr><td colspan="9" class="admin-entity__empty">Unexpected response format</td></tr>`;
}

async function performSearch(query) {
    currentQuery = query.trim();
    currentOffset = 0;

    const users = await fetchUsers(DEFAULT_LIMIT, 0, currentQuery);
    const tbody = document.getElementById("users-table-body");
    const loadMoreWrapper = document.getElementById("users_load-more-wrapper");

    if (!tbody) return;

    if (users.error) {
        tbody.innerHTML = `<tr><td colspan="9" class="admin-entity__empty">${escapeHtml(users.error)}</td></tr>`;
        if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        return;
    }

    if (Array.isArray(users)) {
        if (users.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="admin-entity__empty">No users found</td></tr>`;
            if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        } else {
            tbody.innerHTML = users.map(renderUserRow).join("");
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = users.length >= DEFAULT_LIMIT
                    ? `<button id="users_load-more-btn" class="btn btn-outline btn-sm">Load More Users</button>`
                    : "";
            }
        }
    }
}

// ---------- MAIN EXPORT ---------- //
export const Users = async (search = "") => {
    currentOffset = 0;
    currentQuery = search;

    const users = await fetchUsers(DEFAULT_LIMIT, 0, currentQuery);
    let rows = "";
    let count = 0;
    let error = null;

    if (users.error) {
        error = users.error;
    } else if (Array.isArray(users)) {
        rows = users.map(renderUserRow).join("");
        count = users.length;
    }

    const loadMoreButton = count === DEFAULT_LIMIT ? `<button id="users_load-more-btn" class="btn btn-outline btn-sm">Load More Users</button>` : "";
    const emptyState = error
        ? `<tr><td colspan="9" class="admin-entity__empty">Error: ${escapeHtml(error)}</td></tr>`
        : `<tr><td colspan="9" class="admin-entity__empty">No users found.</td></tr>`;

    return `
<div class="admin-entity">
    <div class="admin-entity__header">
        <h2 class="admin-entity__title">Users Management (${count}${count === DEFAULT_LIMIT ? "+" : ""})</h2>
        <div class="admin-entity__actions">
            <input type="text" id="users-search-input" class="admin-entity__search" placeholder="Search name, email or phone..." value="${escapeHtml(currentQuery)}" />
            <button class="btn btn-primary js-admin-create" data-entity="user">➕ Create User</button>
            <button id="users_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="admin-entity__table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                ${rows || emptyState}
            </tbody>
        </table>
    </div>

    <div id="users_load-more-wrapper" style="text-align:center;">
        ${loadMoreButton}
    </div>
</div>`;
};

// ---------- EVENT DELEGATION ---------- //
export const usersListeners = async (container) => {
    if (!container) return null;

    const debouncedSearch = debounce((e) => performSearch(e.target.value), 300);
    const abortController = new AbortController();
    const signal = abortController.signal;

    // View button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-view');
        if (!btn || btn.dataset.entity !== 'user') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Users] View button clicked:', { id });

        try {
            const result = await fetchModalDetails(id);
            if (result.error) {
                throw new Error(result.error);
            }

            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = userDetailsHtml(result.user);
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';
        } catch (err) {
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">${escapeHtml(err.message)}</div>`
            });
        }
    }, { signal });

    // Edit button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-edit');
        if (!btn || btn.dataset.entity !== 'user') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Users] Edit button clicked:', { id });

        try {
            const formHtml = await renderUserEdit(parseInt(id));
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initUserEditHandlers(modalBody, parseInt(id), (data, action) => {
                if (action === 'updated' || action === 'deleted') {
                    Users().then(html => {
                        const container = document.getElementById('content');
                        if (container) {
                            container.querySelector('.admin-entity').outerHTML = html;
                            usersListeners(container);
                        }
                    });
                }
            });
        } catch (error) {
            console.error('[Users] Error opening edit form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Create button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-create');
        if (!btn || btn.dataset.entity !== 'user') return;

        e.preventDefault();
        console.log('[Users] Create button clicked');

        try {
            const formHtml = await renderUserCreate();
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initUserCreateHandlers(modalBody, (data) => {
                Users().then(html => {
                    const container = document.getElementById('content');
                    if (container) {
                        container.querySelector('.admin-entity').outerHTML = html;
                        usersListeners(container);
                    }
                });
            });
        } catch (error) {
            console.error('[Users] Error opening create form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Load More handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'users_load-more-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Loading...";

        const html = await loadMoreUsers();
        document.getElementById("users-table-body").insertAdjacentHTML("beforeend", html);

        if (html.includes("No more") || html.includes("admin-entity__empty")) {
            btn.remove();
        } else {
            btn.disabled = false;
            btn.textContent = "Load More Users";
        }
    }, { signal });

    // Refresh handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'users_refresh-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Refreshing...";
        await performSearch(currentQuery);
        btn.disabled = false;
        btn.textContent = "🔄 Refresh";
    }, { signal });

    // Search handler
    container.addEventListener('input', (e) => {
        if (e.target.id === 'users-search-input') {
            debouncedSearch(e);
        }
    }, { signal });

    return {
        cleanup: () => abortController.abort()
    };
};
