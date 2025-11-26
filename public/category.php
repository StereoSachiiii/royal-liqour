<?php
require_once __DIR__ . '/header/header.php';








?>

<section class="categories">
  <div class="category-hero">
    <h2 class="category-name"></h2>
    <p class="category-description"></p>
    <p class="category-meta"></p>
    <p class="category-dates">
    </p>
  </div>

  <div>
    <div class="section-title">Browse Categories</div>
    <div class="categories-container"></div>
</div>
</section>



<script type="module">
    import {updateCartCount} from './header/header.js'
    const categoryHero = document.querySelector('.category-hero')

    const name = document.querySelector('.category-name')
    const description = document.querySelector('.category-description')
    const meta = document.querySelector('.category-meta')
    const dates = document.querySelector('.category-dates')

    const categoryId = <?= $_GET['id'] ?>

    const fetchCategory = async (id) => {
        try{
            const response  = await fetch(`http://localhost/royal-liquor/admin/api/categories.php?id=${id}`,{
                method:'GET',
                headers:{
                    'Content-Type':'application/json'
                },
                credentials:"same-origin"
            })
            if(!response.ok){
                throw Error(`Failed to fetch Category ${response.statusText}:${response.status}`);
            }
            const categories = await response.json()
            if(!categories.success){
                throw Error(`Failed to fetch Category ${categories.message}`)
            }
            return categories.data || []
        }catch(error){
            return {error:error};
        }
    }

     const renderCard = async (category) => {
        const categoryId = category.category_id || 'N/A';
        const statusBadge = category.is_active 
            ? '<span class="catergory-badge active">In Stock</span>'
            : '<span class="catergory-badge inactive">Out of Stock</span>';
        
        const imageUrl = category.image_url
        
        return `
            <div class="category-card" data-category-id="${category.id}">
                <div class="category-image-container">
                   
                    ${statusBadge}
                </div>
                
                <div class="category-info">
                    <h3 class="category-name">${category.name}</h3>
                    
                    <p class="category-description">${category.description || 'No description available'}</p>
                    

                    
                    <div class="category-actions">
                        
                        <button class="btn-add-cart" >
                        <a href="<?= BASE_URL ?>category.php?id=${category.id}" target="_blank" id="browse">
                            browse available products
                        </a>   
                        </button>
                       
                        <button class="btn-details" data-id="${category.id}">
                             view details
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    const renderGrid  = async (categories) =>{

        const cards = categories.map(async function(category){
            return await renderCard(category)
        });
        const promiseStrings = await Promise.all(cards);
        const html = promiseStrings.join('')
        return html

    }

    const fetchAllProducts =  async (id) => {
    try{
    const response = await fetch(`http://localhost/royal-liquor/admin/api/products.php?getProductsByCategory=${id}`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials:"same-origin"
        });

        if(!response.ok){
            throw Error("Failed to fetch products")
        }

        const productsData =  await response.json();

        if(!productsData.success){
            throw Error("Failed to fetch products")

        }

        return productsData.data || [];
        
    }catch(error){
        return {error:error};

    }
}
    const loadCategoryDetails = async (id) => {
        try{
            const data = await fetchProducts(categoryId)
            if(data.error){
                throw data.error
            }
            const category = data.name
            const categoryDescription = data.description
            const isActive = data.is_active ? "Yes" : "No"
            const categoryMeta = `ID: ${data.id} | Active: ${isActive}`
            const categoryDates = `Created: ${data.created_at} | Updated: ${data.updated_at}`
            name.textContent = category
            description.textContent = categoryDescription
            meta.textContent = categoryMeta
            dates.textContent = categoryDates
        }catch(error){
            console.error(error)
        }
    }

    loadCategoryDetails(categoryId)

    document.addEventListener('DOMContentLoaded', async () => {
        updateCartCount();
        const container = document.querySelector('.categories-container');
        const data = await fetchAllProducts(categoryId);
       console.log(data);
        if(data.error){
            console.error(data.error)
            container.innerHTML = `<p class="error-message">Failed to load categories. Please try again later.</p>`;
            return;
        }
        const html = await renderGrid(data);
        container.innerHTML = html;
       
    });



</script>