import { fetchAllOrders, fetchOrder, fetchModalDetails } from "./Orders.utils.js";
import { escapeHtml, formatDate } from "../../utils.js";

const DEFAULT_LIMIT = 50;
let currentOffset = 0;

async function loadMoreOrders() {
    currentOffset += DEFAULT_LIMIT;
    const orders = await fetchAllOrders(DEFAULT_LIMIT, currentOffset);
    if (orders.error) {
        return `<tr><td colspan="8" class="orders_error-cell">Error: ${escapeHtml(orders.error)}</td></tr>`;
    }
    if (!orders || orders.length === 0) {
        return `<tr><td colspan="8" class="orders_no-data-cell">No more orders to load</td></tr>`;
    }
    return orders.map(order => renderOrderRow(order)).join('');
}

function renderOrderRow(order) {
    const total = (order.total_cents != null) ? (order.total_cents / 100).toFixed(2) : '-';
    const itemCount = order.item_count ?? (order.items ? order.items.length : '-');
    return `
        <tr class="orders_row" data-order-id="${order.id}">
            <td class="orders_cell">${order.id}</td>
            <td class="orders_cell">${escapeHtml(order.order_number)}</td>
            <td class="orders_cell orders_status">${escapeHtml(order.status)}</td>
            <td class="orders_cell orders_total">$${total}</td>
            <td class="orders_cell">${escapeHtml(order.user_name ?? order.user_email ?? '-')}</td>
            <td class="orders_cell">${itemCount}</td>
            <td class="orders_cell">${formatDate(order.created_at)}</td>
            <td class="orders_cell orders_actions">
                <button class="orders_btn-view" data-id="${order.id}" title="View Details">👁️ View</button>
                <a href="manage/order/update.php?id=${order.id}" class="orders_btn-edit" title="Edit Order">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const Orders = async () => {
    currentOffset = 0;
    const orders = await fetchAllOrders(DEFAULT_LIMIT, currentOffset);

    if (orders.error) {
        return `
            <div class="orders_table">
                <div class="orders_error-box">
                    <strong>Error:</strong> ${escapeHtml(orders.error)}
                </div>
            </div>
        `;
    }

    if (!orders || orders.length === 0) {
        return `
            <div class="orders_table">
                <div class="orders_no-data-box">
                    <p>No orders found.</p>
                </div>
            </div>
        `;
    }

    const rows = orders.map(o => renderOrderRow(o)).join('');

    return `
        <div class="orders_table">
            <div class="orders_header">
                <h2>Orders (${orders.length}${orders.length === DEFAULT_LIMIT ? '+' : ''})</h2>
                <a href="manage/order/create.php" class="orders_btn-primary">Edit Order</a>
                <button id="orders_refresh-btn" class="orders_btn-refresh">🔄 Refresh</button>
            </div>

            <div class="orders_wrapper">
                <table class="orders_data-table">
                    <thead>
                        <tr class="orders_header-row">
                            <th>ID</th>
                            <th>Order Number</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>User</th>
                            <th>Items</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="orders_table-body">
                        ${rows}
                    </tbody>
                </table>
            </div>

            ${orders.length === DEFAULT_LIMIT ? `
                <div class="orders_load-more-wrapper">
                    <button id="orders_load-more-btn" class="orders_btn-load-more">Load More Orders</button>
                </div>
            ` : ''}
        </div>
    `;
};

const orderDetailsHtml = (order) => {
    const total = (order.total_cents != null) ? (order.total_cents / 100).toFixed(2) : '-';
    return `
<div class="orders_card">
    <div class="orders_card-header">
        <span>Order Details</span>
        <span class="orders_badge orders_status_${escapeHtml(order.status)}">${escapeHtml(order.status)}</span>
        <button class="orders_close-btn">&times;</button>
    </div>

    <div class="orders_section-title">Order Info</div>
    <div class="orders_data-grid">
        <div class="orders_field"><strong class="orders_label">Order #</strong><span class="orders_value">${escapeHtml(order.order_number)}</span></div>
        <div class="orders_field"><strong class="orders_label">Total</strong><span class="orders_value">$${total}</span></div>
        <div class="orders_field"><strong class="orders_label">Created</strong><span class="orders_value">${formatDate(order.created_at)}</span></div>
        <div class="orders_field"><strong class="orders_label">Paid At</strong><span class="orders_value">${order.paid_at ? formatDate(order.paid_at) : '-'}</span></div>
    </div>

    <div class="orders_section-title">Customer</div>
    <div class="orders_data-grid">
        <div class="orders_field"><strong class="orders_label">Name</strong><span class="orders_value">${escapeHtml(order.user_name ?? '-')}</span></div>
        <div class="orders_field"><strong class="orders_label">Email</strong><span class="orders_value">${escapeHtml(order.user_email ?? '-')}</span></div>
        <div class="orders_field"><strong class="orders_label">Phone</strong><span class="orders_value">${escapeHtml(order.user_phone ?? '-')}</span></div>
    </div>

    <div class="orders_section-title">Shipping Address</div>
    <div class="orders_address">
        ${order.shipping_address ? `
            <div class="orders_addr-block">
                <div>${escapeHtml(order.shipping_address.recipient_name ?? '')}</div>
                <div>${escapeHtml(order.shipping_address.address_line1 ?? '')}</div>
                ${order.shipping_address.address_line2 ? `<div>${escapeHtml(order.shipping_address.address_line2)}</div>` : ''}
                <div>${escapeHtml(order.shipping_address.city ?? '')} ${escapeHtml(order.shipping_address.postal_code ?? '')}</div>
                <div>${escapeHtml(order.shipping_address.country ?? '')}</div>
                <div>${escapeHtml(order.shipping_address.phone ?? '')}</div>
            </div>
        ` : `<div class="orders_empty">No shipping address</div>`}
    </div>

    ${order.items && order.items.length ? `
        <div class="orders_section-title">Items (${order.items.length})</div>
        <div class="orders_items">
            ${order.items.map(it => `
                <div class="orders_item-row">
                    <div class="orders_item-name">${escapeHtml(it.product_name)}</div>
                    <div class="orders_item-qty">Qty: ${it.quantity}</div>
                    <div class="orders_item-price">$${(it.price_cents/100).toFixed(2)}</div>
                </div>
            `).join('')}
        </div>
    ` : ''}

    ${order.payments && order.payments.length ? `
        <div class="orders_section-title">Payments</div>
        <div class="orders_payments">
            ${order.payments.map(p => `
                <div class="orders_payment-row">
                    <div class="orders_payment-gateway">${escapeHtml(p.gateway)}</div>
                    <div class="orders_payment-amount">$${(p.amount_cents/100).toFixed(2)}</div>
                    <div class="orders_payment-status">${escapeHtml(p.status)}</div>
                </div>
            `).join('')}
        </div>
    ` : ''}

    <div class="orders_footer">
        <a href="manage/order/update.php?id=${order.id}" class="orders_btn-primary">Edit Order</a>
    </div>
</div>
`;
};

document.addEventListener('click', async (e) => {
    if (e.target.matches('.orders_btn-view') || e.target.closest('.orders_btn-view')) {
        const btn = e.target.matches('.orders_btn-view') ? e.target : e.target.closest('.orders_btn-view');
        const id = btn.dataset.id;
        const modal = document.getElementById('modal');
        const modalBody = document.getElementById('modal-body');


        if (!id || !modal || !modalBody) return;
        modalBody.innerHTML = '<div class="orders_loading">⏳ Loading order...</div>';
        modal.classList.add('active');

        try {
            const { success, order, error } = await fetchModalDetails(id);
            console.log(order);
            if (error) throw new Error(error);
            const o = order ?? (success ? order : null);
            if (!o || !o.id) throw new Error('Invalid order data');
            modalBody.innerHTML = orderDetailsHtml(o);
            const closeBtn = modalBody.querySelector('.orders_close-btn');
            if (closeBtn) closeBtn.addEventListener('click', () => modal.classList.remove('active'));
        } catch (err) {
            modalBody.innerHTML = `<div class="orders_error"><div class="orders_error-msg">${escapeHtml(err.message || 'Failed to load order')}</div><button class="orders_close-btn">Close</button></div>`;
            const cb = modalBody.querySelector('.orders_close-btn');
            if (cb) cb.addEventListener('click', () => document.getElementById('modal').classList.remove('active'));
        }
    }

    if (e.target && e.target.id === 'orders_load-more-btn') {
        const btn = e.target;
        btn.disabled = true;
        btn.textContent = 'Loading...';
        try {
            const html = await loadMoreOrders();
            document.getElementById('orders_table-body').insertAdjacentHTML('beforeend', html);
            const newItems = await fetchAllOrders(DEFAULT_LIMIT, currentOffset);
            if (newItems.length < DEFAULT_LIMIT) {
                btn.remove();
            } else {
                btn.disabled = false;
                btn.textContent = 'Load More Orders';
            }
        } catch {
            btn.disabled = false;
            btn.textContent = 'Load More Orders';
        }
    }

    if (e.target && e.target.id === 'orders_refresh-btn') {
        const btn = e.target;
        btn.disabled = true;
        btn.textContent = 'Refreshing...';
        try {
            currentOffset = 0;
            const content = await Orders();
            document.querySelector('.orders_table').outerHTML = content;
        } catch {
            btn.disabled = false;
            btn.textContent = '🔄 Refresh';
        }
    }
});

document.addEventListener('click', (e) => {
    const modal = document.getElementById('modal');
    if (!modal) return;
    if (e.target === modal) modal.classList.remove('active');
    const modalClose = document.getElementById('modal-close');
    if (modalClose && e.target === modalClose) modal.classList.remove('active');
});

window.loadMoreOrders = loadMoreOrders;
window.fetchAllOrders = fetchAllOrders;