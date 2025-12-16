import { fetchAllOrders, fetchModalDetails } from "./Orders.utils.js";
import { renderOrderCreate, initOrderCreateHandlers } from "./OrderCreate.js";
import { renderOrderEdit, initOrderEditHandlers } from "./OrderEdit.js";
import { API_ROUTES } from "../../dashboard.routes.js";
import {
    escapeHtml,
    formatDate,
    debounce,
    openStandardModal,
    closeModal
} from "../../utils.js";

const DEFAULT_LIMIT = 50;
let currentOffset = 0;
let currentSearch = "";

// ---------- ROW RENDER ---------- //
function renderOrderRow(order) {
    if (!order || !order.id) return '';

    const total = (order.total_cents != null) ? (order.total_cents / 100).toFixed(2) : '-';
    const itemCount = order.item_count ?? (Array.isArray(order.items) ? order.items.length : '-');
    const orderNumber = order.order_number || '-';
    const status = order.status || 'unknown';
    const createdAt = order.created_at || null;

    return `
<tr class="orders_row" data-order-id="${order.id}">
    <td>${order.id}</td>
    <td>${escapeHtml(orderNumber)}</td>
    <td><span class="badge badge-status-${status.toLowerCase()}">${escapeHtml(status)}</span></td>
    <td>$${total}</td>
    <td>${escapeHtml(order.user_name || order.user_email || '-')}</td>
    <td>${itemCount}</td>
    <td>${createdAt ? formatDate(createdAt) : '-'}</td>
    <td>
        <button class="btn btn-outline btn-sm js-admin-view" data-entity="order" data-id="${order.id}" title="View Details">👁️ View</button>
        <button class="btn btn-primary btn-sm js-admin-edit" data-entity="order" data-id="${order.id}" title="Edit Order">✏️ Edit</button>
    </td>
</tr>`;
}

