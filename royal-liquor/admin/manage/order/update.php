<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update Order</h1>

        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>Order Overview</h2>
            <div class="modal-content">
                <h2>Order Detail</h2>
                <div class="detail-section">
                    <h3>Basic Info</h3>
                    <div class="detail-field"><strong>Order Number:</strong> <span id="detail_order_number">--</span></div>
                    <div class="detail-field"><strong>Status:</strong> <span id="detail_status">--</span></div>
                    <div class="detail-field"><strong>Total (Cents):</strong> <span id="detail_total_cents">--</span></div>
                </div>
                <div class="detail-section">
                    <h3>User Info</h3>
                    <div class="detail-field"><strong>User Name:</strong> <span id="detail_user_name">--</span></div>
                    <div class="detail-field"><strong>User Email:</strong> <span id="detail_user_email">--</span></div>
                    <div class="detail-field"><strong>User Phone:</strong> <span id="detail_user_phone">--</span></div>
                </div>
                <div class="detail-section">
                    <h3>Shipping Address</h3>
                    <div id="detail_shipping_address" class="json-data-block">--</div>
                </div>
                <div class="detail-section">
                    <h3>Billing Address</h3>
                    <div id="detail_billing_address" class="json-data-block">--</div>
                </div>
                <div class="detail-section">
                    <h3>Items</h3>
                    <div id="detail_items" class="json-data-block">--</div>
                </div>
                <div class="detail-section">
                    <h3>Payments</h3>
                    <div id="detail_payments" class="json-data-block">--</div>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>Edit Order</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">

                <div class="form-group">
                    <label for="order_number" class="form-label required">Order Number</label>
                    <input type="text" id="order_number" name="order_number" class="form-input" required readonly>
                </div>

                <div class="form-group">
                    <label class="form-label required">User</label>
                    <input
                        list="usersList"
                        id="user_search"
                        class="form-input"
                        placeholder="Search user by name or email..."
                        autocomplete="off"
                        readonly
                    >
                    <datalist id="usersList"></datalist>
                    <input type="hidden" id="user_id" name="user_id">
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-input">
                        <option value="pending">Pending</option>
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
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                    <button type="button" id="softDeleteBtn" class="btn-danger">Soft Delete</button>
                    <button type="button" id="hardDeleteBtn" class="btn-danger">Hard Delete</button>
                </div>
            </form>
        </div>
    </div>

    <!-- CONFIRM DELETE MODAL -->
    <div id="confirmModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle">Confirm Delete</div>
            <div class="modal-body" id="modalMessage"></div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-danger">Confirm</button>
            </div>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header">Order Updated Successfully</div>
            <div class="modal-body" id="successModalMessage"></div>
            <div class="modal-actions">
                <button id="successModalStay" class="btn-secondary">Stay on Page</button>
                <button id="successModalReturn" class="btn-primary">Return to List</button>
            </div>
        </div>
    </div>

    <script type="module">
        const API_ORDERS = '/royal-liquor/api/v1/orders';
        const API_USERS = '/royal-liquor/api/v1/users';
        const API_ADDRESS = '/royal-liquor/api/v1/addresses';

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        if (!recordId) {
            alert('No order ID provided');
            window.location.href = '../../index.php';
        }

        const loadUrl = `${API_ORDERS}/${recordId}?enriched=true`;

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const modal = document.getElementById('confirmModal');
        const successModal = document.getElementById('successModal');

        let currentAction = null;
        let userAddresses = [];

        const el = id => document.getElementById(id);

        const userSearch = el('user_search');
        const shippingAddressSearch = el('shipping_address_search');
        const billingAddressSearch = el('billing_address_search');

        const inputs = {
            user_id: el('user_id'),
            order_number: el('order_number'),
            status: el('status'),
            total_cents: el('total_cents'),
            shipping_address_id: el('shipping_address_id'),
            billing_address_id: el('billing_address_id'),
            notes: el('notes')
        };

        const q = {
            orderNumber: el('detail_order_number'),
            status: el('detail_status'),
            totalCents: el('detail_total_cents'),
            userName: el('detail_user_name'),
            userEmail: el('detail_user_email'),
            userPhone: el('detail_user_phone'),
            shippingAddress: el('detail_shipping_address'),
            billingAddress: el('detail_billing_address'),
            items: el('detail_items'),
            payments: el('detail_payments')
        };

        // Format address for display
        const formatAddress = (addr) => {
            if (!addr) return 'No address available';
            return `${addr.recipient_name}\n${addr.phone}\n${addr.address_line1}${addr.address_line2 ? '\n' + addr.address_line2 : ''}\n${addr.city}, ${addr.state || ''} ${addr.postal_code}\n${addr.country}`;
        };

        // Format items for display
        const formatItems = (items) => {
            if (!items || items.length === 0) return 'No items found';
            return items.map(item => `${item.product_name} - Qty: ${item.quantity} - Price: ${item.price_cents} cents`).join('\n');
        };

        // Format payments for display
        const formatPayments = (payments) => {
            if (!payments || payments.length === 0) return 'No payments found';
            return payments.map(p => `${p.method} - ${p.amount_cents} cents - ${p.status}`).join('\n');
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

        // Load addresses for a user
        async function loadAddresses(userId) {
            try {
                const response = await fetch(`${API_ADDRESS}?user_id=${userId}`, {
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

        // Handle address search input
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

        // Load order data from server
        async function loadOrder() {
            try {
                const response = await fetch(loadUrl, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error('Failed to load order');

                const json = await response.json();
                const data = json.data?.data ?? json.data ?? json;

                // Quick view
                q.orderNumber.textContent = data.order_number || '--';
                q.status.textContent = data.status || '--';
                q.totalCents.textContent = data.total_cents || 0;
                q.userName.textContent = data.user_name || '--';
                q.userEmail.textContent = data.user_email || '--';
                q.userPhone.textContent = data.user_phone || '--';
                q.shippingAddress.textContent = formatAddress(data.shipping_address);
                q.billingAddress.textContent = formatAddress(data.billing_address);
                q.items.textContent = formatItems(data.items);
                q.payments.textContent = formatPayments(data.payments);

                // Form fields
                inputs.user_id.value = data.user_id;
                userSearch.value = `${data.user_name} (${data.user_email})`;
                inputs.order_number.value = data.order_number || '';
                inputs.status.value = data.status || 'pending';
                inputs.total_cents.value = data.total_cents || 0;
                inputs.notes.value = data.notes || '';

                el('recordId').value = data.id;

                // Load addresses for this user
                await loadAddresses(data.user_id);

                // Set selected addresses
                if (data.shipping_address) {
                    const shippingText = `${data.shipping_address.recipient_name} - ${data.shipping_address.address_line1}, ${data.shipping_address.city}`;
                    shippingAddressSearch.value = shippingText;
                    inputs.shipping_address_id.value = data.shipping_address_id;
                }

                if (data.billing_address) {
                    const billingText = `${data.billing_address.recipient_name} - ${data.billing_address.address_line1}, ${data.billing_address.city}`;
                    billingAddressSearch.value = billingText;
                    inputs.billing_address_id.value = data.billing_address_id;
                }

            } catch (err) {
                errorDiv.textContent = 'Failed to load order: ' + (err.message || 'Unknown error');
            }
        }

        // Save changes
        form.addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.textContent = '';
            successDiv.textContent = '';

            const payload = {
                id: recordId,
                status: inputs.status.value,
                total_cents: Number(inputs.total_cents.value),
                shipping_address_id: inputs.shipping_address_id.value ? Number(inputs.shipping_address_id.value) : null,
                billing_address_id: inputs.billing_address_id.value ? Number(inputs.billing_address_id.value) : null,
                notes: inputs.notes.value.trim()
            };

            try {
                const response = await fetch(`${API_ORDERS}/${recordId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(text || 'Failed to update order');
                }

                const json = await response.json();
                const data = json.data ?? json;
                console.log('Update response:', json);
                
                // Show success modal with updated details
                const modalMsg = el('successModalMessage');
                modalMsg.innerHTML = `
                    <div style="text-align: left;">
                        <p><strong>Updated Order Details:</strong></p>
                        <p><strong>Order Number:</strong> ${data.order_number}</p>
                        <p><strong>Status:</strong> ${data.status}</p>
                        <p><strong>Total:</strong> ${data.total_cents} cents</p>
                        <p><strong>Notes:</strong> ${data.notes || 'None'}</p>
                    </div>
                `;
                successModal.style.display = 'flex';
                
                await loadOrder();
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to update order';
                errorDiv.style.display = 'block';
            }
        });

        // Delete handlers
        el('softDeleteBtn').onclick = () => {
            currentAction = 'soft';
            el('modalTitle').textContent = 'Soft Delete Order';
            el('modalMessage').textContent = 'This order will be hidden but kept in database.';
            modal.style.display = 'flex';
        };

        el('hardDeleteBtn').onclick = () => {
            currentAction = 'hard';
            el('modalTitle').textContent = 'Permanently Delete Order';
            el('modalMessage').textContent = 'This action cannot be undone.';
            modal.style.display = 'flex';
        };

        el('modalCancel').onclick = () => modal.style.display = 'none';
        el('modalConfirm').onclick = async () => {
            modal.style.display = 'none';
            try {
                const response = await fetch(`${API_ORDERS}/${recordId}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        id: recordId,
                        hard: currentAction === 'hard'
                    })
                });

                if (!response.ok) throw new Error('Delete failed');

                successDiv.textContent = currentAction === 'hard' ? 'Permanently deleted!' : 'Order hidden!';
                successDiv.style.display = 'block';
                setTimeout(() => location.href = '../../index.php', 1500);
            } catch (err) {
                errorDiv.textContent = err.message || 'Delete failed';
                errorDiv.style.display = 'block';
            }
        };

        // Success modal handlers
        el('successModalStay').onclick = () => {
            successModal.style.display = 'none';
        };

        el('successModalReturn').onclick = () => {
            location.href = '../../index.php';
        };

        // Initialize
        loadUsers();
        loadOrder().then(() => {
            form.scrollIntoView({ behavior: 'smooth' });
        });
    </script>
</body>
</html>