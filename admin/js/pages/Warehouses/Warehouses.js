import { fetchAllWarehouses, fetchWarehouseDetails } from "./Warehouses.utils.js";

const DEFAULT_LIMIT = 50;
let currentOffset = 0;
let currentSearch = "";

// ---------- LOADING ---------- //
async function loadWarehouses(offset = 0, limit = DEFAULT_LIMIT, search = "") {
    return await fetchAllWarehouses(limit, offset, search);
}

async function loadMoreWarehouses() {
    currentOffset += DEFAULT_LIMIT;
    const warehouses = await loadWarehouses(currentOffset, DEFAULT_LIMIT, currentSearch);

    if (warehouses.error) {
        return `<tr><td colspan="7" class="warehouses_error-cell">Error: ${escapeHtml(warehouses.error)}</td></tr>`;
    }

    if (warehouses.length === 0) {
        return `<tr><td colspan="7" class="warehouses_no-data-cell">No more warehouses to load</td></tr>`;
    }

    return warehouses.map(w => renderWarehouseRow(w)).join("");
}

// ---------- ROW RENDER ---------- //
function renderWarehouseRow(w) {
    return `
<tr class="warehouses_row" data-warehouse-id="${w.id}">
    <td class="warehouses_cell">${w.id}</td>
    <td class="warehouses_cell">${escapeHtml(w.name)}</td>
    <td class="warehouses_cell">${escapeHtml(w.address || "-")}</td>
    <td class="warehouses_cell">${escapeHtml(w.phone || "-")}</td>
    <td class="warehouses_cell">
        ${w.image_url ? `<img src="${escapeHtml(w.image_url)}" class="warehouses_img">` : "-"}
    </td>
    <td class="warehouses_cell">${w.is_active ? "Active" : "Inactive"}</td>
    <td class="warehouses_cell warehouses_actions">
        <button class="warehouses_btn-view" data-id="${w.id}" title="View Details">👁️ View</button>
        <a href="manage/warehouse/update.php?id=${w.id}" class="warehouses_btn-edit" title="Edit Warehouse">✏️ Edit</a>
    </td>
</tr>`;
}

// ---------- MODAL HTML ---------- //
const warehouseDetailsHtml = (w) => {
    return `
<div class="warehouses_card">
    <div class="warehouses_card-header">
        <span>Warehouse Details</span>
        <span class="badge ${w.is_active ? "badge-active" : "badge-inactive"}">
            ${w.is_active ? "Active" : "Inactive"}
        </span>
        <button class="warehouses_close-btn">&times;</button>
    </div>

    <div class="warehouses_section-title">Basic Information</div>
    <div class="warehouses_data-grid">
        <div><strong>ID:</strong> ${w.id}</div>
        <div><strong>Name:</strong> ${escapeHtml(w.name)}</div>
        <div><strong>Address:</strong> ${escapeHtml(w.address || "-")}</div>
        <div><strong>Phone:</strong> ${escapeHtml(w.phone || "-")}</div>
        <div><strong>Created:</strong> ${formatDate(w.created_at)}</div>
        <div><strong>Updated:</strong> ${formatDate(w.updated_at)}</div>
    </div>

    <div class="warehouses_section-title">Statistics</div>
    <div class="warehouses_data-grid">
        <div><strong>Total Stock Entries:</strong> ${w.total_stock_entries}</div>
        <div><strong>Unique Products:</strong> ${w.unique_products}</div>
        <div><strong>Total Quantity:</strong> ${w.total_quantity}</div>
        <div><strong>Total Reserved:</strong> ${w.total_reserved}</div>
        <div><strong>Total Available:</strong> ${w.total_available}</div>
    </div>

    <div class="warehouses_section-title">Image</div>
    <div class="warehouses_image-wrapper">
        ${w.image_url ? `<img src="${escapeHtml(w.image_url)}" class="warehouses_modal-img">` : "No image"}
    </div>

    <div class="warehouses_footer">
        <a href="manage/warehouses/update.php?id=${w.id}" target="_blank" class="warehouses_btn-primary">Edit Warehouse</a>
    </div>
</div>`;
};

// ---------- MAIN RENDER ---------- //
export const Warehouses = async (search = "") => {
    currentOffset = 0;
    currentSearch = search;

    const warehouses = await loadWarehouses(0, DEFAULT_LIMIT, search);

    if (warehouses.error) {
        return `<div class="warehouses_table"><div class="warehouses_error-box">Error: ${escapeHtml(warehouses.error)}</div></div>`;
    }

    if (warehouses.length === 0) {
        return `<div class="warehouses_table"><div class="warehouses_no-data-box">📦 No warehouses found.</div></div>`;
    }

    const rows = warehouses.map(renderWarehouseRow).join("");

    return `
<div class="warehouses_table">
    <div class="warehouses_header">
        <h2>Warehouses (${warehouses.length}${warehouses.length === DEFAULT_LIMIT ? "+" : ""})</h2>
        <input type="text" id="warehouses_search" placeholder="Search warehouses..." value="${escapeHtml(search)}" />
        <a href="manage/warehouse/create.php" target="_blank" class="warehouses_btn-primary">Create</a>
        <button id="warehouses_refresh-btn" class="warehouses_btn-refresh">🔄 Refresh</button>
    </div>

    <div class="warehouses_wrapper">
        <table class="warehouses_data-table">
            <thead>
                <tr class="warehouses_header-row">
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
                ${rows}
            </tbody>
        </table>
    </div>

    <div id="warehouses_load-more-wrapper" class="warehouses_load-more-wrapper">
        ${warehouses.length === DEFAULT_LIMIT ? `
            <button id="warehouses_load-more-btn" class="warehouses_btn-load-more">Load More Warehouses</button>
        ` : ""}
    </div>
</div>`;
};

