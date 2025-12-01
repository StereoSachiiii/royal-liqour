import { fetchPayments, fetchModalDetails } from "./Payments.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMorePayments() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const payments = await fetchPayments(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (payments.error) {
            return `<tr><td colspan="7" class="payments_error-cell">Error: ${escapeHtml(payments.error)}</td></tr>`;
        }

        if (payments.length === 0) {
            return `<tr><td colspan="7" class="payments_no-data-cell">No more payments to load</td></tr>`;
        }

        return payments.map(payment => renderPaymentRow(payment)).join('');
    } catch (error) {
        console.error('Error loading more payments:', error);
        return `<tr><td colspan="7" class="payments_error-cell">Failed to load payments</td></tr>`;
    }
}

function renderPaymentRow(payment) {
    return `
        <tr class="payments_row payment-row" data-payment-id="${payment.id}">
            <td class="payments_cell">${payment.id}</td>
            <td class="payments_cell">${payment.order_id}</td>
            <td class="payments_cell">${escapeHtml(payment.order_number)}</td>
            <td class="payments_cell">$${(payment.amount_cents / 100).toFixed(2)}</td>
            <td class="payments_cell">${escapeHtml(payment.gateway)}</td>
            <td class="payments_cell">
                <span class="payments_badge ${getStatusClass(payment.status)}">
                    ${payment.status.toUpperCase()}
                </span>
            </td>
            <td class="payments_cell">${formatDate(payment.created_at)}</td>
            <td class="payments_cell payments_actions">
                <button class="payments_btn-view" data-id="${payment.id}" title="View Details">👁️ View</button>
           
                <a href="manage/payments/update.php?id=${payment.id}" class="payments_btn-edit btn-edit" title="Edit Payment">✏️ Edit</a>
            </td>
        </tr>
    `;
}

function getStatusClass(status) {
    switch (status) {
        case 'captured':
            return 'payments_status_paid';
        case 'pending':
            return 'payments_status_pending';
        case 'failed':
        case 'refunded':
        case 'voided':
            return 'payments_status_cancelled';
        default:
            return 'payments_status_processing';
    }
}

