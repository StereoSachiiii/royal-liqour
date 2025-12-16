import { fetchOrderItems, fetchModalDetails } from "./OrderItems.utils.js";
import { renderOrderItemEdit, initOrderItemEditHandlers } from "./OrderItemEdit.js";
import { escapeHtml, formatDate, formatCurrency, debounce, saveState, getState, openStandardModal, openFormModal } from "../../utils.js";

const DEFAULT_LIMIT = 10;
let currentOffset = 0;
let currentQuery = getState('admin:order_items:query', '');
let lastResults = [];

function renderOrderItemRow(item) {
    if (!item || !item.id) return '';

    const productName = escapeHtml(item.product_name || item.current_product_name || '-');
    const userName = escapeHtml(item.user_name || '-');
    const price = formatCurrency(item.price_cents || 0);
    const subtotal = formatCurrency((item.price_cents || 0) * (item.quantity || 0));

    return `
        <tr data-item-id="${item.id}">
            <td>${item.id}</td>
            <td><a href="#" class="link" onclick="event.preventDefault(); console.log('Order ${item.order_id}')">#${item.order_id}</a></td>
            <td>${productName}</td>
            <td>${userName}</td>
            <td>${item.quantity || 0}</td>
            <td>${price}</td>
            <td>${subtotal}</td>
            <td><span class="badge badge-${item.order_status || 'pending'}">${escapeHtml(item.order_status || 'N/A')}</span></td>
            <td>${formatDate(item.created_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm js-admin-view" data-entity="order_item" data-id="${item.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm js-admin-edit" data-entity="order_item" data-id="${item.id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

async function loadMoreOrderItems() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const items = await fetchOrderItems(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (items.error) {
            return `<tr><td colspan="10" class="admin-entity__empty">Error: ${escapeHtml(items.error)}</td></tr>`;
        }

        if (!items.length) {
            return `<tr><td colspan="10" class="admin-entity__empty">No more order items to load</td></tr>`;
        }

        return items.map(renderOrderItemRow).join('');
    } catch (error) {
        console.error('Error loading more order items:', error);
        return `<tr><td colspan="10" class="admin-entity__empty">Failed to load order items</td></tr>`;
    }
}

export const OrderItems = async () => {
    try {
        currentOffset = 0;
        const items = await fetchOrderItems(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (items.error) {
            lastResults = [];
        } else {
            lastResults = Array.isArray(items) ? items : [];
        }

        const hasData = lastResults && lastResults.length > 0;
        const tableRows = hasData
            ? lastResults.map(renderOrderItemRow).join('')
            : `<tr><td colspan="10" class="admin-entity__empty">${items && items.error ? escapeHtml(items.error) : 'No order items found'}</td></tr>`;

        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        return `
            <div class="admin-entity">
                <div class="admin-entity__header">
                    <h2 class="admin-entity__title">Order Items Management (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="order_items-search-input" class="admin-entity__search" type="search" placeholder="Search..." value="${escapeHtml(currentQuery)}" />
                        <button id="order_items_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Order</th>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="order_items_table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
                <div id="order_items_load-more-wrapper" style="text-align:center; margin-top: var(--space-4);">
                    ${hasData && lastResults.length === DEFAULT_LIMIT ? `<button id="order_items_load-more-btn" class="btn btn-outline btn-sm">Load More Order Items</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering order items table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> Failed to load order items table</div></div>`;
    }
};

const orderItemDetailsHtml = (item) => {
    if (!item) return '<div class="admin-entity__empty">No order item data</div>';

    const productName = escapeHtml(item.product_name || '-');
    const currentProductName = escapeHtml(item.current_product_name || '-');
    const price = formatCurrency(item.price_cents || 0);
    const subtotal = formatCurrency((item.price_cents || 0) * (item.quantity || 0));
    const orderTotal = formatCurrency(item.order_total || 0);

    return `
<div class="admin-modal admin-modal--lg">
    <div class="bg-white border-b px-6 py-4 rounded-t-xl d-flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Order Item Details</h2>
            <p class="text-sm text-gray-500">Order #${item.order_id}</p>
        </div>
        <span class="badge badge-${item.order_status || 'pending'}">${escapeHtml(item.order_status || 'N/A')}</span>
    </div>
    
    <div class="admin-modal__body bg-gray-50 p-6">
        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
            <!-- Left Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Item Information</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Item ID</span><span>${item.id}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Order ID</span><span>#${item.order_id}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Product (at order)</span><span>${productName}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Current Product</span><span>${currentProductName}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Quantity</span><span class="font-medium">${item.quantity || 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Unit Price</span><span>${price}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-medium">${subtotal}</span></div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Timeline</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Created</span><span>${formatDate(item.created_at)}</span></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Order Details</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Order Status</span><span class="badge badge-${item.order_status || 'pending'}">${escapeHtml(item.order_status || 'N/A')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Order Total</span><span class="font-medium">${orderTotal}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Order Date</span><span>${formatDate(item.order_created_at)}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Customer</span><span>${escapeHtml(item.user_name || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Email</span><span>${escapeHtml(item.user_email || '-')}</span></div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Product Info</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Product ID</span><span>${item.product_id}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Current Price</span><span>${formatCurrency(item.current_product_price || 0)}</span></div>
                    </div>
                    ${item.current_product_image ? `
                    <div class="mt-3">
                        <img src="${escapeHtml(item.current_product_image)}" alt="${currentProductName}" style="max-width: 150px; border-radius: 4px;">
                    </div>` : ''}
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
            <button class="btn btn-outline" onclick="closeModal()">Close</button>
            <button class="btn btn-primary js-admin-edit" data-entity="order_item" data-id="${item.id}">Edit Order Item</button>
        </div>
    </div>
</div>`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(Number(id));

        if (result.error) {
            throw new Error(result.error);
        }

        return orderItemDetailsHtml(result.order_item);
    } catch (error) {
        throw new Error(error.message || 'Failed to load order item details');
    }
};

