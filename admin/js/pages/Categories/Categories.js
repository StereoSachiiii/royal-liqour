import { fetchAllCategories, fetchCategory, fetchModalDetails } from "./Categories.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

const DEFAULT_LIMIT = 50;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreCategories() {
    currentOffset += DEFAULT_LIMIT;
    const categories = await fetchAllCategories(DEFAULT_LIMIT, currentOffset, currentQuery);
    
    if (categories.error) {
        return `<tr><td colspan="7" class="categories_error-cell">Error: ${escapeHtml(categories.error)}</td></tr>`;
    }
    
    if (categories.length === 0) {
        return `<tr><td colspan="7" class="categories_no-data-cell">No more categories to load</td></tr>`;
    }

    return categories.map(category => renderCategoryRow(category)).join('');
}

function renderCategoryRow(category) {
    return `
        <tr class="categories_row" data-category-id="${category.id}">
            <td class="categories_cell">${category.id}</td>
            <td class="categories_cell">${escapeHtml(category.name)}</td>
            <td class="categories_cell">${escapeHtml(category.slug)}</td>
            <td class="categories_cell categories_image-cell">
                <img src="${escapeHtml(category.image_url || '')}" class="categories_thumb" alt="${escapeHtml(category.name || '')}" />
            </td>
            <td class="categories_cell">${category.product_count || '0'}</td>
            <td class="categories_cell">${formatDate(category.created_at)}</td>
            <td class="categories_cell categories_actions">
                <button class="categories_btn-view" data-id="${category.id}" title="View Details">👁️ View</button>
                <a href="manage/category/update.php?id=${category.id}" class="categories_btn-edit" title="Edit Category">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const Categories = async () => {
    currentOffset = 0;
    currentQuery = '';
    const categories = await fetchAllCategories(DEFAULT_LIMIT, currentOffset, currentQuery);

    if (categories.error) {
        return `
            <div class="categories_table">
                <div class="categories_error-box">
                    <strong>Error:</strong> ${escapeHtml(categories.error)}
                </div>
            </div>
        `;
    }

    if (categories.length === 0) {
        return `
            <div class="categories_table">
                <div class="categories_no-data-box">
                    <p>📭 No categories found.</p>
                </div>
            </div>
        `;
    }

    const tableRows = categories.map(category => renderCategoryRow(category)).join('');

    return `
        <div class="categories_table">
            <div class="categories_header">
                <h2>Categories Management (${categories.length}${categories.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                <div class="categories_header-actions" style="display:flex; gap:8px; align-items:center;">
                    <input id="categories-search-input" class="categories_search-input" type="search" placeholder="Search name, slug or products" aria-label="Search categories" />
                    <a href="manage/category/create.php" target="_blank" class="categories_btn-primary">create Category</a>
                    <button id="categories_refresh-btn" class="categories_btn-refresh">🔄 Refresh</button>
                </div>
            </div>
            
            <div class="categories_wrapper">
                <table class="categories_data-table">
                    <thead>
                        <tr class="categories_header-row">
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Image</th>
                            <th>Products</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categories_table-body">
                        ${tableRows}
                    </tbody>
                </table>
            </div>
            
            ${categories.length === DEFAULT_LIMIT ? `
                <div class="categories_load-more-wrapper">
                    <button id="categories_load-more-btn" class="categories_btn-load-more">Load More Categories</button>
                </div>
            ` : ''}
        </div>
    `;
};

const categoryDetailsHtml = (category) => {
    const avgPrice = parseFloat(category.avg_price_cents || 0) / 100 || 0;
    const minPrice = parseFloat(category.min_price_cents || 0) / 100 || 0;
    const maxPrice = parseFloat(category.max_price_cents || 0) / 100 || 0;
    
    return `
<div class="categories_card">
    <div class="categories_card-header">
        <span>Category Details</span>
        <span class="badge ${category.is_active ? 'badge-active' : 'badge-inactive'}">
            ${category.is_active ? 'Active' : 'Inactive'}
        </span>
        <button class="categories_close-btn">&times;</button>
    </div>

    <div class="categories_section-title">Basic Information</div>
    <div class="categories_data-grid">
        <div class="categories_field">
            <strong class="categories_label">ID</strong>
            <span class="categories_value">${category.id}</span>
        </div>
        <div class="categories_field">
            <strong class="categories_label">Name</strong>
            <span class="categories_value">${escapeHtml(category.name)}</span>
        </div>
        <div class="categories_field">
            <strong class="categories_label">Slug</strong>
            <span class="categories_value">${escapeHtml(category.slug)}</span>
        </div>
        <div class="categories_field">
            <strong class="categories_label">Description</strong>
            <span class="categories_value">${escapeHtml(category.description || '-')}</span>
        </div>
    </div>

    <div class="categories_section-title">Category Image</div>
    <div class="categories_image-container">
        ${category.image_url ? `<img src="${escapeHtml(category.image_url)}" alt="${escapeHtml(category.name)}" class="categories_image" />` : '<span class="categories_empty">No image available</span>'}
    </div>

    <div class="categories_section-title">Products & Pricing</div>
    <div class="categories_data-grid">
        <div class="categories_field">
            <strong class="categories_label">Total Products</strong>
            <span class="categories_value">${category.total_products || '0'}</span>
        </div>
        <div class="categories_field">
            <strong class="categories_label">Active Products</strong>
            <span class="categories_value categories_success">${category.active_products || '0'}</span>
        </div>
        <div class="categories_field">
            <strong class="categories_label">Avg Price</strong>
            <span class="categories_value">${avgPrice > 0 ? '$' + avgPrice.toFixed(2) : '-'}</span>
        </div>
        <div class="categories_field">
            <strong class="categories_label">Min Price</strong>
            <span class="categories_value">${minPrice > 0 ? '$' + minPrice.toFixed(2) : '-'}</span>
        </div>
        <div class="categories_field">
            <strong class="categories_label">Max Price</strong>
            <span class="categories_value">${maxPrice > 0 ? '$' + maxPrice.toFixed(2) : '-'}</span>
        </div>
    </div>

    <div class="categories_section-title">Inventory</div>
    <div class="categories_data-grid">
        <div class="categories_field">
            <strong class="categories_label">Total Inventory</strong>
            <span class="categories_value">${category.total_inventory || '0'}</span>
        </div>
        <div class="categories_field">
            <strong class="categories_label">Total Reserved</strong>
            <span class="categories_value categories_warning">${category.total_reserved || '0'}</span>
        </div>
        <div class="categories_field">
            <strong class="categories_label">Total Sales</strong>
            <span class="categories_value categories_large categories_success">${category.total_sales || '0'}</span>
        </div>
    </div>

    ${category.top_products && category.top_products.length > 0 ? `
        <div class="categories_section-title">Top Products</div>
        <div class="categories_products-container">
            ${category.top_products.map(product => `
                <div class="categories_product-row">
                    <span class="categories_product-name">${escapeHtml(product.name)}</span>
                    <span class="categories_product-price">$${(product.price_cents / 100).toFixed(2)}</span>
                    <span class="categories_product-status ${product.is_active ? 'active' : 'inactive'}">
                        ${product.is_active ? '✓ Active' : '✗ Inactive'}
                    </span>
                </div>
            `).join('')}
        </div>
    ` : ''}

    <div class="categories_section-title">Timeline</div>
    <div class="categories_data-grid">
        <div class="categories_field">
            <strong class="categories_label">Created At</strong>
            <span class="categories_value">${formatDate(category.created_at)}</span>
        </div>
        <div class="categories_field">
            <strong class="categories_label">Updated At</strong>
            <span class="categories_value">${formatDate(category.updated_at)}</span>
        </div>
    </div>

    <div class="categories_footer">
        <a href="manage/category/update.php?id=${category.id}" target="_blank" class="categories_btn-primary">Edit Category</a>
    </div>
</div>
`;
};

export const categoriesListeners = async () => {
    

    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

    // debounce helper
    function debounce(fn, wait = 300) {
        let t = null;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), wait);
        };
    }

    async function performSearch(query) {
        try {
            currentQuery = query || '';
            currentOffset = 0;
            const results = await fetchAllCategories(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('categories_table-body');
            const loadMoreWrapper = document.querySelector('.categories_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="7" class="categories_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="7" class="categories_no-data-cell">No categories found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderCategoryRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="categories_load-more-btn" class="categories_btn-load-more">Load More Categories</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        if (e.target.matches('.categories_btn-view') || e.target.closest('.categories_btn-view')) {
            const button = e.target.matches('.categories_btn-view') ? e.target : e.target.closest('.categories_btn-view');
            const categoryId = button.dataset.id;
            
            if (!categoryId) return;
            
            modalBody.innerHTML = '<div class="categories_loading">⏳ Loading category details...</div>';
            modal.classList.add('active');
            
            try {
                const { success, category, error } = await fetchModalDetails(categoryId);
                if (error || !category || !category.id) {
                    throw new Error(error || 'Invalid category data');
                }
                
                modalBody.innerHTML = categoryDetailsHtml(category);
                
                const closeBtn = modalBody.querySelector('.categories_close-btn');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        modal.classList.remove('active');
                    });
                }
                
            } catch (err) {
                console.error('Error loading category details:', err);
                modalBody.innerHTML = `
                    <div class="categories_error">
                        <div class="categories_error-icon">⚠️</div>
                        <h3 class="categories_error-title">Error Loading Category</h3>
                        <p class="categories_error-msg">${escapeHtml(err.message)}</p>
                        <button class="categories_close-btn categories_error-btn">Close</button>
                    </div>
                `;
                
                const errorCloseBtn = modalBody.querySelector('.categories_close-btn');
                if (errorCloseBtn) {
                    errorCloseBtn.addEventListener('click', () => {
                        modal.classList.remove('active');
                    });
                }
            }
        }

        if (e.target.id === 'categories_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';
            
            try {
                const html = await loadMoreCategories();
                document.getElementById('categories_table-body').insertAdjacentHTML('beforeend', html);
                
                const newCategories = await fetchAllCategories(DEFAULT_LIMIT, currentOffset, currentQuery);
                if (newCategories.length < DEFAULT_LIMIT) {
                    button.remove();
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Categories';
                }
            } catch (error) {
                button.disabled = false;
                button.textContent = 'Load More Categories';
            }
        }

        if (e.target.id === 'categories_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';
            
            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await Categories();
                document.querySelector('.categories_table').outerHTML = content;
            } catch (error) {
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'categories-search-input') {
            debouncedSearch(e);
        }
    });

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    if (modalClose) {
        modalClose.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }
;
}


window.loadMoreCategories = loadMoreCategories;
window.fetchAllCategories = fetchAllCategories;
