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

<script type="module">
import { getCart, removeCart, saveCart  } from './utils/cart.js';
import { updateCartCount } from './header/header.js';

const cartItemsList = document.querySelector('.cart-items-list');
const cartSubtotalElement = document.getElementById('cart-subtotal');
const cartDetailsWrapper = document.querySelector('.cart-details-wrapper');
const sectionTitle = document.querySelector('.cart-title-section');
const clearBtn = document.querySelector('.clear-cart-btn')
const checkout = document.getElementById('checkout')


const renderEmpty = () => {
   
    return `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-cart-x" viewBox="0 0 16 16">
        <path d="M7.354 5.646a.5.5 0 1 0-.708.708L7.793 7.5 6.646 8.646a.5.5 0 1 0 .708.708L8.5 8.207l1.146 1.147a.5.5 0 0 0 .708-.708L9.207 7.5l1.147-1.146a.5.5 0 0 0-.708-.708L8.5 6.793z"/>
        <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zm3.915 10L3.102 4h10.796l-1.313 7zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
    </svg>`
}

const renderCartItem = (item) => {
    
    const itemTotal = (item.price * item.quantity).toFixed(2);
    
    return `
        <div class="cart-item">
            <div class="item-info">
                <span class="item-name">${item.name}</span>
                <span class="item-quantity">Quantity: ${item.quantity}</span>
            </div>
            <div class="item-price">$${itemTotal}</div>
        </div>
    `
}



const renderCart = async () => {
    const cart = await getCart();
    
   
    const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0).toFixed(2);

    if (!cart || cart.length === 0) {
        
        cartItemsList.innerHTML = '';
        cartDetailsWrapper.innerHTML = `
            <div class="empty-cart">
                <h3>Your Private Reserve is Empty</h3>
                ${renderEmpty()}
                <p class="cart-subtitle" style="font-style: italic; margin-top: 15px;">Discover our exceptional spirits.</p>
            </div>`;
        cartSubtotalElement.textContent = '$0.00';
        sectionTitle.style.display = 'none';
        return;
    }

   
    let cartHTML = '';
    cart.forEach(item => {
        cartHTML += renderCartItem(item);
    });

    cartItemsList.innerHTML = cartHTML;
    
    
    cartSubtotalElement.textContent = `$${subtotal}`;
    sectionTitle.style.display = ''; 

}

checkout.addEventListener('click',async ()=>{
    const cart = await getCart();
    if(!cart){
        return
    }
    const subtotal =cart.reduce((total, item) => total + (item.price * item.quantity), 0).toFixed(2) 
    const user_id = <?= $session->getUserId(); ?>;
    const session_id = "<?=  $session->getSessionID() ?>";
    const success = saveCart(user_id, session_id, subtotal) 
    if(success){
        removeCart()
    }
   
})

document.addEventListener('DOMContentLoaded', () => {

    clearBtn.addEventListener('click',()=>{updateCartCount();removeCart()})
    updateCartCount();
    renderCart();
    

});
</script>