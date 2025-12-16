<?php 
$pageName = 'checkout';
$pageTitle = 'Checkout - Royal Liquor';
require_once __DIR__ . "/components/header.php"; 

// Redirect if not logged in
$userId = $session->getUserId();
if (!$userId) {
    header('Location: ' . BASE_URL . 'auth/auth.php?redirect=checkout');
    exit;
}
?>

<main class="checkout-page">
    <div class="checkout-container container">
        <!-- Progress Steps -->
        <div class="checkout-progress">
            <div class="progress-step active" data-step="1">
                <span class="step-number">1</span>
                <span class="step-label">Shipping</span>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step" data-step="2">
                <span class="step-number">2</span>
                <span class="step-label">Payment</span>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step" data-step="3">
                <span class="step-number">3</span>
                <span class="step-label">Review</span>
            </div>
        </div>

        <div class="checkout-layout">
            <!-- Main Content -->
            <div class="checkout-main">
                <!-- Step 1: Shipping -->
                <section class="checkout-step step-1 active" id="step1">
                    <h2 class="step-title">Shipping Information</h2>
                    
                    <!-- Saved Addresses -->
                    <div class="saved-addresses-section" id="savedAddresses">
                        <h3 class="subsection-title">Your Saved Addresses</h3>
                        <div class="address-cards" id="addressCards">
                            <!-- Addresses loaded dynamically -->
                        </div>
                        <button class="btn btn-outline btn-new-address" id="addNewAddressBtn">
                            + Add New Address
                        </button>
                    </div>

                    <!-- New Address Form (hidden by default) -->
                    <div class="new-address-form" id="newAddressForm" style="display: none;">
                        <h3 class="subsection-title">Add New Address</h3>
                        <form id="addressForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="recipientName">Full Name *</label>
                                    <input type="text" id="recipientName" name="recipient_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="addressLine1">Address Line 1 *</label>
                                <input type="text" id="addressLine1" name="address_line1" placeholder="Street address" required>
                            </div>
                            <div class="form-group">
                                <label for="addressLine2">Address Line 2</label>
                                <input type="text" id="addressLine2" name="address_line2" placeholder="Apartment, suite, etc. (optional)">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City *</label>
                                    <input type="text" id="city" name="city" required>
                                </div>
                                <div class="form-group">
                                    <label for="state">State/Province *</label>
                                    <input type="text" id="state" name="state" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="postalCode">Postal Code *</label>
                                    <input type="text" id="postalCode" name="postal_code" required>
                                </div>
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text" id="country" name="country" value="Sri Lanka" readonly>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" id="cancelAddressBtn">Cancel</button>
                                <button type="submit" class="btn btn-gold">Save Address</button>
                            </div>
                        </form>
                    </div>

                    <div class="step-navigation">
                        <a href="<?= BASE_URL ?>cart.php" class="btn btn-outline">← Back to Cart</a>
                        <button class="btn btn-gold btn-lg" id="toStep2" disabled>Continue to Payment</button>
                    </div>
                </section>

                <!-- Step 2: Payment -->
                <section class="checkout-step step-2" id="step2">
                    <h2 class="step-title">Payment Method</h2>
                    
                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod" value="card">
                            <div class="payment-card">
                                <div class="payment-icon">💳</div>
                                <div class="payment-info">
                                    <span class="payment-name">Credit / Debit Card</span>
                                    <span class="payment-desc">Visa, Mastercard, Amex</span>
                                </div>
                            </div>
                        </label>
                        
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod" value="gpay">
                            <div class="payment-card">
                                <div class="payment-icon">📱</div>
                                <div class="payment-info">
                                    <span class="payment-name">Google Pay</span>
                                    <span class="payment-desc">Fast & secure</span>
                                </div>
                            </div>
                        </label>
                        
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod" value="paypal">
                            <div class="payment-card">
                                <div class="payment-icon">🅿️</div>
                                <div class="payment-info">
                                    <span class="payment-name">PayPal</span>
                                    <span class="payment-desc">Pay with PayPal account</span>
                                </div>
                            </div>
                        </label>
                        
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod" value="cod">
                            <div class="payment-card">
                                <div class="payment-icon">💵</div>
                                <div class="payment-info">
                                    <span class="payment-name">Cash on Delivery</span>
                                    <span class="payment-desc">Pay when you receive</span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Card Details (shown for card payment) -->
                    <div class="card-details-form" id="cardDetails" style="display: none;">
                        <h3 class="subsection-title">Card Details</h3>
                        <div class="form-group">
                            <label for="cardNumber">Card Number</label>
                            <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry">Expiry Date</label>
                                <input type="text" id="expiry" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" placeholder="123" maxlength="4">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cardName">Name on Card</label>
                            <input type="text" id="cardName" placeholder="John Doe">
                        </div>
                    </div>

                    <div class="step-navigation">
                        <button class="btn btn-outline" id="backToStep1">← Back</button>
                        <button class="btn btn-gold btn-lg" id="toStep3" disabled>Review Order</button>
                    </div>
                </section>

                <!-- Step 3: Review & Confirm -->
                <section class="checkout-step step-3" id="step3">
                    <h2 class="step-title">Review Your Order</h2>
                    
                    <div class="review-sections">
                        <!-- Items Review -->
                        <div class="review-block">
                            <h3 class="review-block-title">Order Items</h3>
                            <div class="review-items" id="reviewItems"></div>
                        </div>
                        
                        <!-- Shipping Review -->
                        <div class="review-block">
                            <h3 class="review-block-title">Shipping Address</h3>
                            <div class="review-address" id="reviewAddress"></div>
                            <button class="btn-edit" id="editAddress">Edit</button>
                        </div>
                        
                        <!-- Payment Review -->
                        <div class="review-block">
                            <h3 class="review-block-title">Payment Method</h3>
                            <div class="review-payment" id="reviewPayment"></div>
                            <button class="btn-edit" id="editPayment">Edit</button>
                        </div>
                    </div>

                    <div class="order-note">
                        <label for="orderNote">Order Notes (optional)</label>
                        <textarea id="orderNote" placeholder="Any special instructions for your order..."></textarea>
                    </div>

                    <div class="step-navigation">
                        <button class="btn btn-outline" id="backToStep2">← Back</button>
                        <button class="btn btn-gold btn-lg" id="placeOrderBtn">
                            Place Order
                        </button>
                    </div>
                </section>

                <!-- Success State -->
                <section class="checkout-step step-success" id="stepSuccess">
                    <div class="success-content">
                        <div class="success-icon">✓</div>
                        <h2 class="success-title">Order Placed Successfully!</h2>
                        <p class="success-order-number">Order Number: <strong id="orderNumber"></strong></p>
                        <p class="success-message">Thank you for your purchase. You will receive a confirmation email shortly.</p>
                        <div class="success-actions">
                            <a href="<?= BASE_URL ?>myaccount/orders.php" class="btn btn-gold">View My Orders</a>
                            <a href="<?= BASE_URL ?>shop.php" class="btn btn-outline">Continue Shopping</a>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Order Summary Sidebar -->
            <aside class="checkout-sidebar">
                <div class="order-summary">
                    <h3 class="summary-title">Order Summary</h3>
                    <div class="summary-items" id="summaryItems">
                        <!-- Items will be loaded dynamically -->
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="summarySubtotal">$0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span id="summaryShipping">Free</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span id="summaryTotal">$0.00</span>
                    </div>
                    <div class="secure-badge">
                        🔒 Secure Checkout
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<style>
/* Checkout Page */
.checkout-page {
    background: var(--gray-50);
    min-height: calc(100vh - 150px);
    padding: var(--space-2xl) 0 var(--space-3xl);
}

