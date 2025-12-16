<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Cart</title>
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../assets/css/products.css">
    <style>
        .edit-card {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-row {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        .detail-row {
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: #f9f9f9;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        
        <div id="quickViewCard" class="quick-view-card">
            <h2>🛒 Cart Overview</h2>
            <div class="modal-content">
                <div class="detail-section">
                    <div class="detail-field"><strong>ID:</strong> <span id="detail_id">-</span></div>
                    <div class="detail-field"><strong>User:</strong> <span id="detail_user">-</span></div>
                    <div class="detail-field"><strong>Session:</strong> <span id="detail_session">-</span></div>
                    <div class="detail-field"><strong>Status:</strong> <span id="detail_status">-</span></div>
                    <div class="detail-field"><strong>Total:</strong> <span id="detail_total">-</span></div>
                    <div class="detail-field"><strong>Items:</strong> <span id="detail_items">-</span></div>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>✏️ Edit Cart</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">
                
                <div class="form-group">
                    <label for="user_id" class="form-label required">User ID</label>
                    <input type="text" id="user_id" name="user_id" class="form-input" required readonly style="background:#eee; cursor:not-allowed;">
                    <div class="form-text">User association cannot be changed here.</div>
                </div>

                <div class="form-group">
                    <label for="session_id" class="form-label required">Session ID</label>
                    <input type="text" id="session_id" name="session_id" class="form-input" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-input">
                            <option value="active">Active</option>
                            <option value="converted">Converted</option>
                            <option value="abandoned">Abandoned</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="item_count" class="form-label">Item Count</label>
                        <input type="number" id="item_count" name="item_count" class="form-input" min="0" readonly style="background:#eee; cursor:not-allowed;">
                         <div class="form-text">Calculated from items. Edit items to change.</div>
                    </div>
                </div>

                <div class="form-group">
                     <label for="total_cents" class="form-label">Total Amount (Cents)</label>
                     <input type="number" id="total_cents" name="total_cents" class="form-input" min="0" readonly style="background:#eee; cursor:not-allowed;">
                     <div class="form-text">Calculated from items.</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                     <button type="button" onclick="window.parent.postMessage('closeModal', '*')" class="btn-secondary">Cancel</button>
                    <button type="button" id="deleteBtn" class="btn-danger" style="margin-left:auto;">Delete Cart</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">Confirm Delete</div>
            <div class="modal-body">Are you sure you want to delete this cart? This action cannot be undone.</div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-danger">Delete</button>
            </div>
        </div>
    </div>

    <script type="module">
        import { API_URL_CARTS } from "../../js/pages/config.js";

        // Internal API helper to bypass importing module with full path
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
                // Use getByIdEnriched indirectly via direct access
                const response = await apiCall(`${API_URL_CARTS}?id=${recordId}`);
                
                if (response.success === false) { // Handle explicit clean error
                     throw new Error(response.error || response.message || 'Failed to load data');
                }
                
                const data = response.data || response; // Handle wrapped or unwrapped

                if (!data || !data.id) throw new Error('Invalid data received');

                // Quick View
                el('detail_id').textContent = data.id;
                el('detail_user').textContent = data.user_name || 'Guest/Unknown';
                el('detail_session').textContent = data.session_id || '-';
                el('detail_status').textContent = (data.status || 'active').toUpperCase();
                el('detail_total').textContent = '$' + (data.total_cents / 100).toFixed(2);
                el('detail_items').textContent = data.item_count || 0;

                // Form
                el('recordId').value = data.id;
                el('user_id').value = data.user_id || '';
                el('session_id').value = data.session_id || '';
                el('status').value = data.status || 'active';
                el('item_count').value = data.item_count || 0;
                el('total_cents').value = data.total_cents || 0;

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
                user_id: Number(el('user_id').value),
                session_id: el('session_id').value.trim(),
                status: el('status').value,
                // These are readonly effectively, but sent anyway
                item_count: Number(el('item_count').value),
                total_cents: Number(el('total_cents').value)
            };

            try {
                const response = await apiCall(`${API_URL_CARTS}?id=${recordId}`, 'PUT', payload);
                
                if (response.success) {
                    successDiv.textContent = '✅ Cart updated successfully!';
                    successDiv.style.display = 'block';
                    loadData(); 
                    // Notify parent
                    window.parent.postMessage('refreshData', '*');
                } else {
                    throw new Error(response.message || response.error || 'Update failed');
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
                const response = await apiCall(`${API_URL_CARTS}?id=${recordId}`, 'DELETE');
                if (response.success) {
                    successDiv.textContent = '✅ Cart deleted. Closing...';
                    successDiv.style.display = 'block';
                    setTimeout(() => {
                        window.parent.postMessage('closeModal', '*');
                        window.parent.postMessage('refreshData', '*');
                    }, 1500);
                } else {
                    throw new Error(response.message || response.error || 'Delete failed');
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