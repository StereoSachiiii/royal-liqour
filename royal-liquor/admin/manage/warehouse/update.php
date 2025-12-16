<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Warehouse</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update Warehouse</h1>
        
        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card" >
            <h2>📊 Warehouse Overview</h2>
            <div id="quickViewContent">
<div class="modal-content">
  <h2> 🏠 Warehouse Detail: <span id="warehouse_detail_title"></span></h2>
  
  <div class="detail-section">
    <h3>Basic Info</h3>
    <div class="detail-field"><strong>Name:</strong> <span id="warehouse_detail_name"></span></div>
    <div class="detail-field"><strong>Phone:</strong> <span id="warehouse_detail_phone"></span></div>
    <div class="detail-field"><strong>Is Active:</strong> <span id="warehouse_detail_is_active"></span></div>
  </div>

  <div class="detail-section">
    <h3>Inventory Summary</h3>
    <div class="detail-field"><strong>Unique Products:</strong> <span id="warehouse_detail_unique_products"></span></div>
    <div class="detail-field"><strong>Total Quantity:</strong> <span id="warehouse_detail_total_quantity"></span></div>
    <div class="detail-field"><strong>Total Available:</strong> <span id="warehouse_detail_total_available"></span></div>
  </div>

  <div class="detail-section">
    <h3>Low Stock Items</h3>
    <div id="warehouse_detail_low_stock_items" class="json-data-block"></div>
  </div>
</div>
            </div>
        </div>
        
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>✏️ Edit Warehouse</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">

            <div class="form-group">
                <label for="name" class="form-label required">Name</label>
                <input type="text" id="name" name="name" class="form-input" required placeholder="">
            </div>
            
            <div class="form-group">
                <label for="address" class="form-label ">Address</label>
                <textarea id="address" name="address" class="form-input" ></textarea>
            </div>
            
            <div class="form-group">
                <label for="phone" class="form-label ">Phone</label>
                <input type="tel" id="phone" name="phone" class="form-input"  placeholder="+94 77 123 4567">
            </div>
            
            <div class="form-group">
                <label for="image_url" class="form-label ">Image</label>
                <div id="currentImage" class="file-current" style="display:none;"></div>
<input type="file" id="image_url" name="image_url" class="form-file" accept="image/*" >
                <div id="imagePreview" class="file-preview"></div>
            </div>
            
        <div class="form-group">
            <div class="checkbox-inline">
                
            <div class="checkbox-wrapper">
                <input type="checkbox" id="is_active" name="is_active" class="form-checkbox" checked>
                <label for="is_active">Is Active</label>
            </div>
            
            </div>
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
      import { fetchHandler, API_URL, API_WAREHOUSE } from '../utils.js';

const recordId = Number(new URLSearchParams(window.location.search).get('id'));
const apiUrl = `${API_WAREHOUSE}/${recordId}?enriched=true`;
const updateUrl = `${API_WAREHOUSE}/${recordId}`;

const form = document.getElementById('mainForm');
const errorDiv = document.getElementById('errorDiv');
const successDiv = document.getElementById('successDiv');

const warehouseDetailName = document.getElementById('warehouse_detail_name');
const warehouseDetailPhone = document.getElementById('warehouse_detail_phone');
const warehouseDetailIsactive = document.getElementById('warehouse_detail_is_active');
const warehouseDetailUniqueproducts = document.getElementById('warehouse_detail_unique_products');
const warehouseDetailTotalquantity = document.getElementById('warehouse_detail_total_quantity');
const warehouseDetailTotalavailable = document.getElementById('warehouse_detail_total_available');
const warehouseDetailLowstockitems = document.getElementById('warehouse_detail_low_stock_items');

const nameElement = document.getElementById('name');
const addressElement = document.getElementById('address');
const phoneElement = document.getElementById('phone');
const is_activeElement = document.getElementById('is_active');

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
        const deleteUrl = `${API_WAREHOUSE}`;
        const response = await fetchHandler(deleteUrl, 'DELETE', {id: recordId});
        successDiv.textContent = 'Warehouse deleted successfully!';
        setTimeout(() => { window.location.href = '../../index.php'; }, 2000);
    } catch (err) {
        errorDiv.textContent = err.message || 'Failed to delete warehouse';
    }
    currentAction = null;
});
function formatLowStockItems(items) {
    if (!items || items.length === 0) {
        return 'No low stock items';
    }
    
    let text = '';
    items.forEach((item, index) => {
        if (index > 0) text += '\n\n';
        text += `Product: ${item.product_name || 'Unknown'}`;
        if (item.sku) text += ` (SKU: ${item.sku})`;
        text += `\nAvailable: ${item.available_quantity}`;
        text += ` | Total: ${item.total_quantity}`;
        if (item.reorder_level) text += ` | Reorder Level: ${item.reorder_level}`;
    });
    return text;
}
const loadWarehouseData = async () => {
    const data = await fetchHandler(apiUrl, 'GET');

    warehouseDetailName.textContent = data.name;
    warehouseDetailPhone.textContent = data.phone;
    warehouseDetailIsactive.textContent = data.is_active ? 'Yes' : 'No';
    warehouseDetailUniqueproducts.textContent = data.unique_products;
    warehouseDetailTotalquantity.textContent = data.total_quantity;
    warehouseDetailTotalavailable.textContent = data.total_available;
warehouseDetailLowstockitems.textContent = formatLowStockItems(data.low_stock_items);
    document.getElementById('recordId').value = data.id;
    form.name.value = data.name;
    form.address.value = data.address || '';
    form.phone.value = data.phone;
    form.is_active.checked = data.is_active;

    if (data.image_url) {
        currentImage.innerHTML = `<img src="${data.image_url}" alt="Current Image_url">`;
        currentImage.style.display = 'block';
    }
};

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    errorDiv.textContent = '';
    successDiv.textContent = '';

    // Get the file input
    const fileInput = document.getElementById('image_url');
    const file = fileInput.files[0];

    // TODO: Upload the image to your image API and get back the URL
    /*
    let image = null;
    if (file) {
        const formData = new FormData();
        formData.append('file', file);

        try {
            const uploadResponse = await fetch('https://your-image-api.com/upload', {
                method: 'POST',
                body: formData
            });
            const result = await uploadResponse.json();

            if (result.url) {
                image = result.url; // success: set image URL
            } else {
                // API responded but no URL returned
                return { error: 'Image upload failed: No URL returned from API' };
            }
        } catch (err) {
            console.error('Image upload failed', err);
            return { error: err.message || 'Image upload failed' };
        }
    }
    */

    // Current request payload (image variable not used yet)
    const payload = {
        id: recordId,
        name: nameElement.value,
        address: addressElement.value,
        phone: phoneElement.value,
        is_active: is_activeElement.checked,
        image_url: null // replace with 'image' when image API is ready
    };

    try {
        const result = await fetchHandler(updateUrl, "PUT", payload);
        successDiv.textContent = 'Warehouse updated successfully!';
        await loadWarehouseData();
    } catch (err) {
        errorDiv.textContent = err.message || 'Failed to update warehouse';
    }
});

loadWarehouseData().then(() => {
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
    </script>
</body>
</html>