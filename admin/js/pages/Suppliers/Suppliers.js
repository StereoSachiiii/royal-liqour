import { fetchAllSuppliers, fetchSupplierDetails } from "./Suppliers.utils.js";

const DEFAULT_LIMIT = 50;
let currentOffset = 0;
let currentSearch = '';

// --- HTML Generators ---

function renderSupplierRow(supplier) {
    return `
        <tr class="suppliers_row" data-supplier-id="${supplier.id}">
            <td class="suppliers_cell">${supplier.id}</td>
            <td class="suppliers_cell">${escapeHtml(supplier.name)}</td>
            <td class="suppliers_cell">${escapeHtml(supplier.email || '-')}</td>
            <td class="suppliers_cell">${escapeHtml(supplier.phone || '-')}</td>
            <td class="suppliers_cell">${escapeHtml(supplier.address || '-')}</td>
            <td class="suppliers_cell">${supplier.is_active ? 'Active' : 'Inactive'}</td>
            <td class="suppliers_cell suppliers_actions">
                <button class="suppliers_btn-view" data-id="${supplier.id}" title="View Details">👁️ View</button>
                <a href="manage/supplier/create.php" target="_blank" class="suppliers_btn-primary">Create</a>
                <a href="manage/supplier/update.php?id=${supplier.id}" class="suppliers_btn-edit" title="Edit Supplier">✏️ Edit</a>
            </td>
        </tr>
    `;
}

const supplierDetailsHtml = (supplier) => {
    const avgPrice = parseFloat(supplier.avg_product_price_cents || 0) / 100 || 0;

    return `
<div class="suppliers_card">
    <div class="suppliers_card-header">
        <span>Supplier Details</span>
        <span class="badge ${supplier.is_active ? 'badge-active' : 'badge-inactive'}">
            ${supplier.is_active ? 'Active' : 'Inactive'}
        </span>
        <button class="suppliers_close-btn">&times;</button>
    </div>

    <div class="suppliers_section-title">Basic Information</div>
    <div class="suppliers_data-grid">
        <div class="suppliers_field">
            <strong class="suppliers_label">ID</strong>
            <span class="suppliers_value">${supplier.id}</span>
        </div>
        <div class="suppliers_field">
            <strong class="suppliers_label">Name</strong>
            <span class="suppliers_value">${escapeHtml(supplier.name)}</span>
        </div>
        <div class="suppliers_field">
            <strong class="suppliers_label">Email</strong>
            <span class="suppliers_value">${escapeHtml(supplier.email || '-')}</span>
        </div>
        <div class="suppliers_field">
            <strong class="suppliers_label">Phone</strong>
            <span class="suppliers_value">${escapeHtml(supplier.phone || '-')}</span>
        </div>
        <div class="suppliers_field">
            <strong class="suppliers_label">Address</strong>
            <span class="suppliers_value">${escapeHtml(supplier.address || '-')}</span>
        </div>
    </div>

    <div class="suppliers_section-title">Stats</div>
    <div class="suppliers_data-grid">
        <div class="suppliers_field">
            <strong class="suppliers_label">Total Products</strong>
            <span class="suppliers_value">${supplier.total_products || '0'}</span>
        </div>
        <div class="suppliers_field">
            <strong class="suppliers_label">Active Products</strong>
            <span class="suppliers_value">${supplier.active_products || '0'}</span>
        </div>
        <div class="suppliers_field">
            <strong class="suppliers_label">Avg Product Price</strong>
            <span class="suppliers_value">$${avgPrice.toFixed(2)}</span>
        </div>
        <div class="suppliers_field">
            <strong class="suppliers_label">Total Inventory</strong>
            <span class="suppliers_value">${supplier.total_inventory || '0'}</span>
        </div>
    </div>

    ${supplier.products && supplier.products.length > 0 ? `
        <div class="suppliers_section-title">Products Supplied</div>
        <div class="suppliers_products-list">
            ${supplier.products.map(p => `
                <div class="suppliers_product-row">
                    <span>${escapeHtml(p.name)}</span> - $${(p.price_cents/100).toFixed(2)} 
                    <span class="badge ${p.is_active ? 'badge-active' : 'badge-inactive'}">${p.is_active ? 'Active' : 'Inactive'}</span>
                </div>
            `).join('')}
        </div>
    ` : ''}
    
    <div class="suppliers_section-title">Timeline</div>
    <div class="suppliers_data-grid">
        <div class="suppliers_field">
            <strong class="suppliers_label">Created At</strong>
            <span class="suppliers_value">${formatDate(supplier.created_at)}</span>
        </div>
        <div class="suppliers_field">
            <strong class="suppliers_label">Updated At</strong>
            <span class="suppliers_value">${formatDate(supplier.updated_at)}</span>
        </div>
    </div>

    <div class="suppliers_footer">
        <a href="manage/supplier/update.php?id=${supplier.id}" target="_blank" class="suppliers_btn-primary">Edit Supplier</a>
    </div>
</div>
`;
};

