<?php require_once __DIR__.'/../../header/header.php';?>

<div class="order-history-container">
    <div class="order-header">
        <h1>My Orders</h1>
        <div class="order-summary">
            <span class="order-count">0 orders</span>
            <span class="order-total">$0.00</span>
        </div>
    </div>

    <div class="order-grid" id="orderGrid">
        <div class="order-empty-state">
            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M9 2v4m6-4v4M4 9h16M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path>
            </svg>
            <p>No orders yet</p>
        </div>
    </div>
</div>

<div class="order-modal-overlay" id="orderModalOverlay">
    <div class="order-modal">
        <div class="order-modal-header">
            <h2>Order Details</h2>
            <button class="order-close-modal" id="closeModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="order-modal-body" id="modalBody">
            <div class="order-items-list" id="orderItemsList">
                <div class="order-loading-spinner">
                    <div class="order-spinner"></div>
                </div>
            </div>
            
            <div class="order-summary-section">
                <div class="order-summary-row">
                    <span>Subtotal</span>
                    <span class="order-summary-value" id="modalSubtotal">$0.00</span>
                </div>
                <div class="order-summary-row">
                    <span>Tax</span>
                    <span class="order-summary-value" id="modalTax">$0.00</span>
                </div>
                <div class="order-summary-row total">
                    <span>Total</span>
                    <span class="order-summary-value" id="modalTotal">$0.00</span>
                </div>
            </div>
        </div>
        
        <div class="order-modal-footer">
            <button class="order-btn-secondary" id="closeOrderDetails">Close</button>
            <button class="order-btn-primary" id="trackOrderBtn">Track Order</button>
        </div>
    </div>
</div>

<script type="module">
import {fetchOrders} from '../../utils/orders.js';
import {fetchOrderItems} from '../../utils/order-items.js'
import {fetchUserAddresses, formatAddress} from '../../utils/addresses.js'
const getStatusClass = (status) => {
    const statusMap = {
        'pending': 'pending',
        'processing': 'pending',
        'shipped': 'active',
        'delivered': 'active',
        'cancelled': 'inactive'
    };
    return statusMap[status] || 'pending';
};

const renderOrders = async () => {
    const orderGrid = document.getElementById('orderGrid');
    const orderCount = document.querySelector('.order-count');
    const orderTotal = document.querySelector('.order-total');

    const orders = await fetchOrders(<?= $session->getUserId(); ?>);
    
    console.log(orders);
    
    if (orders.error || !orders || orders.length <= 0) {
        orderGrid.innerHTML = `
            <div class="order-empty-state">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 2v4m6-4v4M4 9h16M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path>
                </svg>
                <p>No orders yet</p>
            </div>
        `;
        return;
    }
    
    const totalAmount = orders.reduce((sum, order) => sum + parseFloat(order.total_cents || 0), 0);
    orderCount.textContent = `${orders.length} order${orders.length !== 1 ? 's' : ''}`;
    orderTotal.textContent = `$${(totalAmount / 100).toFixed(2)}`;
    
    let html = ''

    html = await Promise.all(orders.map(async (order) => {
        return`
        <div class="order-card" data-order-id="${order.id}">
            <div class="order-card-header">
                <span class="order-number">${order.order_number}</span>
                <span class="order-status ${getStatusClass(order.status)}">
                    ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                </span>
            </div>
            <div class="order-info">
                <div class="order-info-row">
                    <label>Order ID</label>
                    <span>#${order.id}</span>
                </div>
                <div class="order-info-row">
                    <label>Cart ID</label>
                    <span>#${order.cart_id}</span>
                </div>
                <div class="order-info-row">
                    <label>Total Amount</label>
                    <span class="order-amount">$${(parseFloat(order.total_cents || 0) / 100).toFixed(2)}</span>
                </div>
            </div>
            <div class="order-dates">
                <div class="order-date-item">
                    <label>Ordered</label>
                    <span>${new Date(order.created_at).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</span>
                </div>
                ${order.paid_at ? `
                <div class="order-date-item">
                    <label>Paid</label>
                    <span>${new Date(order.paid_at).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric'
                    })}</span>
                </div>
                ` : ''}
                ${order.shipped_at ? `
                <div class="order-date-item">
                    <label>Shipped</label>
                    <span>${new Date(order.shipped_at).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric'
                    })}</span>
                </div>
                ` : ''}
                ${order.delivered_at ? `
                <div class="order-date-item">
                    <label>Delivered</label>
                    <span>${new Date(order.delivered_at).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric'
                    })}</span>
                </div>
                ` : ''}
                ${ `
                <div class="order-date-item">
                    <label>Billing address</label>
                    <span>${await fetchUserAddresses(order.billing_address_id) }</span>
                </div>
                ` }
                ${  `
                <div class="order-date-item">
                    <label>Shipping address</label>
                    <span>${await fetchUserAddresses(order.shipping_address_id)}</span>
                </div>
                ` }
            </div>
        </div>
    `}));

    const OrderGridhtml = html.join('')
    orderGrid.innerHTML = OrderGridhtml
    document.querySelectorAll('.order-card').forEach(card => {
        card.addEventListener('click', () => {
            const orderId = card.dataset.orderId;
            openOrderModal(orderId);
        });
    });
};

