import { fetchStock, fetchModalDetails } from "./Stocks.utils.js";
import { renderStockCreate, initStockCreateHandlers } from "./StockCreate.js";
import { renderStockEdit, initStockEditHandlers } from "./StockEdit.js";
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
function renderStockRow(item) {
    return `
        <tr class="stock_row" data-stock-id="${item.id}">
            <td>${item.id}</td>
            <td>${escapeHtml(item.product_name || '-')}</td>
            <td>${escapeHtml(item.warehouse_name || '-')}</td>
            <td>${item.quantity || 0}</td>
            <td>${item.reserved || 0}</td>
            <td>${item.available || 0}</td>
            <td>${formatDate(item.updated_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm js-admin-view" data-entity="stock" data-id="${item.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm js-admin-edit" data-entity="stock" data-id="${item.id}" title="Edit Stock">✏️ Edit</button>
            </td>
        </tr>
    `;
}

// ---------- MODAL HTML ---------- //
function getStatusClass(status) {
    switch (status) {
        case 'paid':
        case 'delivered':
            return 'badge-status-active';
        case 'pending':
        case 'processing':
            return 'badge-status-pending';
        case 'cancelled':
        case 'refunded':
            return 'badge-status-cancelled';
        default:
            return 'badge-status-pending';
    }
}

