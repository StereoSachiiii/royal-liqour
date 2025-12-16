<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Cart</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Cart</h1>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>New Cart</h2>
            <form id="mainForm">
                <div class="form-group">
                    <label class="form-label">User (Optional)</label>
                    <input list="usersList" id="user_search" class="form-input" placeholder="Search user (leave blank for guest)..." autocomplete="off">
                    <datalist id="usersList"></datalist>
                    <input type="hidden" id="user_id" name="user_id">
                </div>

                <div class="form-group">
                    <label for="session_id" class="form-label required">Session ID</label>
                    <input type="text" id="session_id" name="session_id" class="form-input" required placeholder="e.g., sess_abc123">
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-input">
                        <option value="active" selected>Active</option>
                        <option value="converted">Converted</option>
                        <option value="abandoned">Abandoned</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="total_cents" class="form-label">Total (Cents)</label>
                    <input type="number" id="total_cents" name="total_cents" class="form-input" value="0">
                </div>

                <div class="form-group">
                    <label for="item_count" class="form-label">Item Count</label>
                    <input type="number" id="item_count" name="item_count" class="form-input" value="0">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Cart</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header">Cart Created Successfully</div>
            <div class="modal-body" id="successModalMessage"></div>
            <div class="modal-actions">
                <button id="successModalClose" class="btn-secondary">Close</button>
                <button id="successModalView" class="btn-primary">View Cart</button>
            </div>
        </div>
    </div>

    <script type="module">
        const API_USERS = '/royal-liquor/api/v1/users';
        const API_CARTS = '/royal-liquor/api/v1/carts';

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successModal = document.getElementById('successModal');
        const el = id => document.getElementById(id);

        let createdCartId = null;

        const userSearch = el('user_search');
        const inputs = {
            user_id: el('user_id'),
            session_id: el('session_id'),
            status: el('status'),
            total_cents: el('total_cents'),
            item_count: el('item_count')
        };

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

        userSearch.addEventListener('input', e => {
            const val = e.target.value;
            const found = [...el('usersList').options].find(o => o.value === val);
            inputs.user_id.value = found ? found.dataset.id : '';
        });

        el('successModalClose').onclick = () => {
            successModal.style.display = 'none';
            form.reset();
            userSearch.value = '';
            inputs.user_id.value = '';
        };

        el('successModalView').onclick = () => {
            window.location.href = `update.php?id=${createdCartId}`;
        };

        form.addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.textContent = '';

            const payload = {
                user_id: inputs.user_id.value ? Number(inputs.user_id.value) : null,
                session_id: inputs.session_id.value.trim(),
                status: inputs.status.value,
                total_cents: Number(inputs.total_cents.value),
                item_count: Number(inputs.item_count.value)
            };

            try {
                const response = await fetch(API_CARTS, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(text || 'Failed to create cart');
                }
                const json = await response.json();
                const data = json.data ?? json;
                createdCartId = data.id;

                const modalMsg = el('successModalMessage');
                modalMsg.innerHTML = `
                    <div style="text-align: left;">
                        <p><strong>Cart Created Successfully!</strong></p>
                        <p><strong>Cart ID:</strong> ${data.id}</p>
                        <p><strong>Session ID:</strong> ${data.session_id}</p>
                        <p><strong>Status:</strong> ${data.status}</p>
                        <p><strong>Total:</strong> ${data.total_cents} cents</p>
                        <p><strong>Items:</strong> ${data.item_count}</p>
                    </div>
                `;
                successModal.style.display = 'flex';
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to create cart';
                errorDiv.style.display = 'block';
            }
        });

        loadUsers();
    </script>
</body>
</html>