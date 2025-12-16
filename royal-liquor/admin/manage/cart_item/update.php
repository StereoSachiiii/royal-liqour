<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Cart Item</title>
    <link rel="stylesheet" href="../../admin.css">
    <link rel="stylesheet" href="../../admin.css">
    <link rel="stylesheet" href="../../assets/css/cart_items.css">
</head>
<body>
    <div class="admin-container cart-items-edit">
        
        <div id="quickViewCard" class="quick-view-card">
            <h2>📦 Item Overview</h2>
            <div class="modal-content">
                <div class="detail-section">
                    <div class="detail-field"><strong>Product:</strong> <span id="detail_name">-</span></div>
                    <div class="detail-field"><strong>Price:</strong> <span id="detail_price">-</span></div>
                    <div class="detail-field"><strong>Subtotal:</strong> <span id="detail_subtotal">-</span></div>
                    <div class="detail-field"><strong>Cart ID:</strong> <span id="detail_cart">-</span></div>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>✏️ Edit Cart Item</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cart_id" class="form-label required">Cart ID</label>
                        <input type="text" id="cart_id" name="cart_id" class="form-input" required readonly>
                    </div>
                    <div class="form-group">
                        <label for="product_id" class="form-label required">Product ID</label>
                        <input type="text" id="product_id" name="product_id" class="form-input" required readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity" class="form-label required">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-input" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="price_at_add_cents" class="form-label required">Price (Cents)</label>
                        <input type="number" id="price_at_add_cents" name="price_at_add_cents" class="form-input" required min="0">
                         <div class="form-text">Price stored when item was added.</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                     <button type="button" onclick="window.parent.postMessage('closeModal', '*')" class="btn-secondary">Cancel</button>
                    <button type="button" id="deleteBtn" class="btn-danger" style="margin-left:auto;">Delete Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">Confirm Delete</div>
            <div class="modal-body">Are you sure you want to delete this cart item?</div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-danger">Delete</button>
            </div>
        </div>
    </div>

    <script type="module">
        import { API_URL_CART_ITEMS } from "../../js/pages/config.js";

        async function apiCall(url, method = 'GET', body = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            };
            if (body) options.body = JSON.stringify(body);
            
            const res = await fetch(url, options);
            const data = await res.json();
            return data;
        }

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        if (!recordId) {
            document.getElementById('errorDiv').textContent = 'No record ID provided';
            document.getElementById('errorDiv').style.display = 'block';
        }

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const deleteBtn = document.getElementById('deleteBtn');
        const confirmModal = document.getElementById('confirmModal');

        const el = id => document.getElementById(id);

        async function loadData() {
            try {
                const response = await apiCall(`${API_URL_CART_ITEMS}?id=${recordId}`);
                
                if (response.success === false) throw new Error(response.message);
                
                const data = response.data || response;
                if (!data || !data.id) throw new Error('Invalid data');

                // Quick View
                el('detail_name').textContent = data.product_name || `Product #${data.product_id}`;
                el('detail_price').textContent = '$' + (data.price_at_add_cents / 100).toFixed(2);
                el('detail_subtotal').textContent = '$' + (data.subtotal_cents / 100).toFixed(2);
                el('detail_cart').textContent = `#${data.cart_id} (${data.cart_status || 'unknown'})`;

                // Form
                el('recordId').value = data.id;
                el('cart_id').value = data.cart_id;
                el('product_id').value = data.product_id;
                el('quantity').value = data.quantity;
                el('price_at_add_cents').value = data.price_at_add_cents;

            } catch (err) {
                console.error(err);
                errorDiv.textContent = 'Error: ' + err.message;
                errorDiv.style.display = 'block';
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';

            const payload = {
                id: recordId,
                quantity: Number(el('quantity').value),
                price_at_add_cents: Number(el('price_at_add_cents').value)
            };

            try {
                const response = await apiCall(`${API_URL_CART_ITEMS}?id=${recordId}`, 'PUT', payload);
                if (response.success) {
                    successDiv.textContent = '✅ Updated successfully!';
                    successDiv.style.display = 'block';
                    loadData(); 
                    window.parent.postMessage('refreshData', '*');
                } else {
                    throw new Error(response.message || 'Update failed');
                }
            } catch (err) {
                errorDiv.textContent = '❌ ' + err.message;
                errorDiv.style.display = 'block';
            }
        });

        // Delete
        deleteBtn.addEventListener('click', () => confirmModal.style.display = 'flex');
        el('modalCancel').addEventListener('click', () => confirmModal.style.display = 'none');
        
        el('modalConfirm').addEventListener('click', async () => {
            confirmModal.style.display = 'none';
            try {
                const response = await apiCall(`${API_URL_CART_ITEMS}?id=${recordId}`, 'DELETE');
                if (response.success) {
                    successDiv.textContent = '✅ Deleted. Closing...';
                    successDiv.style.display = 'block';
                    setTimeout(() => {
                        window.parent.postMessage('closeModal', '*');
                        window.parent.postMessage('refreshData', '*');
                    }, 1500);
                } else {
                    throw new Error(response.message || 'Delete failed');
                }
            } catch (err) {
                errorDiv.textContent = '❌ ' + err.message;
                errorDiv.style.display = 'block';
            }
        });

        if (recordId) loadData();
    </script>
</body>
</html>