<?php 
$pageName = 'cart';
$pageTitle = 'Your Cart - Royal Liquor';
require_once __DIR__.'/components/header.php'; 
?>

<div class="page-content-wrapper">
    <div class="cart-title-section">
        <h1 class="section-title">Your Private Reserve</h1>
        <p class="cart-subtitle">Review your selection before checkout.</p>
    </div>

    <div class="cart-details-wrapper">
        <div class="cart-items-list"></div>

        <div class="cart-summary-panel">
            <h3 class="summary-heading">Order Summary</h3>
            <div class="summary-total-placeholder">
                <span class="summary-label">Subtotal:</span>
                <span class="summary-value" id="cart-subtotal">$0.00</span>
            </div>
            <div class="summary-actions">
                <button id="checkout" class="btn-checkout">Proceed to Checkout</button>
                <div class="cart-utility-links">
                    <button class="utility-link clear-cart-btn">Clear Selection</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="checkout-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Checkout</h2>
            <button class="modal-close">&times;</button>
        </div>

        <div class="modal-steps-wrapper">
            <div class="step-progress">
                <div class="step-item active" data-step="1">Items</div>
                <div class="step-item" data-step="2">Address</div>
                <div class="step-item" data-step="3">Payment</div>
                <div class="step-item" data-step="4">Confirm</div>
            </div>

            <!-- Step 1: Review Items -->
            <div class="modal-step step-1 active">
                <h3 class="step-title">Review Your Items</h3>
                <div class="order-items-list"></div>
                <div class="order-total">
                    <span class="total-label">Total:</span>
                    <span class="total-value"></span>
                </div>
                <div class="step-actions">
                    <button class="btn-next step-1-next">Next</button>
                </div>
            </div>

            <!-- Step 2: Address -->
            <div class="modal-step step-2">
                <h3 class="step-title">Select Delivery Address</h3>
                <div class="address-section">
                    <label class="address-dropdown-label">Choose Address:</label>
                    <select class="address-dropdown"></select>
                    <button class="btn-add-new-address">Add New Address</button>
                </div>
                <div class="step-actions">
                    <button class="btn-back step-2-back">Back</button>
                    <button class="btn-next step-2-next">Next</button>
                </div>
            </div>

            <!-- Step 3: Payment -->
            <div class="modal-step step-3">
                <h3 class="step-title">Choose Payment Method</h3>
                <div class="payment-methods">
                    <label class="payment-option">
                        <input type="radio" name="payment-method" value="card" />
                        Credit / Debit Card
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment-method" value="gpay" />
                        Google Pay
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment-method" value="paypal" />
                        PayPal
                    </label>
                </div>
                <div class="step-actions">
                    <button class="btn-back step-3-back">Back</button>
                    <button class="btn-next step-3-next">Next</button>
                </div>
            </div>

            <!-- Step 4: Confirm -->
            <div class="modal-step step-4">
                <h3 class="step-title">Confirm Your Order</h3>
                <div class="confirmation-review">
                    <div class="confirm-items"></div>
                    <div class="confirm-address"></div>
                    <div class="confirm-payment-method"></div>
                </div>
                <div class="step-actions">
                    <button class="btn-back step-4-back">Back</button>
                    <button class="btn-confirm-order">Place Order</button>
                </div>
            </div>

            <!-- Success Step -->
            <div class="modal-step step-success">
                <h3 class="order-success-title">Order Placed Successfully!</h3>
                <p class="order-success-message">
                    Your order has been successfully placed and is being processed.
                </p>
                <div class="step-actions">
                    <a href="<?= BASE_URL ?>myaccount/orders" class="btn-view-orders">
                        View My Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
import { 
    getCart, 
    removeCart, 
    removeCartItem,
    fetchAddresses,
    getOrCreateCartRecord,
    syncCartItemsToDB,
    createOrder,
    createOrderItems,
    createPayment
} from './utils/cart.js';
import { updateCartCount } from './utils/header.js';

const cartItemsList = document.querySelector('.cart-items-list');
const cartSubtotalElement = document.getElementById('cart-subtotal');
const cartDetailsWrapper = document.querySelector('.cart-details-wrapper');
const sectionTitle = document.querySelector('.cart-title-section');
const clearBtn = document.querySelector('.clear-cart-btn');
const checkout = document.getElementById('checkout');
const modal = document.getElementById('checkout-modal');
const modalClose = document.querySelector('.modal-close');
const steps = document.querySelectorAll('.modal-step');
const progressItems = document.querySelectorAll('.step-item');
const nextButtons = document.querySelectorAll('.btn-next');
const backButtons = document.querySelectorAll('.btn-back');
const confirmButton = document.querySelector('.btn-confirm-order');
const addAddressButton = document.querySelector('.btn-add-new-address');
const addressSelect = document.querySelector('.address-dropdown');