// --- Data Loaders ---

async function loadSuppliers(offset = 0, limit = DEFAULT_LIMIT, search = '') {
    return await fetchAllSuppliers(limit, offset, search);
}

async function loadMoreSuppliers() {
    currentOffset += DEFAULT_LIMIT;
    const suppliers = await loadSuppliers(currentOffset, DEFAULT_LIMIT, currentSearch);

    if (suppliers.error) {
        return `<tr><td colspan="7" class="suppliers_error-cell">Error: ${suppliers.error}</td></tr>`;
    }

    if (suppliers.length === 0) {
        return `<tr><td colspan="7" class="suppliers_no-data-cell">No more suppliers to load</td></tr>`;
    }

    return suppliers.map(supplier => renderSupplierRow(supplier)).join('');
}

// --- Main Component ---

export const Suppliers = async (search = '') => {
    currentOffset = 0;
    currentSearch = search;
    const suppliers = await loadSuppliers(0, DEFAULT_LIMIT, search);

    if (suppliers.error) {
        return `<div class="suppliers_table"><div class="suppliers_error-box">Error: ${escapeHtml(suppliers.error)}</div></div>`;
    }

    if (suppliers.length === 0 && !search) {
        return `<div class="suppliers_table"><div class="suppliers_no-data-box"><p>📭 No suppliers found.</p></div></div>`;
    }

    const tableRows = suppliers.map(renderSupplierRow).join('');
    const countText = `${suppliers.length}${suppliers.length === DEFAULT_LIMIT ? '+' : ''}`;

    return `
<div class="suppliers_table">
    <div class="suppliers_header">
        <h2>Suppliers Management (${countText})</h2>
        <input type="text" id="suppliers_search" placeholder="Search suppliers..." value="${escapeHtml(search)}" />
        <a href="manage/supplier/create.php" target="_blank" class="suppliers_btn-primary">Create</a>
        <button id="suppliers_refresh-btn" class="suppliers_btn-refresh">🔄 Refresh</button>
    </div>
    <div class="suppliers_wrapper">
        <table class="suppliers_data-table">
            <thead>
                <tr class="suppliers_header-row">
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="suppliers_table-body">
                ${tableRows}
            </tbody>
        </table>
    </div>
    <div id="suppliers_load-more-wrapper" class="suppliers_load-more-wrapper">
        ${suppliers.length === DEFAULT_LIMIT ? `
            <button id="suppliers_load-more-btn" class="suppliers_btn-load-more">Load More Suppliers</button>
        ` : ''}
    </div>
</div>
`;
};

// --- Utility Functions ---

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
        return date.toLocaleString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    } catch {
        return dateString;
    }
}