/* Progress Steps */
.checkout-progress {
    display: flex;
    align-items: center;
    justify-content: center;
    max-width: 500px;
    margin: 0 auto var(--space-2xl);
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-xs);
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    background: var(--white);
    border: 2px solid var(--gray-300);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: var(--gray-400);
    transition: all var(--duration-normal);
}

.progress-step.active .step-number,
.progress-step.completed .step-number {
    background: var(--gold);
    border-color: var(--gold);
    color: var(--black);
}

.step-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--gray-400);
}

.progress-step.active .step-label,
.progress-step.completed .step-label {
    color: var(--black);
}

.progress-line {
    flex: 1;
    height: 2px;
    background: var(--gray-300);
    margin: 0 var(--space-md);
    margin-bottom: 20px;
}

.progress-line.completed {
    background: var(--gold);
}

/* Layout */
.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: var(--space-2xl);
    align-items: start;
}

/* Main Content */
.checkout-main {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    padding: var(--space-2xl);
}

.checkout-step {
    display: none;
}

.checkout-step.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.step-title {
    font-family: var(--font-serif);
    font-size: 1.75rem;
    font-weight: 300;
    font-style: italic;
    color: var(--black);
    margin-bottom: var(--space-xl);
    padding-bottom: var(--space-md);
    border-bottom: 1px solid var(--gray-200);
}

