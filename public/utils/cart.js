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
        
        return body.data
        ;
    }catch(error){
        return{error:error}
    }
}


const addToCart = async (id, quantity = 1, prevCartJson) => {
    let prevCart = []
    try{
        prevCart = prevCartJson ? JSON.parse(prevCartJson) : []
    }catch(error){
        prevCart = []
    }
    const newItem = await fetchCartItems(id);

    const existingIndex = prevCart.findIndex((item)=>(item.id === id)) 
    
    if(existingIndex !== -1){
        const newCart = prevCart.map((item, index)=>(
            index === existingIndex ? {...item,quantity:item.quantity + quantity} : item
        ))
        return newCart      
    }
    
    if(newItem){
        newItem.quantity = quantity 
        return [...prevCart,newItem]
    }
    
}

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
    return cart.reduce((sum,currentVal)=>(sum+currentVal.quantity),0)
        
}

export const getCart = () => {
    return JSON.parse(localStorage.getItem('cart'))
}

export const removeCart = () => {
    localStorage.removeItem('cart')
    
}

export const parseCart = async (cart_id) => {
    const cart = await getCart();
    return {
        cart_id:cart_id,
        is_active:true,
        cart_items:cart.map((cartItem)=>({
            product_id:cartItem.id,
            quantity:cartItem.quantity,
            price_at_add:cartItem.price
        }))
    }
}

export const saveCartToDB = async (user_id, session_id, total) => {
    try{
        const response = await fetch(`http://localhost/royal-liquor/admin/api/cart.php`,{
            method:'POST',
            headers:{
                'Content-Type':'application/json'
            },
            body:JSON.stringify({
                user_id:user_id,
                session_id:session_id,
                total:total
            }),
            credentials:'same-origin'
        })

        if(!response.ok){
            throw Error(`Error saving the card record to db ${response.statusText}`);
        }
        const body = await response.json()
        return body.data

    }catch(error){
        return {error:error}
    }

}

export const saveCartItems = async (parsedCart) =>{
    try{
        const response = await fetch(`http://localhost/royal-liquor/admin/api/cart-items.php`,{
            method:'POST',
            headers:{
                'Content-Type':'application/json'
            },
            body:JSON.stringify(parsedCart),
            credentials:'same-origin'
        })

        if(!response.ok){
            throw Error(`Error saving the card record to db ${response.statusText}`);
        }
        const body = await response.json()
        return body.data

    }catch(error){
        return {error:error}
    }

}

export const saveCart = async (user_id, session_id, total) => {
    const cart = await saveCartToDB(user_id, session_id, total)
    
    const parsedCart = await parseCart(cart.id)
    
    return saveCartItems(parsedCart)
}