// ---------- SEARCH LOGIC ---------- //
async function performSearch(query) {
    try {
        currentQuery = query || '';
        saveState('admin:order_items:query', currentQuery);
        currentOffset = 0;
        const results = await fetchOrderItems(DEFAULT_LIMIT, 0, currentQuery);

        lastResults = results.error ? [] : (Array.isArray(results) ? results : []);
        const hasData = lastResults.length > 0;
        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        const titleEl = document.querySelector('.admin-entity__title');
        if (titleEl) titleEl.textContent = `Order Items Management (${countLabel})`;

        const tbody = document.getElementById('order_items_table-body');
        if (tbody) {
            tbody.innerHTML = hasData
                ? lastResults.map(renderOrderItemRow).join('')
                : `<tr><td colspan="10" class="admin-entity__empty">${results.error ? escapeHtml(results.error) : 'No order items found'}</td></tr>`;
        }

        const loadMoreWrapper = document.getElementById('order_items_load-more-wrapper');
        if (loadMoreWrapper) {
            loadMoreWrapper.innerHTML = hasData && lastResults.length === DEFAULT_LIMIT
                ? `<button id="order_items_load-more-btn" class="btn btn-outline btn-sm">Load More Order Items</button>`
                : '';
        }
    } catch (error) {
        console.error('Search error:', error);
    }
}

// ---------- EVENT DELEGATION ---------- //
export const orderItemsListeners = async (container) => {
    if (!container) return null;

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);
    const abortController = new AbortController();
    const signal = abortController.signal;

    // View button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-view');
        if (!btn || btn.dataset.entity !== 'order_item') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[OrderItems] View button clicked:', { id });

        try {
            const html = await renderModal(id);
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = html;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';
        } catch (err) {
            openStandardModal({
                title: 'Error loading order item',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(err.message)}</div>`,
                size: 'xl'
            });
        }
    }, { signal });

    // Edit button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-edit');
        if (!btn || btn.dataset.entity !== 'order_item') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[OrderItems] Edit button clicked:', { id });

        try {
            const formHtml = await renderOrderItemEdit(parseInt(id));
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initOrderItemEditHandlers(modalBody, parseInt(id), (data, action) => {
                if (action === 'updated') {
                    // Refresh the table
                    performSearch(currentQuery);
                }
            });
        } catch (error) {
            console.error('[OrderItems] Error opening edit form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Load more handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'order_items_load-more-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = 'Loading...';

        try {
            const html = await loadMoreOrderItems();
            document.getElementById('order_items_table-body').insertAdjacentHTML('beforeend', html);

            if (html.includes('No more order items') || html.includes('Error')) {
                btn.remove();
            } else {
                btn.disabled = false;
                btn.textContent = 'Load More Order Items';
            }
        } catch {
            btn.disabled = false;
            btn.textContent = 'Load More Order Items';
        }
    }, { signal });

    // Refresh handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'order_items_refresh-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = 'Refreshing...';
        await performSearch(currentQuery);
        btn.disabled = false;
        btn.textContent = '🔄 Refresh';
    }, { signal });

    // Search handler
    container.addEventListener('input', (e) => {
        if (e.target.id === 'order_items-search-input') {
            debouncedSearch(e);
        }
    }, { signal });

    return {
        cleanup: () => abortController.abort()
    };
};

window.loadMoreOrderItems = loadMoreOrderItems;