<?php
require_once __DIR__ .'/header/header.php';
?>

<div class="page-content-wrapper">

    <div class="cart-title-section">
        <h1 class="section-title">Your Private Reserve</h1>
        <p class="cart-subtitle">Review your selection before checkout.</p>
    </div>

    <div class="cart-details-wrapper">
        <div class="cart-items-list">
            </div>

        <div class="cart-summary-panel">
            <h3 class="summary-heading">Order Summary</h3>

            <div class="summary-total-placeholder">
                <span class="summary-label">Subtotal:</span>
                <span class="summary-value" id="cart-subtotal">$0.00</span>
            </div>
            
            <div class="summary-actions">
                <div  id ="checkout" class="btn-checkout">Proceed to Checkout</div>

                <div class="cart-utility-links">
                    <button class="utility-link clear-cart-btn">Clear Selection</button>
                    <button class="utility-link save-cart-btn">Save for Later</button>
                    <span class="login-required-hint">login required</span>
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

           
            <div class="modal-step step-1 active">

                <h3 class="step-title">Review Your Items</h3>

                <div class="order-items-list">
                </div>

                <div class="order-total">
                    <span class="total-label">Total:</span>
                    <span class="total-value"></span>
                </div>

                <div class="step-actions">
                    <button class="btn-next step-1-next">Next</button>
                </div>
            </div>


            <div class="modal-step step-2">

                <h3 class="step-title">Select Delivery Address</h3>

                <div class="address-section">

                    <label class="address-dropdown-label">Choose Address:</label>

                    <select class="address-dropdown">
                    </select>

                    <button class="btn-add-new-address">Add New Address</button>
                </div>

                <div class="step-actions">
                    <button class="btn-back step-2-back">Back</button>
                    <button class="btn-next step-2-next">Next</button>
                </div>
            </div>

     
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

                </div>

                <div class="step-actions">
                    <button class="btn-back step-3-back">Back</button>
                    <button class="btn-next step-3-next">Next</button>
                </div>
            </div>


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


            <div class="modal-step step-success">

                <h3 class="order-success-title">Order Placed!</h3>

                <p class="order-success-message">
                    Your order has been successfully placed.
                </p>

                <a href="<?= BASE_URL ?>myaccount/orders" class="btn-view-orders">
                    View Order History
                </a>
            </div>

        </div>
    </div>
</div>


<script type="module">
import { getCart, removeCart, fetchAddresses } from './utils/cart.js';
import { updateCartCount } from './header/header.js';

const API_BASE = 'http://localhost/royal-liquor/admin/api';

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
const paymentOptions = document.querySelectorAll('input[name="payment-method"]');

let currentCartId = null;
let selectedAddress = null;
let selectedPayment = null;
let currentCart = null;
const user_id = <?= $session->getUserId() ?? 'null'; ?>;
const session_id = "<?= $session->getSessionID(); ?>";

const renderEmpty = () => {
    return `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-cart-x" viewBox="0 0 16 16">
        <path d="M7.354 5.646a.5.5 0 1 0-.708.708L7.793 7.5 6.646 8.646a.5.5 0 1 0 .708.708L8.5 8.207l1.146 1.147a.5.5 0 0 0 .708-.708L9.207 7.5l1.147-1.146a.5.5 0 0 0-.708-.708L8.5 6.793z"/>
        <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zm3.915 10L3.102 4h10.796l-1.313 7zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
    </svg>`;
}

const renderCartItem = (item) => {
    const itemTotal = (item.price_cents * item.quantity / 100).toFixed(2);
    
    return `
        <div class="cart-item">
            <div class="item-info">
                <span class="item-name">${item.name}</span>
                <span class="item-quantity">Quantity: ${item.quantity}</span>
            </div>
            <div class="item-price">$${itemTotal}</div>
        </div>
    `;
}

const renderCart = async () => {
    const cart = await getCart();
    
    if (!cart || cart.length === 0) {
        cartItemsList.innerHTML = '';
        cartDetailsWrapper.innerHTML = `
            <div class="empty-cart">
                <h3>Your Private Reserve is Empty</h3>
                ${renderEmpty()}
                <p class="cart-subtitle" style="font-style: italic; margin-top: 15px;">Discover our exceptional spirits.</p>
            </div>`;
        cartSubtotalElement.textContent = '$0.00';
        sectionTitle.classList.add('hidden');
        return;
    }
    
    const subtotal = cart.reduce((total, item) => total + (item.price_cents * item.quantity / 100), 0).toFixed(2);

    let cartHTML = '';
    cart.forEach(item => {
        cartHTML += renderCartItem(item);
    });

    cartItemsList.innerHTML = cartHTML;
    cartSubtotalElement.textContent = `$${subtotal}`;
    sectionTitle.classList.remove('hidden');
}

