import { fetchAllSuppliers, fetchSupplierDetails } from "./Suppliers.utils.js";
import { renderSupplierCreate, initSupplierCreateHandlers } from "./SupplierCreate.js";
import { renderSupplierEdit, initSupplierEditHandlers } from "./SupplierEdit.js";
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
function renderSupplierRow(supplier) {
    if (!supplier || !supplier.id) return '';

    return `
        <tr data-supplier-id="${supplier.id}">
            <td>${supplier.id}</td>
            <td>${escapeHtml(supplier.name || '-')}</td>
            <td>${escapeHtml(supplier.email || '-')}</td>
            <td>${escapeHtml(supplier.phone || '-')}</td>
            <td>${escapeHtml(supplier.address || '-')}</td>
            <td>
                <span class="badge badge-status-${supplier.is_active !== false ? 'active' : 'inactive'}">
                    ${supplier.is_active !== false ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>${formatDate(supplier.created_at)}</td>
            <td>
                <button class="btn btn-outline btn-sm js-admin-view" data-entity="supplier" data-id="${supplier.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm js-admin-edit" data-entity="supplier" data-id="${supplier.id}" title="Edit Supplier">✏️ Edit</button>
            </td>
        </tr>
    `;
}

// ---------- MODAL HTML ---------- //
const supplierDetailsHtml = (supplier) => {
    if (!supplier) return '<div class="admin-entity__empty">No supplier data</div>';

    const avgPrice = parseFloat(supplier.avg_product_price_cents || 0) / 100;

    return `
<div class="admin-modal admin-modal--lg">
    <div class="bg-white border-b px-6 py-4 rounded-t-xl d-flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Supplier Details</h2>
            <p class="text-sm text-gray-500">${escapeHtml(supplier.email || '')}</p>
        </div>
        <span class="badge badge-status-${supplier.is_active !== false ? 'active' : 'inactive'}">
            ${supplier.is_active !== false ? 'Active' : 'Inactive'}
        </span>
    </div>
    
    <div class="admin-modal__body bg-gray-50 p-6">
        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
            <!-- Left Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Contact Info</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">ID</span><span>${supplier.id}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Name</span><span>${escapeHtml(supplier.name || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Email</span><span>${escapeHtml(supplier.email || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Phone</span><span>${escapeHtml(supplier.phone || '-')}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Address</span><span>${escapeHtml(supplier.address || '-')}</span></div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Timeline</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Created</span><span>${supplier.created_at ? formatDate(supplier.created_at) : '-'}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Updated</span><span>${supplier.updated_at ? formatDate(supplier.updated_at) : '-'}</span></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="d-flex flex-col gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Product Statistics</h4>
                    <div class="d-grid gap-2">
                        <div class="d-flex justify-between"><span class="text-gray-500">Total Products</span><span class="font-medium">${supplier.total_products ?? 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Active Products</span><span>${supplier.active_products ?? 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Total Inventory</span><span>${supplier.total_inventory ?? 0}</span></div>
                        <div class="d-flex justify-between"><span class="text-gray-500">Avg Product Price</span><span>$${avgPrice.toFixed(2)}</span></div>
                    </div>
                </div>
                
                ${supplier.products && Array.isArray(supplier.products) && supplier.products.length > 0 ? `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Products Supplied</h4>
                    <div class="d-flex flex-col gap-2">
                        ${supplier.products.slice(0, 5).map(p => `
                            <div class="d-flex justify-between items-center py-1 border-b">
                                <span>${escapeHtml(p.name || 'Unknown')}</span>
                                <span class="font-medium">$${((p.price_cents || 0) / 100).toFixed(2)}</span>
                            </div>
                        `).join('')}
                        ${supplier.products.length > 5 ? `<div class="text-gray-400 text-sm">+ ${supplier.products.length - 5} more...</div>` : ''}
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
        
        <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
            <button class="btn btn-outline" onclick="closeModal()">Close</button>
            <button class="btn btn-primary js-admin-edit" data-entity="supplier" data-id="${supplier.id}">Edit Supplier</button>
        </div>
    </div>
</div>`;
};

// ---------- LOAD LOGIC ---------- //
async function loadMoreSuppliers() {
    currentOffset += DEFAULT_LIMIT;
    const suppliers = await fetchAllSuppliers(DEFAULT_LIMIT, currentOffset, currentQuery);

    if (suppliers.error) {
        return `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(suppliers.error)}</td></tr>`;
    }

    if (Array.isArray(suppliers) && suppliers.length === 0) {
        return `<tr><td colspan="8" class="admin-entity__empty">No more suppliers to load</td></tr>`;
    }

    return suppliers.map(renderSupplierRow).join("");
}

async function performSearch(query) {
    currentQuery = query.trim();
    currentOffset = 0;

    const suppliers = await fetchAllSuppliers(DEFAULT_LIMIT, 0, currentQuery);
    const tbody = document.getElementById("suppliers_table-body");
    const loadMoreWrapper = document.getElementById("suppliers_load-more-wrapper");

    if (!tbody) return;

    if (suppliers.error) {
        tbody.innerHTML = `<tr><td colspan="8" class="admin-entity__empty">${escapeHtml(suppliers.error)}</td></tr>`;
        if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        return;
    }

    if (Array.isArray(suppliers)) {
        if (suppliers.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="admin-entity__empty">No suppliers found</td></tr>`;
            if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
        } else {
            tbody.innerHTML = suppliers.map(renderSupplierRow).join("");
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = suppliers.length >= DEFAULT_LIMIT
                    ? `<button id="suppliers_load-more-btn" class="btn btn-outline btn-sm">Load More Suppliers</button>`
                    : "";
            }
        }
    }
}

