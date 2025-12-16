import { fetchPayments, fetchModalDetails } from "./Payments.utils.js";
import { renderPaymentEdit, initPaymentEditHandlers } from "./PaymentEdit.js";
import { renderPaymentCreate, initPaymentCreateHandlers } from "./PaymentCreate.js";
import { escapeHtml, formatDate, openStandardModal, debounce } from "../../utils.js";

const DEFAULT_LIMIT = 20;
let currentOffset = 0;
let currentQuery = '';
let lastResults = [];

function getStatusClass(status) {
    switch (status) {
        case 'captured':
            return 'badge-active';
        case 'pending':
            return 'badge-warning';
        case 'failed':
        case 'refunded':
        case 'voided':
            return 'badge-inactive';
        default:
            return 'badge-default';
    }
}

function renderPaymentRow(payment) {
    return `
        <tr data-id="${payment.id}">
            <td>${payment.id}</td>
            <td>${payment.order_id}</td>
            <td>${escapeHtml(payment.order_number || '-')}</td>
            <td>$${(payment.amount_cents / 100).toFixed(2)}</td>
            <td>${escapeHtml(payment.gateway || '-')}</td>
            <td>
                <span class="badge ${getStatusClass(payment.status)}">
                    ${(payment.status || 'pending').toUpperCase()}
                </span>
            </td>
            <td>${formatDate(payment.created_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm payment-view" data-id="${payment.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm payment-edit" data-id="${payment.id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

async function loadMorePayments() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const payments = await fetchPayments(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (payments.error) {
            return `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(payments.error)}</td></tr>`;
        }

        if (payments.length === 0) {
            return `<tr><td colspan="8" class="admin-entity__empty">No more payments to load</td></tr>`;
        }

        lastResults = [...lastResults, ...payments];
        return payments.map(renderPaymentRow).join('');
    } catch (error) {
        console.error('Error loading more payments:', error);
        return `<tr><td colspan="8" class="admin-entity__empty">Failed to load payments</td></tr>`;
    }
}

export const Payments = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const payments = await fetchPayments(DEFAULT_LIMIT, currentOffset, currentQuery);

        lastResults = payments.error ? [] : (Array.isArray(payments) ? payments : []);
        const hasData = lastResults.length > 0;
        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        const tableRows = hasData
            ? lastResults.map(renderPaymentRow).join('')
            : `<tr><td colspan="8" class="admin-entity__empty">${payments.error ? escapeHtml(payments.error) : 'No payments found'}</td></tr>`;

        return `
            <div class="admin-entity">
                <div class="admin-entity__header">
                    <h2 class="admin-entity__title">Payments (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="payments-search-input" class="admin-entity__search" type="search" 
                               placeholder="Search by order number or gateway..." value="${escapeHtml(currentQuery)}" />
                        <button id="payments-create-btn" class="btn btn-primary">Add Payment</button>
                        <button id="payments_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
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

                <div id="payments_load-more-wrapper" style="text-align:center; margin-top: var(--space-4);">
                    ${hasData && lastResults.length === DEFAULT_LIMIT ? `<button id="payments_load-more-btn" class="btn btn-outline btn-sm">Load More Payments</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering payments table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> Failed to load payments table</div></div>`;
    }
};