let currentCartId = null;
let selectedAddress = null;
let selectedPayment = null;
let currentCart = null;

<?php $userId = $session->getUserId(); ?>
const user_id = <?= $userId ? $userId : 'null'; ?>;
const session_id = "<?= $session->getSessionID(); ?>";

const renderEmpty = () => {
    return `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-cart-x" viewBox="0 0 16 16">
        <path d="M7.354 5.646a.5.5 0 1 0-.708.708L7.793 7.5 6.646 8.646a.5.5 0 1 0 .708.708L8.5 8.207l1.146 1.147a.5.5 0 0 0 .708-.708L9.207 7.5l1.147-1.146a.5.5 0 0 0-.708-.708L8.5 6.793z"/>
        <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zm3.915 10L3.102 4h10.796l-1.313 7zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
    </svg>`;
};

const renderCartItem = (item) => {
    const itemTotal = (item.price_cents * item.quantity / 100).toFixed(2);
    
    return `
        <div class="cart-item" data-item-id="${item.id}">
            <div class="item-info">
                <span class="item-name">${item.name}</span>
                <span class="item-quantity">Quantity: ${item.quantity}</span>
            </div>
            <div class="item-actions">
                <span class="item-price">$${itemTotal}</span>
                <button class="btn-remove-item" data-item-id="${item.id}">Remove</button>
            </div>
        </div>
    `;
};

const renderCart = () => {
    const cart = getCart();
    
    if (!cart || cart.length === 0) {
        cartItemsList.innerHTML = '';
        cartDetailsWrapper.innerHTML = `
            <div class="empty-cart">
                <h3>Your Private Reserve is Empty</h3>
                ${renderEmpty()}
                <p class="cart-subtitle" style="margin-top: 15px;">Discover our exceptional spirits.</p>
                <a href="<?= BASE_URL ?>shop.php" class="btn-back-to-shopping">
                    ← Back to Shopping
                </a>
            </div>`;
        cartSubtotalElement.textContent = '$0.00';
        sectionTitle.classList.add('hidden');
        return;
    }
    
    const subtotal = cart.reduce((total, item) => 
        total + (item.price_cents * item.quantity / 100), 0
    ).toFixed(2);

    cartItemsList.innerHTML = cart.map(item => renderCartItem(item)).join('');
    cartSubtotalElement.textContent = `$${subtotal}`;
    sectionTitle.classList.remove('hidden');

    // Add remove button listeners
    document.querySelectorAll('.btn-remove-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const itemId = e.target.dataset.itemId;
            removeCartItem(itemId);
            updateCartCount();
            renderCart();
        });
    });
};

const syncCartToDB = async () => {
    const cart = getCart();
    
    if (!cart || cart.length === 0) {
        return;
    }
    
    const cartRecord = await getOrCreateCartRecord(user_id, session_id);
    
    if (cartRecord.error) {
        console.error('Failed to get/create cart:', cartRecord.error);
        return;
    }
    
    currentCartId = cartRecord.id;
    
    const result = await syncCartItemsToDB(cartRecord.id, cart);
    
    if (result.error) {
        console.error('Failed to sync cart items:', result.error);
    }
};

const setActiveStep = (step) => {
    progressItems.forEach(item => item.classList.remove('active'));
    steps.forEach(s => s.classList.remove('active'));

    if (step === 'success') {
        document.querySelector('.modal-step.step-success').classList.add('active');
    } else {
        document.querySelector(`.step-item[data-step="${step}"]`).classList.add('active');
        document.querySelector(`.modal-step.step-${step}`).classList.add('active');
        if (step === 4) {
            populateConfirm();
        }
    }
};

const populateConfirm = () => {
    const confirmItems = document.querySelector('.confirm-items');
    let html = '<h4>Items:</h4>';
    currentCart.forEach(item => {
        html += `<p>${item.name} x${item.quantity} - $${(item.price_cents / 100 * item.quantity).toFixed(2)}</p>`;
    });
    confirmItems.innerHTML = html;

    const confirmAddress = document.querySelector('.confirm-address');
    const selectedOpt = addressSelect.options[addressSelect.selectedIndex].textContent;
    confirmAddress.innerHTML = `<h4>Delivery Address:</h4><p>${selectedOpt}</p>`;

    const confirmPayment = document.querySelector('.confirm-payment-method');
    const paymentLabel = selectedPayment === 'card' ? 'Credit/Debit Card' : 
                         selectedPayment === 'gpay' ? 'Google Pay' : 'PayPal';
    confirmPayment.innerHTML = `<h4>Payment Method:</h4><p>${paymentLabel}</p>`;
};