const getOrCreateCart = async (user_id, session_id) => {
    
    try {
        let existingCart = null;
        
        if (user_id) {
            const userCartResponse = await fetch(`${API_BASE}/cart.php?user_id=${user_id}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            
            if (userCartResponse.ok) {
                const userCartBody = await userCartResponse.json();
                
                
                if (userCartBody.success && userCartBody.data) {
                    if (Array.isArray(userCartBody.data) && userCartBody.data.length > 0) {
                        existingCart = userCartBody.data[0];
                        
                    } else if (typeof userCartBody.data === 'object' && userCartBody.data.id) {
                        existingCart = userCartBody.data;
                        
                    }
                }
            }
        }
        
        if (existingCart) {
            
            return existingCart;
        }
        
        const response = await fetch(`${API_BASE}/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: user_id,
                session_id: session_id
            }),
            credentials: 'same-origin'
        });

        
        const responseText = await response.text();
        
        
        let body;
        try {
            body = JSON.parse(responseText);
        } catch (e) {
            console.error('Response is not valid JSON:', responseText);
            throw Error('Server returned invalid JSON');
        }
        
        
        if (!response.ok) {
            throw Error(`Error creating cart: ${body.error || body.message || response.statusText}`);
        }
        
        return body.data;
    } catch (error) {
        console.error('Error in getOrCreateCart:', error);
        return { error: error };
    }
}

const clearExistingCartItems = async (cart_id) => {
    
    
    try {
        const response = await fetch(`${API_BASE}/cart-items.php?cart_id=${cart_id}`, {
            method: 'DELETE',
            credentials: 'same-origin'
        });
        
        
        if (response.ok) {
            const body = await response.json();
            
        }
    } catch (error) {
        console.error('Error clearing cart items:', error);
    }
}

const saveCartItems = async (cart_id, cartItems) => {
    
    
    await clearExistingCartItems(cart_id);
    
    try {
        const promises = cartItems.map((item, index) => {
            
            return fetch(`${API_BASE}/cart-items.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    cart_id: cart_id,
                    product_id: item.id,
                    quantity: item.quantity,
                    price_at_add_cents: item.price_cents
                }),
                credentials: 'same-origin'
            });
        });

        const responses = await Promise.all(promises);
        

        for (let response of responses) {
            if (!response.ok) {
                const errorBody = await response.json();
                
                throw Error(`Error saving cart item: ${errorBody.error || response.statusText}`);
            }
        }

        const results = await Promise.all(responses.map(r => r.json()));
        
        return results.map(r => r.data);
    } catch (error) {
        console.error('Error in saveCartItems:', error);
        return { error: error };
    }
}

const syncCartToDB = async () => {
    
    
    const cart = await getCart();
    
    
    if (!cart || cart.length === 0) {
        
        return;
    }
    
    
    
    const cartRecord = await getOrCreateCart(user_id, session_id);
    
    
    if (cartRecord.error) {
        console.error('Failed to get/create cart:', cartRecord.error);
        return;
    }
    
    currentCartId = cartRecord.id;
    
    
    const savedCartItems = await saveCartItems(cartRecord.id, cart);
    
    
    if (savedCartItems.error) {
        console.error('Failed to sync cart items:', savedCartItems.error);
        return;
    }
    
    
}
/** */
const createOrder = async (cart_id, total_cents, user_id, address_id, payment_method) => {
    
    
    try {
        const response = await fetch(`${API_BASE}/orders.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cart_id: cart_id,
                total_cents: total_cents,
                user_id: user_id,
                address_id: address_id,
                payment_method: payment_method
            }),
            credentials: 'same-origin'
        });

        
        const body = await response.json();
        
        
        if (!response.ok) {
            throw Error(`Error creating order: ${body.error || response.statusText}`);
        }
        
        return body.data;
    } catch (error) {
        console.error('Error in createOrder:', error);
        return { error: error };
    }
}

