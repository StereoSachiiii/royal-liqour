<?php
/**
 * MyAccount Dashboard
 * Main account overview page
 */
$pageName = 'account';
$pageTitle = 'My Account - Royal Liquor';
require_once __DIR__ . "/_layout.php";
?>

<h1 class="account-page-title">Dashboard</h1>

<!-- Quick Stats -->
<div class="dashboard-stats">
    <div class="stat-card card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <span class="stat-value" id="orderCount">-</span>
            <span class="stat-label">Total Orders</span>
        </div>
    </div>
    <div class="stat-card card">
        <div class="stat-icon">❤️</div>
        <div class="stat-info">
            <span class="stat-value" id="wishlistCount">-</span>
            <span class="stat-label">Wishlist Items</span>
        </div>
    </div>
    <div class="stat-card card">
        <div class="stat-icon">📍</div>
        <div class="stat-info">
            <span class="stat-value" id="addressCount">-</span>
            <span class="stat-label">Saved Addresses</span>
        </div>
    </div>
    <div class="stat-card card">
        <div class="stat-icon">🏆</div>
        <div class="stat-info">
            <span class="stat-value">Gold</span>
            <span class="stat-label">Member Status</span>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="account-card">
    <div class="account-card-header">
        <h2 class="account-card-title">Recent Orders</h2>
        <a href="<?= BASE_URL ?>myaccount/orders.php" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div id="recentOrders" class="orders-list">
        <!-- TODO: Populate via JS -->
        <div class="empty-state-sm">
            <p>No orders yet. <a href="<?= getPageUrl('shop') ?>">Start shopping</a></p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="dashboard-actions">
    <a href="<?= BASE_URL ?>myaccount/addresses.php" class="action-card card card-hover">
        <span class="action-icon">➕</span>
        <span class="action-text">Add New Address</span>
    </a>

    <a href="<?= getPageUrl('shop') ?>" class="action-card card card-hover">
        <span class="action-icon">🛒</span>
        <span class="action-text">Continue Shopping</span>
    </a>
</div>

<style>
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-lg);
    margin-bottom: var(--space-xl);
}

.stat-card {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-lg);
}

.stat-icon {
    font-size: 2rem;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--black);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.account-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-lg);
    padding-bottom: var(--space-md);
    border-bottom: 1px solid var(--gray-100);
}

.account-card-header .account-card-title {
    margin: 0;
    padding: 0;
    border: none;
}

.orders-list {
    min-height: 100px;
}

.empty-state-sm {
    text-align: center;
    padding: var(--space-xl);
    color: var(--gray-500);
}

.empty-state-sm a {
    color: var(--gold);
    font-weight: 600;
}

.dashboard-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
}

.action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-sm);
    padding: var(--space-xl);
    text-decoration: none;
    text-align: center;
}

.action-icon {
    font-size: 2rem;
}

.action-text {
    font-weight: 600;
    color: var(--black);
}

@media (max-width: 1024px) {
    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    .dashboard-actions {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<script type="module">
import { getWishlist } from '<?= BASE_URL ?>utils/wishlist-storage.js';
import { getOrders } from '<?= BASE_URL ?>utils/orders.js';

// Load dashboard data
async function loadDashboard() {
    // Get wishlist count
    const wishlist = getWishlist();
    document.getElementById('wishlistCount').textContent = wishlist.length;
    
    // Get orders count from localStorage
    const orders = getOrders();
    document.getElementById('orderCount').textContent = orders.length;
    
    // Get addresses count from localStorage
    const addresses = JSON.parse(localStorage.getItem('userAddresses') || '[]');
    document.getElementById('addressCount').textContent = addresses.length;
    
    // Render recent orders (last 3)
    renderRecentOrders(orders.slice(0, 3));
}

function renderRecentOrders(orders) {
    const container = document.getElementById('recentOrders');
    
    if (orders.length === 0) {
        return; // Keep the empty state
    }
    
    const statusColors = {
        pending: '#f59e0b',
        processing: '#3b82f6',
        shipped: '#8b5cf6',
        delivered: '#22c55e',
        cancelled: '#ef4444'
    };
    
    container.innerHTML = orders.map(order => `
        <div class="order-item">
            <div class="order-info">
                <span class="order-id">Order #${order.id}</span>
                <span class="order-date">${new Date(order.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
            </div>
            <div class="order-summary">
                <span class="order-items">${order.items?.length || 0} item${(order.items?.length || 0) !== 1 ? 's' : ''}</span>
                <span class="order-total">$${(order.total / 100).toFixed(2)}</span>
            </div>
            <div class="order-status" style="background: ${statusColors[order.status] || statusColors.pending}">${order.status}</div>
        </div>
    `).join('');
    
    // Add styling for order items
    const style = document.createElement('style');
    style.textContent = `
        .order-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-md) var(--space-lg);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-sm);
            transition: all var(--duration-fast);
        }
        .order-item:hover {
            border-color: var(--gold);
            box-shadow: var(--shadow-sm);
        }
        .order-info { display: flex; flex-direction: column; gap: 4px; }
        .order-id { font-weight: 600; color: var(--black); }
        .order-date { font-size: 0.85rem; color: var(--gray-500); }
        .order-summary { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
        .order-items { font-size: 0.875rem; color: var(--gray-600); }
        .order-total { font-weight: 700; font-size: 1.1rem; color: var(--gold); }
        .order-status {
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: white;
        }
        @media (max-width: 640px) {
            .order-item { flex-wrap: wrap; gap: var(--space-sm); }
            .order-info { flex: 1 1 100%; }
        }
    `;
    document.head.appendChild(style);
}

loadDashboard();
console.log('[Dashboard] MyAccount dashboard loaded');
</script>

<?php require_once __DIR__ . "/_layout_end.php"; ?>
