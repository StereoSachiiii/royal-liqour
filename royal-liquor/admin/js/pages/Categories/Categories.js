import { fetchAllCategories, fetchModalDetails } from "./Categories.utils.js";
import { renderCategoryCreate, initCategoryCreateHandlers } from "./CategoryCreate.js";
import { renderCategoryEdit, initCategoryEditHandlers } from "./CategoryEdit.js";
import { API_ROUTES } from "../../dashboard.routes.js";
import {
    escapeHtml,
    formatDate,
    debounce,
    openStandardModal,
    closeModal
} from "../../utils.js";

const DEFAULT_LIMIT = 20;
let currentOffset = 0;
let currentQuery = '';

// ---------- ROW RENDER ---------- //
function renderCategoryRow(category) {
    if (!category || !category.id) return '';

    return `
        <tr data-category-id="${category.id}">
            <td>${category.id}</td>
            <td>${escapeHtml(category.name || '-')}</td>
            <td>${escapeHtml(category.slug || '-')}</td>
            <td>
                ${category.image_url
            ? `<img src="${escapeHtml(category.image_url)}" class="admin-entity__thumb" alt="${escapeHtml(category.name)}" />`
            : '<span class="admin-entity__no-image">-</span>'
        }
            </td>
            <td>${category.product_count ?? 0}</td>
            <td>
                <span class="badge badge-status-${category.is_active !== false ? 'active' : 'inactive'}">
                    ${category.is_active !== false ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>${formatDate(category.created_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm js-admin-view" data-entity="category" data-id="${category.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm js-admin-edit" data-entity="category" data-id="${category.id}" title="Edit Category">✏️ Edit</button>
            </td>
        </tr>
    `;
}

