import { fetchCartItems, fetchModalDetails } from "./CartItems.utils.js";
import { renderCartItemEdit, initCartItemEditHandlers } from "./CartItemEdit.js";
import { escapeHtml, formatDate, openStandardModal, debounce, saveState, getState, closeModal } from "../../utils.js";

const DEFAULT_LIMIT = 20;
let currentOffset = 0;
let currentQuery = '';
let lastResults = [];

function renderCartItemRow(item) {
    if (!item || !item.id) return '';

    return `
        <tr class="cart-item-row" data-id="${item.id}">
            <td>${item.id}</td>
            <td>${item.cart_id}</td>
            <td title="${escapeHtml(item.session_id || '')}">${escapeHtml(item.session_id || '-').substring(0, 8)}...</td>
            <td>
                <div style="display:flex; align-items:center; gap:8px;">
                    ${item.product_image ? `<img src="${item.product_image}" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">` : ''}
                    <div>${escapeHtml(item.product_name || 'Item #' + item.product_id)}</div>
                </div>
            </td>
            <td>${item.quantity}</td>
            <td>$${(item.price_at_add_cents / 100).toFixed(2)}</td>
            <td>$${((item.price_at_add_cents * item.quantity) / 100).toFixed(2)}</td>
            <td>${formatDate(item.created_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm cart-item-view" data-id="${item.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm cart-item-edit" data-id="${item.id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

async function loadMoreCartItems() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const items = await fetchCartItems(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (items.error) {
            return `<tr><td colspan="9" class="admin-entity__empty">Error: ${escapeHtml(items.error)}</td></tr>`;
        }

        if (!items.length) {
            return `<tr><td colspan="9" class="admin-entity__empty">No more items to load</td></tr>`;
        }

        return items.map(renderCartItemRow).join('');
    } catch (error) {
        console.error('Error loading more cart items:', error);
        return `<tr><td colspan="9" class="admin-entity__empty">Failed to load items</td></tr>`;
    }
}

export const CartItems = async () => {
    try {
        currentOffset = 0;
        const items = await fetchCartItems(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (items.error) {
            lastResults = [];
        } else {
            lastResults = Array.isArray(items) ? items : [];
        }

        const hasData = lastResults && lastResults.length > 0;
        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        const tableRows = hasData
            ? lastResults.map(renderCartItemRow).join('')
            : `<tr><td colspan="9" class="admin-entity__empty">${items.error ? escapeHtml(items.error) : 'No cart items found'}</td></tr>`;

        return `
            <div class="admin-entity">
                 <div class="admin-entity__header">
                    <h2 class="admin-entity__title">Cart Items Management (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="cart_items-search-input" class="admin-entity__search" type="search" placeholder="Search products/users..." value="${escapeHtml(currentQuery)}" />
                        <button id="cart_items_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cart</th>
                                <th>Session</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cart_items-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
                <div id="cart_items_load-more-wrapper" style="text-align:center; margin-top: var(--space-4);">
                    ${hasData && lastResults.length === DEFAULT_LIMIT ? `<button id="cart_items_load-more-btn" class="btn btn-outline btn-sm">Load More Only</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering cart items table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> ${escapeHtml(error.toString())}</div></div>`;
    }
};

const cartItemDetailsHtml = (item) => {
    if (!item) return '<div class="admin-entity__empty">No data</div>';

    const subtotal = parseFloat(item.subtotal_cents) || 0;
    const priceDiff = parseFloat(item.price_difference_cents) || 0;

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Item #${item.id} Details</span>
                <span>Cart: ${item.cart_id}</span>
            </div>
            
            <div class="products_section-title">Product</div>
            <div class="products_data-grid">
                <div class="products_field">
                    <strong>Image</strong>
                    <span>${item.product_image ? `<img src="${item.product_image}" style="width:64px;height:64px;object-fit:cover;border-radius:4px;">` : '-'}</span>
                </div>
                <div class="products_field"><strong>Name</strong><span>${escapeHtml(item.product_name)}</span></div>
                <div class="products_field"><strong>Product ID</strong><span>${item.product_id}</span></div>
            </div>

            <div class="products_section-title">Pricing & Quantity</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Quantity</strong><span>${item.quantity}</span></div>
                <div class="products_field"><strong>Price (At Add)</strong><span>$${(item.price_at_add_cents / 100).toFixed(2)}</span></div>
                <div class="products_field"><strong>Current Price</strong><span>$${(item.current_price_cents / 100).toFixed(2)}</span></div>
                <div class="products_field"><strong>Difference</strong><span class="${priceDiff > 0 ? 'text-success' : (priceDiff < 0 ? 'text-danger' : '')}">$${(priceDiff / 100).toFixed(2)}</span></div>
                <div class="products_field"><strong>Subtotal</strong><span>$${(subtotal / 100).toFixed(2)}</span></div>
            </div>

            <div class="products_section-title">Cart Context</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Cart ID</strong><span>${item.cart_id}</span></div>
                <div class="products_field"><strong>Session</strong><span>${escapeHtml(item.session_id)}</span></div>
                 <div class="products_field"><strong>User</strong><span>${escapeHtml(item.user_name || 'Guest')}</span></div>
                <div class="products_field"><strong>Cart Status</strong><span>${(item.cart_status || '').toUpperCase()}</span></div>
            </div>

            <div class="products_footer">
                <button class="btn btn-primary js-edit-cart-item" data-id="${item.id}">Edit Item</button>
            </div>
        </div>
    `;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(Number(id));
        console.log('[CartItems] renderModal result:', result);
        if (result.error) throw new Error(result.error);
        return cartItemDetailsHtml(result.cart_item);
    } catch (error) {
        throw new Error(error.message || 'Failed to load details');
    }
};

export const cartItemsListeners = async () => {
    // Debounced search handler
    const debouncedSearch = debounce(async (query) => {
        currentQuery = query;
        currentOffset = 0;

        const tableBody = document.getElementById('cart_items-table-body');
        if (!tableBody) return;

        tableBody.innerHTML = '<tr><td colspan="9" class="admin-entity__empty">Searching...</td></tr>';

        try {
            const items = await fetchCartItems(DEFAULT_LIMIT, 0, query);

            if (items.error) {
                tableBody.innerHTML = `<tr><td colspan="9" class="admin-entity__empty">Error: ${escapeHtml(items.error)}</td></tr>`;
                return;
            }

            lastResults = Array.isArray(items) ? items : [];

            if (lastResults.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="9" class="admin-entity__empty">No cart items found</td></tr>';
            } else {
                tableBody.innerHTML = lastResults.map(renderCartItemRow).join('');
            }
        } catch (error) {
            console.error('Search error:', error);
            tableBody.innerHTML = `<tr><td colspan="9" class="admin-entity__empty">Search failed: ${escapeHtml(error.message)}</td></tr>`;
        }
    }, 300);

    // Search input listener
    document.addEventListener('input', (e) => {
        if (e.target.id === 'cart_items-search-input') {
            debouncedSearch(e.target.value.trim());
        }
    });

    // Refresh button
    document.addEventListener('click', async (e) => {
        if (e.target.id === 'cart_items_refresh-btn') {
            currentOffset = 0;
            currentQuery = '';
            const searchInput = document.getElementById('cart_items-search-input');
            if (searchInput) searchInput.value = '';

            const tableBody = document.getElementById('cart_items-table-body');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="9" class="admin-entity__empty">Loading...</td></tr>';
            }

            try {
                const items = await fetchCartItems(DEFAULT_LIMIT, 0, '');
                lastResults = Array.isArray(items) ? items : [];

                if (tableBody) {
                    if (lastResults.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="9" class="admin-entity__empty">No cart items found</td></tr>';
                    } else {
                        tableBody.innerHTML = lastResults.map(renderCartItemRow).join('');
                    }
                }
            } catch (error) {
                console.error('Refresh error:', error);
            }
        }
    });

    // Delegated event listeners
    document.addEventListener('click', async (e) => {
        // View - cart-item-view class only
        if (e.target.matches('.cart-item-view') || e.target.closest('.cart-item-view')) {
            const btn = e.target.closest('.cart-item-view') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const html = await renderModal(id);
                openStandardModal({
                    title: 'Cart Item Details',
                    bodyHtml: html,
                    size: 'xl'
                });
            } catch (err) {
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(err.message)}</div>`
                });
            }
        }

        // Edit - cart-item-edit class only
        if (e.target.matches('.cart-item-edit') || e.target.closest('.cart-item-edit')) {
            const btn = e.target.closest('.cart-item-edit') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const formHtml = await renderCartItemEdit(parseInt(id));
                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initCartItemEditHandlers(modalBody, parseInt(id), (data, action) => {
                    if (action === 'updated' || action === 'deleted') {
                        const refreshBtn = document.getElementById('cart_items_refresh-btn');
                        if (refreshBtn) refreshBtn.click();
                    }
                });
            } catch (error) {
                console.error('Error opening edit cart item:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Edit internal
        if (e.target.matches('.js-edit-cart-item')) {
            const btn = e.target;
            const id = btn.dataset.id;
            if (id) {
                try {
                    const formHtml = await renderCartItemEdit(parseInt(id));
                    const modal = document.getElementById('modal');
                    const modalBody = document.getElementById('modal-body');

                    const standardModal = document.getElementById('standard-modal');
                    if (standardModal) standardModal.classList.remove('active');

                    modalBody.innerHTML = formHtml;
                    modal.classList.remove('hidden');
                    modal.classList.add('active');
                    modal.style.display = 'flex';

                    initCartItemEditHandlers(modalBody, parseInt(id), (data, action) => {
                        if (action === 'updated' || action === 'deleted') {
                            const refreshBtn = document.getElementById('cart_items_refresh-btn');
                            if (refreshBtn) refreshBtn.click();
                        }
                    });
                } catch (error) {
                    console.error('Error opening edit cart item:', error);
                }
            }
        }

        // Load More
        if (e.target.id === 'cart_items_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';
            try {
                const html = await loadMoreCartItems();
                document.getElementById('cart_items-table-body').insertAdjacentHTML('beforeend', html);
                if (html.includes('No more') || html.includes('Error')) button.remove();
                else {
                    button.disabled = false;
                    button.textContent = 'Load More';
                }
            } catch {
                button.disabled = false;
                button.textContent = 'Load More';
            }
        }

        // Refresh
        if (e.target.id === 'cart_items_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            try {
                const html = await CartItems();
                // Replace the entire container
                const container = document.querySelector('.admin-entity');
                if (container && container.parentNode) {
                    // Simple replacement might not work if not re-rendered by router, but for simple refresh we can try to re-render table body or reload page logic
                    // Better approach for SPA: Trigger router reload or re-call render function
                    // Here we will just reload content of wrapper
                    const { CartItems } = await import("./CartItems.js");
                    const newContent = await CartItems();
                    const mainContent = document.getElementById('content');
                    if (mainContent) mainContent.innerHTML = newContent;
                }
            } catch (e) {
                console.error(e);
            } finally {
                // button.disabled = false; // logic replaced above
            }
        }
    });
};
