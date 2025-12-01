import { fetchOrderItems, fetchModalDetails } from "./OrderItems.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreOrderItems() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const items = await fetchOrderItems(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (items.error) {
            return `<tr><td colspan="8" class="order_items_error-cell">Error: ${escapeHtml(items.error)}</td></tr>`;
        }

        if (items.length === 0) {
            return `<tr><td colspan="8" class="order_items_no-data-cell">No more order items to load</td></tr>`;
        }

        return items.map(item => renderOrderItemRow(item)).join('');
    } catch (error) {
        console.error('Error loading more order items:', error);
        return `<tr><td colspan="8" class="order_items_error-cell">Failed to load order items</td></tr>`;
    }
}

function renderOrderItemRow(item) {
    return `
        <tr class="order_items_row order-item-row" data-item-id="${item.id}">
            <td class="order_items_cell">${item.id}</td>
            <td class="order_items_cell">${item.order_id}</td>
            <td class="order_items_cell">${escapeHtml(item.order_number)}</td>
            <td class="order_items_cell">${escapeHtml(item.product_name)}</td>
            <td class="order_items_cell">${item.quantity}</td>
            <td class="order_items_cell">$${(item.price_cents / 100).toFixed(2)}</td>
            <td class="order_items_cell">${formatDate(item.created_at)}</td>
            <td class="order_items_cell">${escapeHtml(item.warehouse_name || '-')}</td>
            <td class="order_items_cell order_items_actions">
                <button class="order_items_btn-view " data-id="${item.id}" title="View Details">👁️ View</button>
                <a href="manage/order_item/update.php?id=${item.id}" class="order_items_btn-edit btn-edit" title="Edit Order Item">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const OrderItems = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const items = await fetchOrderItems(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (items.error) {
            return `
                <div class="order_items_table order-items-table">
                    <div class="order_items_error-box">
                        <strong>Error:</strong> ${escapeHtml(items.error)}
                    </div>
                </div>
            `;
        }

        if (items.length === 0) {
            return `
                <div class="order_items_table order-items-table">
                    <div class="order_items_no-data-box">
                        <p>📭 No order items found.</p>
                    </div>
                </div>
            `;
        }

        const tableRows = items.map(item => renderOrderItemRow(item)).join('');

        return `
            <div class="order_items_table order-items-table">
                <div class="order_items_header table-header">
                    <h2>Order Items Management (${items.length}${items.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="order_items_header-actions" style="display:flex; gap:8px; align-items:center;">

                        <input id="order_items-search-input" class="order_items_search-input" type="search" placeholder="Search order number or product name" aria-label="Search order items" />
                        <a href="manage/order_item/create.php" class="order_items_btn-primary btn-primary"> Create </a>
                        <button id="order_items_refresh-btn" class="order_items_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="order_items_wrapper table-wrapper">
                    <table class="order_items_data-table order-items-data-table">
                        <thead>
                            <tr class="order_items_header-row table-header-row">
                                <th>ID</th>
                                <th>Order ID</th>
                                <th>Order Number</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Created At</th>
                                <th>Warehouse Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="order_items-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="order_items_load-more-wrapper" class="order_items_load-more-wrapper" style="text-align:center;">
                    ${items.length === DEFAULT_LIMIT ? `
                        <button id="order_items_load-more-btn" class="order_items_btn-load-more btn-load-more">
                            Load More Order Items
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering order items table:', error);
        return `
            <div class="order_items_table order-items-table">
                <div class="order_items_error-box">
                    <strong>Error:</strong> Failed to load order items table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (item) => {
    const subtotal = parseFloat(item.subtotal_cents) || 0;

    const currentProduct = item.current_product_info || {};

    return `
<div class="order_items_card order-item-card">
    <div class="order_items_card-header order-item-card-header">
        <span>Order Item Details</span>
        <button class="order_items_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="order_items_section-title order-item-section-title">Basic Info</div>
    <div class="order_items_data-grid order-item-data-grid">
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">ID</strong>
            <span class="order_items_value data-value">${item.id || 'N/A'}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Order Number</strong>
            <span class="order_items_value data-value">${escapeHtml(item.order_number) || 'N/A'}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Order Status</strong>
            <span class="order_items_value data-value">${item.order_status.toUpperCase()}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Product Name</strong>
            <span class="order_items_value data-value">${item.product_name ? escapeHtml(item.product_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Image</strong>
            <span class="order_items_value data-value">
                ${item.product_image_url ? `<a href="${escapeHtml(item.product_image_url)}" target="_blank" class="link-primary">View Image</a>` : '<span class="data-empty">No Image</span>'}
            </span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Quantity</strong>
            <span class="order_items_value data-value">${item.quantity || 0}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Price</strong>
            <span class="order_items_value data-value">$${(item.price_cents / 100).toFixed(2)}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Subtotal</strong>
            <span class="order_items_value data-value">$${ (subtotal / 100).toFixed(2) }</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Warehouse Name</strong>
            <span class="order_items_value data-value">${item.warehouse_name ? escapeHtml(item.warehouse_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Warehouse Address</strong>
            <span class="order_items_value data-value">${item.warehouse_address ? escapeHtml(item.warehouse_address) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Created At</strong>
            <span class="order_items_value data-value">${item.created_at ? formatDate(item.created_at) : 'N/A'}</span>
        </div>
    </div>

    <div class="order_items_section-title order-item-section-title">Current Product Info</div>
    <div class="order_items_data-grid order-item-data-grid">
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Name</strong>
            <span class="order_items_value data-value">${currentProduct.name ? escapeHtml(currentProduct.name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Slug</strong>
            <span class="order_items_value data-value">${currentProduct.slug ? escapeHtml(currentProduct.slug) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Price</strong>
            <span class="order_items_value data-value">$${currentProduct.price_cents ? (currentProduct.price_cents / 100).toFixed(2) : '0.00'}</span>
        </div>
        <div class="order_items_field data-field">
            <strong class="order_items_label data-label">Active</strong>
            <span class="order_items_value data-value">${currentProduct.is_active ? 'Yes' : 'No'}</span>
        </div>
    </div>

    <div class="order_items_footer card-footer">
        <a href="manage/order_item/update.php?id=${item.id}" class="order_items_btn-primary btn-primary">
            Edit Order Item
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch order item details');
        }

        const item = result.order_item;

        if (!item || typeof item !== 'object' || !item.id) {
            throw new Error('Invalid order item data format');
        }

        return detailsHtml(item);

    } catch (error) {
        throw new Error(error.message || 'Failed to load order item details');
    }
};

export const orderItemsListeners = async () => {
    

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
            const results = await fetchOrderItems(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('order_items-table-body');
            const loadMoreWrapper = document.getElementById('order_items_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="8" class="order_items_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="order_items_no-data-cell">No order items found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderOrderItemRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="order_items_load-more-btn" class="order_items_btn-load-more btn-load-more">Load More Order Items</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.order_items_btn-view ') || e.target.closest('.order_items_btn-view ')) {
            e.preventDefault();
            const button = e.target.matches('.order_items_btn-view ') ? e.target : e.target.closest('.order_items_btn-view ');
            const itemId = button.dataset.id;

            if (!itemId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading order item details...</div>';
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
                        <h3 class="modal-error-title">Error Loading Order Item</h3>
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
        if (e.target.id === 'order_items_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreOrderItems();
                document.getElementById('order_items-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more order items to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more order items to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Order Items';
                }
            } catch (error) {
                console.error('Error loading more order items:', error);
                button.disabled = false;
                button.textContent = 'Load More Order Items';
            }
        }

        // refresh
        if (e.target.id === 'order_items_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await OrderItems();
                document.querySelector('.order-items-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing order items:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'order_items-search-input') {
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



window.loadMoreOrderItems = loadMoreOrderItems;
window.fetchOrderItems = fetchOrderItems;