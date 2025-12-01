<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Update Stock</title>

    <link rel="stylesheet" href="../admin.css">

</head>

<body>

    <div class="admin-container">

        <h1>Update Stock</h1>

        

        <!-- QUICK VIEW CARD -->

        <div id="quickViewCard" class="quick-view-card" >

            <h2>📊 Stock Overview</h2>

            <div id="quickViewContent">

                <div class="modal-content"><h2> 📦 Stock Detail:</h2><div class="detail-section"><h3>Basic Info</h3><div class="detail-field"><strong>Quantity:</strong> <span id="stock_detail_quantity"></span></div><div class="detail-field"><strong>Reserved:</strong> <span id="stock_detail_reserved"></span></div><div class="detail-field"><strong>Available:</strong> <span id="stock_detail_available"></span></div></div><div class="detail-section"><h3>Product Info</h3><div class="detail-field"><strong>Product Name:</strong> <span id="stock_detail_product_name"></span></div><div class="detail-field"><strong>Product Slug:</strong> <span id="stock_detail_product_slug"></span></div><div class="detail-field"><strong>Price (Cents):</strong> <span id="stock_detail_price_cents"></span></div></div><div class="detail-section"><h3>Warehouse Info</h3><div class="detail-field"><strong>Warehouse Name:</strong> <span id="stock_detail_warehouse_name"></span></div><div class="detail-field"><strong>Warehouse Address:</strong> <span id="stock_detail_warehouse_address"></span></div></div><div class="detail-section"><h3>Inventory Value</h3><div class="detail-field"><strong>Inventory Value:</strong> <span id="stock_detail_inventory_value"></span></div></div><div class="detail-section"><h3>Recent Movements</h3><div id="stock_detail_recent_movements" class="json-data-block"></div></div></div>

            </div>

        </div>

        

        <div id="errorDiv" class="error-message"></div>

        <div id="successDiv" class="success-message"></div>

        

        <div class="edit-card">

            <h2>✏️ Edit Stock</h2>

            <form id="mainForm">

                <input type="hidden" id="recordId" name="id">


            <div class="form-group">

                <label for="product_id" class="form-label required">Product ID</label>

                <input type="number" id="product_id" name="product_id" class="form-input" required placeholder="">

            </div>

            

            <div class="form-group">

                <label for="warehouse_id" class="form-label required">Warehouse ID</label>

                <input type="number" id="warehouse_id" name="warehouse_id" class="form-input" required placeholder="">

            </div>

            

            <div class="form-group">

                <label for="quantity" class="form-label ">Quantity</label>

                <input type="number" id="quantity" name="quantity" class="form-input"  placeholder="">

            </div>

            

            <div class="form-group">

                <label for="reserved" class="form-label ">Reserved</label>

                <input type="number" id="reserved" name="reserved" class="form-input"  placeholder="">

            </div>

            

                

                <div class="form-actions">

                    <button type="submit" class="btn-primary">Save Changes</button>

                    <a href="../../index.php" class="btn-secondary">Cancel</a>

                    <button type="button" id="softDeleteBtn" class="btn-danger">Soft Delete</button>

                    <button type="button" id="hardDeleteBtn" class="btn-danger">Hard Delete</button>

                </div>

            </form>

        </div>

    </div>

    

    <!-- MODAL -->

    <div id="confirmModal" class="modal-overlay">

        <div class="modal-content">

            <div class="modal-header" id="modalTitle"></div>

            <div class="modal-body" id="modalMessage"></div>

            <div class="modal-actions">

                <button id="modalCancel" class="btn-secondary">Cancel</button>

                <button id="modalConfirm" class="btn-danger">Confirm</button>

            </div>

        </div>

    </div>

    

    <script type="module">

      import { fetchHandler, API_URL, API_UPDATE } from '../utils.js';

const recordId = Number(new URLSearchParams(window.location.search).get('id'));

const apiUrl = `${API_URL}?entity=stock&id=${recordId}`;

const updateUrl = `${API_UPDATE}?entity=stock&action=update`;

const form = document.getElementById('mainForm');

const errorDiv = document.getElementById('errorDiv');

const successDiv = document.getElementById('successDiv');

