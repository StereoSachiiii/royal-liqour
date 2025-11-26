const fetchCartItems = async (id) =>{

     try {
        const response =  await fetch(`http://localhost/royal-liquor/admin/api/products.php?id=${id}`,{
            method:'GET',
            headers:{
            'Content-Type':'application/json'
            },
            credentials:"same-origin"
        })

        if(!response.ok){
            throw Error(`Error fetching products : ${response.statusText}`)
        }

        const data  =  await response.json()

        return data 
    
    }catch(error){
        return{error:error}
    }

}


const addToCart = async (id) => {
    const  cartItemList = [];

   return cartItemList.push( await fetchCartItems(id))

}

const pushCartToStorage = async (cartItemList)=> {

   if(localStorage.getItem('cart') ){
    localStorage.setItem('cart',cartItemList)
    return
   }  

}