import { fetchCarts, fetchModalDetails } from "./Carts.utils.js";
import { renderCartEdit, initCartEditHandlers } from "./CartEdit.js";
import { renderCartCreate, initCartCreateHandlers } from "./CartCreate.js";
import { escapeHtml, formatDate, openStandardModal, debounce, saveState, getState, closeModal } from "../../utils.js";

const DEFAULT_LIMIT = 20; // Increased limit for better view
let currentOffset = 0;
let currentQuery = getState('admin:carts:query', '');
let lastResults = [];

function renderCartRow(cart) {
    if (!cart || !cart.id) return '';

    // Check fields (enriched data from getAllPaginated)
    const userName = escapeHtml(cart.user_name || '-');
    const userEmail = escapeHtml(cart.user_email || '-');

    return `
        <tr class="cart-row" data-id="${cart.id}">
            <td>${cart.id}</td>
            <td>${escapeHtml(cart.session_id || '').substring(0, 8)}...</td>
            <td>
                <span class="carts_badge ${getStatusClass(cart.status)}">
                    ${(cart.status || '').toUpperCase()}
                </span>
            </td>
            <td>$${(cart.total_cents / 100).toFixed(2)}</td>
            <td>${cart.item_count || 0}</td>
            <td>
                <div style="font-size:0.9em; font-weight:bold;">${userName}</div>
                <div style="font-size:0.8em; color:#666;">${userEmail}</div>
            </td>
            <td>${formatDate(cart.created_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm cart-view" data-id="${cart.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm cart-edit" data-id="${cart.id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

function getStatusClass(status) {
    if (!status) return 'carts_status_pending';
    switch (status.toLowerCase()) {
        case 'active': return 'carts_status_paid'; // Reusing classes
        case 'converted': return 'carts_status_processing';
        case 'abandoned': return 'carts_status_cancelled';
        case 'expired': return 'carts_status_cancelled';
        default: return 'carts_status_pending';
    }
}

async function loadMoreCarts() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const carts = await fetchCarts(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (carts.error) {
            return `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(carts.error)}</td></tr>`;
        }

        if (!carts.length) {
            return `<tr><td colspan="8" class="admin-entity__empty">No more carts to load</td></tr>`;
        }

        return carts.map(renderCartRow).join('');
    } catch (error) {
        console.error('Error loading more carts:', error);
        return `<tr><td colspan="8" class="admin-entity__empty">Failed to load carts</td></tr>`;
    }
}

export const Carts = async () => {
    try {
        currentOffset = 0;
        const carts = await fetchCarts(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (carts.error) {
            lastResults = [];
        } else {
            lastResults = Array.isArray(carts) ? carts : [];
        }

        const hasData = lastResults && lastResults.length > 0;
        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        const tableRows = hasData
            ? lastResults.map(renderCartRow).join('')
            : `<tr><td colspan="8" class="admin-entity__empty">${carts.error ? escapeHtml(carts.error) : 'No carts found'}</td></tr>`;

        return `
            <div class="admin-entity">
                <style>
                    /* Scoped Cart Styles */
                    .carts_badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8em; text-transform: uppercase; }
                    .carts_status_paid { background: #e6f4ea; color: #1e8e3e; } /* Active/Paid */
                    .carts_status_processing { background: #fef7e0; color: #f9ab00; } /* Converted */
                    .carts_status_cancelled { background: #fce8e6; color: #d93025; } /* Abandoned */
                    .carts_status_pending { background: #f1f3f4; color: #5f6368; }
                </style>    
                <div class="admin-entity__header">
                    <h2 class="admin-entity__title">Carts Management (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="carts-search-input" class="admin-entity__search" type="search" placeholder="Search users/sessions..." value="${escapeHtml(currentQuery)}" />
                        <button id="carts_create-btn" class="btn btn-primary btn-sm">➕ Create Cart</button>
                        <button id="carts_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Session</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Items</th>
                                <th>User</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="carts-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
                <div id="carts_load-more-wrapper" style="text-align:center; margin-top: var(--space-4);">
                    ${hasData && lastResults.length === DEFAULT_LIMIT ? `<button id="carts_load-more-btn" class="btn btn-outline btn-sm">Load More Carts</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering carts table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> ${escapeHtml(error.toString())}</div></div>`;
    }
};

const cartDetailsHtml = (cart) => {
    if (!cart) return '<div class="admin-entity__empty">No cart data</div>';

    // Items table
    const itemsHtml = cart.items && cart.items.length > 0
        ? `
            <table class="admin-entity__table" style="margin-top:1rem;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price (At Add)</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${cart.items.map(item => `
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    ${item.product_image ? `<img src="${item.product_image}" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">` : ''}
                                    <div>${escapeHtml(item.product_name || 'Unknown Item')}</div>
                                </div>
                            </td>
                            <td>$${(item.price_at_add_cents / 100).toFixed(2)}</td>
                            <td>${item.quantity}</td>
                            <td>$${((item.price_at_add_cents * item.quantity) / 100).toFixed(2)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
          `
        : '<div class="admin-entity__empty">No items in this cart.</div>';

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Cart #${cart.id} Details</span>
                <span class="carts_badge ${getStatusClass(cart.status)}">${(cart.status || '').toUpperCase()}</span>
            </div>
            
            <div class="products_section-title">Overview</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Status</strong><span>${escapeHtml(cart.status)}</span></div>
                <div class="products_field"><strong>Session</strong><span>${escapeHtml(cart.session_id)}</span></div>
                <div class="products_field"><strong>Total</strong><span>$${(cart.total_cents / 100).toFixed(2)}</span></div>
                <div class="products_field"><strong>Item Count</strong><span>${cart.item_count}</span></div>
            </div>

            <div class="products_section-title">User Information</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Name</strong><span>${escapeHtml(cart.user_name || '-')}</span></div>
                <div class="products_field"><strong>Email</strong><span>${escapeHtml(cart.user_email || '-')}</span></div>
                <div class="products_field"><strong>User ID</strong><span>${cart.user_id || '-'}</span></div>
            </div>
            
            <div class="products_section-title">Timeline</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Created</strong><span>${formatDate(cart.created_at)}</span></div>
                <div class="products_field"><strong>Updated</strong><span>${formatDate(cart.updated_at)}</span></div>
                <div class="products_field"><strong>Converted</strong><span>${cart.converted_at ? formatDate(cart.converted_at) : '-'}</span></div>
                <div class="products_field"><strong>Abandoned</strong><span>${cart.abandoned_at ? formatDate(cart.abandoned_at) : '-'}</span></div>
            </div>

            <div class="products_section-title">Cart Items</div>
            ${itemsHtml}

            <div class="products_footer">
                <button class="btn btn-primary js-edit-cart" data-id="${cart.id}">Edit Cart</button>
            </div>
        </div>
    `;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(Number(id));
        if (result.error) throw new Error(result.error);
        return cartDetailsHtml(result.cart);
    } catch (error) {
        throw new Error(error.message || 'Failed to load cart details');
    }
};

// Attach delegated handlers
(() => {
    // Debounced search handler
    const debouncedSearch = debounce(async (query) => {
        currentQuery = query;
        currentOffset = 0;
        saveState('admin:carts:query', query);

        const tableBody = document.getElementById('carts-table-body');
        if (!tableBody) return;

        tableBody.innerHTML = '<tr><td colspan="8" class="admin-entity__empty">Searching...</td></tr>';

        try {
            const carts = await fetchCarts(DEFAULT_LIMIT, 0, query);

            if (carts.error) {
                tableBody.innerHTML = `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(carts.error)}</td></tr>`;
                return;
            }

            lastResults = Array.isArray(carts) ? carts : [];

            if (lastResults.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="8" class="admin-entity__empty">No carts found</td></tr>';
            } else {
                tableBody.innerHTML = lastResults.map(renderCartRow).join('');
            }

            // Update count in title
            const title = document.querySelector('.admin-entity__title');
            if (title) {
                const countLabel = lastResults.length + (lastResults.length === DEFAULT_LIMIT ? '+' : '');
                title.textContent = `Carts Management (${countLabel})`;
            }
        } catch (error) {
            console.error('Search error:', error);
            tableBody.innerHTML = `<tr><td colspan="8" class="admin-entity__empty">Search failed: ${escapeHtml(error.message)}</td></tr>`;
        }
    }, 300);

    // Search input listener
    document.addEventListener('input', (e) => {
        if (e.target.id === 'carts-search-input') {
            debouncedSearch(e.target.value.trim());
        }
    });

    // Refresh button
    document.addEventListener('click', async (e) => {
        if (e.target.id === 'carts_refresh-btn') {
            currentOffset = 0;
            currentQuery = '';
            const searchInput = document.getElementById('carts-search-input');
            if (searchInput) searchInput.value = '';

            const tableBody = document.getElementById('carts-table-body');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="8" class="admin-entity__empty">Loading...</td></tr>';
            }

            try {
                const carts = await fetchCarts(DEFAULT_LIMIT, 0, '');
                lastResults = Array.isArray(carts) ? carts : [];

                if (tableBody) {
                    if (lastResults.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="8" class="admin-entity__empty">No carts found</td></tr>';
                    } else {
                        tableBody.innerHTML = lastResults.map(renderCartRow).join('');
                    }
                }
            } catch (error) {
                console.error('Refresh error:', error);
            }
        }
    });

    // Click handlers for view/edit/create/load-more
    document.addEventListener('click', async (e) => {
        // View - cart-view class only
        if (e.target.matches('.cart-view') || e.target.closest('.cart-view')) {
            const btn = e.target.closest('.cart-view') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const html = await renderModal(id);
                openStandardModal({
                    title: 'Cart Details',
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

        // Edit - cart-edit class only
        if (e.target.matches('.cart-edit') || e.target.closest('.cart-edit')) {
            const btn = e.target.closest('.cart-edit') || e.target;
            const id = btn.dataset.id;
            if (!id) return;

            try {
                const formHtml = await renderCartEdit(parseInt(id));
                const modal = document.getElementById('modal'); // Use standard modal ID
                const modalBody = document.getElementById('modal-body');

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initCartEditHandlers(modalBody, parseInt(id), (data, action) => {
                    if (action === 'updated') {
                        // Refresh the list
                        loadMoreCarts(); // Or re-fetch current view
                        // Ideally we should re-fetch the list or update the row
                        // For now proper refresh:
                        const refreshBtn = document.getElementById('carts_refresh-btn');
                        if (refreshBtn) refreshBtn.click();
                    }
                });
            } catch (error) {
                console.error('Error opening edit cart:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Edit internal from View Modal
        if (e.target.matches('.js-edit-cart')) {
            const btn = e.target;
            const id = btn.dataset.id;
            if (id) {
                // Close view modal first if needed, or re-use it. 
                // Since our modals are singular #modal, just overwriting content works if we don't close it explicitly first.
                // But view modal might be standard-modal vs modal?
                // In Carts.js view uses openStandardModal which uses #standard-modal usually?
                // Let's check openStandardModal imp. Assuming it uses same or compatible overlay.
                // Actually openStandardModal usually uses a specific modal ID. 
                // Let's close any open modal first.
                // closeModal(); // Might close everything.

                // Let's just render into the main modal
                try {
                    const formHtml = await renderCartEdit(parseInt(id));
                    const modal = document.getElementById('modal');
                    const modalBody = document.getElementById('modal-body');

                    // If we are in "standard-modal", we might need to close it and open "modal"
                    // checking utils.js would confirm but let's assume we can transition.
                    // Ideally we close previous modal
                    const standardModal = document.getElementById('standard-modal');
                    if (standardModal) standardModal.classList.remove('active');

                    modalBody.innerHTML = formHtml;
                    modal.classList.remove('hidden');
                    modal.classList.add('active');
                    modal.style.display = 'flex';

                    initCartEditHandlers(modalBody, parseInt(id), (data, action) => {
                        if (action === 'updated') {
                            const refreshBtn = document.getElementById('carts_refresh-btn');
                            if (refreshBtn) refreshBtn.click();
                        }
                    });
                } catch (error) {
                    console.error('Error opening edit cart:', error);
                    alert('Failed to load edit form');
                }
            }
        }

        // Create Cart
        if (e.target.id === 'carts_create-btn') {
            try {
                const formHtml = renderCartCreate();
                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initCartCreateHandlers(modalBody, (data, action) => {
                    if (action === 'created') {
                        // Refresh list
                        const refreshBtn = document.getElementById('carts_refresh-btn');
                        if (refreshBtn) refreshBtn.click();
                    }
                });
            } catch (error) {
                console.error('Error opening create cart:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Load More
        if (e.target.id === 'carts_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';
            try {
                const html = await loadMoreCarts();
                document.getElementById('carts-table-body').insertAdjacentHTML('beforeend', html);
                if (html.includes('No more') || html.includes('Error')) button.remove();
                else {
                    button.disabled = false;
                    button.textContent = 'Load More Carts';
                }
            } catch {
                button.disabled = false;
                button.textContent = 'Load More Carts';
            }
        }
    });

})();

export const cartsListeners = async () => {
    console.log('cartsListeners initialized');
};

window.loadMoreCarts = loadMoreCarts;