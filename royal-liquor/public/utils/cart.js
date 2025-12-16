/**
 * Cart Utility
 * Uses centralized API helper for backend calls
 */

import { API } from './api-helper.js';

const API_BASE = '/royal-liquor/api/v1';

/**
 * Fetches product details by ID
 */
export const fetchCartItems = async (id) => {
    try {
        const response = await API.products.get(id);
        return response.data;
    } catch (error) {
        console.error('Error in fetchCartItems:', error);
        return { error: error };
    }
};

/**
 * Adds item to cart (internal helper)
 */
const addToCart = async (id, quantity = 1, prevCartJson) => {
    let prevCart = [];

    try {
        prevCart = prevCartJson ? JSON.parse(prevCartJson) : [];
    } catch (error) {
        prevCart = [];
    }

    id = Number(id);
    const newItem = await fetchCartItems(id);

    if (newItem.error) {
        return prevCart;
    }

    const existingIndex = prevCart.findIndex(item => Number(item.id) === id);

    if (existingIndex !== -1) {
        prevCart[existingIndex].quantity += Number(quantity);
        return [...prevCart];
    }

    if (newItem) {
        newItem.quantity = Number(quantity);
        return [...prevCart, newItem];
    }

    return prevCart;
};

/**
 * Updates cart quantity (internal helper)
 */
const updateCartQuantity = (id, newQuantity, prevCartJson) => {
    let prevCart = [];
    try {
        prevCart = prevCartJson ? JSON.parse(prevCartJson) : [];
    } catch (error) {
        prevCart = [];
    }

    const newCart = prevCart.map(item => (
        Number(item.id) === Number(id) ? { ...item, quantity: Number(newQuantity) } : item
    ));

    return newCart;
};

/**
 * Saves cart to localStorage (internal helper)
 */
const pushCartToStorage = (cartItemList) => {
    const cartJson = JSON.stringify(cartItemList);
    localStorage.setItem('cart', cartJson);
};

/**
 * Gets cart from localStorage
 */
export const getCart = () => {
    try {
        return JSON.parse(localStorage.getItem('cart')) || [];
    } catch (error) {
        console.error('Error reading cart from localStorage:', error);
        return [];
    }
};

/**
 * Adds item to cart in localStorage
 */
export const cartAddItem = async (id, quantity = 1) => {
    const prevCartJson = localStorage.getItem('cart');
    const newCart = await addToCart(id, quantity, prevCartJson);

    if (newCart) {
        pushCartToStorage(newCart);
    }
};

/**
 * Updates quantity of an item in cart
 */
export const cartUpdateItemQuantity = (id, quantity) => {
    const prevCartJson = localStorage.getItem('cart');
    const newCart = updateCartQuantity(id, quantity, prevCartJson);

    if (newCart) {
        pushCartToStorage(newCart);
    }
};

// Alias for better naming consistency
export const updateCartItemQuantity = cartUpdateItemQuantity;

/**
 * Removes a single item from cart
 */
export const removeCartItem = (id) => {
    const cart = getCart();
    const updatedCart = cart.filter(item => Number(item.id) !== Number(id));
    pushCartToStorage(updatedCart);
};

/**
 * Clears entire cart
 */
export const removeCart = () => {
    localStorage.removeItem('cart');
};

/**
 * Gets total item count in cart
 */
export const getCartCount = () => {
    const cart = getCart();
    const count = cart.reduce((sum, currentVal) => (sum + currentVal.quantity), 0);
    return count > 0 ? count : '';
};

/**
 * Parses cart for database format
 */
export const parseCart = (cart) => {
    return cart.map((cartItem) => ({
        product_id: cartItem.id,
        quantity: cartItem.quantity,
        price_at_add_cents: cartItem.price_cents
    }));
};

/**
 * Saves cart record to database
 */
export const saveCartToDB = async (user_id, session_id) => {
    try {
        const response = await API.cart.create({
            user_id: user_id,
            session_id: session_id
        });
        return response.data;
    } catch (error) {
        console.error('Error in saveCartToDB:', error);
        return { error: error };
    }
};

/**
 * Saves cart items to database
 */
