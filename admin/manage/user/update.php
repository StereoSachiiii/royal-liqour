<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update User</h1>
        
        <!-- SUCCESS/ERROR MODAL -->
        <div id="messageModal" class="confirmation_overlay" style="display: none;">
            <div class="confirmation_box">
                <h2 id="messageModalTitle"></h2>
                <p id="messageModalText" style="white-space: pre-wrap;"></p>
                <div class="confirmation_actions">
                    <button id="messageModalClose" class="confirmation_btn">Close</button>
                </div>
            </div>
        </div>
        
        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>📊 User Overview</h2>
            <div id="quickViewContent">
                <div class="modal-content">
                    <h2>👤 User Detail: <span id="user_detail_name"></span></h2>
                    <div class="detail-section">
                        <h3>Basic Info</h3>
                        <div class="detail-field"><strong>Email:</strong> <span id="user_detail_email"></span></div>
                        <div class="detail-field"><strong>Phone:</strong> <span id="user_detail_phone"></span></div>
                        <div class="detail-field"><strong>Is Admin:</strong> <span id="user_detail_is_admin"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>Order Summary</h3>
                        <div class="detail-field"><strong>Total Orders:</strong> <span id="user_detail_total_orders"></span></div>
                        <div class="detail-field"><strong>Lifetime Value (Cents):</strong> <span id="user_detail_ltv"></span></div>
                        <div class="detail-field"><strong>Avg. Order Value (Cents):</strong> <span id="user_detail_aov"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>Recent Orders (Last 5)</h3>
                        <div id="user_detail_recent_orders" class="json-data-block"></div>
                    </div>
                </div>
            </div>
        </div>
        
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
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <div id="currentImage" class="file-current" style="display:none;"></div>
                    <input type="file" id="profile_image" name="profile_image" class="form-file" accept="image/*">
                    <div id="imagePreview" class="file-preview"></div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-inline">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="is_active" name="is_active" class="form-checkbox">
                            <label for="is_active">Is Active</label>
                        </div>
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="is_admin" name="is_admin" class="form-checkbox">
                            <label for="is_admin">Is Admin</label>
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
    <div id="confirmModal" class="modal-overlay" style="display: none;">
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
        import { fetchHandler, API_URL, API_USER } from '../utils.js';

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        const apiUrl = `${API_URL}?entity=users&id=${recordId}`;

        const form = document.getElementById('mainForm');
        
        const messageModal = document.getElementById('messageModal');
        const messageModalTitle = document.getElementById('messageModalTitle');
        const messageModalText = document.getElementById('messageModalText');
        const messageModalClose = document.getElementById('messageModalClose');

        const userDetailName = document.getElementById('user_detail_name');
        const userDetailEmail = document.getElementById('user_detail_email');
        const userDetailPhone = document.getElementById('user_detail_phone');
        const userDetailIsAdmin = document.getElementById('user_detail_is_admin');
        const userDetailTotalOrders = document.getElementById('user_detail_total_orders');
        const userDetailLTV = document.getElementById('user_detail_ltv');
        const userDetailAOV = document.getElementById('user_detail_aov');
        const detailSection = document.getElementById('user_detail_recent_orders');

        // Show success/error modal
        function showMessageModal(title, message, isError = false) {
            messageModalTitle.textContent = title;
            messageModalText.textContent = message;
            messageModalTitle.style.color = isError ? '#dc3545' : '#28a745';
            messageModal.style.display = 'flex';
        }

        messageModalClose.addEventListener('click', () => {
            messageModal.style.display = 'none';
        });

        const loadUserData = async () => {
            try {
                const data = await fetchHandler(apiUrl, 'GET');
                
                if (data.error) {
                    showMessageModal('Error Loading User', data.msg || 'Failed to load user data', true);
                    return;
                }

                // Populate quick view
                userDetailName.textContent = data.name || 'N/A';
                userDetailEmail.textContent = data.email || 'N/A';
                userDetailPhone.textContent = data.phone || 'N/A';
                userDetailIsAdmin.textContent = data.is_admin ? 'Yes' : 'No';
                userDetailTotalOrders.textContent = data.total_orders || 0;
                userDetailLTV.textContent = data.lifetime_value_cents || 0;
                userDetailAOV.textContent = data.avg_order_value_cents || 0;
                
                if (data.recent_orders && data.recent_orders.length > 0) {
                    detailSection.textContent = JSON.stringify(data.recent_orders, null, 2);
                } else {
                    detailSection.textContent = 'No recent orders';
                }

                // Populate form
                document.getElementById('recordId').value = data.id;
                form.name.value = data.name || '';
                form.email.value = data.email || '';
                form.phone.value = data.phone || '';
                form.is_active.checked = data.is_active;
                form.is_admin.checked = data.is_admin;

            } catch (err) {
                console.error('Error loading user:', err);
                showMessageModal('Error', 'Failed to load user data', true);
            }
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const payload = {
                action: "updateProfile",
                id: recordId,
                name: form.name.value,
                email: form.email.value,
                phone: form.phone.value,
                is_active: document.getElementById('is_active').checked,
                is_admin: document.getElementById('is_admin').checked
            };

            try {
                const result = await fetchHandler(API_USER, "POST", payload);
                
                if (result.error) {
                    showMessageModal('Update Failed', result.msg || 'Failed to update user', true);
                    return;
                }

                showMessageModal('Success!', 'User updated successfully!', false);
                await loadUserData();
                
            } catch (err) {
                console.error('Error updating user:', err);
                showMessageModal('Error', err.message || 'Failed to update user', true);
            }
        });

        // Initial load
        loadUserData().then(() => {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    </script>
</body>
</html>