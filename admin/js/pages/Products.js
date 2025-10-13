const API_URL = 'http://localhost/royal-liquor/admin/api/products.php';
const DEFAULT_LIMIT = 50;

let currentOffset = 0;

const fetchAllProducts = async (limit = DEFAULT_LIMIT , Offset = 0)=>{

    try{
        const response = await fetch(`${API_URL}?action=getAllProducts&limit=${limit}&offset=${Offset}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include' 
        })

        console.log(response);

         if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const products = await response.json();
        console.log(products);

        if (!products.success){
            throw new Error(products.message|"failed to fetch products")
        }

        return products.data
    }catch(error){
        console.log(error);
        return({error:error.message});
    }

}



const loadMoreProducts =async ()=> {
    currentOffset += DEFAULT_LIMIT;
    const products = await fetchAllProducts(DEFAULT_LIMIT, currentOffset);
    if (products.error) {
        return `<div class="products-table-error">Error: ${products.error}</div>`;
    }

    if (products.length === 0) {
        return '<div> no products to show </div>';
    }

    return products.map(product => `
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">${product.id}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${product.name}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${product.email}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${product.phone || '-'}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${product.is_active ? 'Yes' : 'No'}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${product.is_admin ? 'Yes' : 'No'}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${new Date(product.created_at).toLocaleString()}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${product.last_login_at ? new Date(user.last_login_at).toLocaleString() : '-'}</td>
        </tr>
    `).join('')

}

export const Products = async () => {

    currentOffset=0;
    const products = await fetchAllProducts(DEFAULT_LIMIT,currentOffset);

    if(products.error){
        return `<div style="color:red;"> ${products.error} <div>`
    }

    if(products.length===0){
        return `<div style="color:red;">there are no active users<div>`
    }

    return `
         <div class="table">
            <table style="border-collapse: collapse; width: 100%; border: 1px solid #ddd;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">ID</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Name</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Description</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Price</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Image path</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Category name</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Supplier name</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Created at</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">updated at</th>
                    </tr>
                </thead>
                                <tbody id="table-body">

            ${products.map(product=>
                `
                    <tr style="background-color: #f2f2f2;">
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${product.id}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${product.name}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${product.description}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${product.price}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${product.image_url}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${product.category_name}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${product.supplier_name}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${product.created_at}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${product.updated_at}</td>
                    </tr>



                
                `
               
            )
            }
             </tbody>
             ${products.length === DEFAULT_LIMIT ? `
                <button onclick="loadMoreProducts().then(html => document.getElementById('table-body').insertAdjacentHTML('beforeend', html))"
                        style="margin-top: 10px; padding: 8px 16px; cursor: pointer;">
                    Load More
                </button>
            ` : ''}

    `
}

window.loadMoreProducts =loadMoreProducts