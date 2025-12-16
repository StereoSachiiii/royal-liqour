/**
 * Cart Storage Utility
 * Handles cart operations in localStorage only
 * Single Responsibility: Local cart storage management
 */

import { fetchProduct } from './products.js';
import { showCartSlideIn } from './cart-slide-in.js';

const CART_STORAGE_KEY = 'cart';

/**
 * Get cart from localStorage
 * @returns {Array} - Cart items array
 */
export function getCart() {
    try {
        const cartData = localStorage.getItem(CART_STORAGE_KEY);
        return cartData ? JSON.parse(cartData) : [];
    } catch (error) {
        console.error('Error reading cart from storage:', error);
        return [];
    }
}

/**
 * Save cart to localStorage
 * @param {Array} cartItems - Cart items to save
 */
function saveCart(cartItems) {
    try {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartItems));
    } catch (error) {
        console.error('Error saving cart to storage:', error);
    }
}

/**
 * Add item to cart
 * @param {number|string} productId - Product ID
 * @param {number} quantity - Quantity to add
 * @param {boolean} showSlideIn - Whether to show slide-in preview
 * @returns {Promise<boolean>} - Success status
 */
export async function addItemToCart(productId, quantity = 1, showSlideIn = true) {
    try {
        const cart = getCart();
        const numId = Number(productId);
        const numQty = Number(quantity);

        // Check if item already exists
        const existingItemIndex = cart.findIndex(item => Number(item.id) === numId);
        let product;

        if (existingItemIndex !== -1) {
            // Update quantity
            cart[existingItemIndex].quantity += numQty;
            product = cart[existingItemIndex];
        } else {
            // Fetch product details
            product = await fetchProduct(numId);

            if (!product) {
                console.error(`Product ${productId} not found`);
                return false;
            }

            // Add new item
            cart.push({
                ...product,
                quantity: numQty
            });
        }

        saveCart(cart);

        // Show slide-in preview
        if (showSlideIn && product) {
            const cartTotal = cart.reduce((t, i) => t + (i.price_cents * i.quantity), 0);
            const cartCount = cart.reduce((t, i) => t + i.quantity, 0);
            showCartSlideIn(product, numQty, cartTotal, cartCount);
        }

        return true;
    } catch (error) {
        console.error('Error adding item to cart:', error);
        return false;
    }
}

/**
 * Update item quantity in cart
 * @param {number|string} productId - Product ID
 * @param {number} newQuantity - New quantity
 * @returns {boolean} - Success status
 */
export function updateItemQuantity(productId, newQuantity) {
    try {
        const cart = getCart();
        const numId = Number(productId);
        const numQty = Number(newQuantity);

        if (numQty <= 0) {
            return removeItemFromCart(productId);
        }

        const itemIndex = cart.findIndex(item => Number(item.id) === numId);

        if (itemIndex === -1) {
            console.error(`Item ${productId} not found in cart`);
            return false;
        }

        cart[itemIndex].quantity = numQty;
        saveCart(cart);
        return true;
    } catch (error) {
        console.error('Error updating item quantity:', error);
        return false;
    }
}

/**
 * Remove item from cart
 * @param {number|string} productId - Product ID
 * @returns {boolean} - Success status
 */
export function removeItemFromCart(productId) {
    try {
        const cart = getCart();
        const numId = Number(productId);
        const updatedCart = cart.filter(item => Number(item.id) !== numId);

        saveCart(updatedCart);
        return true;
    } catch (error) {
        console.error('Error removing item from cart:', error);
        return false;
    }
}

/**
 * Clear entire cart
 */
export function clearCart() {
    try {
        localStorage.removeItem(CART_STORAGE_KEY);
        return true;
    } catch (error) {
        console.error('Error clearing cart:', error);
        return false;
    }
}

/**
 * Get total item count in cart
 * @returns {number} - Total quantity of all items
 */
export function getCartItemCount() {
    const cart = getCart();
    return cart.reduce((total, item) => total + (item.quantity || 0), 0);
}

/**
 * Get cart total in cents
 * @returns {number} - Total price in cents
 */
export function getCartTotal() {
    const cart = getCart();
    return cart.reduce((total, item) => {
        return total + ((item.price_cents || 0) * (item.quantity || 0));
    }, 0);
}

/**
 * Check if product is in cart
 * @param {number|string} productId - Product ID
 * @returns {boolean} - True if in cart
 */
export function isInCart(productId) {
    const cart = getCart();
    const numId = Number(productId);
    return cart.some(item => Number(item.id) === numId);
}

/**
 * Get item quantity from cart
 * @param {number|string} productId - Product ID
 * @returns {number} - Item quantity (0 if not in cart)
 */
export function getItemQuantity(productId) {
    const cart = getCart();
    const numId = Number(productId);
    const item = cart.find(item => Number(item.id) === numId);
    return item ? item.quantity : 0;
}
