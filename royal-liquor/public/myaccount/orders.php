<?php
/**
 * MyAccount - Orders
 * Order history and tracking
 */
$pageName = 'orders';
$pageTitle = 'My Orders - Royal Liquor';
require_once __DIR__ . "/_layout.php";
?>

<h1 class="account-page-title">My Orders</h1>

<!-- Order Filters -->
<div class="orders-filters">
    <div class="filter-tabs">
        <button class="filter-tab active" data-status="all">All Orders</button>
        <button class="filter-tab" data-status="pending">Pending</button>
        <button class="filter-tab" data-status="processing">Processing</button>
        <button class="filter-tab" data-status="shipped">Shipped</button>
        <button class="filter-tab" data-status="delivered">Delivered</button>
    </div>
</div>

<!-- Orders List -->
<div class="orders-container" id="ordersContainer">
    <!-- Sample Order Card -->
    <div class="order-card card">
        <div class="order-header">
            <div class="order-info">
                <span class="order-number">Order #RL-2024-0012</span>
                <span class="order-date">December 10, 2024</span>
            </div>
            <span class="order-status badge badge-gold">Processing</span>
        </div>
        <div class="order-items">
            <div class="order-item">
                <div class="order-item-image skeleton skeleton-card" style="width:60px;height:60px;"></div>
                <div class="order-item-details">
                    <span class="order-item-name">Johnnie Walker Blue Label</span>
                    <span class="order-item-qty">Qty: 1</span>
                </div>
                <span class="order-item-price">Rs. 45,000</span>
            </div>
            <div class="order-item">
                <div class="order-item-image skeleton skeleton-card" style="width:60px;height:60px;"></div>
                <div class="order-item-details">
                    <span class="order-item-name">Glenfiddich 18 Year</span>
                    <span class="order-item-qty">Qty: 2</span>
                </div>
                <span class="order-item-price">Rs. 28,000</span>
            </div>
        </div>
        <div class="order-footer">
            <div class="order-total">
                <span>Total:</span>
                <span class="order-total-value">Rs. 73,000</span>
            </div>
            <div class="order-actions">
                <button class="btn btn-sm btn-outline">Track Order</button>
                <button class="btn btn-sm btn-outline">View Details</button>
            </div>
        </div>
    </div>

    <!-- Empty State (hidden by default) -->
    <div class="empty-state hidden" id="emptyOrders">
        <div class="empty-state-icon">📦</div>
        <h3 class="empty-state-title">No Orders Yet</h3>
        <p class="empty-state-text">You haven't placed any orders yet. Start exploring our collection!</p>
        <a href="<?= getPageUrl('shop') ?>" class="btn btn-gold">Shop Now</a>
    </div>
</div>

<style>
.orders-filters {
    margin-bottom: var(--space-xl);
}

.filter-tabs {
    display: flex;
    gap: var(--space-sm);
    flex-wrap: wrap;
}

.filter-tab {
    padding: var(--space-sm) var(--space-lg);
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-full);
    font-size: 0.9rem;
    cursor: pointer;
    transition: all var(--duration-fast) var(--ease-out);
}

.filter-tab:hover {
    border-color: var(--gray-300);
}

.filter-tab.active {
    background: var(--black);
    color: var(--white);
    border-color: var(--black);
}

.orders-container {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
}

.order-card {
    padding: 0;
    overflow: hidden;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-lg);
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-100);
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.order-number {
    font-weight: 600;
    color: var(--black);
}

.order-date {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.order-items {
    padding: var(--space-lg);
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.order-item {
    display: flex;
    align-items: center;
    gap: var(--space-md);
}

.order-item-image {
    border-radius: var(--radius-sm);
    overflow: hidden;
}

.order-item-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.order-item-name {
    font-weight: 500;
}

.order-item-qty {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.order-item-price {
    font-weight: 600;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-lg);
    background: var(--gray-50);
    border-top: 1px solid var(--gray-100);
}

.order-total {
    display: flex;
    gap: var(--space-sm);
}

.order-total-value {
    font-weight: 700;
    font-size: 1.125rem;
}

.order-actions {
    display: flex;
    gap: var(--space-sm);
}

@media (max-width: 640px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-sm);
    }
    .order-footer {
        flex-direction: column;
        gap: var(--space-md);
    }
    .order-actions {
        width: 100%;
    }
    .order-actions .btn {
        flex: 1;
    }
}
</style>

<script type="module">
import { getOrders } from '<?= BASE_URL ?>utils/orders.js';
import { toast } from '<?= BASE_URL ?>utils/toast.js';

