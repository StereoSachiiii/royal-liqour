import { fetchCarts, fetchModalDetails } from "./Carts.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreCarts() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const carts = await fetchCarts(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (carts.error) {
            return `<tr><td colspan="9" class="carts_error-cell">Error: ${escapeHtml(carts.error)}</td></tr>`;
        }

        if (carts.length === 0) {
            return `<tr><td colspan="9" class="carts_no-data-cell">No more carts to load</td></tr>`;
        }

        return carts.map(cart => renderCartRow(cart)).join('');
    } catch (error) {
        console.error('Error loading more carts:', error);
        return `<tr><td colspan="9" class="carts_error-cell">Failed to load carts</td></tr>`;
    }
}

function renderCartRow(cart) {
    return `
        <tr class="carts_row cart-row" data-cart-id="${cart.id}">
            <td class="carts_cell">${cart.id}</td>
            <td class="carts_cell">${escapeHtml(cart.session_id)}</td>
            <td class="carts_cell">
                <span class="carts_badge ${getStatusClass(cart.status)}">
                    ${cart.status.toUpperCase()}
                </span>
            </td>
            <td class="carts_cell">$${(cart.total_cents / 100).toFixed(2)}</td>
            <td class="carts_cell">${cart.item_count || 0}</td>
            <td class="carts_cell">${formatDate(cart.created_at)}</td>
            <td class="carts_cell">${formatDate(cart.updated_at)}</td>
            <td class="carts_cell">${escapeHtml(cart.user_name || '-')}</td>
            <td class="carts_cell">${escapeHtml(cart.user_email || '-')}</td>
            <td class="carts_cell carts_actions">
                <button class="carts_btn-view " data-id="${cart.id}" title="View Details">👁️ View</button>
               
                <a href="manage/cart/update.php?id=${cart.id}" class="carts_btn-edit btn-edit" title="Edit Cart">✏️ Edit</a>
            </td>
        </tr>
    `;
}

function getStatusClass(status) {
    switch (status) {
        case 'active':
            return 'carts_status_paid';
        case 'converted':
            return 'carts_status_processing';
        case 'abandoned':
        case 'expired':
            return 'carts_status_cancelled';
        default:
            return 'carts_status_pending';
    }
}

