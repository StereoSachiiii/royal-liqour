<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Item</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update Order Item</h1>
        
        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>📊 Order Item Overview</h2>
            <div id="quickViewContent">
<div class="modal-content">
  <h2>📦 Order Item Detail: #<span id="order_item_detail_title"></span></h2>
  
  <div class="detail-section">
    <h3>Basic Info</h3>
    <div class="detail-field"><strong>Quantity:</strong> <span id="order_item_detail_quantity"></span></div>
    <div class="detail-field"><strong>Price (Cents):</strong> <span id="order_item_detail_price_cents"></span></div>
    <div class="detail-field"><strong>Subtotal (Cents):</strong> <span id="order_item_detail_subtotal_cents"></span></div>
  </div>

  <div class="detail-section">
    <h3>Order Info</h3>
    <div class="detail-field"><strong>Order Number:</strong> <span id="order_item_detail_order_number"></span></div>
    <div class="detail-field"><strong>Order Status:</strong> <span id="order_item_detail_order_status"></span></div>
  </div>

  <div class="detail-section">
    <h3>Product Info</h3>
    <div class="detail-field"><strong>Product Name:</strong> <span id="order_item_detail_product_name"></span></div>
    <div class="detail-field"><strong>Product Image URL:</strong> <span id="order_item_detail_product_image_url"></span></div>
  </div>

  <div class="detail-section">
    <h3>Warehouse Info</h3>
    <div class="detail-field"><strong>Warehouse Name:</strong> <span id="order_item_detail_warehouse_name"></span></div>
    <div class="detail-field"><strong>Warehouse Address:</strong> <span id="order_item_detail_warehouse_address"></span></div>
  </div>

  <div class="detail-section">
    <h3>Current Product Info</h3>
    <div id="order_item_detail_current_product_info" class="json-data-block"></div>
  </div>
</div>
            </div>
        </div>
        
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>✏️ Edit Order Item</h2>
            <p class="form-help">You can only modify quantity and warehouse assignment. Other fields are locked as part of transaction history.</p>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">
                
            <div class="form-group">
                <label for="order_id" class="form-label">Order ID</label>
                <input type="number" id="order_id" name="order_id" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label for="product_id" class="form-label">Product ID</label>
                <input type="number" id="product_id" name="product_id" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label for="product_name" class="form-label">Product Name</label>
                <input type="text" id="product_name" name="product_name" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label for="price_cents" class="form-label">Price (Cents)</label>
                <input type="number" id="price_cents" name="price_cents" class="form-input" readonly>
            </div>
            
            <hr style="margin: 20px 0; border: 1px solid #ddd;">
            
            <div class="form-group">
                <label for="quantity" class="form-label required">Quantity</label>
                <input type="number" id="quantity" name="quantity" class="form-input" required placeholder="" min="1" max="10000">
                <small class="form-help">Adjusting quantity will update stock reservations</small>
            </div>
            
            <div class="form-group">
                <label for="warehouse_id" class="form-label required">Warehouse</label>
                <select id="warehouse_id" name="warehouse_id" class="form-input" required>
                    <option value="">Loading warehouses...</option>
                </select>
                <small class="form-help">Changing warehouse will move stock reservation</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="../../index.php" class="btn-secondary">Cancel</a>
                <button type="button" id="deleteBtn" class="btn-danger">Delete Item</button>
            </div>
            </form>
        </div>
    </div>
    
    <!-- DELETE CONFIRMATION MODAL -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle">Confirm Delete</div>
            <div class="modal-body" id="modalMessage">Are you sure you want to delete this order item? This will release any reserved stock.</div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-danger">Delete</button>
            </div>
        </div>
    </div>
    
    <script type="module">
      import { fetchHandler, API_ORDER_ITEM, API_WAREHOUSE } from '../utils.js';

const recordId = Number(new URLSearchParams(window.location.search).get('id'));
const apiUrl = `${API_ORDER_ITEM}/${recordId}?enriched=true`;
const updateUrl = `${API_ORDER_ITEM}/${recordId}`;

const form = document.getElementById('mainForm');
const errorDiv = document.getElementById('errorDiv');
const successDiv = document.getElementById('successDiv');

const order_itemDetailTitle = document.getElementById('order_item_detail_title');
const order_itemDetailQuantity = document.getElementById('order_item_detail_quantity');
const order_itemDetailPricecents = document.getElementById('order_item_detail_price_cents');
const order_itemDetailSubtotalcents = document.getElementById('order_item_detail_subtotal_cents');
const order_itemDetailOrdernumber = document.getElementById('order_item_detail_order_number');
const order_itemDetailOrderstatus = document.getElementById('order_item_detail_order_status');
const order_itemDetailProductname = document.getElementById('order_item_detail_product_name');
const order_itemDetailProductimageurl = document.getElementById('order_item_detail_product_image_url');
const order_itemDetailWarehousename = document.getElementById('order_item_detail_warehouse_name');
const order_itemDetailWarehouseaddress = document.getElementById('order_item_detail_warehouse_address');
const order_itemDetailCurrentproductinfo = document.getElementById('order_item_detail_current_product_info');

