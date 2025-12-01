<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Cart</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update Cart</h1>

        <!-- SUCCESS MODAL - THIS WILL POP UP AFTER SAVE -->
        <div id="successModal" class="modal-overlay" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">Cart Updated Successfully!</div>
                <div class="modal-body">
                    <p>Your changes have been saved.</p>
                    <p><strong>Cart ID:</strong> <span id="savedCartId"></span></p>
                </div>
                <div class="modal-actions">
                    <button id="closeSuccessModal" class="btn-primary">OK</button>
                </div>
            </div>
        </div>

        <!-- QUICK VIEW -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>Cart Overview</h2>
            <div class="modal-content">
                <div class="detail-section">
                    <h3>Basic Info</h3>
                    <div class="detail-field"><strong>ID:</strong> <span id="detail_id">--</span></div>
                    <div class="detail-field"><strong>User:</strong> <span id="detail_user">--</span></div>
                    <div class="detail-field"><strong>Session ID:</strong> <span id="detail_session">--</span></div>
                    <div class="detail-field"><strong>Status:</strong> <span id="detail_status">--</span></div>
                    <div class="detail-field"><strong>Total (LKR):</strong> <span id="detail_total">--</span></div>
                    <div class="detail-field"><strong>Items:</strong> <span id="detail_items">--</span></div>
                    <div class="detail-field"><strong>Created:</strong> <span id="detail_created">--</span></div>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>

        <div class="edit-card">
            <h2>Edit Cart</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId">

                <!-- User Datalist -->
                <div class="form-group">
                    <label class="form-label required">User</label>
                    <input list="usersList" id="user_search" class="form-input" placeholder="Type to search user..." readonly>
                    <datalist id="usersList"></datalist>
                    <input type="hidden" id="user_id">
                </div>

                <div class="form-group">
                    <label for="session_id" class="form-label required">Session ID</label>
                    <input type="text" id="session_id" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" class="form-input">
                        <option value="active">Active</option>
                        <option value="converted">Converted</option>
                        <option value="abandoned">Abandoned</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="total_cents" class="form-label">Total Amount (Cents)</label>
                    <input type="number" id="total_cents" class="form-input" min="0" step="1">
                </div>

                <div class="form-group">
                    <label for="item_count" class="form-label">Item Count</label>
                    <input type="number" id="item_count" class="form-input" min="0" step="1">
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

    <!-- DELETE CONFIRM MODAL -->
    <div id="confirmModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle">Confirm Action</div>
            <div class="modal-body" id="modalMessage"></div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-danger">Confirm</button>
            </div>
        </div>
    </div>

    <script type="module">
        import { fetchHandler, API_URL, API_CART } from '../utils.js';

        const getData = (res) => res?.data ?? res;

        const params = new URLSearchParams(location.search);
        const cartId = Number(params.get('id'));
        if (!cartId) {
            alert('No cart ID');
            location.href = '../../index.php';
        }

        const loadUrl = `${API_URL}?entity=carts&id=${cartId}`;

        const el = id => document.getElementById(id);
        const successModal = el('successModal');
        const confirmModal = el('confirmModal');
        let deleteMode = null;

        // Load users
        async function loadUsers() {
            try {
                const res = await fetch(`${API_URL}?entity=users&limit=1000`);
                const json = await res.json();
                const users = json.data?.items || json.data || [];
                const list = el('usersList');
                list.innerHTML = '';
                users.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = `${u.name} (${u.email})`;
                    opt.dataset.id = u.id;
                    list.appendChild(opt);
                });
            } catch (e) { console.error('Failed to load users'); }
        }

        // Load cart
        async function loadCart() {
            try {
                const raw = await fetchHandler(loadUrl, 'GET');
                const c = getData(raw);

                el('detail_id').textContent = c.id;
                el('detail_user').textContent = `${c.user_name} (${c.user_email})`;
                el('detail_session').textContent = c.session_id || '—';
                el('detail_status').textContent = (c.status || 'active').charAt(0).toUpperCase() + (c.status || 'active').slice(1);
                el('detail_total').textContent = (c.total_cents / 100).toFixed(2) + ' LKR';
                el('detail_items').textContent = c.item_count || 0;
                el('detail_created').textContent = new Date(c.created_at).toLocaleString();

                el('user_id').value = c.user_id;
                el('user_search').value = `${c.user_name} (${c.user_email})`;
                el('session_id').value = c.session_id || '';
                el('status').value = c.status || 'active';
                el('total_cents').value = c.total_cents || 0;
                el('item_count').value = c.item_count || 0;
                el('recordId').value = c.id;

            } catch (e) {
                el('errorDiv').textContent = 'Failed to load cart';
            }
        }

        // SAVE CART — THIS IS WHERE THE SUCCESS MODAL IS TRIGGERED
        el('mainForm').addEventListener('submit', async e => {
            e.preventDefault();
            el('errorDiv').textContent = '';

            const payload = {
                id: cartId,
                user_id: Number(el('user_id').value),
                session_id: el('session_id').value.trim(),
                status: el('status').value,
                total_cents: Number(el('total_cents').value) || 0,
                item_count: Number(el('item_count').value) || 0
            };

            try {
                await fetchHandler(`${API_CART}?entity=carts&action=update`, 'PUT', payload);

                // SUCCESS MODAL APPEARS HERE
                el('savedCartId').textContent = cartId;
                successModal.style.display = 'flex';

                // Refresh data
                loadCart();

            } catch (err) {
                el('errorDiv').textContent = err.message || 'Update failed';
            }
        });

        // Close success modal
        el('closeSuccessModal').onclick = () => successModal.style.display = 'none';
        window.addEventListener('click', (e) => {
            if (e.target === successModal || e.target === confirmModal) {
                successModal.style.display = 'none';
                confirmModal.style.display = 'none';
            }
        });

        // Delete handlers
        el('softDeleteBtn').onclick = () => {
            deleteMode = 'soft';
            el('modalTitle').textContent = 'Soft Delete Cart';
            el('modalMessage').textContent = 'Cart will be hidden (can be restored later)';
            confirmModal.style.display = 'flex';
        };

        el('hardDeleteBtn').onclick = () => {
            deleteMode = 'hard';
            el('modalTitle').textContent = 'Permanently Delete Cart';
            el('modalMessage').textContent = 'This action cannot be undone!';
            confirmModal.style.display = 'flex';
        };

        el('modalCancel').onclick = () => confirmModal.style.display = 'none';

        el('modalConfirm').onclick = async () => {
            confirmModal.style.display = 'none';
            try {
                await fetchHandler(`${API_CART}?entity=carts&action=delete`, 'DELETE', {
                    id: cartId,
                    hard: deleteMode === 'hard'
                });
                alert(deleteMode === 'hard' ? 'Cart permanently deleted!' : 'Cart soft deleted');
                setTimeout(() => location.href = '../../index.php', 1000);
            } catch (err) {
                el('errorDiv').textContent = 'Delete failed';
            }
        };

        // Init
        loadUsers();
        loadCart();
    </script>
</body>
</html>