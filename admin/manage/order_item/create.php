<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Order Item</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Order Item</h1>
        
        <!-- CREATE CONFIRMATION MODAL -->
        <div id="confirmation_modal" class="confirmation_overlay">
            <div class="confirmation_box">
                <h2>Order Item Created</h2>
                <p id="confirmation_message"></p>
                <div class="confirmation_actions">
                    <button id="confirmation_close" class="confirmation_btn">Close</button>
                    <a id="confirmation_view" class="confirmation_btn" href="#">View Order Item</a>
                </div>
            </div>
        </div>
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>➕ New Order Item</h2>
            <form id="mainForm">
                
                <div class="form-group">
                    <label for="order_id" class="form-label required">Order ID</label>
                    <input type="number" id="order_id" name="order_id" class="form-input" required placeholder="">
                </div>
                
                <div class="form-group">
                    <label for="product_id" class="form-label required">Product ID</label>
                    <input type="number" id="product_id" name="product_id" class="form-input" required placeholder="">
                </div>
                
                <div class="form-group">
                    <label for="product_name" class="form-label required">Product Name</label>
                    <input type="text" id="product_name" name="product_name" class="form-input" required placeholder="">
                </div>
                
                <div class="form-group">
                    <label for="product_image_url" class="form-label">Product Image URL</label>
                    <input type="text" id="product_image_url" name="product_image_url" class="form-input" placeholder="">
                </div>
                
                <div class="form-group">
                    <label for="price_cents" class="form-label required">Price (Cents)</label>
                    <input type="number" id="price_cents" name="price_cents" class="form-input" required placeholder="">
                </div>
                
                <div class="form-group">
                    <label for="quantity" class="form-label required">Quantity</label>
                    <input type="number" id="quantity" name="quantity" class="form-input" required placeholder="" min="1">
                </div>
                
                <div class="form-group">
                    <label for="warehouse_id" class="form-label">Warehouse</label>
                    <select id="warehouse_id" name="warehouse_id" class="form-input">
                        <option value="">Select Warehouse (Optional)</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Order Item</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
<script type="module">
import { fetchHandler, API_ORDER_ITEM } from '../utils.js';

const order_idElement = document.getElementById('order_id');
const product_idElement = document.getElementById('product_id');
const product_nameElement = document.getElementById('product_name');
const product_image_urlElement = document.getElementById('product_image_url');
const price_centsElement = document.getElementById('price_cents');
const quantityElement = document.getElementById('quantity');
const warehouse_idElement = document.getElementById('warehouse_id');

const form = document.getElementById('mainForm');
const errorDiv = document.getElementById('errorDiv');
const successDiv = document.getElementById('successDiv');   
const modal = document.getElementById('confirmation_modal');
const modalMsg = document.getElementById('confirmation_message');
const modalClose = document.getElementById('confirmation_close');
const modalView = document.getElementById('confirmation_view');

// Load warehouses on page load
const loadWarehouses = async () => {
    try {
        const response = await fetch('http://localhost/royal-liquor/admin/api/warehouses.php', {
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
        warehouse_idElement.innerHTML = '<option value="">Select Warehouse (Optional)</option>';
        
        warehouses.forEach(wh => {
            const option = document.createElement('option');
            option.value = wh.id;
            option.textContent = `${wh.name} - ${wh.address || 'No address'}`;
            warehouse_idElement.appendChild(option);
        });

    } catch (error) {
        console.error('Error loading warehouses:', error);
        // Non-fatal error - user can still create without warehouse
        console.warn('Warehouse dropdown will be empty');
    }
};

function showConfirmationModal(order_item) {
    modalMsg.textContent = `Order Item #${order_item.id} was created successfully!`;
    modalView.href = `../../index.php?id`;
    modal.style.display = 'flex';
}

modalClose.addEventListener('click', () => {
    modal.style.display = 'none';
});

form.addEventListener('submit', async (e) => {
    e.preventDefault(); 
    
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
    successDiv.style.display = 'none';
    successDiv.textContent = '';

    const body = {
        order_id: parseInt(order_idElement.value),
        product_id: parseInt(product_idElement.value),
        product_name: product_nameElement.value,
        product_image_url: product_image_urlElement.value || null,
        price_cents: parseInt(price_centsElement.value),
        quantity: parseInt(quantityElement.value),
        warehouse_id: warehouse_idElement.value ? parseInt(warehouse_idElement.value) : null
    };

    try {
        const apiUrl = `${API_ORDER_ITEM}`;
        const response = await fetchHandler(apiUrl, 'POST', body);

        if (!response.error) {
            showConfirmationModal(response);
            form.reset();
        } else {
            throw new Error(response.message || 'Failed to create order item');
        }

    } catch (error) {
        console.error('Error creating order item:', error);
        errorDiv.textContent = error.message || 'An error occurred while creating the order item.';
        errorDiv.style.display = 'block';
    }
});

// Load warehouses when page loads
loadWarehouses();
</script>
</body>
</html>