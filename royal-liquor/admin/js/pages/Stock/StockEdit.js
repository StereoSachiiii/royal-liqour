import { API_ROUTES, buildQueryString } from '../../dashboard.routes.js';
import {
    renderTextInput,
    renderSelect,
    getFormData
} from '../../FormHelpers.js';
import { apiRequest, escapeHtml, closeModal } from '../../utils.js';

/**
 * Render stock edit form (adjustment-based) with Material Design styling
 * @param {number} stockId - Stock ID to edit
 * @returns {Promise<string>} Form HTML
 */
export async function renderStockEdit(stockId) {
    try {
        // Fetch enriched stock data
        const stockRes = await apiRequest(API_ROUTES.STOCK.GET(stockId));
        const stock = stockRes.data || {};
        const available = (stock.quantity || 0) - (stock.reserved || 0);

        return `
            <div class="admin-modal admin-modal--lg">
                <!-- Header -->
                <div class="bg-white border-b px-6 py-4 rounded-t-xl">
                    <h2 class="text-xl font-semibold text-gray-900">Adjust Stock</h2>
                    <p class="text-sm text-gray-500 mt-1">${escapeHtml(stock.product_name || 'Product')} @ ${escapeHtml(stock.warehouse_name || 'Warehouse')}</p>
                </div>
                
                <!-- Warning about direct edits -->
                <div class="stock-edit-warning" style="padding: 12px 24px; background: #dbeafe; border-bottom: 1px solid #bfdbfe;">
                    <span style="margin-right: 8px;">ℹ️</span>
                    <strong>Stock Audit:</strong> All quantity changes require a reason for audit trail purposes.
                    Reserved stock is managed automatically by pending orders.
                </div>
                
                <!-- Body -->
                <div class="admin-modal__body bg-gray-50">
                    <form id="stock-edit-form" class="p-6" data-stock-id="${stockId}">
                        <!-- Current Stock Info (Read Only) -->
                        <div class="bg-white p-4 rounded-lg border mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Current Stock Levels</h4>
                            <div class="d-grid gap-4" style="grid-template-columns: repeat(4, 1fr);">
                                <div class="text-center">
                                    <div class="text-2xl font-bold">${stock.quantity || 0}</div>
                                    <div class="text-xs text-gray-500">Total Quantity</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-warning">${stock.reserved || 0}</div>
                                    <div class="text-xs text-gray-500">Reserved</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold ${available <= 0 ? 'text-danger' : available < 20 ? 'text-warning' : 'text-success'}">${available}</div>
                                    <div class="text-xs text-gray-500">Available</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold">${stock.pending_orders?.length || 0}</div>
                                    <div class="text-xs text-gray-500">Pending Orders</div>
                                </div>
                            </div>
                        </div>

                        ${stock.reserved > 0 ? `
                        <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200 mb-4">
                            <span class="text-warning">⚠️</span>
                            <strong>Warning:</strong> ${stock.reserved} units are reserved for pending orders. 
                            You cannot reduce quantity below this amount.
                        </div>
                        ` : ''}
                        
                        <!-- Adjustment Form -->
                        <div class="d-grid gap-6" style="grid-template-columns: 1fr 1fr;">
                            <!-- Left Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Adjustment Type',
            name: 'adjustment_type',
            required: true,
            items: [
                { value: 'add', label: '➕ Add Stock (Restock)' },
                { value: 'remove', label: '➖ Remove Stock (Damage/Loss)' },
                { value: 'set', label: '🔢 Set Absolute Value (Inventory Count)' }
            ],
            valueKey: 'value',
            labelKey: 'label',
            placeholder: 'Select adjustment type'
        })}
                                
                                ${renderTextInput({
            label: 'Amount',
            name: 'adjustment_amount',
            type: 'number',
            required: true,
            placeholder: 'Enter quantity',
            min: 0
        })}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="d-flex flex-col gap-4">
                                ${renderSelect({
            label: 'Reason Category',
            name: 'reason_category',
            required: true,
            items: [
                { value: 'restock', label: 'Restock from Supplier' },
                { value: 'damage', label: 'Damaged Goods' },
                { value: 'loss', label: 'Lost/Stolen' },
                { value: 'inventory_count', label: 'Physical Inventory Count' },
                { value: 'return', label: 'Customer Return' },
                { value: 'transfer_in', label: 'Transfer In (from another warehouse)' },
                { value: 'transfer_out', label: 'Transfer Out (to another warehouse)' },
                { value: 'other', label: 'Other (specify below)' }
            ],
            valueKey: 'value',
            labelKey: 'label',
            placeholder: 'Select reason'
        })}
                                
                                ${renderTextInput({
            label: 'Notes / Details',
            name: 'reason_notes',
            type: 'text',
            required: true,
            placeholder: 'e.g., Invoice #12345, Damaged in transit'
        })}
                            </div>
                        </div>
                        
                        <!-- Preview -->
                        <div id="adjustment-preview" class="bg-gray-100 p-4 rounded-lg mt-4" style="display: none;">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">📊 Adjustment Preview</h4>
                            <div id="preview-content"></div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="d-flex gap-3 justify-between border-t mt-6 pt-4">
                            <button type="button" class="btn btn-danger btn-outline" id="delete-stock">
                                🗑️ Delete Stock Entry
                            </button>
                            <div class="d-flex gap-3">
                                <button type="button" class="btn btn-outline" id="cancel-edit">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <span class="btn-text">Apply Adjustment</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('[StockEdit] Error rendering form:', error);
        return `
            <div class="admin-modal admin-modal--sm">
                <div class="p-8 text-center">
                    <div class="text-danger text-4xl mb-4">⚠️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to Load Stock</h3>
                    <p class="text-gray-500">${escapeHtml(error.message)}</p>
                    <button class="btn btn-outline mt-4" onclick="closeModal()">Close</button>
                </div>
            </div>
        `;
    }
}

/**
 * Initialize stock edit form handlers
 * @param {HTMLElement} container - Modal container
 * @param {number} stockId - Stock ID being edited
 * @param {Function} onSuccess - Callback after successful update
 */
export function initStockEditHandlers(container, stockId, onSuccess) {
    const form = container.querySelector('#stock-edit-form');
    const cancelBtn = container.querySelector('#cancel-edit');
    const deleteBtn = container.querySelector('#delete-stock');
    const adjustmentType = form?.querySelector('[name="adjustment_type"]');
    const adjustmentAmount = form?.querySelector('[name="adjustment_amount"]');
    const previewDiv = container.querySelector('#adjustment-preview');
    const previewContent = container.querySelector('#preview-content');

    if (!form) {
        console.error('[StockEdit] Form not found');
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

    // Get current values from the data stored in the form
    const getCurrentQuantity = () => {
        const statsDiv = container.querySelector('.d-grid.gap-4');
        if (statsDiv) {
            const qtyText = statsDiv.querySelector('.text-2xl.font-bold')?.textContent;
            return parseInt(qtyText) || 0;
        }
        return 0;
    };

    const getReserved = () => {
        const statsDiv = container.querySelector('.d-grid.gap-4');
        if (statsDiv) {
            const reservedEl = statsDiv.querySelector('.text-warning');
            return parseInt(reservedEl?.textContent) || 0;
        }
        return 0;
    };

    // Preview update
    const updatePreview = () => {
        if (!previewDiv || !previewContent) return;

        const type = adjustmentType?.value;
        const amount = parseInt(adjustmentAmount?.value) || 0;
        const currentQty = getCurrentQuantity();
        const reserved = getReserved();

        if (!type || !amount) {
            previewDiv.style.display = 'none';
            return;
        }

        let newQuantity;
        let operation;

        switch (type) {
            case 'add':
                newQuantity = currentQty + amount;
                operation = `${currentQty} + ${amount} = ${newQuantity}`;
                break;
            case 'remove':
                newQuantity = currentQty - amount;
                operation = `${currentQty} - ${amount} = ${newQuantity}`;
                break;
            case 'set':
                newQuantity = amount;
                operation = `Set to ${newQuantity}`;
                break;
            default:
                previewDiv.style.display = 'none';
                return;
        }

        const newAvailable = newQuantity - reserved;
        const valid = newQuantity >= reserved;

        previewContent.innerHTML = `
            <div class="d-flex justify-between items-center">
                <span>New Quantity: <strong>${operation}</strong></span>
                <span class="${valid ? 'text-success' : 'text-danger'} font-medium">
                    ${valid ? '✓ Valid' : `✗ Cannot go below reserved (${reserved})`}
                </span>
            </div>
            <div class="text-sm text-gray-500 mt-1">
                New Available: ${newAvailable} units
            </div>
        `;
        previewDiv.style.display = 'block';
    };

    // Add preview listeners
    adjustmentType?.addEventListener('change', updatePreview);
    adjustmentAmount?.addEventListener('input', updatePreview);

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeModal();
        });
    }

    // Delete button with double-click confirmation
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            // Double-click confirmation pattern
            if (!deleteBtn.dataset.confirmed) {
                deleteBtn.dataset.confirmed = 'pending';
                deleteBtn.innerHTML = '⚠️ Click again to confirm delete';
                deleteBtn.classList.add('btn-warning');
                deleteBtn.classList.remove('btn-outline');

                setTimeout(() => {
                    if (deleteBtn.dataset.confirmed === 'pending') {
                        deleteBtn.dataset.confirmed = '';
                        deleteBtn.innerHTML = '🗑️ Delete Stock Entry';
                        deleteBtn.classList.remove('btn-warning');
                        deleteBtn.classList.add('btn-outline');
                    }
                }, 3000);
                return;
            }

            try {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner"></span> Deleting...';

                const response = await apiRequest(API_ROUTES.STOCK.DELETE(stockId), {
                    method: 'DELETE'
                });

                if (response.success) {
                    console.log('[StockEdit] Stock deleted successfully');
                    closeModal();
                    if (onSuccess) onSuccess(null, 'deleted');
                } else {
                    throw new Error(response.message || 'Failed to delete stock');
                }
            } catch (error) {
                console.error('[StockEdit] Delete error:', error);
                showError(error.message);
                deleteBtn.disabled = false;
                deleteBtn.dataset.confirmed = '';
                deleteBtn.innerHTML = '🗑️ Delete Stock Entry';
                deleteBtn.classList.remove('btn-warning');
                deleteBtn.classList.add('btn-outline');
            }
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
            submitBtn.innerHTML = '<span class="spinner"></span> Applying...';

            const formDataObj = getFormData(form);
            const type = formDataObj.adjustment_type;
            const amount = parseInt(formDataObj.adjustment_amount) || 0;
            const reasonCategory = formDataObj.reason_category;
            const reasonNotes = formDataObj.reason_notes?.trim();

            // Validate
            if (!type) throw new Error('Please select an adjustment type');
            if (!amount || amount <= 0) throw new Error('Please enter a valid amount');
            if (!reasonCategory) throw new Error('Please select a reason category');
            if (!reasonNotes || reasonNotes.length < 3) throw new Error('Please provide notes/details (at least 3 characters)');

            const currentQty = getCurrentQuantity();
            const reserved = getReserved();

            let newQuantity;
            let adjustment;

            switch (type) {
                case 'add':
                    newQuantity = currentQty + amount;
                    adjustment = amount;
                    break;
                case 'remove':
                    newQuantity = currentQty - amount;
                    adjustment = -amount;
                    break;
                case 'set':
                    newQuantity = amount;
                    adjustment = amount - currentQty;
                    break;
                default:
                    throw new Error('Invalid adjustment type');
            }

            // Check against reserved
            if (newQuantity < reserved) {
                throw new Error(`Cannot reduce quantity below reserved amount (${reserved}). Cancel pending orders first.`);
            }

            const reason = `[${reasonCategory.toUpperCase()}] ${reasonNotes}`;

            console.log('[StockEdit] Adjusting stock:', { stockId, adjustment, newQuantity, reason });

            // Use the update endpoint with the new quantity and reason
            const response = await apiRequest(API_ROUTES.STOCK.UPDATE(stockId), {
                method: 'PUT',
                body: {
                    quantity: newQuantity,
                    reason: reason
                }
            });

            if (response.success) {
                console.log('[StockEdit] Stock adjusted successfully:', response.data);
                closeModal();
                if (onSuccess) onSuccess(response.data, 'updated');
            } else {
                throw new Error(response.message || 'Failed to adjust stock');
            }

        } catch (error) {
            console.error('[StockEdit] Error:', error);
            showError(error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}