// ---------- MODAL HTML ---------- //
const categoryDetailsHtml = (category) => {
    if (!category) return '<div class="admin-entity__empty">No category data</div>';

    const avgPrice = category.avg_price_cents ? (parseFloat(category.avg_price_cents) / 100).toFixed(2) : '-';
    const minPrice = category.min_price_cents ? (parseFloat(category.min_price_cents) / 100).toFixed(2) : '-';
    const maxPrice = category.max_price_cents ? (parseFloat(category.max_price_cents) / 100).toFixed(2) : '-';

    return `
<div class="admin-modal admin-modal--lg">
    <div class="bg-white border-b px-6 py-4 rounded-t-xl d-flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Category Details</h2>
            <p class="text-sm text-gray-500">${escapeHtml(category.slug || '')}</p>
        </div>
        <span class="badge badge-status-${category.is_active !== false ? 'active' : 'inactive'}">
            ${category.is_active !== false ? 'Active' : 'Inactive'}
        </span>
    </div>
    
    <div class="admin-modal__body bg-gray-50 p-6">
        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
            <!-- Left Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Basic Info</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">ID</span><span>${category.id}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Name</span><span>${escapeHtml(category.name || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Slug</span><span>${escapeHtml(category.slug || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Description</span><span>${escapeHtml(category.description || '-')}</span></div>
                    </div>
                </div>
                
                ${category.image_url ? `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Image</h4>
                    <img src="${escapeHtml(category.image_url)}" alt="${escapeHtml(category.name)}" class="rounded-lg w-full" style="max-height: 150px; object-fit: cover;" />
                </div>
                ` : ''}
            </div>
            
            <!-- Right Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Statistics</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Total Products</span><span class="font-medium">${category.total_products ?? category.product_count ?? 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Active Products</span><span>${category.active_products ?? 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Avg Price</span><span>$${avgPrice}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Min Price</span><span>$${minPrice}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Max Price</span><span>$${maxPrice}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Total Sales</span><span>${category.total_sales ?? 0}</span></div>
                    </div>
                </div>
                
                ${category.top_products && Array.isArray(category.top_products) && category.top_products.length > 0 ? `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Top Products</h4>
                    <div class="d-flex flex-col gap-2">
                        ${category.top_products.map(p => `
                            <div class="d-flex justify-between items-center py-1 border-b">
                                <span>${escapeHtml(p.name || 'Unknown')}</span>
                                <span class="font-medium">$${((p.price_cents || 0) / 100).toFixed(2)}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
        
        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
            <button class="btn btn-outline" onclick="closeModal()">Close</button>
            <button class="btn btn-primary js-admin-edit" data-entity="category" data-id="${category.id}">Edit Category</button>
        </div>
    </div>
</div>`;
};

// ---------- LOAD LOGIC ---------- //
async function loadMoreCategories() {
    currentOffset += DEFAULT_LIMIT;
    const categories = await fetchAllCategories(DEFAULT_LIMIT, currentOffset, currentQuery);

    if (categories.error) {
        return `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(categories.error)}</td></tr>`;
    }

    if (Array.isArray(categories) && categories.length === 0) {
        return `<tr><td colspan="8" class="admin-entity__empty">No more categories to load</td></tr>`;
    }

    return categories.map(renderCategoryRow).join("");
}

async function performSearch(query) {
    currentQuery = query.trim();
    currentOffset = 0;

    const categories = await fetchAllCategories(DEFAULT_LIMIT, 0, currentQuery);
    const tbody = document.getElementById("categories_table-body");
    const loadMoreWrapper = document.getElementById("categories_load-more-wrapper");

    if (!tbody) return;

    if (categories.error) {
        tbody.innerHTML = `<tr><td colspan="8" class="admin-entity__empty">${escapeHtml(categories.error)}</td></tr>`;
        if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        return;
    }

    if (Array.isArray(categories)) {
        if (categories.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="admin-entity__empty">No categories found</td></tr>`;
            if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        } else {
            tbody.innerHTML = categories.map(renderCategoryRow).join("");
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = categories.length >= DEFAULT_LIMIT
                    ? `<button id="categories_load-more-btn" class="btn btn-outline btn-sm">Load More Categories</button>`
                    : "";
            }
        }
    }
}

// ---------- MAIN EXPORT ---------- //
export const Categories = async (search = "") => {
    currentOffset = 0;
    currentQuery = search;

    const categories = await fetchAllCategories(DEFAULT_LIMIT, 0, currentQuery);
    let rows = "";
    let count = 0;
    let error = null;

    if (categories.error) {
        error = categories.error;
    } else if (Array.isArray(categories)) {
        rows = categories.map(renderCategoryRow).join("");
        count = categories.length;
    }

    const loadMoreButton = count === DEFAULT_LIMIT ? `<button id="categories_load-more-btn" class="btn btn-outline btn-sm">Load More Categories</button>` : "";
    const emptyState = error
        ? `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(error)}</td></tr>`
        : `<tr><td colspan="8" class="admin-entity__empty">No categories found.</td></tr>`;

    return `
<div class="admin-entity">
    <div class="admin-entity__header">
        <h2 class="admin-entity__title">Categories Management (${count}${count === DEFAULT_LIMIT ? "+" : ""})</h2>
        <div class="admin-entity__actions">
            <input type="text" id="categories-search-input" class="admin-entity__search" placeholder="Search name or slug..." value="${escapeHtml(currentQuery)}" />
            <button class="btn btn-primary js-admin-create" data-entity="category">➕ Add Category</button>
            <button id="categories_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="admin-entity__table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Image</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="categories_table-body">
                ${rows || emptyState}
            </tbody>
        </table>
    </div>

    <div id="categories_load-more-wrapper" style="text-align:center;">
        ${loadMoreButton}
    </div>
</div>`;
};

// ---------- EVENT DELEGATION ---------- //
export const categoriesListeners = async (container) => {
    if (!container) return null;

    const debouncedSearch = debounce((e) => performSearch(e.target.value), 300);
    const abortController = new AbortController();
    const signal = abortController.signal;

    // View button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-view');
        if (!btn || btn.dataset.entity !== 'category') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Categories] View button clicked:', { id });

        try {
            const result = await fetchModalDetails(id);
            if (!result.success) {
                throw new Error(result.error);
            }

            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = categoryDetailsHtml(result.category);
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';
        } catch (err) {
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">${escapeHtml(err.message)}</div>`
            });
        }
    }, { signal });

    // Edit button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-edit');
        if (!btn || btn.dataset.entity !== 'category') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Categories] Edit button clicked:', { id });

        try {
            const formHtml = await renderCategoryEdit(parseInt(id));
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initCategoryEditHandlers(modalBody, parseInt(id), (data, action) => {
                if (action === 'updated' || action === 'deleted') {
                    Categories().then(html => {
                        const existingEntity = container.querySelector('.admin-entity');
                        if (existingEntity) {
                            existingEntity.outerHTML = html;
                            categoriesListeners(container);
                        }
                    });
                }
            });
        } catch (error) {
            console.error('[Categories] Error opening edit form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Create button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-create');
        if (!btn || btn.dataset.entity !== 'category') return;

        e.preventDefault();
        console.log('[Categories] Create button clicked');

        try {
            const formHtml = await renderCategoryCreate();
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initCategoryCreateHandlers(modalBody, (data) => {
                Categories().then(html => {
                    const existingEntity = container.querySelector('.admin-entity');
                    if (existingEntity) {
                        existingEntity.outerHTML = html;
                        categoriesListeners(container);
                    }
                });
            });
        } catch (error) {
            console.error('[Categories] Error opening create form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Load More handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'categories_load-more-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Loading...";

        const html = await loadMoreCategories();
        document.getElementById("categories_table-body").insertAdjacentHTML("beforeend", html);

        if (html.includes("No more") || html.includes("admin-entity__empty")) {
            btn.remove();
        } else {
            btn.disabled = false;
            btn.textContent = "Load More Categories";
        }
    }, { signal });

    // Refresh handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'categories_refresh-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Refreshing...";
        await performSearch(currentQuery);
        btn.disabled = false;
        btn.textContent = "🔄 Refresh";
    }, { signal });

    // Search handler
    container.addEventListener('input', (e) => {
        if (e.target.id === 'categories-search-input') {
            debouncedSearch(e);
        }
    }, { signal });

    return {
        cleanup: () => abortController.abort()
    };
};