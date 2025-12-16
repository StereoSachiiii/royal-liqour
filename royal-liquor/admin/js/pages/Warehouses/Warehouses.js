import { fetchAllWarehouses, fetchWarehouseDetails } from "./Warehouses.utils.js";
import { renderWarehouseCreate, initWarehouseCreateHandlers } from "./WarehouseCreate.js";
import { renderWarehouseEdit, initWarehouseEditHandlers } from "./WarehouseEdit.js";
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
function renderWarehouseRow(w) {
    if (!w || !w.id) return '';

    return `
        <tr data-warehouse-id="${w.id}">
            <td>${w.id}</td>
            <td>${escapeHtml(w.name || '-')}</td>
            <td>${escapeHtml(w.address || '-')}</td>
            <td>${escapeHtml(w.phone || '-')}</td>
            <td>
                ${w.image_url
            ? `<img src="${escapeHtml(w.image_url)}" class="admin-entity__thumb" alt="Warehouse" />`
            : '<span class="admin-entity__no-image">-</span>'
        }
            </td>
            <td>
                <span class="badge badge-status-${w.is_active !== false ? 'active' : 'inactive'}">
                    ${w.is_active !== false ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <button class="btn btn-outline btn-sm js-admin-view" data-entity="warehouse" data-id="${w.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm js-admin-edit" data-entity="warehouse" data-id="${w.id}" title="Edit Warehouse">✏️ Edit</button>
            </td>
        </tr>
    `;
}

// ---------- MODAL HTML ---------- //
const warehouseDetailsHtml = (w) => {
    if (!w) return '<div class="admin-entity__empty">No warehouse data</div>';

    return `
<div class="admin-modal admin-modal--lg">
    <div class="bg-white border-b px-6 py-4 rounded-t-xl d-flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Warehouse Details</h2>
            <p class="text-sm text-gray-500">${escapeHtml(w.address || '')}</p>
        </div>
        <span class="badge badge-status-${w.is_active !== false ? 'active' : 'inactive'}">
            ${w.is_active !== false ? 'Active' : 'Inactive'}
        </span>
    </div>
    
    <div class="admin-modal__body bg-gray-50 p-6">
        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
            <!-- Left Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Basic Info</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">ID</span><span>${w.id}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Name</span><span>${escapeHtml(w.name || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Address</span><span>${escapeHtml(w.address || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Phone</span><span>${escapeHtml(w.phone || '-')}</span></div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Timeline</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Created</span><span>${w.created_at ? formatDate(w.created_at) : '-'}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Updated</span><span>${w.updated_at ? formatDate(w.updated_at) : '-'}</span></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Inventory Statistics</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Stock Entries</span><span class="font-medium">${w.total_stock_entries ?? 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Unique Products</span><span>${w.unique_products ?? 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Total Quantity</span><span>${w.total_quantity ?? 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Reserved</span><span>${w.total_reserved ?? 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Available</span><span class="text-success font-medium">${w.total_available ?? 0}</span></div>
                    </div>
                </div>
                
                ${w.image_url ? `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Image</h4>
                    <img src="${escapeHtml(w.image_url)}" alt="${escapeHtml(w.name)}" class="rounded-lg w-full" style="max-height: 150px; object-fit: cover;" />
                </div>
                ` : ''}
            </div>
        </div>
        
        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
            <button class="btn btn-outline" onclick="closeModal()">Close</button>
            <button class="btn btn-primary js-admin-edit" data-entity="warehouse" data-id="${w.id}">Edit Warehouse</button>
        </div>
    </div>
</div>`;
};

// ---------- LOAD LOGIC ---------- //
async function loadMoreWarehouses() {
    currentOffset += DEFAULT_LIMIT;
    const warehouses = await fetchAllWarehouses(DEFAULT_LIMIT, currentOffset, currentQuery);

    if (warehouses.error) {
        return `<tr><td colspan="7" class="admin-entity__empty">Error: ${escapeHtml(warehouses.error)}</td></tr>`;
    }

    if (Array.isArray(warehouses) && warehouses.length === 0) {
        return `<tr><td colspan="7" class="admin-entity__empty">No more warehouses to load</td></tr>`;
    }

    return warehouses.map(renderWarehouseRow).join("");
}

