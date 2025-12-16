import { fetchAllProducts, fetchModalDetails } from "./Products.utils.js";
import { escapeHtml, formatDate, debounce, saveState, getState, openStandardModal, closeModal } from "../../utils.js";
import { renderProductCreate, initProductCreateHandlers } from "./ProductCreate.js";
import { renderProductEdit, initProductEditHandlers } from "./ProductEdit.js";
import { API_ROUTES } from "../../dashboard.routes.js";

const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = getState('admin:products:query', '');
let currentSort = getState('admin:products:sort', 'newest');
let lastResults = [];

async function loadMoreProducts() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const products = await fetchAllProducts(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (products.error) {
            return `<tr><td colspan="10" class="admin-entity__empty">Error: ${escapeHtml(products.error)}</td></tr>`;
        }

        if (!products.length) {
            return `<tr><td colspan="10" class="admin-entity__empty">No more products to load</td></tr>`;
        }

        return products.map(renderProductRow).join('');
    } catch (error) {
        console.error('Error loading more products:', error);
        return `<tr><td colspan="10" class="admin-entity__empty">Failed to load products</td></tr>`;
    }
}

function renderProductRow(product) {
    return `
        <tr data-product-id="${product.id}">
            <td>${product.id}</td>
            <td>${escapeHtml(product.name)}</td>
            <td>${escapeHtml(product.description || '-')}</td>
            <td>$${parseFloat(product.price_cents || product.price || 0) / 100}</td>
            <td>
                <img src="${escapeHtml(product.image_url)}" class="admin-entity__thumb" alt="${escapeHtml(product.name)}" />
            </td>
            <td>${escapeHtml(product.category_name || '-')}</td>
            <td>${escapeHtml(product.supplier_name || '-')}</td>
            <td>${formatDate(product.created_at)}</td>
            <td>${product.updated_at ? formatDate(product.updated_at) : '-'}</td>
            <td>
                <button class="btn btn-outline btn-sm js-admin-view" data-entity="product" data-id="${product.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm js-admin-edit" data-entity="product" data-id="${product.id}" title="Edit Product">✏️ Edit</button>
            </td>
        </tr>
    `;
}