const stockDetailsHtml = (item) => {
    if (!item) return '<div class="admin-entity__empty">No stock data</div>';

    const inventoryValue = ((item.quantity || 0) * (item.price_cents || 0)) / 100;
    const available = (item.quantity || 0) - (item.reserved || 0);

    return `
<div class="admin-modal admin-modal--lg">
    <div class="bg-white border-b px-6 py-4 rounded-t-xl d-flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Stock Details</h2>
            <p class="text-sm text-gray-500">${escapeHtml(item.product_name || '')} @ ${escapeHtml(item.warehouse_name || '')}</p>
        </div>
        ${item.out_of_stock ? `<span class="badge badge-status-cancelled">⚠️ OUT OF STOCK</span>` :
            item.low_stock_warning ? `<span class="badge badge-status-pending">⚠️ LOW STOCK</span>` :
                `<span class="badge badge-status-active">✓ In Stock</span>`}
    </div>
    
    ${item.out_of_stock || item.low_stock_warning ? `
    <div class="stock-alert-banner ${item.out_of_stock ? 'stock-alert-danger' : 'stock-alert-warning'}" style="padding: 12px 24px; background: ${item.out_of_stock ? '#fee2e2' : '#fef3c7'}; border-bottom: 1px solid ${item.out_of_stock ? '#fecaca' : '#fde68a'};">
        <strong>${item.out_of_stock ? '⛔ Out of Stock!' : '⚠️ Low Stock Warning'}</strong>
        <span style="margin-left: 8px;">Only ${available} units available${item.reserved > 0 ? ` (${item.reserved} reserved for pending orders)` : ''}</span>
    </div>
    ` : ''}
    
    <div class="admin-modal__body bg-gray-50 p-6">
        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
            <!-- Left Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Basic Info</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">ID</span><span>${item.id}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Product</span><span>${escapeHtml(item.product_name || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Slug</span><span>${escapeHtml(item.product_slug || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Price</span><span>$${((item.price_cents || 0) / 100).toFixed(2)}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Warehouse</span><span>${escapeHtml(item.warehouse_name || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Address</span><span>${escapeHtml(item.warehouse_address || '-')}</span></div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Stock Levels</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Total Quantity</span><span class="font-medium">${item.quantity || 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Reserved (Pending Orders)</span><span class="text-warning">${item.reserved || 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Available for Sale</span><span class="${available <= 0 ? 'text-danger' : available < 20 ? 'text-warning' : 'text-success'} font-medium">${available}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Inventory Value</span><span>$${inventoryValue.toFixed(2)}</span></div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Timeline</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Created</span><span>${item.created_at ? formatDate(item.created_at) : '-'}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Updated</span><span>${item.updated_at ? formatDate(item.updated_at) : '-'}</span></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="d-flex flex-col gap-4">
                ${item.pending_orders && Array.isArray(item.pending_orders) && item.pending_orders.length > 0 ? `
                <div class="bg-white p-4 rounded-lg border border-warning">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">📦 Pending Orders (${item.pending_orders.length})</h4>
                    <p class="text-xs text-gray-500 mb-2">These orders have reserved stock that cannot be sold</p>
                    <div class="d-flex flex-col gap-2" style="max-height: 200px; overflow-y: auto;">
                        ${item.pending_orders.map(o => `
                            <div class="d-flex justify-between items-center py-1 border-b">
                                <div>
                                    <span class="font-medium">${escapeHtml(o.order_number || 'N/A')}</span>
                                    ${o.customer_name ? `<span class="text-xs text-gray-500 ml-2">${escapeHtml(o.customer_name)}</span>` : ''}
                                </div>
                                <div class="d-flex items-center gap-2">
                                    <span class="badge ${getStatusClass(o.status)}">${o.status || '-'}</span>
                                    <span class="text-sm">Qty: ${o.quantity || 0}</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
                
                ${item.recent_movements && Array.isArray(item.recent_movements) && item.recent_movements.length > 0 ? `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">📋 Recent Order History (${item.recent_movements.length})</h4>
                    <div class="d-flex flex-col gap-2" style="max-height: 200px; overflow-y: auto;">
                        ${item.recent_movements.map(m => `
                            <div class="d-flex justify-between items-center py-1 border-b">
                                <span>${escapeHtml(m.order_number || 'N/A')}</span>
                                <span class="badge ${getStatusClass(m.status)}">${m.status || '-'}</span>
                                <span>Qty: ${m.quantity || 0}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">📋 Order History</h4>
                    <p class="text-gray-500 text-sm">No orders found for this stock location</p>
                </div>
                `}
            </div>
        </div>
        
        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
            <button class="btn btn-outline" onclick="closeModal()">Close</button>
            <button class="btn btn-primary js-admin-edit" data-entity="stock" data-id="${item.id}">Adjust Stock</button>
        </div>
    </div>
</div>`;
};

// ---------- LOAD LOGIC ---------- //
async function loadMoreStock() {
    currentOffset += DEFAULT_LIMIT;
    const result = await fetchStock(DEFAULT_LIMIT, currentOffset, currentQuery);

    if (result.error) {
        return `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(result.error)}</td></tr>`;
    }

    const stocks = result.stocks || [];
    if (stocks.length === 0) {
        return `<tr><td colspan="8" class="admin-entity__empty">No more stock to load</td></tr>`;
    }
    return stocks.map(renderStockRow).join("");
}

async function performSearch(query) {
    currentQuery = query.trim();
    currentOffset = 0;

    const result = await fetchStock(DEFAULT_LIMIT, 0, currentQuery);
    const tbody = document.getElementById("stock-table-body");
    const loadMoreWrapper = document.getElementById("stock_load-more-wrapper");

    if (!tbody) return;

    if (result.error) {
        tbody.innerHTML = `<tr><td colspan="8" class="admin-entity__empty">${escapeHtml(result.error)}</td></tr>`;
        if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        return;
    }

    const stocks = result.stocks || [];
    if (stocks.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="admin-entity__empty">No stock found</td></tr>`;
        if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
    } else {
        tbody.innerHTML = stocks.map(renderStockRow).join("");
        if (loadMoreWrapper) {
            loadMoreWrapper.innerHTML = stocks.length >= DEFAULT_LIMIT
                ? `<button id="stock_load-more-btn" class="btn btn-outline btn-sm">Load More Stock</button>`
                : "";
        }
    }
}

// ---------- MAIN EXPORT ---------- //
export const Stock = async (search = "") => {
    currentOffset = 0;
    currentQuery = search;

    const result = await fetchStock(DEFAULT_LIMIT, 0, currentQuery);
    let rows = "";
    let count = 0;
    let error = null;

    if (result.error) {
        error = result.error;
    } else {
        const stocks = result.stocks || [];
        rows = stocks.map(renderStockRow).join("");
        count = stocks.length;
    }

    const loadMoreButton = count === DEFAULT_LIMIT ? `<button id="stock_load-more-btn" class="btn btn-outline btn-sm">Load More Stock</button>` : "";
    const emptyState = error
        ? `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(error)}</td></tr>`
        : `<tr><td colspan="8" class="admin-entity__empty">No stock found.</td></tr>`;

    return `
<div class="admin-entity">
    <div class="admin-entity__header">
        <h2 class="admin-entity__title">Stock Management (${count}${count === DEFAULT_LIMIT ? "+" : ""})</h2>
        <div class="admin-entity__actions">
            <input type="text" id="stock-search-input" class="admin-entity__search" placeholder="Search product or warehouse..." value="${escapeHtml(currentQuery)}" />
            <button class="btn btn-primary js-admin-create" data-entity="stock">➕ Create Stock</button>
            <button id="stock_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="admin-entity__table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Warehouse</th>
                    <th>Quantity</th>
                    <th>Reserved</th>
                    <th>Available</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="stock-table-body">
                ${rows || emptyState}
            </tbody>
        </table>
    </div>

    <div id="stock_load-more-wrapper" style="text-align:center;">
        ${loadMoreButton}
    </div>
</div>`;
};

// ---------- EVENT DELEGATION ---------- //
export const stockListeners = async (container) => {
    if (!container) return null;

    const debouncedSearch = debounce((e) => performSearch(e.target.value), 300);
    const abortController = new AbortController();
    const signal = abortController.signal;

    // View button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-view');
        if (!btn || btn.dataset.entity !== 'stock') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Stock] View button clicked:', { id });

        try {
            const result = await fetchModalDetails(id);
            if (result.error) {
                throw new Error(result.error);
            }

            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = stockDetailsHtml(result.stock);
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
        if (!btn || btn.dataset.entity !== 'stock') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Stock] Edit button clicked:', { id });

        try {
            const formHtml = await renderStockEdit(parseInt(id));
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initStockEditHandlers(modalBody, parseInt(id), (data, action) => {
                if (action === 'updated' || action === 'deleted') {
                    Stock().then(html => {
                        const existingEntity = container.querySelector('.admin-entity');
                        if (existingEntity) {
                            existingEntity.outerHTML = html;
                            stockListeners(container);
                        }
                    });
                }
            });
        } catch (error) {
            console.error('[Stock] Error opening edit form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Create button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-create');
        if (!btn || btn.dataset.entity !== 'stock') return;

        e.preventDefault();
        console.log('[Stock] Create button clicked');

        try {
            const formHtml = await renderStockCreate();
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initStockCreateHandlers(modalBody, (data) => {
                Stock().then(html => {
                    const existingEntity = container.querySelector('.admin-entity');
                    if (existingEntity) {
                        existingEntity.outerHTML = html;
                        stockListeners(container);
                    }
                });
            });
        } catch (error) {
            console.error('[Stock] Error opening create form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Load More handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'stock_load-more-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Loading...";

        const html = await loadMoreStock();
        document.getElementById("stock-table-body").insertAdjacentHTML("beforeend", html);

        if (html.includes("No more") || html.includes("admin-entity__empty")) {
            btn.remove();
        } else {
            btn.disabled = false;
            btn.textContent = "Load More Stock";
        }
    }, { signal });

    // Refresh handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'stock_refresh-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Refreshing...";
        await performSearch(currentQuery);
        btn.disabled = false;
        btn.textContent = "🔄 Refresh";
    }, { signal });

    // Search handler
    container.addEventListener('input', (e) => {
        if (e.target.id === 'stock-search-input') {
            debouncedSearch(e);
        }
    }, { signal });

    return {
        cleanup: () => abortController.abort()
    };
};