import { API_URL_CATEGORIES, API_URL_PRODUCTS ,DETAIL_VIEW_API_URL} from "../config.js";

/**
 * Fetch categories list.
 * @param {number} limit
 * @param {number} offset
 * @param {string} q optional search query
 * @returns {Promise<Array|{error:string}>}
 */
export const fetchAllCategories = async ( limit = 50, offset = 0, q = '' ) => {
    const url = `${API_URL_CATEGORIES}?action=getAllCategories&limit=${encodeURIComponent(limit)}&offset=${encodeURIComponent(offset)}${q ? '&q=' + encodeURIComponent(q) : ''}`;
    try{
        const response = await fetch(url, {
            method : 'GET',
            headers : {
                'Content-Type': 'application/json',
            },
            credentials:'include'
        });
        if(!response.ok){
            throw new Error(`Error fetching categories :${response.status} status:${response.statusText}`);
        }
        const data =  await response.json();
        if(!data.success){
            throw new Error(`Error fetching categories ${data.message || 'unknown'}`);
        }  
        return data.data || [];
    }catch(error){
        console.error(error);        
        return  { error : error.message || String(error) };
    }
}

/**
 * Fetch single category by ID
 * @param {number} id
 * @returns {Promise<Object|{error:string}>}
 */
export const fetchCategory = async (id) => {
  const url = `${API_URL_CATEGORIES}?action=getCategoryById&id=${encodeURIComponent(id)}` ;

  try{
    const response = await fetch(url, {
      method:"GET",
      headers:{
        'Content-Type':'application/json',
      },
      credentials:'same-origin'
    });

    if(!response.ok){
      throw new Error(`Error fetching category :${response.status} status:${response.statusText}`);
    }

    const result = await response.json();

    return result.data || [];

  }catch(error){
    console.error(error);
    return { error : error.message || String(error) };
  }
}

/**
 * Fetch category details for modal view
 * @param {number} categoryId
 * @returns {Promise<{success:boolean,category:Object}|{error:string}>}
 */
export async function fetchModalDetails(categoryId) {
    try {
        const response = await fetch(`${DETAIL_VIEW_API_URL}?entity=categories&id=${encodeURIComponent(categoryId)}`, {
            method: 'GET',
            credentials: 'include'
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            
            if (response.status === 401) {
                window.location.href = '/royal-liquor/public/auth/auth.php';
                return { error: 'Please login to continue' };
            }
            
            if (response.status === 403) {
                return { error: 'Access denied. Admin privileges required.' };
            }
            
            if (response.status === 404) {
                return { error: 'Category not found' };
            }
            
            throw new Error(errorData.message || 'Failed to fetch category');
        }

        const data = await response.json();
       
        return { success: true, category : data.data.data};
        
    } catch (error) {
        console.error('Error fetching category:', error);
        return { error: error.message || String(error) };
    }
}
