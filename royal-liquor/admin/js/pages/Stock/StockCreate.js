import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderSelect,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render stock create form with Material Design styling
 * @returns {Promise<string>} Form HTML
 */
export async function renderStockCreate() {
    // Fetch products and warehouses for dropdowns
    let products = [];
    let warehouses = [];

    try {
        const [productsRes, warehousesRes] = await Promise.all([
            apiRequest(API_ROUTES.PRODUCTS.LIST + buildQueryString({ limit: 100 })),
            apiRequest(API_ROUTES.WAREHOUSES.LIST + buildQueryString({ limit: 100 }))
        ]);
        products = productsRes.data || [];
        warehouses = warehousesRes.data || [];
    } catch (error) {
        console.error('[StockCreate] Error fetching dropdowns:', error);
    }

    return `
        <div class="admin-modal admin-modal--lg">
            <!-- Header -->
            <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                <h2 class="text-xl font-semibold text-gray-900">Create Stock Entry</h2>
                <p class="text-sm text-gray-500 mt-1">Assign product inventory to a warehouse</p>
            </div>
            
            <!-- Constraint Warning -->
            <div class="stock-create-warning" style="padding: 12px 24px; background: #fef3c7; border-bottom: 1px solid #fde68a;">
                <span style="margin-right: 8px;">⚠️</span>
                <strong>Important:</strong> Each product can only have one stock entry per warehouse. 
                If an entry already exists, you'll be redirected to edit it instead.
            </div>
            
            <!-- Body -->
            <div class="admin-modal__body bg-gray-50">
                <form id="stock-create-form" class="p-6">
                    <!-- Two Column Grid -->
                    <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                        <!-- Left Column -->
                        <div class="d-flex flex-col gap-4">
                            ${renderSelect({
        label: 'Product',
        name: 'product_id',
        required: true,
        items: products,
        valueKey: 'id',
        labelKey: 'name',
        placeholder: 'Select a product'
    })}
                            
                            ${renderSelect({
        label: 'Warehouse',
        name: 'warehouse_id',
        required: true,
        items: warehouses,
        valueKey: 'id',
        labelKey: 'name',
        placeholder: 'Select a warehouse'
    })}
                        </div>
                        
                        <!-- Right Column -->
                        <div class="d-flex flex-col gap-4">
                            ${renderTextInput({
        label: 'Initial Quantity',
        name: 'quantity',
        type: 'number',
        required: true,
        placeholder: '0',
        min: 0
    })}

                            ${renderTextInput({
        label: 'Reason / Notes',
        name: 'reason',
        type: 'text',
        required: true,
        placeholder: 'e.g., Initial inventory, Restock from supplier'
    })}
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="d-flex gap-3 justify-end border-t mt-6 pt-4">
                        <button type="button" class="btn btn-outline" id="cancel-create">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="btn-text">Create Stock</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

/**
 * Initialize stock create form handlers
 * @param {HTMLElement} container - Modal container
 * @param {Function} onSuccess - Callback after successful creation
 * @param {Function} onEditRedirect - Callback to redirect to edit existing stock
 */
export function initStockCreateHandlers(container, onSuccess, onEditRedirect) {
    const form = container.querySelector('#stock-create-form');
    const cancelBtn = container.querySelector('#cancel-create');

    if (!form) {
        console.error('[StockCreate] Form not found');
        return;
    }

    // Helper to show error
    const showError = (message) => {
        let errorEl = form.querySelector('.form-error-banner');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'form-error-banner';
            errorEl.style.cssText = 'padding: 12px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; color: #dc2626; margin-bottom: 16px;';
            form.prepend(errorEl);
        }
        errorEl.innerHTML = `⚠️ ${escapeHtml(message)}`;
        errorEl.style.display = 'block';
    };

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeModal();
        });
    }

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Clear previous errors
        const existingError = form.querySelector('.form-error-banner');
        if (existingError) existingError.style.display = 'none';

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Creating...';

            const formDataObj = getFormData(form);

            // Validate reason is provided
            if (!formDataObj.reason || formDataObj.reason.trim().length < 3) {
                throw new Error('Please provide a reason/notes for this stock entry (at least 3 characters)');
            }

            const payload = {
                product_id: parseInt(formDataObj.product_id),
                warehouse_id: parseInt(formDataObj.warehouse_id),
                quantity: parseInt(formDataObj.quantity) || 0,
                reserved: 0, // Reserved is always 0 for new stock; it's set by orders
                reason: formDataObj.reason.trim()
            };

            console.log('[StockCreate] Creating stock:', payload);

            const response = await apiRequest(API_ROUTES.STOCK.CREATE, {
                method: 'POST',
                body: payload
            });

            if (response.success) {
                console.log('[StockCreate] Stock created successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data);
            } else {
                // Check if it's a duplicate error
                if (response.message && response.message.toLowerCase().includes('already exists')) {
                    showError('A stock entry already exists for this product and warehouse combination. Please use the edit function to modify existing stock.');

                    // If we have an edit redirect callback, show a button
                    const errorEl = form.querySelector('.form-error-banner');
                    if (errorEl && onEditRedirect) {
                        errorEl.innerHTML += `<br><button type="button" class="btn btn-sm btn-primary mt-2" id="redirect-to-edit">Edit Existing Stock</button>`;
                        errorEl.querySelector('#redirect-to-edit')?.addEventListener('click', () => {
                            closeModal();
                            onEditRedirect(payload.product_id, payload.warehouse_id);
                        });
                    }
                } else {
                    throw new Error(response.message || 'Failed to create stock');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }

        } catch (error) {
            console.error('[StockCreate] Error:', error);
            showError(error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}