const createOrderItems = async (order_id, cartItems) => {
    
    
    try {
        const promises = cartItems.map((item, index) => {
            
            return fetch(`${API_BASE}/order-items.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: order_id,
                    product_id: item.id,
                    product_name: item.name,
                    price_cents: item.price_cents,
                    quantity: item.quantity
                }),
                credentials: 'same-origin'
            });
        });

        const responses = await Promise.all(promises);
        

        for (let response of responses) {
            if (!response.ok) {
                const errorBody = await response.json();
                
                throw Error(`Error saving order item: ${errorBody.error || response.statusText}`);
            }
        }

        const results = await Promise.all(responses.map(r => r.json()));
        
        return results.map(r => r.data);
    } catch (error) {
        console.error('Error in createOrderItems:', error);
        return { error: error };
    }
}

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
}

const populateConfirm = () => {
    const confirmItems = document.querySelector('.confirm-items');
    let html = '<h4>Items:</h4>';
    currentCart.forEach(item => {
        html += `<p>${item.name} x${item.quantity} - $${(item.price_cents / 100 * item.quantity).toFixed(2)}</p>`;
    });
    confirmItems.innerHTML = html;

    const confirmAddress = document.querySelector('.confirm-address');
    const selectedOpt = addressSelect.options[addressSelect.selectedIndex].textContent;
    confirmAddress.innerHTML = `<h4>Address:</h4><p>${selectedOpt}</p>`;

    const confirmPayment = document.querySelector('.confirm-payment-method');
    confirmPayment.innerHTML = `<h4>Payment:</h4><p>${selectedPayment.toUpperCase()}</p>`;
}

const loadAddresses = async (id) => {
    const addressList = await fetchAddresses(id)
    let html = ""

    html = addressList.map((address)=>(
        `<option class="options" value=${address.id}><p>${address.address_line1} ${address.address_line2 ?? ''} ${address.city}</p> </option>`
    ))

    addressSelect.innerHTML = html
}

const openCheckout = async () => {
    const cart = await getCart();
    if (!cart || cart.length === 0) return;
    currentCart = cart;

    modal.classList.add('modal-overlay-active');

    const orderItemsList = document.querySelector('.order-items-list');
    let html = '';
    cart.forEach(item => {
        html += `
            <div class="order-item">
                <span class="item-name">${item.name}</span>
                <span class="item-quantity">x${item.quantity}</span>
                <span class="item-price">$${ (item.price_cents / 100 * item.quantity).toFixed(2) }</span>
            </div>
        `;
    });
    orderItemsList.innerHTML = html;

    const total = cart.reduce((sum, item) => sum + item.price_cents * item.quantity, 0) / 100;
    document.querySelector('.order-total .total-value').textContent = `$${total.toFixed(2)}`;

    loadAddresses(Number.parseInt(<?= $session->getUserId() ?>));

    setActiveStep(1);
}

nextButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const stepStr = btn.className.match(/step-(\d)-next/)[1];
        const nextStep = parseInt(stepStr) + 1;

        if (stepStr === '2') {
            selectedAddress = addressSelect.value;
            if (!selectedAddress) {
                alert('Select an address');
                return;
            }
        } else if (stepStr === '3') {
            selectedPayment = document.querySelector('input[name="payment-method"]:checked')?.value;
            if (!selectedPayment) {
                alert('Select a payment method');
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
    if (!currentCartId) return;

    const confirmAddress = document.querySelector('.confirm-address');
    const selectedAddress = addressSelect.options[addressSelect.selectedIndex].value;

    const total_cents = currentCart.reduce((total, item) => total + (item.price_cents * item.quantity), 0);

    const order = await createOrder(currentCartId, total_cents, user_id, selectedAddress, selectedPayment);
    
    if (order.error) {
        return;
    }
    
    const orderItems = await createOrderItems(order.id, currentCart);
    
    if (orderItems.error) {
        return;
    }
    
    removeCart();
    updateCartCount();
    currentCartId = null;

    setActiveStep('success');
    document.querySelector('.order-success-message').innerHTML += `<br>Order Number: ${order.order_number}`;
});

addAddressButton.addEventListener('click', () => {
    alert('Add new address form');
});

modalClose.addEventListener('click', () => {
    modal.classList.remove('modal-overlay-active');
    setActiveStep(1);
});

checkout.addEventListener('click', async () => {
    await openCheckout();
});

document.addEventListener('DOMContentLoaded', async () => {
    clearBtn.addEventListener('click', async () => {
        removeCart();
        await updateCartCount();
        renderCart();
        currentCartId = null;
    });
    
    await updateCartCount();
    renderCart();
    
    await syncCartToDB();
});
</script>