export const Carts = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const carts = await fetchCarts(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (carts.error) {
            return `
                <div class="carts_table carts-table">
                    <div class="carts_error-box">
                        <strong>Error:</strong> ${escapeHtml(carts.error)}
                    </div>
                </div>
            `;
        }

        if (carts.length === 0) {
            return `
                <div class="carts_table carts-table">
                    <div class="carts_no-data-box">
                        <p>📭 No carts found.</p>
                    </div>
                </div>
            `;
        }
        const tableRows = carts.map(cart => renderCartRow(cart)).join('');

        return `
            <div class="carts_table carts-table">
                <div class="carts_header table-header">
                    <h2>Carts Management (${carts.length}${carts.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="carts_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="carts-search-input" class="carts_search-input" type="search" placeholder="Search session id or user" aria-label="Search carts" />
                        <button id="carts_refresh-btn" class="carts_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="carts_wrapper table-wrapper">
                    <table class="carts_data-table carts-data-table">
                        <thead>
                            <tr class="carts_header-row table-header-row">
                                <th>ID</th>
                                <th>Session ID</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Items</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>User Name</th>
                                <th>User Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="carts-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="carts_load-more-wrapper" class="carts_load-more-wrapper" style="text-align:center;">
                    ${carts.length === DEFAULT_LIMIT ? `
                        <button id="carts_load-more-btn" class="carts_btn-load-more btn-load-more">
                            Load More Carts
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering carts table:', error);
        return `
            <div class="carts_table carts-table">
                <div class="carts_error-box">
                    <strong>Error:</strong> Failed to load carts table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (cart) => {
    return `
<div class="carts_card cart-card">
    <div class="carts_card-header cart-card-header">
        <span>Cart Details</span>
        <span class="carts_badge ${getStatusClass(cart.status)}">
            ${cart.status.toUpperCase()}
        </span>
        <button class="carts_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="carts_section-title cart-section-title">Basic Info</div>
    <div class="carts_data-grid cart-data-grid">
        <div class="carts_field data-field">
            <strong class="carts_label data-label">ID</strong>
            <span class="carts_value data-value">${cart.id || 'N/A'}</span>
        </div>
        <div class="carts_field data-field">
            <strong class="carts_label data-label">Session ID</strong>
            <span class="carts_value data-value">${escapeHtml(cart.session_id) || 'N/A'}</span>
        </div>
        <div class="carts_field data-field">
            <strong class="carts_label data-label">Total</strong>
            <span class="carts_value data-value">$${(cart.total_cents / 100).toFixed(2)}</span>
        </div>
        <div class="carts_field data-field">
            <strong class="carts_label data-label">Item Count</strong>
            <span class="carts_value data-value">${cart.item_count || 0}</span>
        </div>
    </div>

    <div class="carts_section-title cart-section-title">User Info</div>
    <div class="carts_data-grid cart-data-grid">
        <div class="carts_field data-field">
            <strong class="carts_label data-label">User Name</strong>
            <span class="carts_value data-value">${cart.user_name ? escapeHtml(cart.user_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="carts_field data-field">
            <strong class="carts_label data-label">User Email</strong>
            <span class="carts_value data-value">${cart.user_email ? escapeHtml(cart.user_email) : '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="carts_section-title cart-section-title">Timeline</div>
    <div class="carts_data-grid cart-data-grid">
        <div class="carts_field data-field">
            <strong class="carts_label data-label">Created At</strong>
            <span class="carts_value data-value">${cart.created_at ? formatDate(cart.created_at) : 'N/A'}</span>
        </div>
        <div class="carts_field data-field">
            <strong class="carts_label data-label">Updated At</strong>
            <span class="carts_value data-value">${cart.updated_at ? formatDate(cart.updated_at) : 'N/A'}</span>
        </div>
        <div class="carts_field data-field">
            <strong class="carts_label data-label">Converted At</strong>
            <span class="carts_value data-value">${cart.converted_at ? formatDate(cart.converted_at) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="carts_field data-field">
            <strong class="carts_label data-label">Abandoned At</strong>
            <span class="carts_value data-value">${cart.abandoned_at ? formatDate(cart.abandoned_at) : '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="carts_section-title cart-section-title">Items</div>
    <div class="carts_items items-container">
        ${cart.items && Array.isArray(cart.items) && cart.items.length > 0 ?
            cart.items.map(item => `
                <div class="carts_item-row item-row">
                    <div class="carts_item-name">${item.product_name || 'N/A'}</div>
                    <div class="carts_item-qty">Qty: ${item.quantity || 0}</div>
                    <div class="carts_item-price">$${ (item.price_at_add_cents / 100).toFixed(2) }</div>
                </div>
            `).join('')
            : '<div class="empty-state">No items found.</div>'
        }
    </div>

    <div class="carts_section-title cart-section-title">Converted Order</div>
    <div class="carts_data-grid cart-data-grid">
        <div class="carts_field data-field">
            <strong class="carts_label data-label">Order Number</strong>
            <span class="carts_value data-value">${cart.converted_order_number || '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="carts_footer card-footer">
        <a href="manage/cart/update.php?id=${cart.id}" class="carts_btn-primary btn-primary">
            Edit Cart
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch cart details');
        }

        const cart = result.cart;

        if (!cart || typeof cart !== 'object' || !cart.id) {
            throw new Error('Invalid cart data format');
        }

        return detailsHtml(cart);

    } catch (error) {
        throw new Error(error.message || 'Failed to load cart details');
    }
};

export const cartsListeners = () => {   

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
            const results = await fetchCarts(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('carts-table-body');
            const loadMoreWrapper = document.getElementById('carts_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="9" class="carts_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="9" class="carts_no-data-cell">No carts found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderCartRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="carts_load-more-btn" class="carts_btn-load-more btn-load-more">Load More Carts</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.carts_btn-view') || e.target.closest('.carts_btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.carts_btn-view') ? e.target : e.target.closest('.carts_btn-view');
            const cartId = button.dataset.id;

            if (!cartId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading cart details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(cartId));
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
                        <h3 class="modal-error-title">Error Loading Cart</h3>
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
        if (e.target.id === 'carts_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreCarts();
                document.getElementById('carts-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more carts to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more carts to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Carts';
                }
            } catch (error) {
                console.error('Error loading more carts:', error);
                button.disabled = false;
                button.textContent = 'Load More Carts';
            }
        }

        // refresh
        if (e.target.id === 'carts_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await Carts();
                document.querySelector('.carts-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing carts:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'carts-search-input') {
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

window.loadMoreCarts = loadMoreCarts;
window.fetchCarts = fetchCarts;