export const Products = async () => {
    try {
        currentOffset = 0;
        const products = await fetchAllProducts(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (products.error) {
            lastResults = [];
        } else {
            lastResults = Array.isArray(products) ? products : [];
        }

        const appliedResults = applySort(lastResults, currentSort);
        const hasData = appliedResults && appliedResults.length > 0;

        const tableRows = hasData
            ? appliedResults.map(renderProductRow).join('')
            : `<tr><td colspan="10" class="admin-entity__empty">${products && products.error ? escapeHtml(products.error) : 'No products found'}</td></tr>`;

        const countLabel = hasData ? `${appliedResults.length}${appliedResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        return `
            <div class="admin-entity">
                <div class="admin-entity__header">
                    <h2 class="admin-entity__title">Products Management (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="products-search-input" class="admin-entity__search" type="search" placeholder="Search name, category or supplier" value="${escapeHtml(currentQuery)}" />
                        <select id="products-sort-select" class="admin-entity__sort">
                            <option value="newest" ${currentSort === 'newest' ? 'selected' : ''}>Newest first</option>
                            <option value="oldest" ${currentSort === 'oldest' ? 'selected' : ''}>Oldest first</option>
                        </select>
                        <button class="btn btn-primary js-admin-create" data-entity="product">➕ Create Product</button>
                        <button id="products_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
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
                    ${hasData && appliedResults.length === DEFAULT_LIMIT ? `<button id="products_load-more-btn" class="btn btn-outline btn-sm">Load More Products</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering products table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> Failed to load products table</div></div>`;
    }
};

const productDetailsHtml = (product) => {
    const priceDollars = parseFloat(product.price_cents || 0) / 100;
    const totalRevenue = parseFloat(product.total_revenue_cents || 0) / 100;
    const imageUrl = product.image_url || '';

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Product Details</span>
                <span class="badge ${product.is_active ? 'badge-active' : 'badge-inactive'}">${product.is_active ? 'Active' : 'Inactive'}</span>
                <button class="products_close-btn">&times;</button>
            </div>
            
            <!-- Image Preview Section -->
            ${imageUrl ? `
            <div class="products_image-section">
                <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(product.name)}" class="products_image-preview" />
            </div>
            ` : `
            <div class="products_image-section products_image-placeholder">
                <div class="products_no-image">
                    <span class="products_no-image-icon">📷</span>
                    <span>No image available</span>
                </div>
            </div>
            `}
            
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
                <button class="btn btn-primary js-admin-edit" data-entity="product" data-id="${product.id}">Edit Product</button>
            </div>
        </div>
    `;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(Number(id));

        // Track API response
        if (result.error) {
            console.error('[Products] Modal fetch error:', result.error);
            throw new Error(result.error);
        }

        if (!result.success || !result.product) {
            throw new Error('Invalid response format from API');
        }

        return productDetailsHtml(result.product);
    } catch (error) {
        console.error('[Products] Render modal error:', error);
        throw new Error(error.message || 'Failed to load product details');
    }
};

export const productsListeners = async (container) => {
    if (!container) return null;

    async function performSearch(query) {
        try {
            currentQuery = query || '';
            saveState('admin:products:query', currentQuery);
            currentOffset = 0;
            const results = await fetchAllProducts(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('products_table-body');
            const loadMoreWrapper = document.getElementById('products_load-more-wrapper');

            if (!tbody) return;
            if (results.error) {
                lastResults = [];
            } else {
                lastResults = Array.isArray(results) ? results : [];
            }

            const appliedResults = applySort(lastResults, currentSort);

            if (!appliedResults.length) {
                tbody.innerHTML = `<tr><td colspan="10" class="admin-entity__empty">${results && results.error ? escapeHtml(results.error) : 'No products found'}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = appliedResults.map(renderProductRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = appliedResults.length === DEFAULT_LIMIT ? `<button id="products_load-more-btn" class="btn btn-outline btn-sm">Load More Products</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    function handleSortChange(e) {
        currentSort = e.target.value === 'oldest' ? 'oldest' : 'newest';
        saveState('admin:products:sort', currentSort);

        const tbody = document.getElementById('products_table-body');
        const loadMoreWrapper = document.getElementById('products_load-more-wrapper');
        if (!tbody) return;

        const appliedResults = applySort(lastResults, currentSort);
        if (!appliedResults.length) {
            tbody.innerHTML = `<tr><td colspan="10" class="products_no-data-cell">No products found</td></tr>`;
            if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
            return;
        }

        tbody.innerHTML = appliedResults.map(renderProductRow).join('');
        if (loadMoreWrapper) {
            loadMoreWrapper.innerHTML = appliedResults.length === DEFAULT_LIMIT ? `<button id="products_load-more-btn" class="btn btn-outline btn-sm">Load More Products</button>` : '';
        }
    }

    // Debounced search handler
    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    // Use AbortController for cleanup if needed
    const abortController = new AbortController();
    const signal = abortController.signal;

    // Bind entity-scoped delegated event handlers
    // View button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-view');
        if (!btn || btn.dataset.entity !== 'product') return;

        const id = btn.dataset.id;
        if (!id) return;

        try {
            const html = await renderModal(parseInt(id));
            openStandardModal({
                title: 'Product Details',
                bodyHtml: html,
                size: 'xl'
            });
        } catch (error) {
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Edit button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-edit');
        if (!btn || btn.dataset.entity !== 'product') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Products] Edit button clicked:', { id });

        try {
            console.log('[Products] Rendering edit form...');
            const formHtml = await renderProductEdit(parseInt(id));
            console.log('[Products] Form HTML rendered, length:', formHtml.length);

            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            if (!modal || !modalBody) {
                console.error('[Products] Modal elements not found!', { modal: !!modal, modalBody: !!modalBody });
                return;
            }

            console.log('[Products] Setting modal content...');
            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';
            console.log('[Products] Modal should be visible now');

            initProductEditHandlers(modalBody, parseInt(id), (data, action) => {
                if (action === 'updated' || action === 'deleted') {
                    // Refresh the table
                    Products().then(html => {
                        const container = document.getElementById('content');
                        if (container) {
                            container.querySelector('.admin-entity').outerHTML = html;
                            productsListeners(container);
                        }
                    });
                }
            });
        } catch (error) {
            console.error('[Products] Error opening edit form:', error);
            alert(`Error: ${error.message}`);
        }
    }, { signal });

    // Create button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-create');
        if (!btn || btn.dataset.entity !== 'product') return;

        e.preventDefault();
        console.log('[Products] Create button clicked');

        try {
            const formHtml = await renderProductCreate();
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initProductCreateHandlers(modalBody, (data) => {
                // Refresh the table after create
                Products().then(html => {
                    const container = document.getElementById('content');
                    if (container) {
                        container.querySelector('.admin-entity').outerHTML = html;
                        productsListeners(container);
                    }
                });
            });
        } catch (error) {
            console.error('[Products] Error opening create form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Load more handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'products_load-more-btn') return;

        const btn = e.target;
        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = 'Loading...';

        try {
            const html = await loadMoreProducts();
            const tbody = document.getElementById('products_table-body');
            if (tbody) {
                tbody.insertAdjacentHTML('beforeend', html);
            }

            if (html.includes('No more products to load') || html.includes('Failed to load')) {
                btn.textContent = 'No more products to load';
                btn.disabled = true;
            } else {
                btn.textContent = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            console.error('Error loading more products:', error);
            btn.textContent = originalText;
            btn.disabled = false;
        }
    }, { signal });

    // Refresh handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'products_refresh-btn') return;

        const btn = e.target;
        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = 'Refreshing...';

        try {
            const content = await Products();
            const existingTable = container.querySelector('.admin-entity');
            if (existingTable) {
                existingTable.outerHTML = content;
                // Re-attach listeners after refresh
                await productsListeners(container);
            }
        } catch (error) {
            console.error('Error refreshing products:', error);
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }, { signal });

    // Search handler
    container.addEventListener('input', (e) => {
        console.log('[Products] Input event:', e.target.id);
        if (e.target.id === 'products-search-input') {
            console.log('[Products] Search triggered:', e.target.value);
            debouncedSearch(e);
        }
    }, { signal });

    // Sort change handler
    container.addEventListener('change', (e) => {
        if (e.target.id === 'products-sort-select') {
            handleSortChange(e);
        }
    }, { signal });

    // Return controller for cleanup
    return {
        cleanup: () => abortController.abort()
    };
};

function applySort(items, sort) {
    if (!Array.isArray(items) || items.length === 0) return [];
    const sorted = [...items].sort((a, b) => {
        const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
        const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
        return sort === 'oldest' ? dateA - dateB : dateB - dateA;
    });
    return sorted;
}
