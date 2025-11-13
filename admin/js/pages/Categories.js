
const API_URL = "http://localhost/royal-liquor/admin/api/categories.php";
const DEFAULT_LIMIT = 50 ;
const fetchAllCategories = async ( limit = 50, offset = 0 ) => {

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

    return data.categories || [];
    }catch(error){
        console.log(error);        
        return  {error : error.message}
    }


}



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
    <div class="users-table">
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Description</th><th>Image</th>
            <th>Created At</th><th>Updated At</th><th>Options</th>
          </tr>
        </thead>
        <tbody id="users-table-body">
          ${categories
            .map(
              (category) => `
              <tr>
                <td>${category.id}</td>
                <td>${category.name}</td>
                <td>${category.description}</td>
                <td>${category.imageUrl || '-'}</td>
                <td>${new Date(category.createdAt).toLocaleString()}</td>
                <td>${new Date(category.updatedAt).toLocaleString()}</td>
                <td><a href="manage.php"><button class="options-btn" data-id="${category.id}">Options</button></a></td>
              </tr>`
            )
            .join('')}
        </tbody>
      </table>
    </div>

 

  
       
    
  `;
};