// ---------- UTIL ---------- //
function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateStr) {
    if (!dateStr) return "";
    try {
        const date = new Date(dateStr);
        return date.toLocaleString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit"
        });
    } catch {
        return dateStr;
    }
}

// ---------- SEARCH FUNCTION ---------- //
async function performSearch(query) {
    try {
        currentSearch = query || "";
        currentOffset = 0;
        const warehouses = await loadWarehouses(0, DEFAULT_LIMIT, currentSearch);

        const tbody = document.getElementById("warehouses_table-body");
        const loadMoreWrapper = document.getElementById("warehouses_load-more-wrapper");

        if (!tbody) return;

        if (warehouses.error) {
            tbody.innerHTML = `<tr><td colspan="7" class="warehouses_error-cell">${escapeHtml(warehouses.error)}</td></tr>`;
            if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
            return;
        }

        if (warehouses.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="warehouses_no-data-cell">📦 No warehouses found</td></tr>`;
            if (loadMoreWrapper) loadMoreWrapper.innerHTML = "";
            return;
        }

        tbody.innerHTML = warehouses.map(renderWarehouseRow).join("");
        if (loadMoreWrapper) {
            loadMoreWrapper.innerHTML = warehouses.length === DEFAULT_LIMIT 
                ? `<button id="warehouses_load-more-btn" class="warehouses_btn-load-more">Load More Warehouses</button>` 
                : "";
        }
    } catch (err) {
        console.error("Search error:", err);
    }
}

// ---------- DEBOUNCE HELPER ---------- //
function debounce(fn, wait = 300) {
    let timeout = null;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
}

// ---------- DOM EVENTS ---------- //
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modal");
    const modalBody = document.getElementById("modal-body");
    const modalClose = document.getElementById("modal-close");

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener("click", async (e) => {

        // View
        if (e.target.matches(".warehouses_btn-view") || e.target.closest(".warehouses_btn-view")) {
            const btn = e.target.closest(".warehouses_btn-view");
            const id = btn.dataset.id;

            modalBody.innerHTML = '<div class="warehouses_loading">⏳ Loading details...</div>';
            modal.classList.add("active");

            try {
                const { supplier } = await fetchWarehouseDetails(Number(id));
                modalBody.innerHTML = await warehouseDetailsHtml(supplier);

                modalBody.querySelector(".warehouses_close-btn")
                    .addEventListener("click", () => modal.classList.remove("active"));

            } catch (err) {
                modalBody.innerHTML = `
                    <div class="warehouses_error">
                        <h3>Error loading warehouse</h3>
                        <p>${escapeHtml(err.message)}</p>
                        <button class="warehouses_close-btn">Close</button>
                    </div>`;
                modalBody.querySelector(".warehouses_close-btn")
                    .addEventListener("click", () => modal.classList.remove("active"));
            }
        }

        // Load more
        if (e.target.id === "warehouses_load-more-btn") {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = "Loading...";

            const html = await loadMoreWarehouses();
            document.getElementById("warehouses_table-body").insertAdjacentHTML("beforeend", html);

            const nextBatch = await loadWarehouses(currentOffset, DEFAULT_LIMIT, currentSearch);
            if (nextBatch.length < DEFAULT_LIMIT) {
                btn.remove();
            } else {
                btn.disabled = false;
                btn.textContent = "Load More Warehouses";
            }
        }

        // Refresh
        if (e.target.id === "warehouses_refresh-btn") {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = "Refreshing...";
            
            try {
                currentOffset = 0;
                currentSearch = "";
                const content = await Warehouses("");
                const tableElement = document.querySelector(".warehouses_table");
                if (tableElement) {
                    tableElement.outerHTML = content;
                }
            } catch (error) {
                console.error("Error refreshing warehouses:", error);
                btn.disabled = false;
                btn.textContent = "🔄 Refresh";
            }
        }
    });

    // Search input with debouncing
    document.addEventListener("input", (e) => {
        if (e.target && e.target.id === "warehouses_search") {
            debouncedSearch(e);
        }
    });

    // Click outside modal
    if (modal) modal.addEventListener("click", (e) => {
        if (e.target === modal) modal.classList.remove("active");
    });
    if (modalClose) modalClose.addEventListener("click", () => modal.classList.remove("active"));
});

// Expose for debugging
window.loadMoreWarehouses = loadMoreWarehouses;
window.fetchAllWarehouses = fetchAllWarehouses;