export const saveCartItems = async (cart_id, cartItems) => {
    try {
        const promises = cartItems.map(item =>
            API.cart.addItem({
                cart_id: cart_id,
                product_id: item.product_id,
                quantity: item.quantity,
                price_at_add_cents: item.price_at_add_cents
            })
        );

        const responses = await Promise.all(promises);
        return responses.map(r => r.data);
    } catch (error) {
        console.error('Error in saveCartItems:', error);
        return { error: error };
    }
};

/**
 * Saves complete cart (record + items) to database
 */
export const saveCart = async (user_id, session_id) => {
    const cart = await saveCartToDB(user_id, session_id);

    if (cart.error) {
        return cart;
    }

    const localCart = getCart();
    const parsedCart = parseCart(localCart);

    return saveCartItems(cart.id, parsedCart);
};

/**
 * Fetches user addresses
 */
export const fetchAddresses = async (userId) => {
    try {
        const response = await API.addresses.list(userId);
        return response.data || [];
    } catch (error) {
        console.error('Error in fetchAddresses:', error);
        return { error: error };
    }
};

/**
 * Gets or creates a cart record in database
 */
export const getOrCreateCartRecord = async (userId, sessionId) => {
    try {
        // Try to get existing cart
        if (userId) {
            try {
                const response = await API.request(`/cart?user_id=${userId}`);
                if (response.success && response.data) {
                    const cart = Array.isArray(response.data) ? response.data[0] : response.data;
                    if (cart && cart.id) {
                        return cart;
                    }
                }
            } catch (e) {
                // No existing cart, create new one
            }
        }

        // Create new cart
        const response = await API.cart.create({
            user_id: userId,
            session_id: sessionId
        });

        return response.data;
    } catch (error) {
        console.error('Error in getOrCreateCartRecord:', error);
        return { error: error.message };
    }
};

/**
 * Clears existing cart items from database
 */
export const clearExistingCartItems = async (cart_id) => {
    try {
        const response = await API.request(`/cart-items?cart_id=${cart_id}`, {
            method: 'DELETE'
        });
        return response;
    } catch (error) {
        console.error('Error clearing cart items:', error);
    }
};

/**
 * Syncs cart items to database
 */
export const syncCartItemsToDB = async (cartId, cartItems) => {
    try {
        // Clear existing cart items
        await clearExistingCartItems(cartId);

        // Save new items
        const promises = cartItems.map(item =>
            API.cart.addItem({
                cart_id: cartId,
                product_id: item.id,
                quantity: item.quantity,
                price_at_add_cents: item.price_cents
            })
        );

        const responses = await Promise.all(promises);
        return responses.map(r => r.data);
    } catch (error) {
        console.error('Error syncing cart items:', error);
        return { error: error.message };
    }
};

/**
 * Creates an order in the database
 */
export const createOrder = async (cartId, totalCents, userId, addressId, paymentMethod) => {
    try {
        const response = await API.orders.create({
            cart_id: cartId,
            total_cents: totalCents,
            user_id: userId,
            address_id: addressId,
            payment_method: paymentMethod
        });
        return response.data;
    } catch (error) {
        console.error('Error creating order:', error);
        return { error: error.message };
    }
};

/**
 * Creates order items in the database
 */
export const createOrderItems = async (orderId, cartItems) => {
    try {
        const promises = cartItems.map(item =>
            API.request('/order-items', {
                method: 'POST',
                body: {
                    order_id: orderId,
                    product_id: item.id,
                    product_name: item.name,
                    price_cents: item.price_cents,
                    quantity: item.quantity
                }
            })
        );

        const responses = await Promise.all(promises);
        return responses.map(r => r.data);
    } catch (error) {
        console.error('Error creating order items:', error);
        return { error: error.message };
    }
};

/**
 * Creates a payment record
 */
export const createPayment = async (orderId, amountCents, paymentMethod, transactionId = null) => {
    try {
        const response = await API.payments.create({
            order_id: orderId,
            amount_cents: amountCents,
            currency: 'USD',
            gateway: paymentMethod,
            transaction_id: transactionId,
            status: 'completed'
        });
        return response.data;
    } catch (error) {
        console.error('Error creating payment:', error);
        return { error: error.message };
    }
};