<?php require_once __DIR__.'/../../header/header.php';?>

<div class="order-history-container">
    <div class="cart-header">
        <h1>My Cart</h1>
        <div class="cart-summary">
            <span class="cart-count">0 items</span>
            <span class="cart-total">$0.00</span>
        </div>
    </div>

    <div class="cart-grid" id="cartGrid">
        <div class="empty-state">
            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <p>Your cart is empty</p>
        </div>
    </div>
</div>

<div class="cart-modal-overlay" id="cartModalOverlay">
    <div class="cart-modal">
        <div class="modal-header">
            <h2>Cart Details</h2>
            <button class="close-modal" id="closeModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="modal-body" id="modalBody">
            <div class="cart-items-list" id="cartItemsList">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                </div>
            </div>
            
            <div class="cart-summary-section">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span class="summary-value" id="modalSubtotal">$0.00</span>
                </div>
                <div class="summary-row">
                    <span>Tax</span>
                    <span class="summary-value" id="modalTax">$0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="summary-value" id="modalTotal">$0.00</span>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn-secondary" id="continueShopping">Continue Shopping</button>
            <button class="btn-primary" id="checkoutBtn">Proceed to Checkout</button>
        </div>
    </div>
</div>



<script>
// Complete fixed script - replace your entire script section with this

const renderCart = async () => {
    const cartGrid = document.getElementById('cartGrid');
    const cartCount = document.querySelector('.cart-count');
    const cartTotal = document.querySelector('.cart-total');
    
    const carts = await fetchCarts(6);
    
    if (carts.error || !carts || carts.length === 0) {
        cartGrid.innerHTML = `
            <div class="empty-state">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <p>Your cart is empty</p>
            </div>
        `;
        return;
    }
    
    const totalAmount = carts.reduce((sum, cart) => sum + parseFloat(cart.total || 0), 0);
    cartCount.textContent = `${carts.length} ${carts.length === 1 ? 'cart' : 'carts'}`;
    cartTotal.textContent = `$${totalAmount.toFixed(2)}`;
    
    cartGrid.innerHTML = carts.map(cart => `
        <div class="cart-card" data-cart-id="${cart.id}">
            <div class="cart-card-header">
                <span class="cart-id">Cart #${cart.id}</span>
                <span class="cart-status ${cart.is_active ? '' : 'inactive'}">
                    ${cart.is_active ? 'Active' : 'Inactive'}
                </span>
            </div>
            <div class="cart-info">
                <div class="cart-info-row">
                    <label>Session</label>
                    <span>${cart.session_id}</span>
                </div>
                <div class="cart-info-row">
                    <label>Items</label>
                    <span>${cart.total} items</span>
                </div>
            </div>
            <div class="cart-total-price">
                $${parseFloat(cart.total || 0).toFixed(2)}
            </div>
            <div class="cart-date">
                Created ${new Date(cart.created_at).toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric' 
                })}
            </div>
        </div>
    `).join('');
    
    document.querySelectorAll('.cart-card').forEach(card => {
        card.addEventListener('click', () => {
            const cartId = card.dataset.cartId;
            openCartModal(cartId);
        });
    });
};

const fetchCarts = async (id) => {
    try {
        const response = await fetch(`http://localhost/royal-liquor/admin/api/cart.php?user_id=${id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw Error(`Error fetching existing carts ${response.statusText}`);
        }
        const body = await response.json();
        return body.data;
    } catch (error) {
        return { error: error };
    }
};

const openCartModal = async (cartId) => {
    const overlay = document.getElementById('cartModalOverlay');
    const itemsList = document.getElementById('cartItemsList');
    
    // First set display and remove hidden class
    overlay.style.display = 'flex';
    
    // Force a reflow to ensure display change is registered
    overlay.offsetHeight;
    
    // Then add active class to trigger animation
    setTimeout(() => {
        overlay.classList.add('active');
    }, 10);
    
    document.body.style.overflow = 'hidden';
    
    itemsList.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
        </div>
    `;
    
    const items = await fetchCartItems(cartId);
    
    if (items.error || !items || items.length === 0) {
        itemsList.innerHTML = `
            <div class="empty-state">
                <p>No items in this cart</p>
            </div>
        `;
        return;
    }
    
    const subtotal = items.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);
    const tax = subtotal * 0.1;
    const total = subtotal + tax;
    
    document.getElementById('modalSubtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('modalTax').textContent = `$${tax.toFixed(2)}`;
    document.getElementById('modalTotal').textContent = `$${total.toFixed(2)}`;
    
    itemsList.innerHTML = items.map(item => `
        <div class="cart-item">
            <div class="item-image"></div>
            <div class="item-details">
                <div class="item-name">Item #${item.id}</div>
                <div class="item-meta">Session: ${item.session_id} • Qty: ${item.total}</div>
                <div class="item-meta">Updated: ${new Date(item.updated_at).toLocaleDateString()}</div>
            </div>
            <div class="item-price">$${parseFloat(item.total || 0).toFixed(2)}</div>
        </div>
    `).join('');
};

const fetchCartItems = async (cartId) => {
    try {
        const response = await fetch(`http://localhost/royal-liquor/admin/api/cart-items.php?cart_id=${cartId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw Error(`Error fetching cart items ${response.statusText}`);
        }
        const body = await response.json();
        return body.data;
    } catch (error) {
        return { error: error };
    }
};

const closeCartModal = () => {
    const overlay = document.getElementById('cartModalOverlay');
    overlay.classList.remove('active');
    
    // Wait for animation to complete before hiding
    setTimeout(() => {
        overlay.style.display = 'none';
    }, 400);
    
    document.body.style.overflow = '';
};

document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    
    document.getElementById('closeModal').addEventListener('click', closeCartModal);
    document.getElementById('continueShopping').addEventListener('click', closeCartModal);
    
    document.getElementById('cartModalOverlay').addEventListener('click', (e) => {
        if (e.target.id === 'cartModalOverlay') {
            closeCartModal();
        }
    });
    
    document.getElementById('checkoutBtn').addEventListener('click', () => {
        console.log('Proceeding to checkout');
    });
});
</script>