const stockDetailQuantity = document.getElementById('stock_detail_quantity');
const stockDetailReserved = document.getElementById('stock_detail_reserved');
const stockDetailAvailable = document.getElementById('stock_detail_available');
const stockDetailProductname = document.getElementById('stock_detail_product_name');
const stockDetailProductslug = document.getElementById('stock_detail_product_slug');
const stockDetailPricecents = document.getElementById('stock_detail_price_cents');
const stockDetailWarehousename = document.getElementById('stock_detail_warehouse_name');
const stockDetailWarehouseaddress = document.getElementById('stock_detail_warehouse_address');
const stockDetailInventoryvalue = document.getElementById('stock_detail_inventory_value');
const stockDetailRecentmovements = document.getElementById('stock_detail_recent_movements');


const product_idElement = document.getElementById('product_id');
const warehouse_idElement = document.getElementById('warehouse_id');
const quantityElement = document.getElementById('quantity');
const reservedElement = document.getElementById('reserved');


let currentAction = null;

const confirmModal = document.getElementById('confirmModal');

const modalTitle = document.getElementById('modalTitle');

const modalMessage = document.getElementById('modalMessage');

const modalCancel = document.getElementById('modalCancel');

const modalConfirm = document.getElementById('modalConfirm');

const softDeleteBtn = document.getElementById('softDeleteBtn');

const hardDeleteBtn = document.getElementById('hardDeleteBtn');

if (softDeleteBtn) {

    softDeleteBtn.addEventListener('click', () => {

        currentAction = 'soft_delete';

        modalTitle.textContent = 'Confirm Soft Delete';

        modalMessage.textContent = 'Are you sure? This will deactivate the record.';

        confirmModal.style.display = 'flex';

    });

}

if (hardDeleteBtn) {

    hardDeleteBtn.addEventListener('click', () => {

        currentAction = 'hard_delete';

        modalTitle.textContent = 'Confirm Hard Delete';

        modalMessage.textContent = 'Are you sure? This will permanently delete the record.';

        confirmModal.style.display = 'flex';

    });

}

modalCancel.addEventListener('click', () => {

    confirmModal.style.display = 'none';

    currentAction = null;

});

modalConfirm.addEventListener('click', async () => {

    confirmModal.style.display = 'none';

    try {

        const deleteUrl = `${API_UPDATE}?entity=stock&action=${currentAction}`;

        const response = await fetchHandler(deleteUrl, 'POST', {id: recordId});

        successDiv.textContent = 'Stock deleted successfully!';

        // Perhaps redirect to list

        setTimeout(() => { window.location.href = '../../index.php'; }, 2000);

    } catch (err) {

        errorDiv.textContent = err.message || 'Failed to delete stock';

    }

    currentAction = null;

});

const loadStockData = async () => {

    const data = await fetchHandler(apiUrl, 'GET');

    stockDetailQuantity.textContent = data.quantity;
stockDetailReserved.textContent = data.reserved;
stockDetailAvailable.textContent = data.available;
stockDetailProductname.textContent = data.product_name;
stockDetailProductslug.textContent = data.product_slug;
stockDetailPricecents.textContent = data.price_cents;
stockDetailWarehousename.textContent = data.warehouse_name;
stockDetailWarehouseaddress.textContent = data.warehouse_address;
stockDetailInventoryvalue.textContent = data.inventory_value;
stockDetailRecentmovements.textContent = JSON.stringify(data.recent_movements, null, 2);


    document.getElementById('recordId').value = data.id;

    form.product_id.value = data.product_id;
form.warehouse_id.value = data.warehouse_id;
form.quantity.value = data.quantity;
form.reserved.value = data.reserved;


};

form.addEventListener('submit', async (e) => {

    e.preventDefault();

    errorDiv.textContent = '';

    successDiv.textContent = '';

    const payload = {

        id: recordId,

        product_id: product_idElement.value,
warehouse_id: warehouse_idElement.value,
quantity: quantityElement.value,
reserved: reservedElement.value,


    };

    try {

        const result = await fetchHandler(updateUrl, "POST", payload);

        successDiv.textContent = 'Stock updated successfully!';

        await loadStockData();

    } catch (err) {

        errorDiv.textContent = err.message || 'Failed to update stock';

    }

});

loadStockData().then(() => {

    form.scrollIntoView({ behavior: 'smooth', block: 'start' });

});

    </script>

</body>

</html>