.subsection-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--black);
    margin: var(--space-lg) 0 var(--space-md);
}

/* Address Cards */
.address-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-md);
    margin-bottom: var(--space-lg);
}

.address-card {
    padding: var(--space-lg);
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all var(--duration-fast);
    position: relative;
}

.address-card:hover {
    border-color: var(--gold);
}

.address-card.selected {
    border-color: var(--gold);
    background: rgba(212, 175, 55, 0.05);
}

.address-card.selected::after {
    content: '✓';
    position: absolute;
    top: var(--space-sm);
    right: var(--space-sm);
    width: 24px;
    height: 24px;
    background: var(--gold);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--black);
}

.address-name {
    font-weight: 600;
    color: var(--black);
    margin-bottom: var(--space-xs);
}

.address-line {
    font-size: 0.9rem;
    color: var(--gray-600);
    line-height: 1.5;
}

.address-phone {
    font-size: 0.85rem;
    color: var(--gray-500);
    margin-top: var(--space-sm);
}

.btn-new-address {
    margin-top: var(--space-md);
}

/* Form Styles */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-md);
}

.form-group {
    margin-bottom: var(--space-md);
}

.form-group label {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: var(--space-xs);
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: var(--space-md);
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: 1rem;
    transition: all var(--duration-fast);
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.15);
}

.form-actions {
    display: flex;
    gap: var(--space-md);
    margin-top: var(--space-lg);
}

/* Payment Methods */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.payment-option {
    cursor: pointer;
}

.payment-option input {
    display: none;
}

.payment-card {
    display: flex;
    align-items: center;
    gap: var(--space-lg);
    padding: var(--space-lg);
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    transition: all var(--duration-fast);
}

.payment-option:hover .payment-card {
    border-color: var(--gold);
}

.payment-option input:checked + .payment-card {
    border-color: var(--gold);
    background: rgba(212, 175, 55, 0.05);
}

.payment-icon {
    font-size: 2rem;
}

.payment-name {
    font-weight: 600;
    color: var(--black);
    display: block;
}

.payment-desc {
    font-size: 0.85rem;
    color: var(--gray-500);
}

/* Card Details Form */
.card-details-form {
    margin-top: var(--space-xl);
    padding: var(--space-lg);
    background: var(--gray-50);
    border-radius: var(--radius-lg);
}

/* Review Blocks */
.review-sections {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
}

.review-block {
    padding: var(--space-lg);
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    position: relative;
}

.review-block-title {
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--gray-500);
    margin-bottom: var(--space-md);
}

.review-item {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-sm) 0;
}

.review-item-image {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-md);
    object-fit: cover;
}

.review-item-name {
    flex: 1;
    font-weight: 500;
}

.review-item-qty {
    color: var(--gray-500);
}

.review-item-price {
    font-weight: 600;
}

