
const API_URL = "http://localhost/royal-liquor/admin/api/categories.php";
const DEFAULT_LIMIT = 5 ;
let currentOffset = 0 ;
const fetchAllCategories = async ( limit = DEFAULT_LIMIT, offset = 0 ) => {

    const url = `${API_URL}?action=getAllCategories&limit=${limit}&offset=${offset}`;
    try{
    const response = await fetch(url, {
        method : 'GET',
        headers : {
            'Content-Type': 'application/json',
        },
        credentials:'include'
    })

    if(!response.ok){
        throw new Error(`Error fetching  categories :${response.status} status:${response.statusText}`)
    }


    const data =  await response.json()
    console.log(data);
    

    if(!data.success){
        throw new Error(`Error fetching categories ${data.message}`);
    }
    
    return data.data || [];
    }catch(error){
        console.log(error);        
        return  {error : error.message}
    }


}

/**
 * 
 * @param number id 
 * @returns []
 */
const fetchCategory = async (id) => {
  const url = `${API_URL}?action=getCategoryById&id=${id}` ;

  try{
    const response = await fetch(url, {
      method:"GET",
      headers:{
        'Content-Type':'application/json',
      },
      credentials:'same-origin'
    });

    if(!response.ok){
      throw new Error(`Error fetching  categories :${response.status} status:${response.statusText}`);
    }

    const result = await response.json();

    return result.data || [];

  }catch(error){

    return{error : error};
    
  }
}


const loadMoreCategories = async () => {
  currentOffset += DEFAULT_LIMIT;

  const categories = await fetchAllCategories(DEFAULT_LIMIT,currentOffset);

   if (categories.error) {
        return `<tr><td colspan="9" style="text-align: center; color: red; padding: 20px;">Error: ${categories.error}</td></tr>`;
    }
    
    if (categories.length === 0) {
        return `<tr><td colspan="9" style="text-align: center; padding: 20px;">No more users to load</td></tr>`;
    }
    return categories.map(category => renderCategoryRow(category)).join('');
}

  //wrapper around the fields
  /**
   * 
   * @param {*} field 
   * @returns 
   */
  const escapeHtml =(field) =>{
   const div = document.createElement('div');
   div.innerText = field;
   return div.innerHTML;
  }


/**
 * Format date to readable string
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

const renderView = async () => {

}

const renderCategoryRow = (category) =>{
    return (
    `
  
        <tr data-category-id="${category.id}">
            <td style="border: 1px solid #ddd; padding: 8px;">${category.id}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(category.name)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(category.description || '-')}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">
                <img src="${escapeHtml(category.image_url)}" style="max-width: 50px; max-height: 50px;" alt="${escapeHtml(category.name)}" />
            </td>
            <td style="border: 1px solid #ddd; padding: 8px;">${formatDate(category.created_at)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${category.updated_at ? formatDate(category.updated_at) : '-'}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center; display: flex; gap:5px">
                <button class="btn-view-category" data-id="${category.id}" style="background-color:#007bff; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; margin-right:4px;" title="View Details">
                    👁️ View
                </button>
                <a href="manage/category/edit.php?id=${category.id}" class="btn-edit-category" style="background-color:#28a745; color:white; text-decoration:none; padding:6px 12px; border-radius:4px; display:inline-block;" title="Edit Category">
                    ✏️ Edit
                </a>
            </td>
        </tr>
    `)
  }
  const tableHeaders = 
  `
   <div class="categories-table">
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Description</th><th>Image</th>
            <th>Created At</th><th>Updated At</th><th>Options</th>
          </tr>
        </thead>
  `;

/**
 * Renders Cateigories
 * @returns string 
 */
export const Categories = async () => {
  const categories = await fetchAllCategories();

  if (categories.error) {
    return `
      <div class="table">
        <p style="color: red;">Error: ${categories.error}</p>
      </div>
    `;
  }

  if (categories.length === 0) {
    return `
      <div class="table">
        <p style="color: red;">There are no active categories</p>
      </div>
    `;
  }


  // build table HTML
  return `
        ${tableHeaders}
        <tbody id="categories-table-body">
           ${categories.map((category)=>renderCategoryRow(category)).join('')}
        </tbody>
      </table>
    </div>

    ${categories.length === DEFAULT_LIMIT ? `
                <div style="margin-top: 15px; text-align: center;">
                    <button id="load-more-btn-categories" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                        Load More Categories
                    </button>
                </div>
            ` : ''}   
  `;
};



document.addEventListener('DOMContentLoaded',()=>{

  const modal = document.getElementById('modal')
  const modalBody = document.getElementById('modal-body')
  
    
document.addEventListener('click',async (e)=>{

  if(e.target.matches('.btn-view-category')||e.target.closest('.btn-view-category')){
    const view = e.target.matches('.btn-view-category') ? e.target : e.target.closest('.btn-view-category');
    const id = view.dataset.id;

    modal.classList.add('active')
    
    try{
      const category = await fetchCategory(id);
      console.log(category);
      
      modalBody.innerHTML = `
                    <div style="padding: 20px;">
                        <h2 style="margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 10px">category Details</h2>
                        
                        <div class="category-details" style="display: grid; gap: 15px;">
                            <div class="field">
                                <strong style="color: #6c757d;">ID:</strong> 
                                <span>${category.id}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Name:</strong> 
                                <span>${escapeHtml(category.name)}</span>
                            </div>

                            <div class = "field>
                                <img src="${escapeHtml(category.image_url)}" style="max-width: 50px; max-height: 50px;" alt="${escapeHtml(category.name)}" />
                            </div>


                            <div class="field">
                                <strong style="color: #6c757d;">Description:</strong> 
                                <span>${category.description ? escapeHtml(category.description) : '-'}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Created At:</strong> 
                                ${formatDate(category.created_at)}
                            </div>
                            
                            <div class="field">
                                <strong style="color: #6c757d;">Updated At:</strong> 
                                ${category.updated_at ? formatDate(category.updated_at) : '-'}
                            </div>
                            
                        </div>

                        <div style="margin-top: 20px; text-align: right;">
                            <a href="manage/category/edit.php?id=${category.id}" class="btn-edit" style="background-color:#28a745; color:white; text-decoration:none; padding:10px 20px; border-radius:4px; display:inline-block;">
                                Edit category
                            </a>
                        </div>
                    </div>
                `;

    }catch(error){
      modalBody.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: red;">
                        Failed to load user details. Please try again.
                    </div>
                `;
    }

  }
  if(e.target.id === "load-more-btn-categories" ){
        const button = e.target;
    button.disabled = true;
    button.textContent = 'Loading...';
  try{
    const loadedCategories = await loadMoreCategories();
    document.getElementById('categories-table-body').insertAdjacentHTML('beforeend',loadedCategories);
  
    const newCategory = await fetchAllCategories(DEFAULT_LIMIT, currentOffset);
    if (newCategory.length < DEFAULT_LIMIT) {
      button.disabled =true; // No more category to load
      button.innerText = 'No more Ucategory to Load'
     }else{
      button.disabled = false;
      button.textContent = 'Load More Category';
    }
  
  }catch(error){
    button.disabled = false;
    button.textContent = 'Load More Categories';
    //alert('Failed to load more categorys. Please try again.');
  }
}

})







})

