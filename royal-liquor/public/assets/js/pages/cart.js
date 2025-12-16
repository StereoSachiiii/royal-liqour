/**
 * Improved Cart Page JavaScript
 * Extracted and refactored with better UX
 */

import { getCart, removeItemFromCart, updateItemQuantity, clearCart } from '../utils/cart-storage.js';
import { updateCartCount } from '../header/header.js';

// DOM Elements
const cartItemsList = document.querySelector('.cart-items-list');
const cartSubtotal = document.getElementById('cart-subtotal');
const cartItemCount = document.getElementById('cart-item-count');
const checkoutBtn = document.getElementById('checkout-btn');
const clearCartBtn = document.getElementById('clear-cart-btn');
const toastContainer = document.querySelector('.toast-container');

// State
let currentCart = [];
let isLoading = false;

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <span>${message}</span>
    `;

    toastContainer.appendChild(toast);

    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Format price
 */
function formatPrice(cents) {
    return `$${(cents / 100).toFixed(2)}`;
}

/**
 * Render cart item HTML
 */
function renderCartItem(item) {
    const itemTotal = item.price_cents * item.quantity;

    return `
        <div class="cart-item" data-item-id="${item.id}">
            <img src="${item.image_url || '/placeholder.jpg'}" alt="${item.name}" class="cart-item-image">
            
            <div class="cart-item-details">
                <h3 class="cart-item-name">${item.name}</h3>
                <div class="cart-item-price">
                    ${formatPrice(item.price_cents)} <span class="cart-item-price-label">each</span>
                </div>
                
                <div class="quantity-controls">
                    <button class="quantity-btn decrease-btn" data-item-id="${item.id}" ${item.quantity <= 1 ? 'disabled' : ''}>
                        −
                    </button>
                    <span class="quantity-display">${item.quantity}</span>
                    <button class="quantity-btn increase-btn" data-item-id="${item.id}">
                        +
                    </button>
                </div>
            </div>
            
            <div class="cart-item-actions">
                <div class="cart-item-total">${formatPrice(itemTotal)}</div>
                <button class="remove-item-btn" data-item-id="${item.id}">
                    Remove
                </button>
            </div>
        </div>
    `;
}

/**
 * Render empty cart state
 */
function renderEmptyCart() {
    return `
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h3>Your cart is empty</h3>
            <p>Add some products to get started</p>
            <a href="shop.php" class="continue-shopping-btn">Continue Shopping</a>
        </div>
    `;
}

/**
 * Calculate cart totals
 */
function calculateTotals(cart) {
    const subtotal = cart.reduce((total, item) => total + (item.price_cents * item.quantity), 0);
    const tax = Math.round(subtotal * 0.1); // 10% tax
    const shipping = subtotal > 5000 ? 0 : 500; // Free shipping over $50
    const total = subtotal + tax + shipping;

    return { subtotal, tax, shipping, total };
}

/**
 * Update summary panel
 */
function updateSummary(cart) {
    const { subtotal, tax, shipping, total } = calculateTotals(cart);

    document.getElementById('summary-subtotal').textContent = formatPrice(subtotal);
    document.getElementById('summary-tax').textContent = formatPrice(tax);
    document.getElementById('summary-shipping').textContent = shipping === 0 ? 'FREE' : formatPrice(shipping);
    document.getElementById('summary-total').textContent = formatPrice(total);

    // Update item count
    const itemCount = cart.reduce((count, item) => count + item.quantity, 0);
    if (cartItemCount) {
        cartItemCount.textContent = `${itemCount} item${itemCount !== 1 ? 's' : ''}`;
    }
}

/**
 * Render cart
 */
function renderCart() {
    const cart = getCart();
    currentCart = cart;

    if (!cart || cart.length === 0) {
        cartItemsList.innerHTML = renderEmptyCart();
        updateSummary([]);
        checkoutBtn.disabled = true;
        return;
    }

    cartItemsList.innerHTML = cart.map(renderCartItem).join('');
    updateSummary(cart);
    checkoutBtn.disabled = false;

    // Attach event listeners
    attachItemEventListeners();
}

/**
 * Attach event listeners to cart items
 */
function attachItemEventListeners() {
    // Remove buttons
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', handleRemoveItem);
    });

    // Quantity buttons
    document.querySelectorAll('.decrease-btn').forEach(btn => {
        btn.addEventListener('click', handleDecreaseQuantity);
    });

    document.querySelectorAll('.increase-btn').forEach(btn => {
        btn.addEventListener('click', handleIncreaseQuantity);
    });
}

/**
 * Handle remove item
 */
async function handleRemoveItem(e) {
    const itemId = e.target.dataset.itemId;

    if (confirm('Remove this item from your cart?')) {
        removeCartItem(itemId);
        await updateCartCount();
        renderCart();
        showToast('Item removed from cart', 'success');
    }
}

/**
 * Handle decrease quantity
 */
async function handleDecreaseQuantity(e) {
    const itemId = e.target.dataset.itemId;
    const item = currentCart.find(i => i.id === parseInt(itemId));

    if (item && item.quantity > 1) {
        updateCartItemQuantity(itemId, item.quantity - 1);
        await updateCartCount();
        renderCart();
    }
}

/**
 * Handle increase quantity
 */
async function handleIncreaseQuantity(e) {
    const itemId = e.target.dataset.itemId;
    const item = currentCart.find(i => i.id === parseInt(itemId));

    if (item) {
        updateCartItemQuantity(itemId, item.quantity + 1);
        await updateCartCount();
        renderCart();
    }
}

/**
 * Handle clear cart
 */
async function handleClearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        clearCart();
        await updateCartCount();
        renderCart();
        showToast('Cart cleared', 'success');
    }
}

/**
 * Handle checkout
 */
async function handleCheckout() {
    const cart = getCart();

    if (!cart || cart.length === 0) {
        showToast('Your cart is empty', 'warning');
        return;
    }

    // Check if user is logged in
    const userId = document.body.dataset.userId;
    if (!userId || userId === 'null') {
        showToast('Please log in to checkout', 'warning');
        setTimeout(() => {
            window.location.href = 'auth/auth.php';
        }, 1500);
        return;
    }

    // Redirect to checkout page (to be implemented)
    showToast('Proceeding to checkout...', 'success');
    // TODO: Implement checkout flow
}

/**
 * Initialize cart page
 */
async function init() {
    // Show loading state
    cartItemsList.innerHTML = `
        <div class="cart-loading">
            <div class="loading-spinner"></div>
            <p>Loading your cart...</p>
        </div>
    `;

    // Small delay for better UX
    await new Promise(resolve => setTimeout(resolve, 300));

    // Render cart
    renderCart();

    // Update cart count in header
    await updateCartCount();

    // Attach global event listeners
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', handleCheckout);
    }

    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', handleClearCart);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Export functions for external use
export { renderCart, showToast };
