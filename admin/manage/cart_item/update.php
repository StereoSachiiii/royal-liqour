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

        <!-- SUCCESS MODAL -->
        <div id="successModal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <div class="modal-header">Cart Updated Successfully!</div>
                <div class="modal-body">
                    <p>Your changes have been saved.</p>
                    <div style="background:#f8f9fa; padding:15px; border-radius:8px; margin:10px 0; font-family:monospace; text-align:left;">
                        <strong>Cart ID:</strong> <span id="success_cart_id"></span><br>
                        <strong>Session ID:</strong> <span id="success_session_id"></span><br>
                        <strong>Status:</strong> <span id="success_status"></span><br>
                        <strong>Total:</strong> <span id="success_total"></span> cents<br>
                        <strong>Items:</strong> <span id="success_items"></span><br>
                        <strong>User:</strong> <span id="success_user"></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <button id="stayBtn" class="btn-secondary">Stay Here</button>
                    <button id="returnBtn" class="btn-primary">Back to List</button>
                </div>
            </div>
        </div>

        <!-- DELETE CONFIRM MODAL -->
        <div id="confirmModal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <div class="modal-header" id="deleteTitle">Confirm Delete</div>
                <div class="modal-body" id="deleteMessage"></div>
                <div class="modal-actions">
                    <button id="cancelDelete" class="btn-secondary">Cancel</button>
                    <button id="confirmDelete" class="btn-danger">Delete</button>
                </div>
            </div>
        </div>

        <!-- QUICK VIEW CARD -->
        <div class="quick-view-card">
            <h2>Cart Overview</h2>
            <div class="modal-content">
                <div class="detail-section">
                    <h3>Basic Info</h3>
                    <div class="detail-field"><strong>Cart ID:</strong> <span id="detail_cart_id">--</span></div>
                    <div class="detail-field"><strong>Session ID:</strong> <span id="detail_session_id">--</span></div>
                    <div class="detail-field"><strong>Status:</strong> <span id="detail_status">--</span></div>
                    <div class="detail-field"><strong>Total (Cents):</strong> <span id="detail_total_cents">--</span></div>
                    <div class="detail-field"><strong>Items:</strong> <span id="detail_item_count">--</span></div>
                </div>
                <div class="detail-section">
                    <h3>User</h3>
                    <div class="detail-field"><strong>Name:</strong> <span id="detail_user_name">Guest</span></div>
                    <div class="detail-field"><strong>Email:</strong> <span id="detail_user_email">N/A</span></div>
                </div>
                <div class="detail-section">
                    <h3>Timestamps</h3>
                    <div class="detail-field"><strong>Created:</strong> <span id="detail_created_at">--</span></div>
                    <div class="detail-field"><strong>Updated:</strong> <span id="detail_updated_at">--</span></div>
                    <div class="detail-field"><strong>Expires:</strong> <span id="detail_expires_at">--</span></div>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message" style="display:none;"></div>

        <div class="edit-card">
            <h2>Edit Cart</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId">

                <div class="form-group">
                    <label class="form-label">User (leave blank for guest)</label>
                    <input list="usersList" id="user_search" class="form-input" placeholder="Search user..." autocomplete="off">
                    <datalist id="usersList"></datalist>
                    <input type="hidden" id="user_id">
                    <small class="form-help">Optional – clear to make guest cart</small>
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

    <script type="module">
        import { API_URL, API_CART } from '../utils.js';

        const urlParams = new URLSearchParams(location.search);
        const cartId = Number(urlParams.get('id'));
        if (!cartId) {
            alert('No cart ID');
            location.href = '../../index.php';
        }

        const el = id => document.getElementById(id);
        const successModal = el('successModal');
        const confirmModal = el('confirmModal');
        const errorDiv = el('errorDiv');
        let deleteMode = null;

        // CORRECTLY EXTRACT NESTED CART DATA
        const getCartData = (json) => {
            return json?.data?.data || json?.data || json;
        };

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
            } catch (e) { console.error(e); }
        }

        el('user_search').addEventListener('input', () => {
            const val = el('user_search').value;
            const opt = [...el('usersList').options].find(o => o.value === val);
            el('user_id').value = opt ? opt.dataset.id : '';
        });

        // Load cart – FIXED FOR YOUR ACTUAL API
        async function loadCart() {
            try {
                const res = await fetch(`${API_URL}?entity=carts&id=${cartId}`);
                if (!res.ok) throw new Error('Cart not found');
                const json = await res.json();
                const c = getCartData(json);  // ← THIS IS THE FIX

                // Quick view
                el('detail_cart_id').textContent = c.id;
                el('detail_session_id').textContent = c.session_id || '—';
                el('detail_status').textContent = (c.status || 'active').charAt(0).toUpperCase() + (c.status || 'active').slice(1);
                el('detail_total_cents').textContent = c.total_cents || 0;
                el('detail_item_count').textContent = c.item_count || 0;
                el('detail_user_name').textContent = c.user_name || 'Guest';
                el('detail_user_email').textContent = c.user_email || 'N/A';
                el('detail_created_at').textContent = new Date(c.created_at).toLocaleString();
                el('detail_updated_at').textContent = c.updated_at ? new Date(c.updated_at).toLocaleString() : '—';
                el('detail_expires_at').textContent = c.expires_at ? new Date(c.expires_at).toLocaleString() : '—';

                // Form
                el('user_id').value = c.user_id || '';
                el('user_search').value = c.user_id ? `${c.user_name} (${c.user_email})` : '';
                el('session_id').value = c.session_id || '';
                el('status').value = c.status || 'active';
                el('total_cents').value = c.total_cents || 0;
                el('item_count').value = c.item_count || 0;
                el('recordId').value = c.id;

            } catch (err) {
                errorDiv.textContent = 'Failed to load cart: ' + err.message;
                errorDiv.style.display = 'block';
            }
        }

        // SAVE
        el('mainForm').addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.style.display = 'none';

            const payload = {
                id: cartId,
                user_id: el('user_id').value ? Number(el('user_id').value) : null,
                session_id: el('session_id').value.trim(),
                status: el('status').value,
                total_cents: Number(el('total_cents').value) || 0,
                item_count: Number(el('item_count').value) || 0
            };

            try {
                const res = await fetch(API_CART, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify(payload)
                });

                if (!res.ok) throw new Error(await res.text() || 'Update failed');

                const json = await res.json();
                const data = getCartData(json);  // ← Also fix success modal data

                // SUCCESS MODAL – now shows correct data
                el('success_cart_id').textContent = data.id;
                el('success_session_id').textContent = data.session_id;
                el('success_status').textContent = (data.status || 'active').charAt(0).toUpperCase() + (data.status || 'active').slice(1);
                el('success_total').textContent = data.total_cents;
                el('success_items').textContent = data.item_count;
                el('success_user').textContent = data.user_name ? `${data.user_name} (${data.user_email})` : 'Guest';

                successModal.style.display = 'flex';
                loadCart();

            } catch (err) {
                errorDiv.textContent = err.message;
                errorDiv.style.display = 'block';
            }
        });

        // Delete & modal controls (unchanged – already perfect)
        el('stayBtn').onclick = () => successModal.style.display = 'none';
        el('returnBtn').onclick = () => location.href = '../../index.php';

        el('softDeleteBtn').onclick = () => {
            deleteMode = 'soft';
            el('deleteTitle').textContent = 'Soft Delete Cart';
            el('deleteMessage').textContent = 'This cart will be hidden but kept in the database.';
            confirmModal.style.display = 'flex';
        };
        el('hardDeleteBtn').onclick = () => {
            deleteMode = 'hard';
            el('deleteTitle').textContent = 'Permanently Delete Cart';
            el('deleteMessage').textContent = 'This action CANNOT be undone!';
            confirmModal.style.display = 'flex';
        };
        el('cancelDelete').onclick = () => confirmModal.style.display = 'none';
        el('confirmDelete').onclick = async () => {
            confirmModal.style.display = 'none';
            await fetch(API_CART, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: cartId, hard: deleteMode === 'hard' })
            });
            alert(deleteMode === 'hard' ? 'Permanently deleted!' : 'Cart hidden');
            setTimeout(() => location.href = '../../index.php', 800);
        };

        window.addEventListener('click', e => {
            if (e.target === successModal || e.target === confirmModal) {
                successModal.style.display = confirmModal.style.display = 'none';
            }
        });

        // Start
        loadUsers();
        loadCart();
    </script>
</body>
</html>