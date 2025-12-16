<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Stock Entry</title>
    <link rel="stylesheet" href="../admin.css">
    <style>
        .stock-operations {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .operation-card {
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .operation-card h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .warning-box {
            padding: 15px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            margin: 15px 0;
        }
        .danger-box {
            padding: 15px;
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            border-radius: 4px;
            margin: 15px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .info-card {
            padding: 15px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .info-card h4 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
        }
        .info-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Update Stock Entry</h1>

        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>Stock Overview</h2>
            
            <div class="info-grid">
                <div class="info-card">
                    <h4>Total Quantity</h4>
                    <div class="value" id="qv_quantity">--</div>
                </div>
                <div class="info-card">
                    <h4>Reserved</h4>
                    <div class="value" id="qv_reserved">--</div>
                </div>
                <div class="info-card">
                    <h4>Available</h4>
                    <div class="value" id="qv_available">--</div>
                </div>
                <div class="info-card">
                    <h4>Inventory Value</h4>
                    <div class="value" id="qv_value">--</div>
                </div>
            </div>

            <div class="detail-section">
                <h3>Product Information</h3>
                <div class="detail-field"><strong>Name:</strong> <span id="qv_product_name">--</span></div>
                <div class="detail-field"><strong>SKU/Slug:</strong> <span id="qv_product_slug">--</span></div>
                <div class="detail-field"><strong>Price:</strong> <span id="qv_product_price">--</span></div>
            </div>

            <div class="detail-section">
                <h3>Warehouse Information</h3>
                <div class="detail-field"><strong>Name:</strong> <span id="qv_warehouse_name">--</span></div>
                <div class="detail-field"><strong>Address:</strong> <span id="qv_warehouse_address">--</span></div>
            </div>

            <div class="detail-section" id="warehouseBreakdown">
                <h3>Stock Across All Warehouses (This Product)</h3>
                <div id="warehouse_list">Loading...</div>
            </div>

            <div id="warningSection"></div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <!-- STOCK OPERATIONS -->
        <div class="stock-operations">
            <!-- Transfer Stock -->
            <div class="operation-card">
                <h3>🔄 Transfer to Another Warehouse</h3>
                <form id="transferForm">
                    <div class="form-group">
                        <label class="form-label">Transfer Quantity</label>
                        <input type="number" id="transfer_quantity" class="form-input" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Destination Warehouse</label>
                        <input list="warehousesList" id="dest_warehouse_search" class="form-input" placeholder="Search..." autocomplete="off" required>
                        <datalist id="warehousesList"></datalist>
                        <input type="hidden" id="dest_warehouse_id">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reason/Notes</label>
                        <textarea id="transfer_reason" class="form-input" rows="2" placeholder="Why are you transferring this stock?"></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Transfer Stock</button>
                </form>
            </div>

            <!-- Adjust Stock -->
            <div class="operation-card">
                <h3>📦 Adjust Stock Level</h3>
                <form id="adjustForm">
                    <div class="form-group">
                        <label class="form-label">Adjustment (+/-)</label>
                        <input type="number" id="adjustment" class="form-input" required placeholder="e.g. +50 or -10">
                        <div class="help-text">Positive to add, negative to remove</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Reason</label>
                        <select id="adjust_reason_type" class="form-input">
                            <option value="">Select reason...</option>
                            <option value="restock">Restocking from supplier</option>
                            <option value="damage">Damaged/Broken items</option>
                            <option value="theft">Theft/Loss</option>
                            <option value="found">Found/Inventory correction</option>
                            <option value="expired">Expired products</option>
                            <option value="return">Customer return</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Additional Notes</label>
                        <textarea id="adjust_notes" class="form-input" rows="2" placeholder="Additional details..."></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Adjust Stock</button>
                </form>
            </div>
        </div>

        <!-- DELETE SECTION -->
        <div class="edit-card" style="margin-top: 30px;">
            <h2>⚠️ Danger Zone</h2>
            <div class="danger-box">
                <strong>Delete Stock Entry:</strong> This will permanently remove this stock record. 
                This action is prevented if there are active reservations.
            </div>
            <div class="form-actions">
                <button type="button" id="deleteBtn" class="btn-danger">Delete Stock Entry</button>
            </div>
        </div>
    </div>

    <!-- CONFIRM MODAL -->
    <div id="confirmModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle">Confirm Action</div>
            <div class="modal-body" id="modalMessage"></div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-primary">Confirm</button>
            </div>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header">Operation Successful</div>
            <div class="modal-body" id="successModalMessage"></div>
            <div class="modal-actions">
                <button id="successModalStay" class="btn-secondary">Stay on Page</button>
                <button id="successModalReturn" class="btn-primary">Return to List</button>
            </div>
        </div>
    </div>

    <script type="module">
        const API_STOCK = '/royal-liquor/api/v1/stock';
        const API_WAREHOUSES = '/royal-liquor/api/v1/warehouses';

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        if (!recordId) {
            alert('No stock ID provided');
            window.location.href = '../../index.php';
        }

        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const modal = document.getElementById('confirmModal');
        const successModal = document.getElementById('successModal');

        let currentStock = null;
        let allWarehouses = [];

        const el = id => document.getElementById(id);

        // Fetch helper
        async function fetchHandler(url, method = 'GET', body = null) {
            const options = {
                method,
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin'
            };
            if (body && method !== 'GET') {
                options.body = JSON.stringify(body);
            }
            const response = await fetch(url, options);
            if (!response.ok) {
                const text = await response.text();
                throw new Error(text || 'Request failed');
            }
            const json = await response.json();
            return json.data ?? json;
        }

        // Load warehouses for transfer
        async function loadWarehouses() {
            try {
                const res = await fetchHandler(`${API_WAREHOUSES}?limit=500`);
                allWarehouses = res.items || [];
                
                const datalist = el('warehousesList');
                datalist.innerHTML = '';
                
                allWarehouses.forEach(w => {
                    const opt = document.createElement('option');
                    opt.value = w.name;
                    opt.dataset.id = w.id;
                    datalist.appendChild(opt);
                });
            } catch (e) {
                console.error('Failed to load warehouses:', e);
            }
        }

        // Load stock data
        async function loadStock() {
            try {
                const data = await fetchHandler(`${API_STOCK}/${recordId}?enriched=true`);
                currentStock = data.data
                const record = data.data

                // Quick view - basic info
                el('qv_quantity').textContent = record.quantity || 0;
                el('qv_reserved').textContent = record.reserved || 0;
                el('qv_available').textContent = record.available || 0;
                el('qv_value').textContent = record.inventory_value ? `LKR ${Number(record.inventory_value).toFixed(2)}` : '--';

                // Product info
                el('qv_product_name').textContent = record.product_name || '--';
                el('qv_product_slug').textContent = record.product_slug || '--';
                el('qv_product_price').textContent = record.price_cents ? `LKR ${Number((record.price_cents / 100)).toFixed(2)}` : '--';

                // Warehouse info
                el('qv_warehouse_name').textContent = record.warehouse_name || '--';
                el('qv_warehouse_address').textContent = record.warehouse_address || '--';

                // Load all stock for this product across warehouses
                await loadProductStockBreakdown(record.product_id);

                // Show warnings
                showWarnings(record);

            } catch (err) {
                errorDiv.textContent = 'Failed to load stock: ' + (err.message || 'Unknown error');
                errorDiv.style.display = 'block';
            }
        }

        // Load stock breakdown across all warehouses for this product
        async function loadProductStockBreakdown(productId) {
            try {
                const res = await fetchHandler(`${API_STOCK}?limit=100`);
                const productStocks = (res.items || []).filter(s => s.product_id === productId);
                
                const listDiv = el('warehouse_list');
                if (productStocks.length === 0) {
                    listDiv.innerHTML = '<em>No stock entries for this product</em>';
                    return;
                }

                listDiv.innerHTML = productStocks.map(s => `
                    <div style="padding: 10px; margin: 5px 0; background: white; border-radius: 4px; border-left: 3px solid ${s.id === recordId ? '#2196F3' : '#ccc'}">
                        <strong>${s.warehouse_name}</strong> ${s.id === recordId ? '(Current)' : ''}<br>
                        Quantity: <strong>${s.quantity}</strong> | 
                        Reserved: <strong>${s.reserved}</strong> | 
                        Available: <strong>${s.available || (s.quantity - s.reserved)}</strong>
                    </div>
                `).join('');

            } catch (e) {
                console.error('Failed to load product stock breakdown:', e);
            }
        }

        // Show warnings
        function showWarnings(data) {
            const section = el('warningSection');
            section.innerHTML = '';

            const available = data.available || 0;
            const reserved = data.reserved || 0;

            if (available < 50) {
                section.innerHTML += `
                    <div class="warning-box">
                        <strong>⚠️ Low Stock Alert:</strong> Available stock (${available}) is below threshold (50 units).
                        Consider restocking soon to avoid stockouts.
                    </div>
                `;
            }

            if (reserved > 0) {
                section.innerHTML += `
                    <div class="warning-box">
                        <strong>🔒 Reserved Stock:</strong> ${reserved} units are reserved for pending orders.
                        Cannot reduce quantity below ${reserved}.
                    </div>
                `;
            }

            if (available === 0 && reserved === 0 && data.quantity === 0) {
                section.innerHTML += `
                    <div class="danger-box">
                        <strong>❌ Out of Stock:</strong> No inventory available. Immediate restocking required.
                    </div>
                `;
            }
        }

        // Handle warehouse selection for transfer
        el('dest_warehouse_search').addEventListener('input', e => {
            const val = e.target.value;
            const found = [...document.querySelectorAll('#warehousesList option')].find(o => o.value === val);
            el('dest_warehouse_id').value = found ? found.dataset.id : '';
        });

        // Transfer form
        el('transferForm').addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.textContent = '';
            successDiv.textContent = '';

            const quantity = Number(el('transfer_quantity').value);
            const destWarehouseId = Number(el('dest_warehouse_id').value);
            const reason = el('transfer_reason').value.trim();

            if (!destWarehouseId) {
                errorDiv.textContent = 'Please select a destination warehouse';
                errorDiv.style.display = 'block';
                return;
            }

            if (destWarehouseId === currentStock.warehouse_id) {
                errorDiv.textContent = 'Cannot transfer to the same warehouse';
                errorDiv.style.display = 'block';
                return;
            }

            if (quantity > currentStock.available) {
                errorDiv.textContent = `Cannot transfer more than available stock (${currentStock.available})`;
                errorDiv.style.display = 'block';
                return;
            }

            const destWarehouse = allWarehouses.find(w => w.id === destWarehouseId);
            
            el('modalTitle').textContent = 'Confirm Stock Transfer';
            el('modalMessage').innerHTML = `
                Transfer <strong>${quantity} units</strong> of <strong>${currentStock.product_name}</strong><br>
                From: <strong>${currentStock.warehouse_name}</strong><br>
                To: <strong>${destWarehouse?.name || 'Unknown'}</strong><br>
                Reason: ${reason || 'No reason provided'}
            `;
            modal.style.display = 'flex';

            modal.dataset.action = 'transfer';
            modal.dataset.payload = JSON.stringify({ quantity, destWarehouseId, reason });
        });

        // Adjust form
        el('adjustForm').addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.textContent = '';
            successDiv.textContent = '';

            const adjustment = Number(el('adjustment').value);
            const reasonType = el('adjust_reason_type').value;
            const notes = el('adjust_notes').value.trim();

            if (!reasonType) {
                errorDiv.textContent = 'Please select a reason for adjustment';
                errorDiv.style.display = 'block';
                return;
            }

            const newQuantity = currentStock.quantity + adjustment;
            
            if (newQuantity < currentStock.reserved) {
                errorDiv.textContent = `Cannot reduce below reserved amount (${currentStock.reserved}). New quantity would be ${newQuantity}.`;
                errorDiv.style.display = 'block';
                return;
            }

            const reason = `${reasonType}: ${notes || 'No additional notes'}`;

            el('modalTitle').textContent = 'Confirm Stock Adjustment';
            el('modalMessage').innerHTML = `
                Adjust stock for <strong>${currentStock.product_name}</strong><br>
                Warehouse: <strong>${currentStock.warehouse_name}</strong><br>
                Current Quantity: <strong>${currentStock.quantity}</strong><br>
                Adjustment: <strong>${adjustment > 0 ? '+' : ''}${adjustment}</strong><br>
                New Quantity: <strong>${newQuantity}</strong><br>
                Reason: <strong>${reasonType}</strong><br>
                Notes: ${notes || 'None'}
            `;
            modal.style.display = 'flex';

            modal.dataset.action = 'adjust';
            modal.dataset.payload = JSON.stringify({ adjustment, reason });
        });

        // Delete button
        el('deleteBtn').onclick = () => {
            if (currentStock.reserved > 0) {
                errorDiv.textContent = `Cannot delete: ${currentStock.reserved} units are reserved for orders`;
                errorDiv.style.display = 'block';
                return;
            }

            el('modalTitle').textContent = 'Confirm Delete';
            el('modalMessage').innerHTML = `
                <div class="danger-box">
                    This will permanently delete the stock entry for:<br>
                    <strong>${currentStock.product_name}</strong> at <strong>${currentStock.warehouse_name}</strong><br>
                    Current Quantity: <strong>${currentStock.quantity}</strong>
                </div>
            `;
            modal.style.display = 'flex';
            modal.dataset.action = 'delete';
        };

        // Modal actions
        el('modalCancel').onclick = () => modal.style.display = 'none';
        el('modalConfirm').onclick = async () => {
            modal.style.display = 'none';
            const action = modal.dataset.action;

            try {
                if (action === 'transfer') {
                    const payload = JSON.parse(modal.dataset.payload);
                    await performTransfer(payload);
                } else if (action === 'adjust') {
                    const payload = JSON.parse(modal.dataset.payload);
                    await performAdjustment(payload);
                } else if (action === 'delete') {
                    await performDelete();
                }
            } catch (err) {
                errorDiv.textContent = err.message || 'Operation failed';
                errorDiv.style.display = 'block';
            }
        };

        // Perform transfer
        async function performTransfer({ quantity, destWarehouseId, reason }) {
            // Reduce from current warehouse
            await fetchHandler(`${API_STOCK}?id=${recordId}`, 'PUT', {
                quantity: currentStock.quantity - quantity
            });

            // Check if dest has stock entry
            const destStock = await fetchHandler(
                `${API_STOCK}?product_id=${currentStock.product_id}&warehouse_id=${destWarehouseId}`
            ).catch(() => null);

            if (destStock) {
                // Update existing
                await fetchHandler(`${API_STOCK}?id=${destStock.id}`, 'PUT', {
                    quantity: destStock.quantity + quantity
                });
            } else {
                // Create new
                await fetchHandler(API_STOCK, 'POST', {
                    product_id: currentStock.product_id,
                    warehouse_id: destWarehouseId,
                    quantity: quantity,
                    reserved: 0
                });
            }

            // Log the transfer
            console.log(`STOCK TRANSFER: ${quantity} units of product ${currentStock.product_id} from warehouse ${currentStock.warehouse_id} to ${destWarehouseId}. Reason: ${reason}`);

            el('successModalMessage').innerHTML = `
                Successfully transferred <strong>${quantity} units</strong><br>
                ${reason ? `Reason: ${reason}` : ''}
            `;
            successModal.style.display = 'flex';
            
            await loadStock(); // Reload
        }

        // Perform adjustment
        async function performAdjustment({ adjustment, reason }) {
            await fetchHandler(API_STOCK, 'POST', {
                adjust: true,
                product_id: currentStock.product_id,
                warehouse_id: currentStock.warehouse_id,
                adjustment: adjustment,
                reason: reason
            });

            console.log(`STOCK ADJUSTMENT: ${adjustment > 0 ? '+' : ''}${adjustment} for product ${currentStock.product_id} at warehouse ${currentStock.warehouse_id}. Reason: ${reason}`);

            el('successModalMessage').innerHTML = `
                Stock adjusted by <strong>${adjustment > 0 ? '+' : ''}${adjustment}</strong><br>
                Reason: ${reason}
            `;
            successModal.style.display = 'flex';
            
            await loadStock(); // Reload
        }

        // Perform delete
        async function performDelete() {
            await fetchHandler(`${API_STOCK}?id=${recordId}`, 'DELETE');

            el('successModalMessage').innerHTML = `
                Stock entry deleted successfully.
            `;
            successModal.style.display = 'flex';
            
            setTimeout(() => location.href = '../../index.php', 1500);
        }

        // Success modal handlers
        el('successModalStay').onclick = () => {
            successModal.style.display = 'none';
            el('transferForm').reset();
            el('adjustForm').reset();
        };

        el('successModalReturn').onclick = () => {
            location.href = '../../index.php';
        };

        // Initialize
        loadWarehouses();
        loadStock();
    </script>
</body>
</html>