// --- Event Listeners ---

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');
    
    const debounce = (fn, wait = 300) => {
        let t = null;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), wait);
        };
    };

    // --- Search Logic ---
    async function performSearch(query) {
        try {
            // FIX: Use currentSearch (defined at top) instead of currentQuery
            currentSearch = query || '';
            currentOffset = 0;

            const searchInput = document.getElementById('suppliers_search');
            const tbody = document.getElementById('suppliers_table-body');
            const loadMoreWrapper = document.getElementById('suppliers_load-more-wrapper');
            const headerTitle = document.querySelector('.suppliers_header h2');

            if (!tbody || !searchInput) return;

            searchInput.disabled = true;
            tbody.innerHTML = `<tr><td colspan="7" class="suppliers_loading-cell">🔍 Searching...</td></tr>`;
            if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';

            const suppliers = await loadSuppliers(0, DEFAULT_LIMIT, currentSearch);

            if (headerTitle) {
                const countText = suppliers && suppliers.length > 0 ? 
                    `${suppliers.length}${suppliers.length === DEFAULT_LIMIT ? '+' : ''}` : '0';
                headerTitle.textContent = `Suppliers Management (${countText})`;
            }

            if (suppliers.error) {
                tbody.innerHTML = `<tr><td colspan="7" class="suppliers_error-cell">Error: ${escapeHtml(suppliers.error)}</td></tr>`;
                return;
            }

            if (suppliers.length === 0) {
                const message = currentSearch ? `No suppliers found matching "${escapeHtml(currentSearch)}"` : 'No suppliers found';
                tbody.innerHTML = `<tr><td colspan="7" class="suppliers_no-data-cell">${message}</td></tr>`;
                return;
            }

            tbody.innerHTML = suppliers.map(renderSupplierRow).join('');

            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = suppliers.length === DEFAULT_LIMIT ? 
                    `<button id="suppliers_load-more-btn" class="suppliers_btn-load-more">Load More Suppliers</button>` : '';
            }

        } catch (err) {
            console.error('Search error:', err);
            const tbody = document.getElementById('suppliers_table-body');
            if (tbody) tbody.innerHTML = `<tr><td colspan="7" class="suppliers_error-cell">Failed to perform search.</td></tr>`;
            const headerTitle = document.querySelector('.suppliers_header h2');
            if (headerTitle) headerTitle.textContent = 'Suppliers Management (Error)';
        } finally {
            const searchInput = document.getElementById('suppliers_search');
            if (searchInput) {
                searchInput.disabled = false;
                searchInput.focus();
                searchInput.value = searchInput.value; 
            }
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    // --- Global Click Listener ---
    document.addEventListener('click', async (e) => {
        // View Modal
        if (e.target.matches('.suppliers_btn-view') || e.target.closest('.suppliers_btn-view')) {
            const button = e.target.closest('.suppliers_btn-view');
            const supplierId = button.dataset.id;
            if (!supplierId) return;

            modalBody.innerHTML = '<div class="suppliers_loading">⏳ Loading supplier details...</div>';
            modal.classList.add('active');

            try {
                const { supplier } = await fetchSupplierDetails(Number.parseInt(supplierId));
                if (!supplier || !supplier.id) throw new Error('Invalid supplier data');
                modalBody.innerHTML = await supplierDetailsHtml(supplier);

                const closeBtn = modalBody.querySelector('.suppliers_close-btn');
                if (closeBtn) closeBtn.addEventListener('click', () => modal.classList.remove('active'));

            } catch (err) {
                console.error(err);
                modalBody.innerHTML = `
                    <div class="suppliers_error">
                        <div class="suppliers_error-icon">⚠️</div>
                        <h3 class="suppliers_error-title">Error Loading Supplier</h3>
                        <p class="suppliers_error-msg">${escapeHtml(err.message)}</p>
                        <button class="suppliers_close-btn suppliers_error-btn">Close</button>
                    </div>`;
                const errorCloseBtn = modalBody.querySelector('.suppliers_close-btn');
                if (errorCloseBtn) errorCloseBtn.addEventListener('click', () => modal.classList.remove('active'));
            }
        }

        // Load More
        if (e.target.id === 'suppliers_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';
            try {
                const html = await loadMoreSuppliers();
                document.getElementById('suppliers_table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more suppliers') || html.includes('Error')) {
                     button.remove();
                } else {
                     button.disabled = false;
                     button.textContent = 'Load More Suppliers';
                }
            } catch {
                button.disabled = false;
                button.textContent = 'Load More Suppliers';
            }
        }

        // Refresh
        if (e.target.id === 'suppliers_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';
            try {
                currentOffset = 0;
                currentSearch = ''; 
                const content = await Suppliers('');
                document.querySelector('.suppliers_table').outerHTML = content; 
            } catch {
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('active'); });
    if (modalClose) modalClose.addEventListener('click', () => modal.classList.remove('active'));

    document.addEventListener('input', (e) => {
        if (e.target.id === 'suppliers_search') {
            debouncedSearch(e);
        }
    });
});

window.loadMoreSuppliers = loadMoreSuppliers;
window.fetchAllSuppliers = fetchAllSuppliers;