let allOrders = [];
let currentFilter = 'all';

// Load and render orders
const loadOrders = () => {
    allOrders = getOrders();
    renderOrders();
};

// Render orders based on current filter
const renderOrders = () => {
    const container = document.getElementById('ordersContainer');
    const emptyState = document.getElementById('emptyOrders');
    
    let filteredOrders = currentFilter === 'all' 
        ? allOrders 
        : allOrders.filter(o => o.status === currentFilter);
    
    // Clear sample and render actual data
    container.querySelectorAll('.order-card').forEach(card => card.remove());
    
    if (filteredOrders.length === 0) {
        emptyState.classList.remove('hidden');
        if (allOrders.length > 0 && filteredOrders.length === 0) {
            emptyState.querySelector('.empty-state-title').textContent = 'No orders found';
            emptyState.querySelector('.empty-state-text').textContent = 'No orders with this status.';
        }
        return;
    }
    
    emptyState.classList.add('hidden');
    
    const statusColors = {
        pending: 'badge-warning',
        processing: 'badge-gold',
        shipped: 'badge-info',
        delivered: 'badge-success',
        cancelled: 'badge-error'
    };
    
    filteredOrders.forEach(order => {
        const orderDate = new Date(order.createdAt).toLocaleDateString('en-US', { 
            month: 'long', day: 'numeric', year: 'numeric' 
        });
        
        const itemsHTML = (order.items || []).slice(0, 3).map(item => `
            <div class="order-item">
                <div class="order-item-image" style="width:60px;height:60px;background:var(--gray-100);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;">
                    ${item.image_url ? `<img src="${item.image_url}" alt="${item.name}" style="width:100%;height:100%;object-fit:cover;">` : '🍾'}
                </div>
                <div class="order-item-details">
                    <span class="order-item-name">${item.name || 'Product'}</span>
                    <span class="order-item-qty">Qty: ${item.quantity || 1}</span>
                </div>
                <span class="order-item-price">$${((item.price || 0) / 100).toFixed(2)}</span>
            </div>
        `).join('');
        
        const moreItems = (order.items?.length || 0) > 3 ? `<p class="text-muted text-sm">+ ${order.items.length - 3} more items</p>` : '';
        
        const orderCard = document.createElement('div');
        orderCard.className = 'order-card card';
        orderCard.innerHTML = `
            <div class="order-header">
                <div class="order-info">
                    <span class="order-number">Order #${order.id}</span>
                    <span class="order-date">${orderDate}</span>
                </div>
                <span class="order-status badge ${statusColors[order.status] || statusColors.pending}">${order.status}</span>
            </div>
            <div class="order-items">
                ${itemsHTML}
                ${moreItems}
            </div>
            <div class="order-footer">
                <div class="order-total">
                    <span>Total:</span>
                    <span class="order-total-value">$${((order.total || 0) / 100).toFixed(2)}</span>
                </div>
                <div class="order-actions">
                    ${order.status === 'shipped' ? '<button class="btn btn-sm btn-outline" onclick="alert(\'Tracking info would display here\')">Track Order</button>' : ''}
                    <button class="btn btn-sm btn-outline view-details-btn" data-id="${order.id}">View Details</button>
                </div>
            </div>
        `;
        container.insertBefore(orderCard, emptyState);
    });
    
    // Add badge styles if not exist
    if (!document.getElementById('badge-styles')) {
        const style = document.createElement('style');
        style.id = 'badge-styles';
        style.textContent = `
            .badge-warning { background: #f59e0b; color: white; }
            .badge-gold { background: var(--gold); color: var(--black); }
            .badge-info { background: #3b82f6; color: white; }
            .badge-success { background: #22c55e; color: white; }
            .badge-error { background: #ef4444; color: white; }
            .badge { padding: 4px 12px; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        `;
        document.head.appendChild(style);
    }
};

// Filter tab click handlers
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentFilter = tab.dataset.status;
        renderOrders();
    });
});

// View details handler
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.view-details-btn');
    if (btn) {
        const orderId = btn.dataset.id;
        const order = allOrders.find(o => o.id == orderId);
        if (order) {
            toast.info(`Order #${orderId} details - ${order.items?.length || 0} items, $${((order.total || 0) / 100).toFixed(2)}`);
        }
    }
});

loadOrders();
console.log('[Orders] Orders page ready');
</script>

<?php require_once __DIR__ . "/_layout_end.php"; ?>
