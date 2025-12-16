<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Supplier</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>

<div class="admin-container">
    <h1>Update Supplier</h1>

    <!-- QUICK VIEW CARD -->
    <div id="quickViewCard" class="quick-view-card">
        <h2>📊 Supplier Overview</h2>
        <div id="quickViewContent">
            <div class="modal-content">
                <h2>🏭 Supplier Detail: <span id="supplier_detail_title"></span></h2>
                
                <div class="detail-section">
                    <h3>Basic Info</h3>
                    <div class="detail-field"><strong>Name:</strong> <span id="supplier_detail_name"></span></div>
                    <div class="detail-field"><strong>Email:</strong> <span id="supplier_detail_email"></span></div>
                    <div class="detail-field"><strong>Phone:</strong> <span id="supplier_detail_phone"></span></div>
                    <div class="detail-field"><strong>Is Active:</strong> <span id="supplier_detail_is_active"></span></div>
                </div>

                <div class="detail-section">
                    <h3>Product Summary</h3>
                    <div class="detail-field"><strong>Total Products:</strong> <span id="supplier_detail_total_products"></span></div>
                    <div class="detail-field"><strong>Active Products:</strong> <span id="supplier_detail_active_products"></span></div>
                    <div class="detail-field"><strong>Avg Product Price (Cents):</strong> <span id="supplier_detail_avg_product_price_cents"></span></div>
                </div>

                <div class="detail-section">
                    <h3>Products</h3>
                    <div id="supplier_detail_products" class="json-data-block"><pre></pre></div>
                </div>
            </div>
        </div>
    </div>

    <div id="errorDiv" class="error-message"></div>
    <div id="successDiv" class="success-message"></div>

    <!-- EDIT FORM -->
    <div class="edit-card">
        <h2>✏️ Edit Supplier</h2>
        <form id="mainForm">
            <input type="hidden" id="recordId" name="id">

            <div class="form-group">
                <label for="name" class="form-label required">Name</label>
                <input type="text" id="name" name="name" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" id="phone" name="phone" class="form-input" placeholder="+94 77 123 4567">
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <textarea id="address" name="address" class="form-input"></textarea>
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

<!-- DELETE CONFIRMATION MODAL -->
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
import { fetchHandler, API_URL , API_SUPPLIER} from '../utils.js';

const recordId = Number(new URLSearchParams(window.location.search).get('id'));
const apiUrl = `${API_SUPPLIER}/${recordId}?enriched=true`;
const updateUrl = `${API_SUPPLIER}/${recordId}`;

// Form & UI elements
const form = document.getElementById('mainForm');
const errorDiv = document.getElementById('errorDiv');
const successDiv = document.getElementById('successDiv');

const supplierDetailTitle = document.getElementById('supplier_detail_title');
const supplierDetailName = document.getElementById('supplier_detail_name');
const supplierDetailEmail = document.getElementById('supplier_detail_email');
const supplierDetailPhone = document.getElementById('supplier_detail_phone');
const supplierDetailIsactive = document.getElementById('supplier_detail_is_active');
const supplierDetailTotalproducts = document.getElementById('supplier_detail_total_products');
const supplierDetailActiveproducts = document.getElementById('supplier_detail_active_products');
const supplierDetailAvgproductpricecents = document.getElementById('supplier_detail_avg_product_price_cents');
const supplierDetailProducts = document.getElementById('supplier_detail_products').querySelector('pre');

const nameElement = document.getElementById('name');
const emailElement = document.getElementById('email');
const phoneElement = document.getElementById('phone');
const addressElement = document.getElementById('address');
const is_activeElement = document.getElementById('is_active');

// Delete modal
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
        modalMessage.textContent = 'Are you sure? This will deactivate the supplier.';
        confirmModal.style.display = 'flex';
    });
}

if (hardDeleteBtn) {
    hardDeleteBtn.addEventListener('click', () => {
        currentAction = 'hard_delete';
        modalTitle.textContent = 'Confirm Hard Delete';
        modalMessage.textContent = 'Are you sure? This will permanently delete the supplier.';
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
        const deleteUrl = `${API_SUPPLIER}/${recordId}`;
        const response = await fetchHandler(deleteUrl, 'PUT', {id: recordId});
        if (response.error) throw new Error(response.error);
        successDiv.textContent = 'Supplier deleted successfully!';
        setTimeout(() => { window.location.href = '../../index.php'; }, 2000);
    } catch (err) {
        errorDiv.textContent = err.message || 'Failed to delete supplier';
    }
    currentAction = null;
});

// Load supplier data
const loadSupplierData = async () => {
    try {
        const data = await fetchHandler(apiUrl, 'GET');
        if (data.error) throw new Error(data.error);

        supplierDetailTitle.textContent = data.name;
        supplierDetailName.textContent = data.name;
        supplierDetailEmail.textContent = data.email || '-';
        supplierDetailPhone.textContent = data.phone || '-';
        supplierDetailIsactive.textContent = data.is_active ? 'Yes' : 'No';
        supplierDetailTotalproducts.textContent = data.total_products ?? 0;
        supplierDetailActiveproducts.textContent = data.active_products ?? 0;
        supplierDetailAvgproductpricecents.textContent = data.avg_product_price_cents ?? 'N/A';
        supplierDetailProducts.textContent = data.products ? JSON.stringify(data.products, null, 2) : 'No products';

        // Populate form fields
        document.getElementById('recordId').value = data.id;
        form.name.value = data.name;
        form.email.value = data.email || '';
        form.phone.value = data.phone || '';
        form.address.value = data.address || '';
        form.is_active.checked = data.is_active;

    } catch (err) {
        errorDiv.textContent = err.message || 'Failed to load supplier data';
    }
};

// Form submission
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorDiv.textContent = '';
    successDiv.textContent = '';

    const payload = {
        id: recordId,
        name: nameElement.value,
        email: emailElement.value,
        phone: phoneElement.value,
        address: addressElement.value,
        is_active: is_activeElement.checked
    };

    try {
        const result = await fetchHandler(updateUrl, "PUT", payload);
        if (result.error) throw new Error(result.error);

        successDiv.textContent = 'Supplier updated successfully!';
        await loadSupplierData();

    } catch (err) {
        errorDiv.textContent = err.message || 'Failed to update supplier';
    }
});

loadSupplierData();
</script>

</body>
</html>