// ---------- MODAL HTML ---------- //
const orderDetailsHtml = (order) => {
    if (!order) return '<div class="orders_empty">No order data</div>';

    const total = (order.total_cents != null) ? (order.total_cents / 100).toFixed(2) : '-';
    const orderNumber = escapeHtml(order.order_number || '-');
    const status = escapeHtml(order.status || 'unknown');
    const userName = escapeHtml(order.user_name || '-');
    const userEmail = escapeHtml(order.user_email || '-');
    const userPhone = escapeHtml(order.user_phone || '-');

    return `
<div class="admin-modal admin-modal--lg">
    <div class="bg-white border-b px-6 py-4 rounded-t-xl d-flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Order Details</h2>
            <p class="text-sm text-gray-500">Order #${orderNumber}</p>
        </div>
        <span class="badge badge-status-${status.toLowerCase()}">${status}</span>
    </div>
    
    <div class="admin-modal__body bg-gray-50 p-6">
        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
            <!-- Left Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Order Info</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Order #</span><span>${orderNumber}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Total</span><span class="font-medium">$${total}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Created</span><span>${order.created_at ? formatDate(order.created_at) : '-'}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Paid At</span><span>${order.paid_at ? formatDate(order.paid_at) : '-'}</span></div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Customer</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Name</span><span>${userName}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Email</span><span>${userEmail}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Phone</span><span>${userPhone}</span></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="d-flex flex-col gap-4">
                ${order.shipping_address ? `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Shipping Address</h4>
                    <div class="text-sm">
                        <div>${escapeHtml(order.shipping_address.recipient_name || '')}</div>
                        <div>${escapeHtml(order.shipping_address.address_line1 || '')}</div>
                        ${order.shipping_address.address_line2 ? `<div>${escapeHtml(order.shipping_address.address_line2)}</div>` : ''}
                        <div>${escapeHtml(order.shipping_address.city || '')} ${escapeHtml(order.shipping_address.postal_code || '')}</div>
                        <div>${escapeHtml(order.shipping_address.country || '')}</div>
                    </div>
                </div>
                ` : ''}
                
                ${order.items && Array.isArray(order.items) && order.items.length ? `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Items (${order.items.length})</h4>
                    <div class="d-flex flex-col gap-2">
                        ${order.items.map(it => `
                            <div class="d-flex justify-between items-center py-1 border-b">
                                <span>${escapeHtml(it.product_name || 'Unknown Product')}</span>
                                <span class="text-gray-500">x${it.quantity || 0}</span>
                                <span class="font-medium">$${((it.price_cents || 0) / 100).toFixed(2)}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
        
        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
            <button class="btn btn-outline" onclick="closeModal()">Close</button>
            <button class="btn btn-primary js-admin-edit" data-entity="order" data-id="${order.id || ''}">Edit Order</button>
        </div>
    </div>
</div>`;
};

// ---------- LOAD LOGIC ---------- //
async function loadMoreOrders() {
    currentOffset += DEFAULT_LIMIT;
    const orders = await fetchAllOrders(DEFAULT_LIMIT, currentOffset, currentSearch);

    if (orders.error) {
        return `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(orders.error)}</td></tr>`;
    }

    if (Array.isArray(orders)) {
        if (orders.length === 0) {
            return `<tr><td colspan="8" class="admin-entity__empty">No more orders to load</td></tr>`;
        }
        return orders.map(renderOrderRow).join("");
    }
    return `<tr><td colspan="8" class="admin-entity__empty">Unexpected response format</td></tr>`;
}

async function performSearch(query) {
    currentSearch = query.trim();
    currentOffset = 0;

    const orders = await fetchAllOrders(DEFAULT_LIMIT, 0, currentSearch);
    const tbody = document.getElementById("orders_table-body");
    const loadMoreWrapper = document.getElementById("orders_load-more-wrapper");

    if (!tbody) return;

    if (orders.error) {
        tbody.innerHTML = `<tr><td colspan="8" class="admin-entity__empty">${escapeHtml(orders.error)}</td></tr>`;
        if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        return;
    }

    if (Array.isArray(orders)) {
        if (orders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="admin-entity__empty">No orders found</td></tr>`;
            if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        } else {
            tbody.innerHTML = orders.map(renderOrderRow).join("");
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = orders.length >= DEFAULT_LIMIT
                    ? `<button id="orders_load-more-btn" class="btn btn-outline btn-sm">Load More Orders</button>`
                    : "";
            }
        }
    }
}

// ---------- MAIN EXPORT ---------- //
export const Orders = async (search = "") => {
    currentOffset = 0;
    currentSearch = search;

    const orders = await fetchAllOrders(DEFAULT_LIMIT, 0, currentSearch);
    let rows = "";
    let count = 0;
    let error = null;

    if (orders.error) {
        error = orders.error;
    } else if (Array.isArray(orders)) {
        rows = orders.map(renderOrderRow).join("");
        count = orders.length;
    }

    const loadMoreButton = count === DEFAULT_LIMIT ? `<button id="orders_load-more-btn" class="btn btn-outline btn-sm">Load More Orders</button>` : "";
    const emptyState = error
        ? `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(error)}</td></tr>`
        : `<tr><td colspan="8" class="admin-entity__empty">No orders found.</td></tr>`;

    return `
<div class="admin-entity">
    <div class="admin-entity__header">
        <h2 class="admin-entity__title">Orders (${count}${count === DEFAULT_LIMIT ? "+" : ""})</h2>
        <div class="admin-entity__actions">
            <input type="text" id="orders-search-input" class="admin-entity__search" placeholder="Search orders..." value="${escapeHtml(currentSearch)}" />
            <button class="btn btn-primary js-admin-create" data-entity="order">➕ Create Order</button>
            <button id="orders_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="admin-entity__table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Order #</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>User</th>
                    <th>Items</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="orders_table-body">
                ${rows || emptyState}
            </tbody>
        </table>
    </div>

    <div id="orders_load-more-wrapper" style="text-align:center;">
        ${loadMoreButton}
    </div>
</div>`;
};

// ---------- EVENT DELEGATION ---------- //
export const ordersListeners = async (container) => {
    if (!container) return null;

    const debouncedSearch = debounce((e) => performSearch(e.target.value), 300);
    const abortController = new AbortController();
    const signal = abortController.signal;

    // View button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-view');
        if (!btn || btn.dataset.entity !== 'order') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Orders] View button clicked:', { id });

        try {
            const result = await fetchModalDetails(id);
            if (result.error) {
                throw new Error(result.error);
            }

            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = orderDetailsHtml(result.order);
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
        if (!btn || btn.dataset.entity !== 'order') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Orders] Edit button clicked:', { id });

        try {
            const formHtml = await renderOrderEdit(parseInt(id));
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initOrderEditHandlers(modalBody, parseInt(id), (data, action) => {
                if (action === 'updated' || action === 'deleted') {
                    Orders().then(html => {
                        const container = document.getElementById('content');
                        if (container) {
                            container.querySelector('.admin-entity').outerHTML = html;
                            ordersListeners(container);
                        }
                    });
                }
            });
        } catch (error) {
            console.error('[Orders] Error opening edit form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Create button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-create');
        if (!btn || btn.dataset.entity !== 'order') return;

        e.preventDefault();
        console.log('[Orders] Create button clicked');

        try {
            const formHtml = await renderOrderCreate();
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initOrderCreateHandlers(modalBody, (data) => {
                Orders().then(html => {
                    const container = document.getElementById('content');
                    if (container) {
                        container.querySelector('.admin-entity').outerHTML = html;
                        ordersListeners(container);
                    }
                });
            });
        } catch (error) {
            console.error('[Orders] Error opening create form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Load More handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'orders_load-more-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Loading...";

        const html = await loadMoreOrders();
        document.getElementById("orders_table-body").insertAdjacentHTML("beforeend", html);

        if (html.includes("No more") || html.includes("admin-entity__empty")) {
            btn.remove();
        } else {
            btn.disabled = false;
            btn.textContent = "Load More Orders";
        }
    }, { signal });

    // Refresh handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'orders_refresh-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Refreshing...";
        await performSearch(currentSearch);
        btn.disabled = false;
        btn.textContent = "🔄 Refresh";
    }, { signal });

    // Search handler
    container.addEventListener('input', (e) => {
        if (e.target.id === 'orders-search-input') {
            debouncedSearch(e);
        }
    }, { signal });

    return {
        cleanup: () => abortController.abort()
    };
};
