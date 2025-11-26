
<div>
    <div class="section-title">Take a look at our products</div>
    <div class="products-container"></div>
</div>



<div class="detail-modal" id="detail-modal-products">
    <div class="detail-modal-body" id="detail-modal-body-products">

    </div>
</div>
<script type="module">

    import { cartAddItem, } from './utils/cart.js';
    import {updateCartCount} from './header/header.js';
    import {fetchStockLevel} from  './utils/stock.js';
    import {addToWishlist} from './utils/wishlist.js';

    const fetchAllProducts = async () => {
    try {
        const response = await fetch(`http://localhost/royal-liquor/admin/api/products.php`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: "same-origin"
        });

        if (!response.ok) {
            throw Error("Failed to fetch products")
        }

        const productsData = await response.json();

        if (!productsData.success) {
            throw Error("Failed to fetch products")

        }

        return productsData.data || [];

    } catch (error) {
        return { error: error };

    }
}

const renderProduct = async (product) => {
    const categoryId = product.category_id || 'N/A';
    const statusBadge = 
        product.total_stock>0? 
        product.total_stock>25?

        `<span class="product-badge active">${product.total_stock} in stock</span>`
        :`<span class="product-badge low">${product.total_stock} in stock </span>`
        : '<span class="product-badge inactive">Out of Stock</span>';

    const imageUrl = product.image_url;

    return `
        <div class="product-card" data-product-id="${product.id}">
            <div class="product-image-container">
                <img 
                    src="../storage/defaults/default-product.jpg" 
                    alt="${product.name}" 
                    class="product-image"
                >
                ${statusBadge}
            </div>
            
            <div class="product-info">

            <div class = "data">
                <section>
                      <h3 class="product-name">${product.name}</h3>
                    <p class="product-description">${product.description || 'No description available'}</p>
                </section>
                <span class="add-wishlist" data-id=${product.id}>
                    + wishlist
                </span>
            </div>
                
              
                
                <div class="product-meta">
                    <span class="product-price">$${parseFloat(product.price_cents).toFixed(2)}</span>
                    <span class="product-category">Cat #${categoryId}</span>
                </div>
                
                <div class="product-actions">
                    <button class="btn-add-cart" data-id="${product.id}">
                        Add to Cart
                    </button>
                    <button class="btn-details btn-details-product" data-id="${product.id}">
                        View
                        Details
                    </button>
                </div>
            </div>
        </div>
    `;
}



const renderProductGrid = async (products) => {  
    const productPromises = products.map(async (product) => {
        
        const {total_stock} = await fetchStockLevel(product.id) 
          
        return await renderProduct({...product,total_stock:Number.parseInt(total_stock)})
    });
        const productCards = await Promise.all(productPromises);
        
        let html = productCards.join("")
        return html
      

    }
const fetchProduct = async (id) => {
    try {
        const response = await fetch(`http://localhost/royal-liquor/admin/api/products.php?id=${id}`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: "same-origin"
        });

        if (!response.ok) {
            throw Error("Failed to fetch Product details")
        }

        const productData = await response.json();

        if (!productData.success) {
            throw Error("Failed to fetch Product details")

        }

        return productData.data || {};

    } catch (error) {
        return { error: error };

    }

}



const renderProductDetail = (product) => {
    if (!product || typeof product !== 'object') {
        return `<h1>Error: Invalid product data provided.</h1>`;
    }

    const statusText = product.is_active ? 'Available' : 'Out of Stock';
    const statusClass = product.is_active ? 'status-active' : 'status-inactive';
    const price = parseFloat(product.price_cents).toFixed(2);
   
    
    const imageUrl = product.image_url || '/assets/product-placeholder.png'; 

    const created_at = product.created_at ? new Date(product.created_at).toLocaleString() : 'N/A';
    const updated_at = product.updated_at ? new Date(product.updated_at).toLocaleString() : 'N/A';

    return `
        <div class="detail-content-wrapper">
            
            <div class="detail-image-box">
<img id="product-image" 
     alt="${product.name || 'Product'}" 
     class="detail-image"/>
            </div>
            
            <div class="detail-text-box">
                <h1 class="detail-name">${product.name || 'Unknown Product'}</h1>
                
                <div class="detail-meta">
                    <p class="detail-id">ID: <span>#${product.id || 'N/A'}</span></p>
                    <p class="detail-status ${statusClass}">Status: <span>${statusText}</span></p>
                </div>
                
                <h3 class="detail-section-title">Price</h3>
                <p class="detail-price-text"><span>$${price}</span></p>

                <h3 class="detail-section-title">Description</h3>
                <p class="detail-description">${product.description || 'No detailed description provided for this product.'}</p>
                
                <h3 class="detail-section-title">Details</h3>
                <div class="detail-timestamps">
                    <p>Category ID: <span>#${product.category_id || 'N/A'}</span></p>
                    <p>Created: <span>${created_at}</span></p>
                    <p>Updated: <span>${updated_at}</span></p>
                </div>
            </div>
            
            <button class="close-modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    `;
}


const viewDetails = async (productId) => {
    try {
        const data = await fetchProduct(productId);
        
        if (data.error) {
            throw data.error
        }

        const modal = document.getElementById('detail-modal-products');
        const modalBody = document.getElementById('detail-modal-body-products');

        modalBody.innerHTML = renderProductDetail(data);


    } catch (error) {
        console.error("Error loading product details:", error);
    }
}

document.addEventListener('DOMContentLoaded', async () => {

    const productsContainer = document.querySelector('.products-container');
    const productData = await fetchAllProducts();
    if (productData.error) {
        productsContainer.innerHTML = `<div class='message'>
            Something went wrong. Kindly visit us later!
        </div>
        `
        return;
    }

    if (productData.length === 0) {
        productsContainer.classList.remove('products-container');

        productsContainer.innerHTML = `<div class='message'>
            There are now products to show. Kindly visit us later!
        </div>
        `
        return;
    }
    productsContainer.innerHTML = await renderProductGrid(productData)
}
);

document.addEventListener('DOMContentLoaded', async function (e) {
    const profile = document.getElementById('profile')
    const addWishlist = document.querySelector('.add-wishlist')
    updateCartCount()

 

    const modal = document.getElementById('detail-modal-products')

    document.addEventListener('click', async (e) => {
        if (e.target.matches('.btn-details-product')) {
            const button = e.target.matches('.btn-details-product') ? e.target : e.target.closest('.btn-details-product')
            const id = button.dataset.id

            await viewDetails(id)
            modal.classList.toggle('detail-modal-active')
            return
        }

        if (modal && modal.classList.contains('detail-modal-active') && !e.target.closest('#detail-modal-body-products')) {
            modal.classList.remove('detail-modal-active')
            return

        }

        if(e.target.matches('.btn-add-cart')){
                const button = e.target.matches('.btn-add-cart') ? e.target : e.target.closest('.btn-add-cart')
                const productCard = button.closest('.product-card')   
                const id = button.dataset.id
                cartAddItem(Number.parseInt(id))
                await updateCartCount()
                return
        }
        if(e.target.matches('.add-wishlist')){

            const id = e.target.closest('.add-wishlist').dataset.id
            addToWishlist(Number.parseInt(id))      
        }
    })


})

</script>