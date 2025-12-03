const API_BASE = `http://localhost/royal-liquor/admin/api`;

/**
 * Fetches product details by ID
 */
export const fetchCartItems = async (id) => {
    try {
        const response = await fetch(`${API_BASE}/products.php?id=${id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`Error fetching products: ${response.statusText}`);
        }
        
        const body = await response.json();
        return body.data;
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
        Number(item.id) === Number(id) ? {...item, quantity: Number(newQuantity)} : item
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

        if (!response.ok) {
            throw new Error(`Error saving the cart record to db ${response.statusText}`);
        }
        
        const body = await response.json();
        return body.data;
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
            fetch(`${API_BASE}/cart-items.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    cart_id: cart_id,
                    product_id: item.product_id,
                    quantity: item.quantity,
                    price_at_add_cents: item.price_at_add_cents
                }),
                credentials: 'same-origin'
            })
        );

        const responses = await Promise.all(promises);
        
        for (let response of responses) {
            if (!response.ok) {
                throw new Error(`Error saving cart item: ${response.statusText}`);
            }
        }
        
        const results = await Promise.all(responses.map(r => r.json()));
        return results.map(r => r.data);
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
export const fetchAddresses = async (id) => {
    try {
        const response = await fetch(`${API_BASE}/addresses.php?user_id=${id}`, {
            method: 'GET',
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Error fetching user Addresses');
        }
        
        const body = await response.json();
        return body.data || [];
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
            const response = await fetch(`${API_BASE}/cart.php?user_id=${userId}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const body = await response.json();
                if (body.success && body.data) {
                    const cart = Array.isArray(body.data) ? body.data[0] : body.data;
                    if (cart && cart.id) {
                        return cart;
                    }
                }
            }
        }
        
        // Create new cart
        const response = await fetch(`${API_BASE}/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                session_id: sessionId
            }),
            credentials: 'same-origin'
        });

        if (!response.ok) {
            const body = await response.json();
            throw new Error(body.error || response.statusText);
        }
        
        const body = await response.json();
        return body.data;
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
        const response = await fetch(`${API_BASE}/cart-items.php?cart_id=${cart_id}`, {
            method: 'DELETE',
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            const body = await response.json();
            return body;
        }
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
            fetch(`${API_BASE}/cart-items.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    cart_id: cartId,
                    product_id: item.id,
                    quantity: item.quantity,
                    price_at_add_cents: item.price_cents
                }),
                credentials: 'same-origin'
            })
        );

        const responses = await Promise.all(promises);
        
        for (let response of responses) {
            if (!response.ok) {
                const errorBody = await response.json();
                throw new Error(errorBody.error || response.statusText);
            }
        }

        const results = await Promise.all(responses.map(r => r.json()));
        return results.map(r => r.data);
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
        const response = await fetch(`${API_BASE}/orders.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cart_id: cartId,
                total_cents: totalCents,
                user_id: userId,
                address_id: addressId,
                payment_method: paymentMethod
            }),
            credentials: 'same-origin'
        });

        if (!response.ok) {
            const body = await response.json();
            throw new Error(body.error || response.statusText);
        }
        
        const body = await response.json();
        return body.data;
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
            fetch(`${API_BASE}/order-items.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId,
                    product_id: item.id,
                    product_name: item.name,
                    price_cents: item.price_cents,
                    quantity: item.quantity
                }),
                credentials: 'same-origin'
            })
        );

        const responses = await Promise.all(promises);
        
        for (let response of responses) {
            if (!response.ok) {
                const errorBody = await response.json();
                throw new Error(errorBody.error || response.statusText);
            }
        }

        const results = await Promise.all(responses.map(r => r.json()));
        return results.map(r => r.data);
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
        const response = await fetch(`${API_BASE}/payments.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                order_id: orderId,
                amount_cents: amountCents,
                currency: 'USD',
                gateway: paymentMethod,
                transaction_id: transactionId,
                status: 'completed'
            }),
            credentials: 'same-origin'
        });

        if (!response.ok) {
            const body = await response.json();
            throw new Error(body.error || response.statusText);
        }
        
        const body = await response.json();
        return body.data;
    } catch (error) {
        console.error('Error creating payment:', error);
        return { error: error.message };
    }
};