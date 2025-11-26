const API_URL = 'http://localhost/royal-liquor/admin/api/products.php';
const DEFAULT_LIMIT = 50;

let currentOffset = 0;

/**
 * Fetch products from API with proper error handling
 * @param {number} limit - Number of products to fetch
 * @param {number} offset - Offset for pagination
 * @returns {Promise<Array|Object>} Array of products or error object
 */
async function fetchAllProducts(limit = DEFAULT_LIMIT, offset = 0) {
    try {
        const response = await fetch(`${API_URL}?action=getAllProducts&limit=${limit}&offset=${offset}`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            const errorData = await response.text().catch(() => ({}));
            
            if (response.status === 401) {
                window.location.href = '/royal-liquor/public/auth/auth.php';
                return { error: 'Please login to continue' };
            }
            
            if (response.status === 403) {
                return { error: 'Access denied. Admin privileges required.' };
            }
            
            throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to fetch products');
        }

        return data.data || [];
        
    } catch (error) {
        console.error('Error fetching products:', error);
        return { error: error.message };
    }
}

/**
 * Fetch single product by ID
 * @param {number} productId - Product ID
 * @returns {Promise<Object>} Product data or error
 */
async function fetchProductById(productId) {
    try {
        const response = await fetch(`${API_URL}?action=getProductById&productId=${productId}`, {
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
                return { error: 'Product not found' };
            }
            
            throw new Error(errorData.message || 'Failed to fetch product');
        }

        const data = await response.json();
        return { success: true, product: data.data };
        
    } catch (error) {
        console.error('Error fetching product:', error);
        return { error: error.message };
    }
}

/**
 * Load more products for pagination
 * @returns {Promise<string>} HTML string for table rows
 */
async function loadMoreProducts() {
    currentOffset += DEFAULT_LIMIT;
    const products = await fetchAllProducts(DEFAULT_LIMIT, currentOffset);
    
    if (products.error) {
        return `<tr><td colspan="10" style="text-align: center; color: red; padding: 20px;">Error: ${products.error}</td></tr>`;
    }
    
    if (products.length === 0) {
        return `<tr><td colspan="10" style="text-align: center; padding: 20px;">No more products to load</td></tr>`;
    }

    return products.map(product => renderProductRow(product)).join('');
}

/**
 * Render a single table row
 * @param {Object} product - Product object
 * @returns {string} HTML string for table row
 */
function renderProductRow(product) {
    return `
        <tr data-product-id="${product.id}">
            <td style="border: 1px solid #ddd; padding: 8px;">${product.id}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(product.name)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(product.description || '-')}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">$${parseFloat(product.price).toFixed(2)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">
                <img src="${escapeHtml(product.image_url)}" style="max-width: 50px; max-height: 50px;" alt="${escapeHtml(product.name)}" />
            </td>
            <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(product.category_name || '-')}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(product.supplier_name || '-')}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${formatDate(product.created_at)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${product.updated_at ? formatDate(product.updated_at) : '-'}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center; display: flex; gap:5px">
                <button class="btn-view-products" data-id="${product.id}" style="background-color:#007bff; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; margin-right:4px;" title="View Details">
                    👁️ View
                </button>
                <a href="manage/product/edit.php?id=${product.id}" class="btn-edit-products" style="background-color:#28a745; color:white; text-decoration:none; padding:6px 12px; border-radius:4px; display:inline-block;" title="Edit Product">
                    ✏️ Edit
                </a>
            </td>
        </tr>
    `;
}

/**
 * Render full products table
 * @returns {Promise<string>} HTML string for complete table
 */
export const Products = async () => {
    currentOffset = 0;
    const products = await fetchAllProducts(DEFAULT_LIMIT, currentOffset);

    if (products.error) {
        return `
            <div class="products-table">
                <div style="padding: 20px; text-align: center; color: red; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
                    <strong>Error:</strong> ${escapeHtml(products.error)}
                </div>
            </div>
        `;
    }

    if (products.length === 0) {
        return `
            <div class="products-table">
                <div style="padding: 40px; text-align: center; color: #6c757d;">
                    <p style="font-size: 18px;">📭 No products found.</p>
                </div>
            </div>
        `;
    }

    const tableRows = products.map(product => renderProductRow(product)).join('');

    return `
        <div class="products-table">
            <div class="table-header" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0;">Products Management (${products.length}${products.length === DEFAULT_LIMIT ? '+' : ''})</h2>
                <button id="refresh-products-btn" style="padding: 8px 16px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    🔄 Refresh
                </button>
            </div>
            
            <div style="overflow-x: auto;">
                <table style="border-collapse: collapse; width: 100%; border: 1px solid #ddd; background-color: white;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">ID</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Name</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Description</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Price</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Image</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Category</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Supplier</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Created At</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: 600;">Updated At</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: center; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body">
                        ${tableRows}
                    </tbody>
                </table>
            </div>
            
            ${products.length === DEFAULT_LIMIT ? `
                <div style="margin-top: 15px; text-align: center;">
                    <button id="load-more-btn-products" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                        Load More Products
                    </button>
                </div>
            ` : ''}
        </div>
    `;
};

