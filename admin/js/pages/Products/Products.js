import { fetchAllProducts, fetchModalDetails } from "./Products.utils.js";

const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreProducts() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const products = await fetchAllProducts(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (products.error) {
            return `<tr><td colspan="10" class="products_error-cell">Error: ${escapeHtml(products.error)}</td></tr>`;
        }

        if (!products.length) {
            return `<tr><td colspan="10" class="products_no-data-cell">No more products to load</td></tr>`;
        }

        return products.map(renderProductRow).join('');
    } catch (error) {
        console.error('Error loading more products:', error);
        return `<tr><td colspan="10" class="products_error-cell">Failed to load products</td></tr>`;
    }
}

function renderProductRow(product) {
    return `
        <tr class="products_row" data-product-id="${product.id}">
            <td class="products_cell">${product.id}</td>
            <td class="products_cell">${escapeHtml(product.name)}</td>
            <td class="products_cell">${escapeHtml(product.description || '-')}</td>
            <td class="products_cell">$${parseFloat(product.price_cents || product.price || 0)/100}</td>
            <td class="products_cell products_image-cell">
                <img src="${escapeHtml(product.image_url)}" class="products_thumb" alt="${escapeHtml(product.name)}" />
            </td>
            <td class="products_cell">${escapeHtml(product.category_name || '-')}</td>
            <td class="products_cell">${escapeHtml(product.supplier_name || '-')}</td>
            <td class="products_cell">${formatDate(product.created_at)}</td>
            <td class="products_cell">${product.updated_at ? formatDate(product.updated_at) : '-'}</td>
            <td class="products_cell products_actions">
                <button class="products_btn-view" data-id="${product.id}" title="View Details">👁️ View</button>
                <a href="manage/product/update.php?id=${product.id}" class="products_btn-edit" title="Edit Product">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const Products = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const products = await fetchAllProducts(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (products.error) {
            return `<div class="products_table"><div class="products_error-box"><strong>Error:</strong> ${escapeHtml(products.error)}</div></div>`;
        }

        if (!products.length) {
            return `<div class="products_table"><div class="products_no-data-box">📭 No products found.</div></div>`;
        }

        const tableRows = products.map(renderProductRow).join('');

        return `
            <div class="products_table">
                <div class="products_header">
                    <h2>Products Management (${products.length}${products.length === DEFAULT_LIMIT ? '+' : ''})</h2>
                    <div class="products_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="products-search-input" class="products_search-input" type="search" placeholder="Search name, category or supplier" />
                         <a href="manage/product/create.php"> make a new product </a>
                        <button id="products_refresh-btn" class="products_btn-refresh">🔄 Refresh</button>
                    </div>
                </div>
                <div class="products_wrapper">
                    <table class="products_data-table">
                        <thead>
                            <tr class="products_header-row">
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Image</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="products_table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
                <div id="products_load-more-wrapper" style="text-align:center;">
                    ${products.length === DEFAULT_LIMIT ? `<button id="products_load-more-btn" class="products_btn-load-more">Load More Products</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering products table:', error);
        return `<div class="products_table"><div class="products_error-box"><strong>Error:</strong> Failed to load products table</div></div>`;
    }
};

const productDetailsHtml = (product) => {
    const priceDollars = parseFloat(product.price_cents || 0)/100;
    const totalRevenue = parseFloat(product.total_revenue_cents || 0)/100;

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Product Details</span>
                <span class="badge ${product.is_active ? 'badge-active' : 'badge-inactive'}">${product.is_active ? 'Active' : 'Inactive'}</span>
                <button class="products_close-btn">&times;</button>
            </div>
            <div class="products_section-title">Basic Info</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>ID</strong><span>${product.id}</span></div>
                <div class="products_field"><strong>Name</strong><span>${escapeHtml(product.name)}</span></div>
                <div class="products_field"><strong>Description</strong><span>${escapeHtml(product.description || '-')}</span></div>
            </div>
            <div class="products_section-title">Pricing & Category</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Price</strong><span>$${priceDollars.toFixed(2)}</span></div>
                <div class="products_field"><strong>Category</strong><span>${escapeHtml(product.category_name || '-')}</span></div>
                <div class="products_field"><strong>Supplier</strong><span>${escapeHtml(product.supplier_name || '-')}</span></div>
            </div>
            <div class="products_section-title">Sales & Inventory</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Total Sold</strong><span>${product.total_sold || 0}</span></div>
                <div class="products_field"><strong>Total Revenue</strong><span>$${totalRevenue.toFixed(2)}</span></div>
            </div>
            <div class="products_footer">
                <a href="manage/product/update.php?id=${product.id}" target="_blank" class="products_btn-primary">Edit Product</a>
            </div>
        </div>
    `;
};

const renderModal = async (id) => {
    try {
        const {success, product} = await fetchModalDetails(Number(id));
        

        if ( !success ) {
            throw new Error(success || 'Failed to fetch product details');
        }
        console.log(product);
        return productDetailsHtml(product);
    } catch (error) {
        throw new Error(error.message || 'Failed to load product details');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

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
            const results = await fetchAllProducts(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('products_table-body');
            const loadMoreWrapper = document.getElementById('products_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="10" class="products_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="10" class="products_no-data-cell">No products found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderProductRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="products_load-more-btn" class="products_btn-load-more">Load More Products</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.products_btn-view') || e.target.closest('.products_btn-view')) {
            const button = e.target.matches('.products_btn-view') ? e.target : e.target.closest('.products_btn-view');
            const productId = button.dataset.id;

            if (!productId) return;

            modalBody.innerHTML = '<div class="products_loading">⏳ Loading product details...</div>';
            modal.classList.add('active');

            try {
                
                const html = await renderModal(parseInt(productId));
                
                modalBody.innerHTML = html;

                const closeBtn = modalBody.querySelector('.products_close-btn');
                if (closeBtn) closeBtn.addEventListener('click', () => modal.classList.remove('active'));
            } catch (error) {
                modalBody.innerHTML = `<div class="products_error">⚠️ ${escapeHtml(error.message)}</div>`;
            }
        }

        // load more
        if (e.target.id === 'products_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            const html = await loadMoreProducts();
            document.getElementById('products_table-body').insertAdjacentHTML('beforeend', html);

            if (html.includes('No more products to load') || html.includes('Failed to load')) {
                button.textContent = 'No more products to load';
                button.disabled = true;
            } else {
                button.disabled = false;
                button.textContent = 'Load More Products';
            }
        }

        // refresh
        if (e.target.id === 'products_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            const content = await Products();
            document.querySelector('.products_table').outerHTML = content;
        }
    });

    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'products-search-input') {
            debouncedSearch(e);
        }
    });

    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('active'); });
    if (modalClose) modalClose.addEventListener('click', () => modal.classList.remove('active'));
});

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
    } catch { return dateString; }
}

window.loadMoreProducts = loadMoreProducts;
window.fetchAllProducts = fetchAllProducts;
