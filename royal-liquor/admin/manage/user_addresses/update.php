<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User Address</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update User Address</h1>

        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>User Address Overview</h2>
            <div class="modal-content">
                <h2>User Address Detail</h2>
                <div class="detail-section">
                    <h3>Basic Info</h3>
                    <div class="detail-field"><strong>Address Type:</strong> <span id="detail_type">--</span></div>
                    <div class="detail-field"><strong>Recipient:</strong> <span id="detail_recipient">--</span></div>
                    <div class="detail-field"><strong>Phone:</strong> <span id="detail_phone">--</span></div>
                    <div class="detail-field"><strong>Default:</strong> <span id="detail_default">--</span></div>
                </div>
                <div class="detail-section">
                    <h3>Address</h3>
                    <div class="detail-field"><strong>Line 1:</strong> <span id="detail_line1">--</span></div>
                    <div class="detail-field"><strong>Line 2:</strong> <span id="detail_line2">--</span></div>
                    <div class="detail-field"><strong>City:</strong> <span id="detail_city">--</span></div>
                    <div class="detail-field"><strong>State:</strong> <span id="detail_state">--</span></div>
                    <div class="detail-field"><strong>Postal:</strong> <span id="detail_postal">--</span></div>
                    <div class="detail-field"><strong>Country:</strong> <span id="detail_country">--</span></div>
                </div>
                <div class="detail-section">
                    <h3>User</h3>
                    <div class="detail-field"><strong>Name:</strong> <span id="detail_user_name">--</span></div>
                    <div class="detail-field"><strong>Email:</strong> <span id="detail_user_email">--</span></div>
                </div>
                <div class="detail-section">
                    <h3>Usage Count</h3>
                    <div class="detail-field"><strong>Used for Shipping:</strong> <span id="detail_shipping">0</span></div>
                    <div class="detail-field"><strong>Used for Billing:</strong> <span id="detail_billing">0</span></div>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>Edit User Address</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">

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
                    <label for="address_type" class="form-label">Address Type</label>
                    <select id="address_type" name="address_type" class="form-input">
                        <option value="both">Both (Shipping + Billing)</option>
                        <option value="shipping">Shipping Only</option>
                        <option value="billing">Billing Only</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="recipient_name" class="form-label required">Recipient Name</label>
                    <input type="text" id="recipient_name" name="recipient_name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label required">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-input" required placeholder="+94 77 123 4567">
                </div>

                <div class="form-group">
                    <label for="address_line1" class="form-label required">Address Line 1</label>
                    <input type="text" id="address_line1" name="address_line1" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="address_line2" class="form-label">Address Line 2</label>
                    <input type="text" id="address_line2" name="address_line2" class="form-input">
                </div>

                <div class="form-group">
                    <label for="city" class="form-label required">City</label>
                    <input type="text" id="city" name="city" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="state" class="form-label">State / Province</label>
                    <input type="text" id="state" name="state" class="form-input">
                </div>

                <div class="form-group">
                    <label for="postal_code" class="form-label required">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="country" class="form-label required">Country</label>
                    <input type="text" id="country" name="country" class="form-input" value="Sri Lanka" required>
                </div>

                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="is_default" name="is_default" class="form-checkbox">
                        <label for="is_default">Set as Default Address</label>
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
            <div class="modal-header">Address Updated Successfully</div>
            <div class="modal-body" id="successModalMessage"></div>
            <div class="modal-actions">
                <button id="successModalStay" class="btn-secondary">Stay on Page</button>
                <button id="successModalReturn" class="btn-primary">Return to List</button>
            </div>
        </div>
    </div>

    <script type="module">
        const API_USERS = '/royal-liquor/api/v1/users';
        const API_ADDRESSES = '/royal-liquor/api/v1/addresses';

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        if (!recordId) {
            alert('No address ID provided');
            window.location.href = '../../index.php';
        }

        const loadUrl = `${API_ADDRESSES}/${recordId}?enriched=true`;

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const modal = document.getElementById('confirmModal');
        const successModal = document.getElementById('successModal');

        let currentAction = null;

        const el = id => document.getElementById(id);

        const userSearch = el('user_search');
        const inputs = {
            user_id: el('user_id'),
            address_type: el('address_type'),
            recipient_name: el('recipient_name'),
            phone: el('phone'),
            address_line1: el('address_line1'),
            address_line2: el('address_line2'),
            city: el('city'),
            state: el('state'),
            postal_code: el('postal_code'),
            country: el('country'),
            is_default: el('is_default')
        };

        const q = {
            type: el('detail_type'),
            recipient: el('detail_recipient'),
            phone: el('detail_phone'),
            default: el('detail_default'),
            line1: el('detail_line1'),
            line2: el('detail_line2'),
            city: el('detail_city'),
            state: el('detail_state'),
            postal: el('detail_postal'),
            country: el('detail_country'),
            userName: el('detail_user_name'),
            userEmail: el('detail_user_email'),
            shipping: el('detail_shipping'),
            billing: el('detail_billing')
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

        // Load address data from server
        async function loadAddress() {
            try {
                const response = await fetch(loadUrl, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error('Failed to load address');

                const json = await response.json();
                const data = json.data?.data ?? json.data ?? json;

                // Quick view
                q.type.textContent = (data.address_type || 'both').charAt(0).toUpperCase() + (data.address_type || 'both').slice(1);
                q.recipient.textContent = data.recipient_name || '--';
                q.phone.textContent = data.phone || '--';
                q.default.textContent = data.is_default ? 'Yes' : 'No';
                q.line1.textContent = data.address_line1 || '--';
                q.line2.textContent = data.address_line2 || '--';
                q.city.textContent = data.city || '--';
                q.state.textContent = data.state || '--';
                q.postal.textContent = data.postal_code || '--';
                q.country.textContent = data.country || '--';
                q.userName.textContent = data.user_name || '--';
                q.userEmail.textContent = data.user_email || '--';
                q.shipping.textContent = data.used_as_shipping || 0;
                q.billing.textContent = data.used_as_billing || 0;

                // Form fields
                inputs.user_id.value = data.user_id;
                userSearch.value = `${data.user_name} (${data.user_email})`;
                inputs.address_type.value = data.address_type || 'both';
                inputs.recipient_name.value = data.recipient_name || '';
                inputs.phone.value = data.phone || '';
                inputs.address_line1.value = data.address_line1 || '';
                inputs.address_line2.value = data.address_line2 || '';
                inputs.city.value = data.city || '';
                inputs.state.value = data.state || '';
                inputs.postal_code.value = data.postal_code || '';
                inputs.country.value = data.country || 'Sri Lanka';
                inputs.is_default.checked = !!data.is_default;

                el('recordId').value = data.id;

            } catch (err) {
                errorDiv.textContent = 'Failed to load address: ' + (err.message || 'Unknown error');
            }
        }

        // Save changes
        form.addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.textContent = '';
            successDiv.textContent = '';

            const payload = {
                id: recordId,
                address_type: inputs.address_type.value,
                recipient_name: inputs.recipient_name.value.trim(),
                phone: inputs.phone.value.trim(),
                address_line1: inputs.address_line1.value.trim(),
                address_line2: inputs.address_line2.value.trim(),
                city: inputs.city.value.trim(),
                state: inputs.state.value.trim(),
                postal_code: inputs.postal_code.value.trim(),
                country: inputs.country.value.trim(),
                is_default: inputs.is_default.checked
            };

            try {
                const response = await fetch(`${API_ADDRESSES}/${recordId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(text || 'Failed to update address');
                }

                const json = await response.json();
                const data = json.data ?? json;
                console.log('Update response:', json);
                
                // Show success modal with updated details
                const modalMsg = el('successModalMessage');
                modalMsg.innerHTML = `
                    <div style="text-align: left;">
                        <p><strong>Updated Address Details:</strong></p>
                        <p><strong>Recipient:</strong> ${data.recipient_name}</p>
                        <p><strong>Phone:</strong> ${data.phone}</p>
                        <p><strong>Address:</strong> ${data.address_line1}${data.address_line2 ? ', ' + data.address_line2 : ''}</p>
                        <p><strong>City:</strong> ${data.city}</p>
                        <p><strong>Postal Code:</strong> ${data.postal_code}</p>
                        <p><strong>Country:</strong> ${data.country}</p>
                        <p><strong>Type:</strong> ${data.address_type}</p>
                        <p><strong>Default:</strong> ${data.is_default ? 'Yes' : 'No'}</p>
                    </div>
                `;
                successModal.style.display = 'flex';
                
                await loadAddress();
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to update address';
                errorDiv.style.display = 'block';
            }
        });

        // Delete handlers
        el('softDeleteBtn').onclick = () => {
            currentAction = 'soft';
            el('modalTitle').textContent = 'Soft Delete Address';
            el('modalMessage').textContent = 'This address will be hidden but kept in database.';
            modal.style.display = 'flex';
        };

        el('hardDeleteBtn').onclick = () => {
            currentAction = 'hard';
            el('modalTitle').textContent = 'Permanently Delete Address';
            el('modalMessage').textContent = 'This action cannot be undone.';
            modal.style.display = 'flex';
        };

        el('modalCancel').onclick = () => modal.style.display = 'none';
        el('modalConfirm').onclick = async () => {
            modal.style.display = 'none';
            try {
                const response = await fetch(`${API_ADDRESSES}/${recordId}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        id: recordId,
                        hard: currentAction === 'hard'
                    })
                });

                if (!response.ok) throw new Error('Delete failed');

                successDiv.textContent = currentAction === 'hard' ? 'Permanently deleted!' : 'Address hidden!';
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
        loadAddress().then(() => {
            form.scrollIntoView({ behavior: 'smooth' });
        });
    </script>
</body>
</html>