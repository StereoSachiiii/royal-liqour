<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Stock Entry</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Stock Entry</h1>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>New Stock Entry</h2>
            <form id="mainForm">
                <div class="form-group">
                    <label for="product_search" class="form-label required">Product</label>
                    <input 
                        list="productsList" 
                        id="product_search" 
                        class="form-input" 
                        placeholder="Search product by name or SKU..." 
                        required 
                        autocomplete="off"
                    >
                    <datalist id="productsList"></datalist>
                    <input type="hidden" id="product_id" name="product_id">
                    <div class="help-text">Selected product details will appear below</div>
                    <div id="productDetails" class="detail-box" style="display:none; margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        <strong>Product:</strong> <span id="prod_name">--</span><br>
                        <strong>Price:</strong> <span id="prod_price">--</span><br>
                        <strong>Current Total Stock:</strong> <span id="prod_stock">--</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="warehouse_search" class="form-label required">Warehouse</label>
                    <input 
                        list="warehousesList" 
                        id="warehouse_search" 
                        class="form-input" 
                        placeholder="Search warehouse by name..." 
                        required 
                        autocomplete="off"
                    >
                    <datalist id="warehousesList"></datalist>
                    <input type="hidden" id="warehouse_id" name="warehouse_id">
                    <div class="help-text">Select the warehouse where stock will be stored</div>
                    <div id="warehouseDetails" class="detail-box" style="display:none; margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        <strong>Warehouse:</strong> <span id="wh_name">--</span><br>
                        <strong>Address:</strong> <span id="wh_address">--</span><br>
                        <strong>Total Items:</strong> <span id="wh_items">--</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="quantity" class="form-label required">Initial Quantity</label>
                    <input 
                        type="number" 
                        id="quantity" 
                        name="quantity" 
                        class="form-input" 
                        min="0" 
                        value="0" 
                        required
                    >
                    <div class="help-text">Starting quantity for this product at this warehouse (default: 0)</div>
                </div>

                <div class="info-box" style="margin: 20px 0; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
                    <strong>ℹ️ Note:</strong>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Reserved quantity will start at 0</li>
                        <li>Cannot create duplicate entries for same product-warehouse combination</li>
                        <li>Stock can be adjusted after creation</li>
                    </ul>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Stock Entry</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <h2>Stock Entry Created Successfully!</h2>
            <p id="modalMessage">New stock entry has been added.</p>
            <div class="modal-actions">
                <button id="modalClose" class="btn-secondary">Close</button>
                <a id="viewLink" class="btn-primary">View/Edit Entry</a>
            </div>
        </div>
    </div>

    <script type="module">
        import { fetchHandler, API_PRODUCT, API_WAREHOUSE, API_STOCK } from '../utils.js';
        
        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const modal = document.getElementById('successModal');
        const viewLink = document.getElementById('viewLink');
        const modalClose = document.getElementById('modalClose');

        // Load products
        async function populateProducts() {
            try {
                const res = await fetchHandler(`${API_PRODUCT}?limit=1000`, 'GET');
                const datalist = document.getElementById('productsList');
                datalist.innerHTML = '';
                
                (res.items || []).forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = `${p.name} (${p.slug})`;
                    opt.textContent = opt.value;
                    opt.dataset.id = p.id;
                    opt.dataset.name = p.name;
                    opt.dataset.price = p.price_cents;
                    opt.dataset.stock = p.available_stock || 0;
                    datalist.appendChild(opt);
                });
            } catch (e) {
                console.error('Failed to load products:', e);
            }
        }

        // Load warehouses
        async function populateWarehouses() {
            try {
                const res = await fetchHandler(`${API_WAREHOUSE}?limit=500`, 'GET');
                const datalist = document.getElementById('warehousesList');
                datalist.innerHTML = '';
                
                (res.items || []).forEach(w => {
                    const opt = document.createElement('option');
                    opt.value = w.name;
                    opt.textContent = w.name;
                    opt.dataset.id = w.id;
                    opt.dataset.name = w.name;
                    opt.dataset.address = w.address || 'N/A';
                    opt.dataset.items = w.unique_products || 0;
                    datalist.appendChild(opt);
                });
            } catch (e) {
                console.error('Failed to load warehouses:', e);
            }
        }

        // Product selection handler
        document.getElementById('product_search').addEventListener('input', e => {
            const val = e.target.value;
            const found = [...document.querySelectorAll('#productsList option')].find(o => o.value === val);
            
            if (found) {
                document.getElementById('product_id').value = found.dataset.id;
                document.getElementById('prod_name').textContent = found.dataset.name;
                document.getElementById('prod_price').textContent = `LKR ${(found.dataset.price / 100).toFixed(2)}`;
                document.getElementById('prod_stock').textContent = found.dataset.stock;
                document.getElementById('productDetails').style.display = 'block';
            } else {
                document.getElementById('product_id').value = '';
                document.getElementById('productDetails').style.display = 'none';
            }
        });

        // Warehouse selection handler
        document.getElementById('warehouse_search').addEventListener('input', e => {
            const val = e.target.value;
            const found = [...document.querySelectorAll('#warehousesList option')].find(o => o.value === val);
            
            if (found) {
                document.getElementById('warehouse_id').value = found.dataset.id;
                document.getElementById('wh_name').textContent = found.dataset.name;
                document.getElementById('wh_address').textContent = found.dataset.address;
                document.getElementById('wh_items').textContent = found.dataset.items;
                document.getElementById('warehouseDetails').style.display = 'block';
            } else {
                document.getElementById('warehouse_id').value = '';
                document.getElementById('warehouseDetails').style.display = 'none';
            }
        });

        modalClose.onclick = () => modal.style.display = 'none';
        window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorDiv.textContent = '';
            successDiv.textContent = '';

            const productId = Number(document.getElementById('product_id').value);
            const warehouseId = Number(document.getElementById('warehouse_id').value);
            const quantity = Number(document.getElementById('quantity').value);

            if (!productId) {
                errorDiv.textContent = 'Please select a valid product';
                errorDiv.style.display = 'block';
                return;
            }

            if (!warehouseId) {
                errorDiv.textContent = 'Please select a valid warehouse';
                errorDiv.style.display = 'block';
                return;
            }

            const payload = {
                product_id: productId,
                warehouse_id: warehouseId,
                quantity: quantity,
                reserved: 0
            };

            try {
                const result = await fetchHandler(API_STOCK, 'POST', payload);

                const prodName = document.getElementById('prod_name').textContent;
                const whName = document.getElementById('wh_name').textContent;
                
                document.getElementById('modalMessage').innerHTML = `
                    <strong>Product:</strong> ${prodName}<br>
                    <strong>Warehouse:</strong> ${whName}<br>
                    <strong>Initial Quantity:</strong> ${quantity}
                `;
                
                viewLink.href = `update.php?id=${result.id}`;
                modal.style.display = 'flex';

                form.reset();
                document.getElementById('product_search').value = '';
                document.getElementById('warehouse_search').value = '';
                document.getElementById('product_id').value = '';
                document.getElementById('warehouse_id').value = '';
                document.getElementById('productDetails').style.display = 'none';
                document.getElementById('warehouseDetails').style.display = 'none';

            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to create stock entry';
                errorDiv.style.display = 'block';
            }
        });

        // Initialize
        populateProducts();
        populateWarehouses();
    </script>
</body>
</html>