.btn-edit {
    position: absolute;
    top: var(--space-lg);
    right: var(--space-lg);
    background: none;
    border: none;
    color: var(--gold);
    font-weight: 600;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-edit:hover {
    text-decoration: underline;
}

/* Order Note */
.order-note {
    margin-top: var(--space-xl);
}

.order-note textarea {
    width: 100%;
    min-height: 100px;
    resize: vertical;
}

/* Step Navigation */
.step-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: var(--space-2xl);
    padding-top: var(--space-xl);
    border-top: 1px solid var(--gray-200);
}

/* Sidebar */
.checkout-sidebar {
    position: sticky;
    top: 120px;
}

.order-summary {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    padding: var(--space-xl);
}

.summary-title {
    font-family: var(--font-serif);
    font-size: 1.25rem;
    font-weight: 600;
    font-style: italic;
    margin-bottom: var(--space-lg);
    padding-bottom: var(--space-md);
    border-bottom: 1px solid var(--gray-200);
}

.summary-items {
    max-height: 250px;
    overflow-y: auto;
    margin-bottom: var(--space-md);
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-sm) 0;
    font-size: 0.9rem;
}

.summary-item-name {
    flex: 1;
    color: var(--gray-700);
}

.summary-item-qty {
    color: var(--gray-500);
    margin: 0 var(--space-md);
}

.summary-item-price {
    font-weight: 600;
    color: var(--black);
}

.summary-divider {
    height: 1px;
    background: var(--gray-200);
    margin: var(--space-md) 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: var(--space-sm) 0;
    font-size: 0.95rem;
}

.summary-total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--black);
    padding-top: var(--space-md);
    border-top: 1px solid var(--gray-200);
    margin-top: var(--space-sm);
}

.summary-total span:last-child {
    color: var(--gold);
}

.secure-badge {
    text-align: center;
    margin-top: var(--space-lg);
    padding: var(--space-md);
    background: rgba(212, 175, 55, 0.1);
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--gray-700);
}

/* Success State */
.step-success {
    text-align: center;
    padding: var(--space-3xl) var(--space-xl);
}

.success-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto var(--space-xl);
    background: var(--success);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: var(--white);
}

.success-title {
    font-family: var(--font-serif);
    font-size: 2rem;
    font-weight: 300;
    font-style: italic;
    margin-bottom: var(--space-md);
}

.success-order-number {
    font-size: 1.1rem;
    color: var(--gray-600);
    margin-bottom: var(--space-sm);
}

.success-message {
    color: var(--gray-500);
    margin-bottom: var(--space-2xl);
}

.success-actions {
    display: flex;
    gap: var(--space-md);
    justify-content: center;
}

/* Empty Cart State */
.empty-cart-message {
    text-align: center;
    padding: var(--space-3xl);
}

.empty-cart-message h2 {
    font-family: var(--font-serif);
    font-style: italic;
    margin-bottom: var(--space-md);
}