/**
 * Initialize event listeners when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');



    // Click delegation for all buttons
    document.addEventListener('click', async (e) => {
        // View product details button
        if (e.target.matches('.btn-view-products') || e.target.closest('.btn-view-products')) {
            const button = e.target.matches('.btn-view-products') ? e.target : e.target.closest('.btn-view-products');
            const productId = button.dataset.id;
            
            if (!productId) return;
            
            // Show loading state
            modalBody.innerHTML = '<div style="text-align: center; padding: 40px;">Loading...</div>';
            modal.classList.add('active');
            
            try {
                const result = await fetchProductById(productId);
                
                if (result.error) {
                    modalBody.innerHTML = `
                        <div style="text-align: center; padding: 20px; color: red;">
                            <strong>Error:</strong> ${escapeHtml(result.error)}
                        </div>
                    `;
                    return;
                }
                
                const product = result.product;
                
                modalBody.innerHTML = `
                    <div style="padding: 20px;">
                        <h2 style="margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Product Details</h2>
                        
                        <div class="product-details" style="display: grid; gap: 15px;">
                            <div class="field">
                                <strong style="color: #6c757d;">ID:</strong> 
                                <span>${product.id}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Name:</strong> 
                                <span>${escapeHtml(product.name)}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Description:</strong> 
                                <span>${escapeHtml(product.description || '-')}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Price:</strong> 
                                <span>$${parseFloat(product.price).toFixed(2)}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Image:</strong> 
                                <img src="${escapeHtml(product.image_url)}" style="max-width: 200px; max-height: 200px; border-radius: 4px;" alt="${escapeHtml(product.name)}" />
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Category:</strong> 
                                <span>${escapeHtml(product.category_name || '-')}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Supplier:</strong> 
                                <span>${escapeHtml(product.supplier_name || '-')}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Created At:</strong> 
                                <span>${formatDate(product.created_at)}</span>
                            </div>

                            <div class="field">
                                <strong style="color: #6c757d;">Updated At:</strong> 
                                <span>${product.updated_at ? formatDate(product.updated_at) : '-'}</span>
                            </div>
                        </div>

                        <div style="margin-top: 20px; text-align: right;">
                            <a href="manage/product/edit.php?id=${product.id}" target="_blank" class="btn-edit" style="background-color:#28a745; color:white; text-decoration:none; padding:10px 20px; border-radius:4px; display:inline-block;">
                                Edit Product
                            </a>
                        </div>
                    </div>
                `;
                
            } catch (err) {
                console.error('Error loading product details:', err);
                modalBody.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: red;">
                        Failed to load product details. Please try again.
                    </div>
                `;
            }
        }

        // Load more button
        if (e.target.id === 'load-more-btn-products') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';
            
            try {
                const html = await loadMoreProducts();
                document.getElementById('products-table-body').insertAdjacentHTML('beforeend', html);
                
                // Check if we got results
                const newProducts = await fetchAllProducts(DEFAULT_LIMIT, currentOffset);
                if (newProducts.length < DEFAULT_LIMIT) {
                    button.remove(); // No more products to load
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Products';
                }
            } catch (error) {
                button.disabled = false;
                button.textContent = 'Load More Products';
                alert('Failed to load more products. Please try again.');
            }
        }

        // Refresh button
        if (e.target.id === 'refresh-products-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';
            
            try {
                // Reload the entire products table
                currentOffset = 0;
                const content = await Products();
                document.querySelector('.products-table').outerHTML = content;
            } catch (error) {
                alert('Failed to refresh products. Please try again.');
            }
        }
    });
});

/**
 * Utility function to escape HTML and prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
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

// Export for use in other modules
window.loadMoreProducts = loadMoreProducts;
window.fetchAllProducts = fetchAllProducts;