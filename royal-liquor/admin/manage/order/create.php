<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Order</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Order</h1>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>New Order</h2>
            <form id="mainForm">
                <div class="form-group">
                    <label class="form-label required">User</label>
                    <input
                        list="usersList"
                        id="user_search"
                        class="form-input"
                        placeholder="Search user by name or email..."
                        autocomplete="off"
                        required
                    >
                    <datalist id="usersList"></datalist>
                    <input type="hidden" id="user_id" name="user_id">
                </div>

                <div class="form-group">
                    <label class="form-label required">Cart</label>
                    <input
                        list="cartsList"
                        id="cart_search"
                        class="form-input"
                        placeholder="Search cart..."
                        autocomplete="off"
                        required
                    >
                    <datalist id="cartsList"></datalist>
                    <input type="hidden" id="cart_id" name="cart_id">
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-input">
                        <option value="pending" selected>Pending</option>
                        <option value="paid">Paid</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="total_cents" class="form-label required">Total (Cents)</label>
                    <input type="number" id="total_cents" name="total_cents" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Shipping Address</label>
                    <input
                        list="shippingAddressesList"
                        id="shipping_address_search"
                        class="form-input"
                        placeholder="Search shipping address..."
                        autocomplete="off"
                    >
                    <datalist id="shippingAddressesList"></datalist>
                    <input type="hidden" id="shipping_address_id" name="shipping_address_id">
                </div>

                <div class="form-group">
                    <label class="form-label">Billing Address</label>
                    <input
                        list="billingAddressesList"
                        id="billing_address_search"
                        class="form-input"
                        placeholder="Search billing address..."
                        autocomplete="off"
                    >
                    <datalist id="billingAddressesList"></datalist>
                    <input type="hidden" id="billing_address_id" name="billing_address_id">
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-input"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Order</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header">Order Created Successfully</div>
            <div class="modal-body" id="successModalMessage"></div>
            <div class="modal-actions">
                <button id="successModalClose" class="btn-secondary">Close</button>
                <button id="successModalView" class="btn-primary">View Order</button>
            </div>
        </div>
    </div>

    <script type="module">
        const API_USERS = '/royal-liquor/api/v1/users';
        const API_ORDERS = '/royal-liquor/api/v1/orders';
        const API_CARTS = '/royal-liquor/api/v1/carts';
        const API_ADDRESSES = '/royal-liquor/api/v1/addresses';

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const successModal = document.getElementById('successModal');

        let userAddresses = [];
        let createdOrderId = null;

        const el = id => document.getElementById(id);

        const userSearch = el('user_search');
        const cartSearch = el('cart_search');
        const shippingAddressSearch = el('shipping_address_search');
        const billingAddressSearch = el('billing_address_search');

        const inputs = {
            user_id: el('user_id'),
            cart_id: el('cart_id'),
            status: el('status'),
            total_cents: el('total_cents'),
            shipping_address_id: el('shipping_address_id'),
            billing_address_id: el('billing_address_id'),
            notes: el('notes')
        };

        // Load users into datalist
        async function loadUsers() {
            try {
                const response = await fetch(`${API_USERS}?limit=500`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error('Failed to load users');

                const json = await response.json();
                const data = json.data?.data ?? json.data ?? json;
                const list = el('usersList');
                list.innerHTML = '';
                
                (data.items || []).forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = `${u.name} (${u.email})`;
                    opt.dataset.id = u.id;
                    list.appendChild(opt);
                });
            } catch (e) { 
                console.error('Failed to load users:', e); 
            }
        }

        // Load carts into datalist
        async function loadCarts() {
            try {
                const response = await fetch(`${API_CARTS}?limit=500`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error('Failed to load carts');

                const json = await response.json();
                const data = json.data?.data ?? json.data ?? json;
                const list = el('cartsList');
                list.innerHTML = '';
                
                (data.items || []).forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = `Cart #${c.id} - ${c.user_name || 'No User'}`;
                    opt.dataset.id = c.id;
                    list.appendChild(opt);
                });
            } catch (e) { 
                console.error('Failed to load carts:', e); 
            }
        }

        // Load addresses for a user
        async function loadAddresses(userId) {
            try {
                const response = await fetch(`${API_ADDRESSES}?user_id=${userId}`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error('Failed to load addresses');

                const json = await response.json();
                const data = json.data?.data ?? json.data ?? json;
                userAddresses = data.items || data || [];

                const shippingList = el('shippingAddressesList');
                const billingList = el('billingAddressesList');
                shippingList.innerHTML = '';
                billingList.innerHTML = '';

                userAddresses.forEach(addr => {
                    const displayText = `${addr.recipient_name} - ${addr.address_line1}, ${addr.city}`;
                    
                    const shippingOpt = document.createElement('option');
                    shippingOpt.value = displayText;
                    shippingOpt.dataset.id = addr.id;
                    shippingList.appendChild(shippingOpt);

                    const billingOpt = document.createElement('option');
                    billingOpt.value = displayText;
                    billingOpt.dataset.id = addr.id;
                    billingList.appendChild(billingOpt);
                });
            } catch (e) {
                console.error('Failed to load addresses:', e);
            }
        }

        // Handle user search input
        userSearch.addEventListener('input', e => {
            const val = e.target.value;
            const found = [...el('usersList').options].find(o => o.value === val);
            const userId = found ? found.dataset.id : '';
            inputs.user_id.value = userId;
            
            if (userId) {
                loadAddresses(userId);
            } else {
                el('shippingAddressesList').innerHTML = '';
                el('billingAddressesList').innerHTML = '';
                shippingAddressSearch.value = '';
                billingAddressSearch.value = '';
                inputs.shipping_address_id.value = '';
                inputs.billing_address_id.value = '';
            }
        });

        // Handle cart search input
        cartSearch.addEventListener('input', e => {
            const val = e.target.value;
            const found = [...el('cartsList').options].find(o => o.value === val);
            inputs.cart_id.value = found ? found.dataset.id : '';
        });

        // Handle address search inputs
        shippingAddressSearch.addEventListener('input', e => {
            const val = e.target.value;
            const found = [...el('shippingAddressesList').options].find(o => o.value === val);
            inputs.shipping_address_id.value = found ? found.dataset.id : '';
        });

        billingAddressSearch.addEventListener('input', e => {
            const val = e.target.value;
            const found = [...el('billingAddressesList').options].find(o => o.value === val);
            inputs.billing_address_id.value = found ? found.dataset.id : '';
        });

        // Success modal handlers
        el('successModalClose').onclick = () => {
            successModal.style.display = 'none';
            form.reset();
            userSearch.value = '';
            cartSearch.value = '';
            shippingAddressSearch.value = '';
            billingAddressSearch.value = '';
            inputs.user_id.value = '';
            inputs.cart_id.value = '';
            inputs.shipping_address_id.value = '';
            inputs.billing_address_id.value = '';
        };

        el('successModalView').onclick = () => {
            window.location.href = `update.php?id=${createdOrderId}`;
        };

        // Handle form submission
        form.addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.textContent = '';
            successDiv.textContent = '';

            if (!inputs.user_id.value) {
                errorDiv.textContent = 'Please select a valid user';
                errorDiv.style.display = 'block';
                return;
            }

            if (!inputs.cart_id.value) {
                errorDiv.textContent = 'Please select a valid cart';
                errorDiv.style.display = 'block';
                return;
            }

            const payload = {
                cart_id: Number(inputs.cart_id.value),
                user_id: Number(inputs.user_id.value),
                status: inputs.status.value,
                total_cents: Number(inputs.total_cents.value),
                shipping_address_id: inputs.shipping_address_id.value ? Number(inputs.shipping_address_id.value) : null,
                billing_address_id: inputs.billing_address_id.value ? Number(inputs.billing_address_id.value) : null,
                notes: inputs.notes.value.trim()
            };

            try {
                const response = await fetch(API_ORDERS, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(text || 'Failed to create order');
                }

                const json = await response.json();
                const data = json.data ?? json;
                createdOrderId = data.id;
                console.log('Create response:', json);

                // Show success modal with created details
                const modalMsg = el('successModalMessage');
                modalMsg.innerHTML = `
                    <div style="text-align: left;">
                        <p><strong>Order Created Successfully!</strong></p>
                        <p><strong>Order Number:</strong> ${data.order_number}</p>
                        <p><strong>Status:</strong> ${data.status}</p>
                        <p><strong>Total:</strong> ${data.total_cents} cents</p>
                        <p><strong>Notes:</strong> ${data.notes || 'None'}</p>
                    </div>
                `;
                successModal.style.display = 'flex';

            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to create order';
                errorDiv.style.display = 'block';
            }
        });

        // Initialize
        loadUsers();
        loadCarts();
    </script>
</body>
</html>