// ---------- MAIN EXPORT ---------- //
export const Suppliers = async (search = "") => {
    currentOffset = 0;
    currentQuery = search;

    const suppliers = await fetchAllSuppliers(DEFAULT_LIMIT, 0, currentQuery);
    let rows = "";
    let count = 0;
    let error = null;

    if (suppliers.error) {
        error = suppliers.error;
    } else if (Array.isArray(suppliers)) {
        rows = suppliers.map(renderSupplierRow).join("");
        count = suppliers.length;
    }

    const loadMoreButton = count === DEFAULT_LIMIT ? `<button id="suppliers_load-more-btn" class="btn btn-outline btn-sm">Load More Suppliers</button>` : "";
    const emptyState = error
        ? `<tr><td colspan="8" class="admin-entity__empty">Error: ${escapeHtml(error)}</td></tr>`
        : `<tr><td colspan="8" class="admin-entity__empty">No suppliers found.</td></tr>`;

    return `
<div class="admin-entity">
    <div class="admin-entity__header">
        <h2 class="admin-entity__title">Suppliers Management (${count}${count === DEFAULT_LIMIT ? "+" : ""})</h2>
        <div class="admin-entity__actions">
            <input type="text" id="suppliers-search-input" class="admin-entity__search" placeholder="Search name or email..." value="${escapeHtml(currentQuery)}" />
            <button class="btn btn-primary js-admin-create" data-entity="supplier">➕ New Supplier</button>
            <button id="suppliers_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="admin-entity__table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="suppliers_table-body">
                ${rows || emptyState}
            </tbody>
        </table>
    </div>

    <div id="suppliers_load-more-wrapper" style="text-align:center;">
        ${loadMoreButton}
    </div>
</div>`;
};

// ---------- EVENT DELEGATION ---------- //
export const suppliersListeners = async (container) => {
    if (!container) return null;

    const debouncedSearch = debounce((e) => performSearch(e.target.value), 300);
    const abortController = new AbortController();
    const signal = abortController.signal;

    // View button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-view');
        if (!btn || btn.dataset.entity !== 'supplier') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Suppliers] View button clicked:', { id });

        try {
            const result = await fetchSupplierDetails(id);
            if (!result.success) {
                throw new Error(result.error);
            }

            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = supplierDetailsHtml(result.supplier);
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
        if (!btn || btn.dataset.entity !== 'supplier') return;

        const id = btn.dataset.id;
        if (!id) return;

        console.log('[Suppliers] Edit button clicked:', { id });

        try {
            const formHtml = await renderSupplierEdit(parseInt(id));
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initSupplierEditHandlers(modalBody, parseInt(id), (data, action) => {
                if (action === 'updated' || action === 'deleted') {
                    Suppliers().then(html => {
                        const existingEntity = container.querySelector('.admin-entity');
                        if (existingEntity) {
                            existingEntity.outerHTML = html;
                            suppliersListeners(container);
                        }
                    });
                }
            });
        } catch (error) {
            console.error('[Suppliers] Error opening edit form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Create button handler
    container.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-admin-create');
        if (!btn || btn.dataset.entity !== 'supplier') return;

        e.preventDefault();
        console.log('[Suppliers] Create button clicked');

        try {
            const formHtml = await renderSupplierCreate();
            const modal = document.getElementById('modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML = formHtml;
            modal.classList.remove('hidden');
            modal.classList.add('active');
            modal.style.display = 'flex';

            initSupplierCreateHandlers(modalBody, (data) => {
                Suppliers().then(html => {
                    const existingEntity = container.querySelector('.admin-entity');
                    if (existingEntity) {
                        existingEntity.outerHTML = html;
                        suppliersListeners(container);
                    }
                });
            });
        } catch (error) {
            console.error('[Suppliers] Error opening create form:', error);
            openStandardModal({
                title: 'Error',
                bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
            });
        }
    }, { signal });

    // Load More handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'suppliers_load-more-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Loading...";

        const html = await loadMoreSuppliers();
        document.getElementById("suppliers_table-body").insertAdjacentHTML("beforeend", html);

        if (html.includes("No more") || html.includes("admin-entity__empty")) {
            btn.remove();
        } else {
            btn.disabled = false;
            btn.textContent = "Load More Suppliers";
        }
    }, { signal });

    // Refresh handler
    container.addEventListener('click', async (e) => {
        if (e.target.id !== 'suppliers_refresh-btn') return;

        const btn = e.target;
        btn.disabled = true;
        btn.textContent = "Refreshing...";
        await performSearch(currentQuery);
        btn.disabled = false;
        btn.textContent = "🔄 Refresh";
    }, { signal });

    // Search handler
    container.addEventListener('input', (e) => {
        if (e.target.id === 'suppliers-search-input') {
            debouncedSearch(e);
        }
    }, { signal });

    return {
        cleanup: () => abortController.abort()
    };
};