/* Responsive */
@media (max-width: 1024px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }
    
    .checkout-sidebar {
        position: static;
        order: -1;
    }
    
    .address-cards {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .checkout-progress {
        padding: 0 var(--space-md);
    }
    
    .step-label {
        display: none;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .step-navigation {
        flex-direction: column;
        gap: var(--space-md);
    }
    
    .step-navigation .btn {
        width: 100%;
    }
    
    .success-actions {
        flex-direction: column;
    }
}
</style>

<script type="module">
import { 
    getCart, 
    removeCart, 
    fetchAddresses,
    getOrCreateCartRecord,
    syncCartItemsToDB,
    createOrder,
    createOrderItems,
    createPayment
} from './utils/cart.js';
import { updateCartCount } from './utils/header.js';

const user_id = <?= $userId ?? 'null'; ?>;
const session_id = "<?= $session->getSessionID(); ?>";

let currentStep = 1;
let cart = [];
let addresses = [];
let selectedAddressId = null;
let selectedPaymentMethod = null;
let currentCartId = null;

// DOM Elements
const steps = document.querySelectorAll('.checkout-step');
const progressSteps = document.querySelectorAll('.progress-step');
const progressLines = document.querySelectorAll('.progress-line');

// Initialize
document.addEventListener('DOMContentLoaded', async () => {
    cart = getCart();
    
    if (!cart || cart.length === 0) {
        showEmptyCart();
        return;
    }
    
    renderSummary();
    await loadAddresses();
    await syncCart();
    setupEventListeners();
});

// Show empty cart state
const showEmptyCart = () => {
    document.querySelector('.checkout-main').innerHTML = `
        <div class="empty-cart-message">
            <h2>Your cart is empty</h2>
            <p>Add some items to your cart before checking out.</p>
            <a href="<?= BASE_URL ?>shop.php" class="btn btn-gold">Browse Products</a>
        </div>
    `;
    document.querySelector('.checkout-sidebar').style.display = 'none';
    document.querySelector('.checkout-progress').style.display = 'none';
};

// Render order summary
const renderSummary = () => {
    const summaryItems = document.getElementById('summaryItems');
    summaryItems.innerHTML = cart.map(item => `
        <div class="summary-item">
            <span class="summary-item-name">${item.name}</span>
            <span class="summary-item-qty">×${item.quantity}</span>
            <span class="summary-item-price">$${(item.price_cents * item.quantity / 100).toFixed(2)}</span>
        </div>
    `).join('');
    
    const subtotal = cart.reduce((sum, item) => sum + (item.price_cents * item.quantity), 0) / 100;
    document.getElementById('summarySubtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('summaryTotal').textContent = `$${subtotal.toFixed(2)}`;
};

// Load addresses
const loadAddresses = async () => {
    if (!user_id) return;
    
    addresses = await fetchAddresses(user_id);
    renderAddressCards();
};

// Render address cards
const renderAddressCards = () => {
    const container = document.getElementById('addressCards');
    
    if (!addresses || addresses.length === 0) {
        container.innerHTML = '<p class="no-addresses">No saved addresses. Please add a new address.</p>';
        document.getElementById('newAddressForm').style.display = 'block';
        return;
    }
    
    container.innerHTML = addresses.map((addr, index) => `
        <div class="address-card ${index === 0 ? 'selected' : ''}" data-id="${addr.id}">
            <div class="address-name">${addr.recipient_name || 'Home'}</div>
            <div class="address-line">${addr.address_line1}</div>
            ${addr.address_line2 ? `<div class="address-line">${addr.address_line2}</div>` : ''}
            <div class="address-line">${addr.city}, ${addr.state} ${addr.postal_code}</div>
            <div class="address-phone">${addr.phone || ''}</div>
        </div>
    `).join('');
    
    // Select first address by default
    if (addresses.length > 0) {
        selectedAddressId = addresses[0].id;
        updateStep1Button();
    }
    
    // Add click handlers
    document.querySelectorAll('.address-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            selectedAddressId = card.dataset.id;
            updateStep1Button();
        });
    });
};

// Update step 1 button state
const updateStep1Button = () => {
    document.getElementById('toStep2').disabled = !selectedAddressId;
};

// Sync cart to database
const syncCart = async () => {
    const cartRecord = await getOrCreateCartRecord(user_id, session_id);
    if (cartRecord && !cartRecord.error) {
        currentCartId = cartRecord.id;
        await syncCartItemsToDB(cartRecord.id, cart);
    }
};

// Navigate to step
const goToStep = (step) => {
    currentStep = step;
    
    // Update progress
    progressSteps.forEach((ps, index) => {
        ps.classList.remove('active', 'completed');
        if (index + 1 < step) ps.classList.add('completed');
        if (index + 1 === step) ps.classList.add('active');
    });
    
    progressLines.forEach((line, index) => {
        line.classList.toggle('completed', index + 1 < step);
    });
    
    // Show active step
    steps.forEach(s => s.classList.remove('active'));
    document.getElementById(`step${step}`)?.classList.add('active');
    
    // Populate review if step 3
    if (step === 3) {
        populateReview();
    }
};