export const Payments = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const payments = await fetchPayments(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (payments.error) {
            return `
                <div class="payments_table payments-table">
                    <div class="payments_error-box">
                        <strong>Error:</strong> ${escapeHtml(payments.error)}
                    </div>
                </div>
            `;
        }

        if (payments.length === 0) {
            return `
                <div class="payments_table payments-table">
                    <div class="payments_no-data-box">
                        <p>📭 No payments found.</p>
                    </div>
                </div>
            `;
        }

        const tableRows = payments.map(payment => renderPaymentRow(payment)).join('');

        return `
            <div class="payments_table payments-table">
                <div class="payments_header table-header">
                    <h2>Payments Management (${payments.length}${payments.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="payments_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="payments-search-input" class="payments_search-input" type="search" placeholder="Search order number or gateway" aria-label="Search payments" />
                              <a href="manage/payments/create.php" class="payments_btn-primary btn-primary">
            Create
        </a>
                        
                        <button id="payments_refresh-btn" class="payments_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="payments_wrapper table-wrapper">
                    <table class="payments_data-table payments-data-table">
                        <thead>
                            <tr class="payments_header-row table-header-row">
                                <th>ID</th>
                                <th>Order ID</th>
                                <th>Order Number</th>
                                <th>Amount</th>
                                <th>Gateway</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="payments-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="payments_load-more-wrapper" class="payments_load-more-wrapper" style="text-align:center;">
                    ${payments.length === DEFAULT_LIMIT ? `
                        <button id="payments_load-more-btn" class="payments_btn-load-more btn-load-more">
                            Load More Payments
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering payments table:', error);
        return `
            <div class="payments_table payments-table">
                <div class="payments_error-box">
                    <strong>Error:</strong> Failed to load payments table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (payment) => {
    return `
<div class="payments_card payment-card">
    <div class="payments_card-header payment-card-header">
        <span>Payment Details</span>
        <span class="payments_badge ${getStatusClass(payment.status)}">
            ${payment.status.toUpperCase()}
        </span>
        <button class="payments_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="payments_section-title payment-section-title">Basic Info</div>
    <div class="payments_data-grid payment-data-grid">
        <div class="payments_field data-field">
            <strong class="payments_label data-label">ID</strong>
            <span class="payments_value data-value">${payment.id || 'N/A'}</span>
        </div>
        <div class="payments_field data-field">
            <strong class="payments_label data-label">Order Number</strong>
            <span class="payments_value data-value">${escapeHtml(payment.order_number) || 'N/A'}</span>
        </div>
        <div class="payments_field data-field">
            <strong class="payments_label data-label">Order Status</strong>
            <span class="payments_value data-value">${payment.order_status.toUpperCase()}</span>
        </div>
        <div class="payments_field data-field">
            <strong class="payments_label data-label">Amount</strong>
            <span class="payments_value data-value">$${(payment.amount_cents / 100).toFixed(2)} ${payment.currency}</span>
        </div>
        <div class="payments_field data-field">
            <strong class="payments_label data-label">Gateway</strong>
            <span class="payments_value data-value">${escapeHtml(payment.gateway)}</span>
        </div>
        <div class="payments_field data-field">
            <strong class="payments_label data-label">Gateway Order ID</strong>
            <span class="payments_value data-value">${payment.gateway_order_id || '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="payments_field data-field">
            <strong class="payments_label data-label">Transaction ID</strong>
            <span class="payments_value data-value">${payment.transaction_id || '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="payments_field data-field">
            <strong class="payments_label data-label">Created At</strong>
            <span class="payments_value data-value">${payment.created_at ? formatDate(payment.created_at) : 'N/A'}</span>
        </div>
    </div>

    <div class="payments_section-title payment-section-title">User Info</div>
    <div class="payments_data-grid payment-data-grid">
        <div class="payments_field data-field">
            <strong class="payments_label data-label">User Name</strong>
            <span class="payments_value data-value">${payment.user_name ? escapeHtml(payment.user_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="payments_field data-field">
            <strong class="payments_label data-label">User Email</strong>
            <span class="payments_value data-value">${payment.user_email ? escapeHtml(payment.user_email) : '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="payments_section-title payment-section-title">Order Info</div>
    <div class="payments_data-grid payment-data-grid">
        <div class="payments_field data-field">
            <strong class="payments_label data-label">Order Total</strong>
            <span class="payments_value data-value">$${(payment.order_total_cents / 100).toFixed(2)}</span>
        </div>
    </div>

    <div class="payments_section-title payment-section-title">Payload</div>
    <div class="payments_data-grid payment-data-grid">
        <div class="payments_field data-field">
            <strong class="payments_label data-label">Payload</strong>
            <span class="payments_value data-value">${payment.payload ? escapeHtml(JSON.stringify(payment.payload, null, 2)) : '<span class="data-empty">-</span>'}</span>
        </div>
    </div>

    <div class="payments_footer card-footer">
        <a href="manage/payments/update.php?id=${payment.id}" class="payments_btn-primary btn-primary">
            Edit Payment
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch payment details');
        }

        const payment = result.payment;

        if (!payment || typeof payment !== 'object' || !payment.id) {
            throw new Error('Invalid payment data format');
        }

        return detailsHtml(payment);

    } catch (error) {
        throw new Error(error.message || 'Failed to load payment details');
    }
};

export const paymentsListeners = async () => {

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
            const results = await fetchPayments(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('payments-table-body');
            const loadMoreWrapper = document.getElementById('payments_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="7" class="payments_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="7" class="payments_no-data-cell">No payments found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderPaymentRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="payments_load-more-btn" class="payments_btn-load-more btn-load-more">Load More Payments</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.payments_btn-view') || e.target.closest('.payments_btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.payments_btn-view') ? e.target : e.target.closest('.payments_btn-view');
            const paymentId = button.dataset.id;

            if (!paymentId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading payment details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(paymentId));
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
                        <h3 class="modal-error-title">Error Loading Payment</h3>
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
        if (e.target.id === 'payments_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMorePayments();
                document.getElementById('payments-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more payments to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more payments to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Payments';
                }
            } catch (error) {
                console.error('Error loading more payments:', error);
                button.disabled = false;
                button.textContent = 'Load More Payments';
            }
        }

        // refresh
        if (e.target.id === 'payments_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await Payments();
                document.querySelector('.payments-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing payments:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'payments-search-input') {
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




window.loadMorePayments = loadMorePayments;
window.fetchPayments = fetchPayments;