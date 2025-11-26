
<div>
    <div class="section-title">Browse Categories</div>
    <div class="categories-container"></div>
</div>

<div class="detail-modal detail-modal-category" id="detail-modal-category">
    <div class="detail-modal-body detail-modal-body-category" id="detail-modal-body-category"></div>
</div>

<script>

    const fetchCategories = async () => {

        try{

            const response  = await fetch("http://localhost/royal-liquor/admin/api/categories.php",{
                method:'GET',
                headers:{
                    'Content-Type':'application/json'
                },
                credentials:"same-origin"
            })

            if(!response.ok){
                throw Error(`Failed to fetch Categories ${response.statusText}:${response.status}`);
            }

            const categories = await response.json()
            if(!categories.success){
                throw Error(`Failed to fetch Categories ${categories.message}`)
            }

            return categories.data || []

        }catch(error){
            return {error:error};
        }
    }

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
                       
                        <button class="btn-details btn-details-category" data-id="${category.id}">
                             view details
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    const renderItemDetail = (category) => {
        if (!category || typeof category !== 'object' || Array.isArray(category)) {
            return `<h1>Error: Invalid category data provided.</h1>`;
        }

        const statusText = category.is_active ? 'Available' : 'Out of Stock';
        const statusClass = category.is_active ? 'status-active' : 'status-inactive';
        
        
        const imageUrl = category.image_url ;

        const created_at = category.created_at ? new Date(category.created_at).toLocaleString() : 'N/A';
        const updated_at = category.updated_at ? new Date(category.updated_at).toLocaleString() : 'N/A';
        
        return `
            <div class="detail-content-wrapper">
                
                <div class="detail-image-box">
                    // <img src="${imageUrl}" alt="${category.name || 'Category'}" class="detail-image"/>
                </div>
                
                <div class="detail-text-box">
                    <h1 class="detail-name">${category.name || 'Unknown Category'}</h1>
                    
                    <div class="detail-meta">
                        <p class="detail-id">ID: <span>#${category.id || 'N/A'}</span></p>
                        <p class="detail-status ${statusClass}">Status: <span>${statusText}</span></p>
                    </div>
                    
                    <h3 class="detail-section-title">Description</h3>
                    <p class="detail-description">${category.description || 'No detailed description provided for this category.'}</p>

                    <h3 class="detail-section-title">Timestamps</h3>
                    <div class="detail-timestamps">
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
    
    const renderGrid = async (categories) => {
        const cards = categories.map(async function(category){
            return renderCard(category)
        });
        const promiseStrings = await Promise.all(cards);
        const html = promiseStrings.join('')
        return html
    }

    document.addEventListener('DOMContentLoaded', async () => {
        const container = document.querySelector('.categories-container');
        const data = await fetchCategories();
        if(data.error){
            container.innerHTML = `<div class='message'>
            Something went wrong. Kindly visit us later!
        </div>`
        }
        if(data.length === 0){
            container.classList.remove('categories-container');
            container.innerHTML = `<div class='message'>
            There are no active products
            </div>`
        }
        const html = await renderGrid(data)
        container.innerHTML = html
    })

    document.addEventListener('click', async function(e){
        const modalCategory = document.getElementById('detail-modal-category')
        const modalBodyCategory = document.getElementById('detail-modal-body-category')
        
        if(e.target.matches('.btn-details-category')){
            const button = e.target.matches('.btn-details-category') ? e.target : e.target.closest('.btn-details-category')
            const id = button.dataset.id
            modalCategory.classList.toggle('detail-modal-active')  
            const data = await fetchCategory(id)
            const html = renderItemDetail(data)
            modalBodyCategory.innerHTML = html
            return
        }
        
        if(modalCategory.classList.contains('detail-modal-active') && !e.target.closest('.detail-modal-body-category')){ 
            modalCategory.classList.remove('detail-modal-active')
            return
        }
    })

</script>






</script>