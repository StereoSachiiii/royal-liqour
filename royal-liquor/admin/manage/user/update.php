<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <link rel="stylesheet" href="../../admin.css">
    <link rel="stylesheet" href="../../assets/css/products.css">
</head>
<body>
    <div class="admin-container products-edit">
        <h1>Update User</h1>
        
        <div id="quickViewCard" class="quick-view-card">
            <h2>📦 User Overview</h2>
            <div id="quickViewContent">
                <div class="modal-content">
                    <h2>👤 <span id="user_detail_name">Loading...</span></h2>
                    <div class="detail-section">
                        <h3>Contact Info</h3>
                        <div class="detail-field"><strong>Email:</strong> <span id="user_detail_email">-</span></div>
                        <div class="detail-field"><strong>Phone:</strong> <span id="user_detail_phone">-</span></div>
                        <div class="detail-field"><strong>Is Admin:</strong> <span id="user_detail_is_admin">-</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>✏️ Edit User</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">
                
                <div class="form-group">
                    <label for="name" class="form-label required">Name</label>
                    <input type="text" id="name" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-input" placeholder="+94 77 123 4567">
                </div>

                <div class="form-group">
                    <label for="profile_image_url" class="form-label">Profile Image URL</label>
                    <input type="text" id="profile_image_url" name="profileImageUrl" class="form-input" placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="is_active" name="is_active" class="form-checkbox">
                        <label for="is_active">Is Active</label>
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

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">Confirm Action</div>
            <div class="modal-body">Are you sure?</div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-danger">Confirm</button>
            </div>
        </div>
    </div>

    <script type="module">
        import { API_URL_USERS } from "../../js/pages/config.js";

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
        const el = id => document.getElementById(id);
        const errorDiv = el('errorDiv');
        const successDiv = el('successDiv');
        const confirmModal = el('confirmModal');
        let deleteMode = 'soft';

        if (!recordId) {
            errorDiv.textContent = 'No user ID provided';
            errorDiv.style.display = 'block';
        }

        async function loadData() {
            try {
                // Get enriched data
                const response = await apiCall(`${API_URL_USERS}?id=${recordId}`);
                if (response.success === false) throw new Error(response.message);
                
                const data = response.data || response;
                
                // Quick View
                el('user_detail_name').textContent = data.name || 'N/A';
                el('user_detail_email').textContent = data.email || 'N/A';
                el('user_detail_phone').textContent = data.phone || 'N/A';
                el('user_detail_is_admin').textContent = data.is_admin ? 'Yes' : 'No';

                // Form
                el('recordId').value = data.id;
                el('name').value = data.name || '';
                el('email').value = data.email || '';
                el('phone').value = data.phone || '';
                el('profile_image_url').value = data.profile_image_url || '';
                el('is_active').checked = data.is_active;

            } catch (err) {
                console.error(err);
                errorDiv.textContent = 'Error: ' + err.message;
                errorDiv.style.display = 'block';
            }
        }

        el('mainForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';

            const payload = {
                id: recordId,
                name: el('name').value,
                email: el('email').value,
                phone: el('phone').value,
                profileImageUrl: el('profile_image_url').value,
                is_active: el('is_active').checked
            };

            try {
                // Determine if we use api/users.php direct PUT or router 
                // Our users.php has PUT handler that calls updateProfile
                const response = await apiCall(`${API_URL_USERS}?id=${recordId}`, 'PUT', payload);
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

        // Delete Handlers
        function showConfirm(mode) {
            deleteMode = mode;
            el('modalCancel').textContent = 'Cancel';
            el('modalConfirm').textContent = mode === 'hard' ? 'Permanently Delete' : 'Delete';
            document.querySelector('.modal-body').textContent = mode === 'hard' 
                ? 'Are you sure you want to permanently delete this user? This cannot be undone.' 
                : 'Are you sure you want to soft delete this user?';
            confirmModal.style.display = 'flex';
        }

        el('softDeleteBtn').addEventListener('click', () => showConfirm('soft'));
        el('hardDeleteBtn').addEventListener('click', () => showConfirm('hard'));
        el('modalCancel').addEventListener('click', () => confirmModal.style.display = 'none');

        el('modalConfirm').addEventListener('click', async () => {
            confirmModal.style.display = 'none';
            try {
                const url = `${API_URL_USERS}?id=${recordId}${deleteMode === 'hard' ? '&hard=true' : ''}`;
                const response = await apiCall(url, 'DELETE');
                
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