const openOrderModal = async (orderId) => {
    const overlay = document.getElementById('orderModalOverlay');
    const itemsList = document.getElementById('orderItemsList');
    
    overlay.style.display = 'flex';
    overlay.offsetHeight;
    
    setTimeout(() => {
        overlay.classList.add('active');
    }, 10);
    
    document.body.style.overflow = 'hidden';
    
    itemsList.innerHTML = `
        <div class="order-loading-spinner">
            <div class="order-spinner"></div>
        </div>
    `;
    
    const items = await fetchOrderItems(orderId);
    console.log(items);
    
    if (items.error || !items || items.length === 0) {
        itemsList.innerHTML = `
            <div class="order-empty-state">
                <p>No items in this order</p>
            </div>
        `;
        document.getElementById('modalSubtotal').textContent = '$0.00';
        document.getElementById('modalTax').textContent = '$0.00';
        document.getElementById('modalTotal').textContent = '$0.00';
        return;
    }
    
    const subtotal = items.reduce((sum, item) => sum + (parseFloat(item.price_cents || 0) * item.quantity / 100), 0);
    const tax = subtotal * 0.1;
    const total = subtotal + tax;
   
    
    document.getElementById('modalSubtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('modalTax').textContent = `$${tax.toFixed(2)}`;
    document.getElementById('modalTotal').textContent = `$${total.toFixed(2)}`;
    
    itemsList.innerHTML = items.map(item => `
        <div class="order-item">
            <div class="order-item-image">
            <img src="${item.product_image_url}" >
            </div>
            <div class="order-item-details">
                <div class="order-item-name">Product #${item.product_name}</div>
                <div class="order-item-meta">Quantity: ${item.quantity} × $${(parseFloat(item.price_cents || 0)/100 ).toFixed(2)}</div>
                <div class="order-item-meta">Added: ${new Date(item.created_at).toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}</div>
            </div>
            <div class="order-item-price">$${((parseFloat(item.price_cents/100 || 0) * item.quantity) ).toFixed(2)}</div>
        </div>
    `).join('');
};


const closeOrderModal = () => {
    const overlay = document.getElementById('orderModalOverlay');
    overlay.classList.remove('active');
    
    setTimeout(() => {
        overlay.style.display = 'none';
    }, 400);
    
    document.body.style.overflow = '';
};

document.addEventListener('DOMContentLoaded', () => {
    renderOrders();
    
    document.getElementById('closeModal').addEventListener('click', closeOrderModal);
    document.getElementById('closeOrderDetails').addEventListener('click', closeOrderModal);
    
    document.getElementById('orderModalOverlay').addEventListener('click', (e) => {
        if (e.target.id === 'orderModalOverlay') {
            closeOrderModal();
        }
    });
    
    document.getElementById('trackOrderBtn').addEventListener('click', () => {
        console.log('Tracking order');
    });
});
</script>