// Populate review step
const populateReview = () => {
    // Items
    const reviewItems = document.getElementById('reviewItems');
    reviewItems.innerHTML = cart.map(item => `
        <div class="review-item">
            <img src="${item.image_url || ''}" alt="${item.name}" class="review-item-image">
            <span class="review-item-name">${item.name}</span>
            <span class="review-item-qty">×${item.quantity}</span>
            <span class="review-item-price">$${(item.price_cents * item.quantity / 100).toFixed(2)}</span>
        </div>
    `).join('');
    
    // Address
    const address = addresses.find(a => a.id == selectedAddressId);
    if (address) {
        document.getElementById('reviewAddress').innerHTML = `
            <strong>${address.recipient_name || ''}</strong><br>
            ${address.address_line1}<br>
            ${address.address_line2 ? address.address_line2 + '<br>' : ''}
            ${address.city}, ${address.state} ${address.postal_code}<br>
            ${address.phone || ''}
        `;
    }
    
    // Payment
    const paymentLabels = {
        'card': 'Credit / Debit Card',
        'gpay': 'Google Pay',
        'paypal': 'PayPal',
        'cod': 'Cash on Delivery'
    };
    document.getElementById('reviewPayment').textContent = paymentLabels[selectedPaymentMethod] || 'Not selected';
};

// Setup event listeners
const setupEventListeners = () => {
    // Step 1 → 2
    document.getElementById('toStep2').addEventListener('click', () => {
        if (selectedAddressId) goToStep(2);
    });
    
    // Step 2 → 3
    document.getElementById('toStep3').addEventListener('click', () => {
        if (selectedPaymentMethod) goToStep(3);
    });
    
    // Back buttons
    document.getElementById('backToStep1').addEventListener('click', () => goToStep(1));
    document.getElementById('backToStep2').addEventListener('click', () => goToStep(2));
    
    // Edit buttons
    document.getElementById('editAddress').addEventListener('click', () => goToStep(1));
    document.getElementById('editPayment').addEventListener('click', () => goToStep(2));
    
    // Payment method selection
    document.querySelectorAll('input[name="paymentMethod"]').forEach(input => {
        input.addEventListener('change', (e) => {
            selectedPaymentMethod = e.target.value;
            document.getElementById('toStep3').disabled = false;
            
            // Show/hide card details
            document.getElementById('cardDetails').style.display = 
                e.target.value === 'card' ? 'block' : 'none';
        });
    });
    
    // New address form
    document.getElementById('addNewAddressBtn').addEventListener('click', () => {
        document.getElementById('savedAddresses').style.display = 'none';
        document.getElementById('newAddressForm').style.display = 'block';
    });
    
    document.getElementById('cancelAddressBtn').addEventListener('click', () => {
        document.getElementById('savedAddresses').style.display = 'block';
        document.getElementById('newAddressForm').style.display = 'none';
    });
    
    // Place order
    document.getElementById('placeOrderBtn').addEventListener('click', placeOrder);
};

// Place order
const placeOrder = async () => {
    const btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.textContent = 'Processing...';
    
    try {
        const totalCents = cart.reduce((sum, item) => sum + (item.price_cents * item.quantity), 0);
        const notes = document.getElementById('orderNote').value;
        
        // Create order
        const order = await createOrder(currentCartId, totalCents, user_id, selectedAddressId, notes);
        
        if (order.error) {
            throw new Error(order.error);
        }
        
        // Create order items
        await createOrderItems(order.id, cart);
        
        // Create payment record
        await createPayment(order.id, totalCents, selectedPaymentMethod, `TXN-${Date.now()}`);
        
        // Show success
        document.getElementById('orderNumber').textContent = order.order_number;
        steps.forEach(s => s.classList.remove('active'));
        document.getElementById('stepSuccess').classList.add('active');
        
        // Hide progress and sidebar
        document.querySelector('.checkout-progress').style.display = 'none';
        document.querySelector('.checkout-sidebar').style.display = 'none';
        
        // Clear cart
        removeCart();
        updateCartCount();
        
    } catch (error) {
        console.error('Order failed:', error);
        alert('Failed to place order. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Place Order';
    }
};
</script>

<?php require_once __DIR__ . "/footer/footer.php"; ?>