async function performSearch(query) {
    currentQuery = query.trim();
    currentOffset = 0;

    const warehouses = await fetchAllWarehouses(DEFAULT_LIMIT, 0, currentQuery);
    const tbody = document.getElementById("warehouses_table-body");
    const loadMoreWrapper = document.getElementById("warehouses_load-more-wrapper");

    if (!tbody) return;

    if (warehouses.error) {
        tbody.innerHTML = `<tr><td colspan="7" class="admin-entity__empty">${escapeHtml(warehouses.error)}</td></tr>`;
        if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        return;
    }

    if (Array.isArray(warehouses)) {
        if (warehouses.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="admin-entity__empty">No warehouses found</td></tr>`;
            if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        } else {
            tbody.innerHTML = warehouses.map(renderWarehouseRow).join("");
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = warehouses.length >= DEFAULT_LIMIT
                    ? `<button id="warehouses_load-more-btn" class="btn btn-outline btn-sm">Load More Warehouses</button>`
                    : "";
            }
        }
    }
}

// ---------- MAIN EXPORT ---------- //
export const Warehouses = async (search = "") => {
    currentOffset = 0;
    currentQuery = search;

    const warehouses = await fetchAllWarehouses(DEFAULT_LIMIT, 0, currentQuery);
    let rows = "";
    let count = 0;
    let error = null;

    if (warehouses.error) {
        error = warehouses.error;
    } else if (Array.isArray(warehouses)) {
        rows = warehouses.map(renderWarehouseRow).join("");
        count = warehouses.length;
    }

    const loadMoreButton = count === DEFAULT_LIMIT ? `<button id="warehouses_load-more-btn" class="btn btn-outline btn-sm">Load More Warehouses</button>` : "";
    const emptyState = error
        ? `<tr><td colspan="7" class="admin-entity__empty">Error: ${escapeHtml(error)}</td></tr>`
        : `<tr><td colspan="7" class="admin-entity__empty">No warehouses found.</td></tr>`;

    return `
<div class="admin-entity">
    <div class="admin-entity__header">
        <h2 class="admin-entity__title">Warehouses Management (${count}${count === DEFAULT_LIMIT ? "+" : ""})</h2>
        <div class="admin-entity__actions">
            <input type="text" id="warehouses-search-input" class="admin-entity__search" placeholder="Search name or address..." value="${escapeHtml(currentQuery)}" />
            <button class="btn btn-primary js-admin-create" data-entity="warehouse">➕ Add Warehouse</button>
            <button id="warehouses_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="admin-entity__table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="warehouses_table-body">
                ${rows || emptyState}
            </tbody>
        </table>
    </div>

    <div id="warehouses_load-more-wrapper" style="text-align:center;">
        ${loadMoreButton}
    </div>
</div>`;
};

// ---------- EVENT DELEGATION ---------- //
export const warehousesListeners = async (container) => {
    if (!container) return null;

    const debouncedSearch = debounce((e) => performSearch(e.target.value), 300);
    const abortController = new AbortController();
    const signal = abortController.signal;

    // View button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-view');
        if (!btn || btn.dataset.entity !== 'warehouse') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Warehouses] View button clicked:', { id });

        try {
            const result = await fetchWarehouseDetails(id);
            if (!result.success) {
                throw new Error(result.error);
            }

            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = warehouseDetailsHtml(result.warehouse);
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
        if (!btn || btn.dataset.entity !== 'warehouse') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Warehouses] Edit button clicked:', { id });

        try {
            const formHtml = await renderWarehouseEdit(parseInt(id));
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initWarehouseEditHandlers(modalBody, parseInt(id), (data, action) => {
                if (action === 'updated' || action === 'deleted') {
                    Warehouses().then(html => {
                        const existingEntity = container.querySelector('.admin-entity');
                        if (existingEntity) {
                            existingEntity.outerHTML = html;
                            warehousesListeners(container);
                        }
                    });
                }
            });
        } catch (error) {
            console.error('[Warehouses] Error opening edit form:', error);
            alert(`Error: ${error.message}`);
        }
    }, { signal });

    // Create button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-create');
        if (!btn || btn.dataset.entity !== 'warehouse') return;

        e.preventDefault();
        console.log('[Warehouses] Create button clicked');

        try {
            const formHtml = await renderWarehouseCreate();
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initWarehouseCreateHandlers(modalBody, (data) => {
                Warehouses().then(html => {
                    const existingEntity = container.querySelector('.admin-entity');
                    if (existingEntity) {
                        existingEntity.outerHTML = html;
                        warehousesListeners(container);
                    }
                });
            });
        } catch (error) {
            console.error('[Warehouses] Error opening create form:', error);
            alert(`Error: ${error.message}`);
        }
    }, { signal });

    // Load More handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'warehouses_load-more-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Loading...";

        const html = await loadMoreWarehouses();
        document.getElementById("warehouses_table-body").insertAdjacentHTML("beforeend", html);

        if (html.includes("No more") || html.includes("admin-entity__empty")) {
            btn.remove();
        } else {
            btn.disabled = false;
            btn.textContent = "Load More Warehouses";
        }
    }, { signal });

    // Refresh handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'warehouses_refresh-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Refreshing...";
        await performSearch(currentQuery);
        btn.disabled = false;
        btn.textContent = "🔄 Refresh";
    }, { signal });

    // Search handler
    container.addEventListener('input', (e) => {
        if (e.target.id === 'warehouses-search-input') {
            debouncedSearch(e);
        }
    }, { signal });

    return {
        cleanup: () => abortController.abort()
    };
};