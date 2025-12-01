const API_BASE = `http://localhost/royal-liquor/admin/api/`;

export const fetchCartItems = async (id) =>{
    try {
        const response = await fetch(`http://localhost/royal-liquor/admin/api/products.php?id=${id}`,{
            method:'GET',
            headers:{
            'Content-Type':'application/json'
            },
            credentials:"same-origin"
        })

        if(!response.ok){
            throw Error(`Error fetching products : ${response.statusText}`)
        }
        const body = await response.json() 
        
        return body.data;
    }catch(error){
        return{error:error}
    }
}


const addToCart = async (id, quantity = 1, prevCartJson) => {
    let prevCart = [];

    try {
        prevCart = prevCartJson ? JSON.parse(prevCartJson) : [];
    } catch (error) {
        prevCart = [];
    }

    id = Number(id); 

    const newItem = await fetchCartItems(id);

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


const updateCartQuantity = (id, newQuantity, prevCartJson) => {
    let prevCart = []
    try {
        prevCart = prevCartJson ? JSON.parse(prevCartJson) : []
    } catch (error) {
        prevCart = []
    }

    const newCart = prevCart.map(item => (
        item.id === id ? {...item, quantity: newQuantity} : item
    ));

    return newCart;
}


const pushCartToStorage = (cartItemList)=> {
    const cartJson = JSON.stringify(cartItemList)
    localStorage.setItem('cart', cartJson)
}

export const cartAddItem = async (id, quantity = 1) => {
    const prevCartJson = localStorage.getItem('cart')
    const newCart = await addToCart(id, quantity, prevCartJson ) 
    
    if (newCart) {
        pushCartToStorage(newCart); 
    }
}

export const cartUpdateItemQuantity = (id, quantity) => {
    const prevCartJson = localStorage.getItem('cart');
    const newCart = updateCartQuantity(id, quantity, prevCartJson);

    if (newCart) {
        pushCartToStorage(newCart);
    }
}

export const getCartCount = () => {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const count = cart.reduce((sum,currentVal)=>(sum+currentVal.quantity),0)
    return  count>0 ? count : ''
        
}

export const getCart = () => {
    return JSON.parse(localStorage.getItem('cart'))
}

export const removeCart = () => {
    localStorage.removeItem('cart')
    
}


export const parseCart = (cart) => {
    return cart.map((cartItem)=>({
        product_id: cartItem.id,
        quantity: cartItem.quantity,
        price_at_add_cents: cartItem.price  
    }));
}

export const saveCartToDB = async (user_id, session_id) => {
    try{
        const response = await fetch(`http://localhost/royal-liquor/admin/api/cart.php`,{
            method:'POST',
            headers:{
                'Content-Type':'application/json'
            },
            body:JSON.stringify({
                user_id: user_id,
                session_id: session_id
            }),
            credentials:'same-origin'
        })

        if(!response.ok){
            throw Error(`Error saving the cart record to db ${response.statusText}`);
        }
        const body = await response.json()
        return body.data

    }catch(error){
        return {error:error}
    }
}

export const saveCartItems = async (cart_id, cartItems) =>{
    try{
        const promises = cartItems.map(item =>
            fetch(`http://localhost/royal-liquor/admin/api/cart-items.php`,{
                method:'POST',
                headers:{
                    'Content-Type':'application/json'
                },
                body:JSON.stringify({
                    cart_id: cart_id,
                    product_id: item.product_id,
                    quantity: item.quantity,
                    price_at_add_cents: item.price_at_add_cents
                }),
                credentials:'same-origin'
            })
        );

        const responses = await Promise.all(promises);
        
        // Check if all requests succeeded
        for(let response of responses) {
            if(!response.ok){
                throw Error(`Error saving cart item: ${response.statusText}`);
            }
        }
        
        // Parse all responses
        const results = await Promise.all(responses.map(r => r.json()));
        return results.map(r => r.data);

    }catch(error){
        return {error:error}
    }
}

// FIX 5: Updated to match new function signatures
export const saveCart = async (user_id, session_id) => {
    const cart = await saveCartToDB(user_id, session_id)
    
    if(cart.error) {
        return cart; 
    }
    
    const localCart = getCart();
    const parsedCart = parseCart(localCart);
    
    return saveCartItems(cart.id, parsedCart)
}


export const fetchAddresses = async (id ) => {

    try{
        const response = await fetch(`${API_BASE}/addresses.php?user_id=${id}`)

        if(!response.ok){
            throw Error(`Error fetching user Addresses`);
        }
        const body = await response.json()

        return body.data
    }catch(error){
        return{error:error}
    }
}