const order_idElement = document.getElementById('order_id');
const product_idElement = document.getElementById('product_id');
const product_nameElement = document.getElementById('product_name');
const price_centsElement = document.getElementById('price_cents');
const quantityElement = document.getElementById('quantity');
const warehouse_idElement = document.getElementById('warehouse_id');

const confirmModal = document.getElementById('confirmModal');
const modalTitle = document.getElementById('modalTitle');
const modalMessage = document.getElementById('modalMessage');
const modalCancel = document.getElementById('modalCancel');
const modalConfirm = document.getElementById('modalConfirm');
const deleteBtn = document.getElementById('deleteBtn');

deleteBtn.addEventListener('click', () => {
    modalTitle.textContent = 'Confirm Delete';
    modalMessage.textContent = 'Are you sure you want to delete this order item? This will release any reserved stock.';
    confirmModal.style.display = 'flex';
});

modalCancel.addEventListener('click', () => {
    confirmModal.style.display = 'none';
});

modalConfirm.addEventListener('click', async () => {
    confirmModal.style.display = 'none';
    
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
    
    try {
        const response = await fetchHandler(`${API_ORDER_ITEM}?id=${recordId}`, 'DELETE');
        
        if (!response.error) {
            successDiv.textContent = 'Order Item deleted successfully! Redirecting...';
            successDiv.style.display = 'block';
            setTimeout(() => { window.location.href = '../../index.php'; }, 2000);
        } else {
            throw new Error(response.message || 'Failed to delete order item');
        }
    } catch (err) {
        errorDiv.textContent = err.message || 'Failed to delete order item';
        errorDiv.style.display = 'block';
    }
});

const loadWarehouses = async (selectedId = null) => {
    try {
        const response = await fetch(`${API_WAREHOUSE}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP error ${response.status}`);
        }

        const json = await response.json();
        
        if (!json.success || !json.data) {
            throw new Error(json.message || 'Failed to load warehouses');
        }

        const warehouses = json.data;
        warehouse_idElement.innerHTML = '<option value="">Select Warehouse</option>';
        
        warehouses.forEach(wh => {
            const option = document.createElement('option');
            option.value = wh.id;
            option.textContent = `${wh.name} - ${wh.address || 'No address'}`;
            if (wh.id === selectedId) {
                option.selected = true;
            }
            warehouse_idElement.appendChild(option);
        });

    } catch (error) {
        console.error('Error loading warehouses:', error);
        errorDiv.textContent = 'Failed to load warehouses: ' + error.message;
        errorDiv.style.display = 'block';
    }
};

const loadOrderItemData = async () => {
    try {
        const data = await fetchHandler(apiUrl, 'GET');

        // Populate detail view
        order_itemDetailTitle.textContent = data.id;
        order_itemDetailQuantity.textContent = data.quantity;
        order_itemDetailPricecents.textContent = data.price_cents;
        order_itemDetailSubtotalcents.textContent = data.subtotal_cents;
        order_itemDetailOrdernumber.textContent = data.order_number;
        order_itemDetailOrderstatus.textContent = data.order_status;
        order_itemDetailProductname.textContent = data.product_name;
        order_itemDetailProductimageurl.textContent = data.product_image_url || 'N/A';
        order_itemDetailWarehousename.textContent = data.warehouse_name || 'Not assigned';
        order_itemDetailWarehouseaddress.textContent = data.warehouse_address || 'N/A';
        order_itemDetailCurrentproductinfo.textContent = JSON.stringify(data.current_product_info, null, 2);

        // Populate form (read-only fields)
        document.getElementById('recordId').value = data.id;
        form.order_id.value = data.order_id;
        form.product_id.value = data.product_id;
        form.product_name.value = data.product_name;
        form.price_cents.value = data.price_cents;
        form.quantity.value = data.quantity;
        
        // Load warehouses and set selected value
        await loadWarehouses(data.warehouse_id);
        
    } catch (error) {
        console.error('Error loading order item:', error);
        errorDiv.textContent = 'Failed to load order item: ' + error.message;
        errorDiv.style.display = 'block';
    }
};

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
    successDiv.style.display = 'none';
    successDiv.textContent = '';

    const payload = {
        id: recordId,
        quantity: parseInt(quantityElement.value),
        warehouse_id: parseInt(warehouse_idElement.value)
    };

    try {
        const result = await fetchHandler(updateUrl, "PUT", payload);
        
        if (!result.error) {
            successDiv.textContent = 'Order Item updated successfully! Stock reservations have been adjusted.';
            successDiv.style.display = 'block';
            await loadOrderItemData();
        } else {
            throw new Error(result.message || 'Failed to update order item');
        }
    } catch (err) {
        errorDiv.textContent = err.message || 'Failed to update order item';
        errorDiv.style.display = 'block';
    }
});

// Load data on page load
loadOrderItemData().then(() => {
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
    </script>
</body>
</html>