import { fetchCartItems, fetchModalDetails } from "./CartItems.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreCartItems() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const items = await fetchCartItems(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (items.error) {
            return `<tr><td colspan="7" class="cart_items_error-cell">Error: ${escapeHtml(items.error)}</td></tr>`;
        }

        if (items.length === 0) {
            return `<tr><td colspan="7" class="cart_items_no-data-cell">No more cart items to load</td></tr>`;
        }

        return items.map(item => renderCartItemRow(item)).join('');
    } catch (error) {
        console.error('Error loading more cart items:', error);
        return `<tr><td colspan="7" class="cart_items_error-cell">Failed to load cart items</td></tr>`;
    }
}

function renderCartItemRow(item) {
    return `
        <tr class="cart_items_row cart-item-row" data-item-id="${item.id}">
            <td class="cart_items_cell">${item.id}</td>
            <td class="cart_items_cell">${item.cart_id}</td>
            <td class="cart_items_cell">${escapeHtml(item.session_id)}</td>
            <td class="cart_items_cell">${item.product_id}</td>
            <td class="cart_items_cell">${escapeHtml(item.product_name)}</td>
            <td class="cart_items_cell">${item.quantity}</td>
            <td class="cart_items_cell">$${(item.price_at_add_cents / 100).toFixed(2)}</td>
            <td class="cart_items_cell">${formatDate(item.created_at)}</td>
            <td class="cart_items_cell cart_items_actions">
                <button class="cart_items_btn-view" data-id="${item.id}" title="View Details">👁️ View</button>
                <a href="manage/cart_item/update.php?id=${item.id}" class="cart_items_btn-edit btn-edit" title="Edit Cart Item">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const CartItems = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const items = await fetchCartItems(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (items.error) {
            return `
                <div class="cart_items_table cart-items-table">
                    <div class="cart_items_error-box">
                        <strong>Error:</strong> ${escapeHtml(items.error)}
                    </div>
                </div>
            `;
        }

        if (items.length === 0) {
            return `
                <div class="cart_items_table cart-items-table">
                    <div class="cart_items_no-data-box">
                        <p>📭 No cart items found.</p>
                    </div>
                </div>
            `;
        }
        console.log(items);
        const tableRows = items.map(item => renderCartItemRow(item)).join('');

        return `
            <div class="cart_items_table cart-items-table">
                <div class="cart_items_header table-header">
                    <h2>Cart Items Management (${items.length}${items.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="cart_items_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="cart_items-search-input" class="cart_items_search-input" type="search" placeholder="Search session id or product name" aria-label="Search cart items" />
                        <button id="cart_items_refresh-btn" class="cart_items_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="cart_items_wrapper table-wrapper">
                    <table class="cart_items_data-table cart-items-data-table">
                        <thead>
                            <tr class="cart_items_header-row table-header-row">
                                <th>ID</th>
                                <th>Cart ID</th>
                                <th>Session ID</th>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price At Add</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cart_items-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="cart_items_load-more-wrapper" class="cart_items_load-more-wrapper" style="text-align:center;">
                    ${items.length === DEFAULT_LIMIT ? `
                        <button id="cart_items_load-more-btn" class="cart_items_btn-load-more btn-load-more">
                            Load More Cart Items
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering cart items table:', error);
        return `
            <div class="cart_items_table cart-items-table">
                <div class="cart_items_error-box">
                    <strong>Error:</strong> Failed to load cart items table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (item) => {
    const subtotal = parseFloat(item.subtotal_cents) || 0;
    const priceDifference = parseFloat(item.price_difference_cents) || 0;

    return `
<div class="cart_items_card cart-item-card">
    <div class="cart_items_card-header cart-item-card-header">
        <span>Cart Item Details</span>
        <button class="cart_items_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="cart_items_section-title cart-item-section-title">Basic Info</div>
    <div class="cart_items_data-grid cart-item-data-grid">
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">ID</strong>
            <span class="cart_items_value data-value">${item.id || 'N/A'}</span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Cart ID</strong>
            <span class="cart_items_value data-value">${item.cart_id || 'N/A'}</span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Session ID</strong>
            <span class="cart_items_value data-value">${escapeHtml(item.session_id) || 'N/A'}</span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Cart Status</strong>
            <span class="cart_items_value data-value">${item.cart_status.toUpperCase()}</span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Product Name</strong>
            <span class="cart_items_value data-value">${item.product_name ? escapeHtml(item.product_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Quantity</strong>
            <span class="cart_items_value data-value">${item.quantity || 0}</span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Price At Add</strong>
            <span class="cart_items_value data-value">$${(item.price_at_add_cents / 100).toFixed(2)}</span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Current Price</strong>
            <span class="cart_items_value data-value">$${(item.current_price_cents / 100).toFixed(2)}</span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Price Difference</strong>
            <span class="cart_items_value data-value $${(priceDifference / 100).toFixed(2)}" class="${priceDifference > 0 ? 'text-success' : (priceDifference < 0 ? 'text-danger' : '')}"></span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Subtotal</strong>
            <span class="cart_items_value data-value">$${ (subtotal / 100).toFixed(2) }</span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Created At</strong>
            <span class="cart_items_value data-value">${item.created_at ? formatDate(item.created_at) : 'N/A'}</span>
        </div>
        <div class="cart_items_field data-field">
            <strong class="cart_items_label data-label">Updated At</strong>
            <span class="cart_items_value data-value">${item.updated_at ? formatDate(item.updated_at) : 'N/A'}</span>
        </div>
    </div>

    <div class="cart_items_footer card-footer">
        <a href="manage/cart_item/update.php?id=${item.id}" class="cart_items_btn-primary btn-primary">
            Edit Cart Item
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch cart item details');
        }

        const item = result.cart_item;

        if (!item || typeof item !== 'object' || !item.id) {
            throw new Error('Invalid cart item data format');
        }

        return detailsHtml(item);

    } catch (error) {
        throw new Error(error.message || 'Failed to load cart item details');
    }
};


export const cartItemsListeners = async () => {
    
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
            const results = await fetchCartItems(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('cart_items-table-body');
            const loadMoreWrapper = document.getElementById('cart_items_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="7" class="cart_items_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="7" class="cart_items_no-data-cell">No cart items found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderCartItemRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="cart_items_load-more-btn" class="cart_items_btn-load-more btn-load-more">Load More Cart Items</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.cart_items_btn-view') || e.target.closest('.cart_items_btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.cart_items_btn-vieww') ? e.target : e.target.closest('.cart_items_btn-view');
            const itemId = button.dataset.id;

            if (!itemId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading cart item details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(itemId));
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
                        <h3 class="modal-error-title">Error Loading Cart Item</h3>
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
        if (e.target.id === 'cart_items_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreCartItems();
                document.getElementById('cart_items-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more cart items to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more cart items to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Cart Items';
                }
            } catch (error) {
                console.error('Error loading more cart items:', error);
                button.disabled = false;
                button.textContent = 'Load More Cart Items';
            }
        }

        // refresh
        if (e.target.id === 'cart_items_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await CartItems();
                document.querySelector('.cart-items-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing cart items:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'cart_items-search-input') {
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

}

window.loadMoreCartItems = loadMoreCartItems;
window.fetchCartItems = fetchCartItems;