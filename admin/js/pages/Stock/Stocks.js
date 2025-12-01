import { fetchStock, fetchModalDetails } from "./Stocks.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreStock() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const stock = await fetchStock(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (stock.error) {
            return `<tr><td colspan="8" class="stock_error-cell">Error: ${escapeHtml(stock.error)}</td></tr>`;
        }

        if (stock.length === 0) {
            return `<tr><td colspan="8" class="stock_no-data-cell">No more stock to load</td></tr>`;
        }

        return stock.map(item => renderStockRow(item)).join('');
    } catch (error) {
        console.error('Error loading more stock:', error);
        return `<tr><td colspan="8" class="stock_error-cell">Failed to load stock</td></tr>`;
    }
}

function renderStockRow(item) {
    return `
        <tr class="stock_row stock-row" data-stock-id="${item.id}">
            <td class="stock_cell">${item.id}</td>
            <td class="stock_cell">${escapeHtml(item.product_name)}</td>
            <td class="stock_cell">${escapeHtml(item.warehouse_name)}</td>
            <td class="stock_cell">${item.quantity}</td>
            <td class="stock_cell">${item.reserved}</td>
            <td class="stock_cell">${item.available}</td>
            <td class="stock_cell">${formatDate(item.updated_at)}</td>
            <td class="stock_cell stock_actions">
                <button class="stock_btn-view " data-id="${item.id}" title="View Details">👁️ View</button>
                <a href="manage/stock/edit.php?id=${item.id}" class="stock_btn-edit btn-edit" title="Edit Stock">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const Stock = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const {stocks} = await fetchStock(DEFAULT_LIMIT, currentOffset, currentQuery);
        

        if (stocks.error) {
            return `
                <div class="stock_table stock-table">
                    <div class="stock_error-box">
                        <strong>Error:</strong> ${escapeHtml(stock.error)}
                    </div>
                </div>
            `;
        }

        if (stocks.length === 0) {
            return `
                <div class="stock_table stock-table">
                    <div class="stock_no-data-box">
                        <p>📭 No stock found.</p>
                    </div>
                </div>
            `;
        }

        const tableRows = stocks.map(item => renderStockRow(item)).join('');

        return `
            <div class="stock_table stock-table">
                <div class="stock_header table-header">
                    <h2>Stock Management (${stocks.length}${stocks.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="stock_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="stock-search-input" class="stock_search-input" type="search" placeholder="Search product or warehouse name" aria-label="Search stock" />
                        <button id="stock_refresh-btn" class="stock_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="stock_wrapper table-wrapper">
                    <table class="stock_data-table stock-data-table">
                        <thead>
                            <tr class="stock_header-row table-header-row">
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Warehouse Name</th>
                                <th>Quantity</th>
                                <th>Reserved</th>
                                <th>Available</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="stock-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="stock_load-more-wrapper" class="stock_load-more-wrapper" style="text-align:center;">
                    ${stocks.length === DEFAULT_LIMIT ? `
                        <button id="stock_load-more-btn" class="stock_btn-load-more btn-load-more">
                            Load More Stock
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering stock table:', error);
        return `
            <div class="stock_table stock-table">
                <div class="stock_error-box">
                    <strong>Error:</strong> Failed to load stock table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (item) => {
    const inventoryValue = parseFloat(item.inventory_value) || 0;
    console.log(item);

    return `
<div class="stock_card stock-card">
    <div class="stock_card-header stock-card-header">
        <span>Stock Details</span>
        <button class="stock_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="stock_section-title stock-section-title">Basic Info</div>
    <div class="stock_data-grid stock-data-grid">
        <div class="stock_field data-field">
            <strong class="stock_label data-label">ID</strong>
            <span class="stock_value data-value">${item.id || 'N/A'}</span>
        </div>
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Product Name</strong>
            <span class="stock_value data-value">${item.product_name ? escapeHtml(item.product_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Product Slug</strong>
            <span class="stock_value data-value">${item.product_slug ? escapeHtml(item.product_slug) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Price</strong>
            <span class="stock_value data-value">$${(item.price_cents / 100).toFixed(2)}</span>
        </div>
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Warehouse Name</strong>
            <span class="stock_value data-value">${item.warehouse_name ? escapeHtml(item.warehouse_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Warehouse Address</strong>
            <span class="stock_value data-value">${item.warehouse_address ? escapeHtml(item.warehouse_address) : '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="stock_section-title stock-section-title">Stock Info</div>
    <div class="stock_data-grid stock-data-grid">
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Quantity</strong>
            <span class="stock_value data-value">${item.quantity || 0}</span>
        </div>
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Reserved</strong>
            <span class="stock_value data-value">${item.reserved || 0}</span>
        </div>
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Available</strong>
            <span class="stock_value data-value">${item.available || 0}</span>
        </div>
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Inventory Value</strong>
            <span class="stock_value data-value">$${inventoryValue.toFixed(2)}</span>
        </div>
    </div>

    <div class="stock_section-title stock-section-title">Timeline</div>
    <div class="stock_data-grid stock-data-grid">
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Created At</strong>
            <span class="stock_value data-value">${item.created_at ? formatDate(item.created_at) : 'N/A'}</span>
        </div>
        <div class="stock_field data-field">
            <strong class="stock_label data-label">Updated At</strong>
            <span class="stock_value data-value">${item.updated_at ? formatDate(item.updated_at) : 'N/A'}</span>
        </div>
    </div>

    <div class="stock_section-title stock-section-title">Recent Movements</div>
    <div class="stock_items recent-movements-container">
        ${item.recent_movements && Array.isArray(item.recent_movements) && item.recent_movements.length > 0 ?
            item.recent_movements.map(movement => `
                <div class="stock_item-row movement-row">
                    <div class="stock_item-name">${movement.order_number || 'N/A'}</div>
                    <div class="stock_item-qty">${movement.created_at ? formatOrderDate(movement.created_at) : 'N/A'}</div>
                    <div class="stock_item-price">
                        <span class="stock_badge ${getStatusClass(movement.status)}">
                            ${movement.status.toUpperCase()}
                        </span>
                        <div>Qty: ${movement.quantity || 0}</div>
                    </div>
                </div>
            `).join('')
            : '<div class="empty-state">No recent movements found.</div>'
        }
    </div>

    <div class="stock_footer card-footer">
        <a href="manage/stock/edit.php?id=${item.id}" class="stock_btn-primary btn-primary">
            Edit Stock
        </a>
    </div>
</div>
`;
};

function getStatusClass(status) {
    switch (status) {
        case 'paid':
        case 'delivered':
            return 'stock_status_paid';
        case 'pending':
        case 'processing':
            return 'stock_status_processing';
        case 'cancelled':
        case 'refunded':
            return 'stock_status_cancelled';
        default:
            return 'stock_status_pending';
    }
}

const renderModal = async (id) => {
    try {
        const {success, stocks} = await fetchModalDetails(id);
        console.log(stocks);

        if (!success) {
            throw new Error(stocks?.error || stocks?.message || 'Failed to fetch stock details');
        }

        const item = stocks;

        if (!item || typeof item !== 'object' || !item.id) {
            throw new Error('Invalid stock data format');
        }

        return detailsHtml(item);

    } catch (error) {
        throw new Error(error.message || 'Failed to load stock details');
    }
};

export const stockListeners = async () => {  
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
            const {stocks} = await fetchStock(DEFAULT_LIMIT, 0, currentQuery);
            console.log(stocks);


            const tbody = document.getElementById('stock-table-body');
            const loadMoreWrapper = document.getElementById('stock_load-more-wrapper');

            if (!tbody) return;

            if (stocks.error) {
                tbody.innerHTML = `<tr><td colspan="8" class="stock_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!stocks.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="stock_no-data-cell">No stock found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = stocks.map(renderStockRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = stocks.length === DEFAULT_LIMIT ? `<button id="stock_load-more-btn" class="stock_btn-load-more btn-load-more">Load More Stock</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.stock_btn-view') || e.target.closest('.stock_btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.stock_btn-view') ? e.target : e.target.closest('.stock_btn-view');
            const stockId = button.dataset.id;

            if (!stockId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading stock details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(Number.parseInt(stockId));
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
                        <h3 class="modal-error-title">Error Loading Stock</h3>
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
        if (e.target.id === 'stock_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreStock();
                document.getElementById('stock-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more stock to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more stock to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Stock';
                }
            } catch (error) {
                console.error('Error loading more stock:', error);
                button.disabled = false;
                button.textContent = 'Load More Stock';
            }
        }

        // refresh
        if (e.target.id === 'stock_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await Stock();
                document.querySelector('.stock-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing stock:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'stock-search-input') {
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
};


window.loadMoreStock = loadMoreStock;
window.fetchStock = fetchStock;