// View modal content (matching products_card styling)
const paymentDetailsHtml = (payment) => {
    if (!payment) return '<div class="admin-entity__empty">No payment data</div>';

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Payment Details</span>
                <span class="badge ${getStatusClass(payment.status)}">${(payment.status || 'pending').toUpperCase()}</span>
            </div>
            <div class="products_section-title">Basic Info</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>ID</strong><span>${payment.id ?? 'N/A'}</span></div>
                <div class="products_field"><strong>Order Number</strong><span>${escapeHtml(payment.order_number || '-')}</span></div>
                <div class="products_field"><strong>Order ID</strong><span>${payment.order_id || '-'}</span></div>
                <div class="products_field"><strong>Amount</strong><span>$${(payment.amount_cents / 100).toFixed(2)} ${payment.currency || 'USD'}</span></div>
                <div class="products_field"><strong>Gateway</strong><span>${escapeHtml(payment.gateway || '-')}</span></div>
                <div class="products_field"><strong>Transaction ID</strong><span>${escapeHtml(payment.transaction_id || '-')}</span></div>
            </div>
            <div class="products_section-title">User Info</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>User</strong><span>${escapeHtml(payment.user_name || '-')}</span></div>
                <div class="products_field"><strong>Email</strong><span>${escapeHtml(payment.user_email || '-')}</span></div>
            </div>
            <div class="products_section-title">Timestamps</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Created</strong><span>${formatDate(payment.created_at)}</span></div>
            </div>
            ${payment.payload ? `
            <div class="products_section-title">Payload</div>
            <div style="padding: 1rem;">
                <pre style="background: #f5f5f5; padding: 0.75rem; border-radius: 4px; overflow-x: auto; font-size: 0.75rem;">${escapeHtml(JSON.stringify(payment.payload, null, 2))}</pre>
            </div>
            ` : ''}
            <div class="products_footer">
                <button class="btn btn-primary payment-edit" data-id="${payment.id}">Edit Payment</button>
            </div>
        </div>
    `;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(Number(id));
        if (result.error) throw new Error(result.error);
        if (!result.payment) throw new Error('No payment data returned from API');
        return paymentDetailsHtml(result.payment);
    } catch (error) {
        console.error('[Payments] Render modal error:', error);
        throw new Error(error.message || 'Failed to load payment details');
    }
};

// Attach delegated handlers
(() => {
    async function performSearch(query) {
        try {
            currentQuery = query || '';
            currentOffset = 0;
            const results = await fetchPayments(DEFAULT_LIMIT, 0, currentQuery);

            lastResults = results.error ? [] : (Array.isArray(results) ? results : []);
            const hasData = lastResults.length > 0;
            const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

            const titleEl = document.querySelector('.admin-entity__title');
            if (titleEl) titleEl.textContent = `Payments (${countLabel})`;

            const tbody = document.getElementById('payments-table-body');
            if (tbody) {
                tbody.innerHTML = hasData
                    ? lastResults.map(renderPaymentRow).join('')
                    : `<tr><td colspan="8" class="admin-entity__empty">${results.error ? escapeHtml(results.error) : 'No payments found'}</td></tr>`;
            }

            const loadMoreWrapper = document.getElementById('payments_load-more-wrapper');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = hasData && lastResults.length === DEFAULT_LIMIT
                    ? '<button id="payments_load-more-btn" class="btn btn-outline btn-sm">Load More Payments</button>'
                    : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // View modal
        if (e.target.matches('.payment-view') || e.target.closest('.payment-view')) {
            const btn = e.target.closest('.payment-view');
            const id = btn?.dataset.id;
            if (!id) return;

            try {
                const html = await renderModal(id);
                openStandardModal({
                    title: 'Payment Details',
                    bodyHtml: html,
                    size: 'lg'
                });
            } catch (error) {
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Edit modal (direct injection like Products)
        if (e.target.matches('.payment-edit') || e.target.closest('.payment-edit')) {
            const btn = e.target.closest('.payment-edit');
            const id = btn?.dataset.id;
            if (!id) return;

            console.log('[Payments] Edit button clicked:', { id });

            try {
                const formHtml = await renderPaymentEdit(parseInt(id));

                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                if (!modal || !modalBody) {
                    console.error('[Payments] Modal elements not found!');
                    return;
                }

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initPaymentEditHandlers(modalBody, parseInt(id), (data, action) => {
                    if (action === 'updated' || action === 'deleted') {
                        Payments().then(html => {
                            const adminEntity = document.querySelector('.admin-entity');
                            if (adminEntity) adminEntity.outerHTML = html;
                        });
                    }
                });
            } catch (error) {
                console.error('[Payments] Error opening edit form:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Create modal (direct injection like Products)
        if (e.target.id === 'payments-create-btn') {
            console.log('[Payments] Create button clicked');

            try {
                const formHtml = await renderPaymentCreate();

                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                if (!modal || !modalBody) {
                    console.error('[Payments] Modal elements not found!');
                    return;
                }

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initPaymentCreateHandlers(modalBody, (data, action) => {
                    if (action === 'created') {
                        Payments().then(html => {
                            const adminEntity = document.querySelector('.admin-entity');
                            if (adminEntity) adminEntity.outerHTML = html;
                        });
                    }
                });
            } catch (error) {
                console.error('[Payments] Error opening create form:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Load More
        if (e.target.id === 'payments_load-more-btn') {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Loading...';
            const html = await loadMorePayments();
            document.getElementById('payments-table-body').insertAdjacentHTML('beforeend', html);
            if (html.includes('No more') || html.includes('Error') || html.includes('Failed')) {
                btn.disabled = true;
                btn.textContent = 'No more items';
            } else {
                btn.disabled = false;
                btn.textContent = 'Load More Payments';
            }
        }

        // Refresh
        if (e.target.id === 'payments_refresh-btn') {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Refreshing...';
            const html = await Payments();
            const container = document.querySelector('.admin-entity');
            if (container) container.outerHTML = html;
        }
    });

    document.addEventListener('input', (e) => {
        if (e.target.id === 'payments-search-input') {
            debouncedSearch(e);
        }
    });
})();

window.loadMorePayments = loadMorePayments;
window.fetchPayments = fetchPayments;

// Legacy export for backward compatibility
export const paymentsListeners = () => {
    console.log('[Payments] Event listeners already attached via IIFE');
};