const loadAddresses = async (id) => {
    const addressList = await fetchAddresses(id);
    
    if (!addressList || addressList.length === 0) {
        addressSelect.innerHTML = '<option value="">No addresses found</option>';
        return;
    }

    addressSelect.innerHTML = addressList.map(address => 
        `<option value="${address.id}">${address.address_line1} ${address.address_line2 || ''} ${address.city}, ${address.state}</option>`
    ).join('');
};

const openCheckout = async () => {
    const cart = getCart();
    if (!cart || cart.length === 0) {
        alert('Your cart is empty');
        return;
    }
    
    currentCart = cart;
    modal.classList.add('modal-overlay-active');

    const orderItemsList = document.querySelector('.order-items-list');
    orderItemsList.innerHTML = cart.map(item => `
        <div class="order-item">
            <span class="item-name">${item.name}</span>
            <span class="item-quantity">x${item.quantity}</span>
            <span class="item-price">$${(item.price_cents / 100 * item.quantity).toFixed(2)}</span>
        </div>
    `).join('');

    const total = cart.reduce((sum, item) => sum + item.price_cents * item.quantity, 0) / 100;
    document.querySelector('.order-total .total-value').textContent = `$${total.toFixed(2)}`;

    if (user_id) {
        await loadAddresses(user_id);
    }

    setActiveStep(1);
};

nextButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const stepStr = btn.className.match(/step-(\d)-next/)[1];
        const nextStep = parseInt(stepStr) + 1;

        if (stepStr === '2') {
            selectedAddress = addressSelect.value;
            if (!selectedAddress) {
                alert('Please select a delivery address');
                return;
            }
        } else if (stepStr === '3') {
            selectedPayment = document.querySelector('input[name="payment-method"]:checked')?.value;
            if (!selectedPayment) {
                alert('Please select a payment method');
                return;
            }
        }

        setActiveStep(nextStep);
    });
});

backButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const stepStr = btn.className.match(/step-(\d)-back/)[1];
        const prevStep = parseInt(stepStr) - 1;
        setActiveStep(prevStep);
    });
});

confirmButton.addEventListener('click', async () => {
    if (!currentCartId) {
        alert('Cart not initialized. Please try again.');
        return;
    }

    confirmButton.disabled = true;
    confirmButton.textContent = 'Processing...';

    const total_cents = currentCart.reduce((total, item) => 
        total + (item.price_cents * item.quantity), 0
    );

    // Create order
    const order = await createOrder(
        currentCartId, 
        total_cents, 
        user_id, 
        selectedAddress, 
        selectedPayment
    );
    
    if (order.error) {
        alert('Failed to create order. Please try again.');
        confirmButton.disabled = false;
        confirmButton.textContent = 'Place Order';
        return;
    }
    
    // Create order items
    const orderItems = await createOrderItems(order.id, currentCart);
    
    if (orderItems.error) {
        alert('Failed to save order items. Please contact support.');
        confirmButton.disabled = false;
        confirmButton.textContent = 'Place Order';
        return;
    }
    
    // Create payment record
    const payment = await createPayment(
        order.id,
        total_cents,
        selectedPayment,
        `TXN-${Date.now()}` // Generate transaction ID
    );
    
    if (payment.error) {
        console.error('Payment record creation failed:', payment.error);
    }
    
    // Clear cart
    removeCart();
    updateCartCount();
    currentCartId = null;

    setActiveStep('success');
    document.querySelector('.order-success-message').innerHTML += 
        `<br><strong>Order Number: ${order.order_number}</strong>`;
    
    confirmButton.disabled = false;
    confirmButton.textContent = 'Place Order';
});

addAddressButton.addEventListener('click', () => {
    window.location.href = '<?= BASE_URL ?>myaccount/addresses';
});

modalClose.addEventListener('click', () => {
    modal.classList.remove('modal-overlay-active');
    setActiveStep(1);
    selectedAddress = null;
    selectedPayment = null;
});

checkout.addEventListener('click', async () => {
    if (!user_id) {
        alert('Please log in to proceed with checkout');
        window.location.href = '<?= BASE_URL ?>auth/auth.php?redirect=checkout';
        return;
    }
    
    // Redirect to checkout page
    window.location.href = '<?= BASE_URL ?>checkout.php';
});

document.addEventListener('DOMContentLoaded', async () => {
    clearBtn.addEventListener('click', async () => {
        if (confirm('Are you sure you want to clear your cart?')) {
            removeCart();
            await updateCartCount();
            renderCart();
            currentCartId = null;
        }
    });
    
    await updateCartCount();
    renderCart();
    
    if (user_id) {
        await syncCartToDB();
    }
});
</script>

<?php require_once __DIR__.'/footer/footer.php'; ?>