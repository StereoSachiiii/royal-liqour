<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Preference</title>
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div id="quickViewCard" class="quick-view-card">
            <h2>⚙️ Preference Overview</h2>
            <div class="modal-content">
                <div class="detail-section">
                    <h3>User Info</h3>
                    <div class="detail-field"><strong>User:</strong> <span id="detail_user"></span></div>
                    <div class="detail-field"><strong>Email:</strong> <span id="detail_email"></span></div>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>✏️ Edit Flavor Profile</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">
                <input type="hidden" id="user_id" name="user_id">

                <div class="form-row">
                    <div class="form-group">
                        <label for="preferred_sweetness" class="form-label">Sweetness (0-10)</label>
                        <input type="number" id="preferred_sweetness" name="preferred_sweetness" class="form-input" min="0" max="10">
                    </div>
                    <div class="form-group">
                        <label for="preferred_bitterness" class="form-label">Bitterness (0-10)</label>
                        <input type="number" id="preferred_bitterness" name="preferred_bitterness" class="form-input" min="0" max="10">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="preferred_strength" class="form-label">Strength (0-10)</label>
                        <input type="number" id="preferred_strength" name="preferred_strength" class="form-input" min="0" max="10">
                    </div>
                    <div class="form-group">
                        <label for="preferred_smokiness" class="form-label">Smokiness (0-10)</label>
                        <input type="number" id="preferred_smokiness" name="preferred_smokiness" class="form-input" min="0" max="10">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="preferred_fruitiness" class="form-label">Fruitiness (0-10)</label>
                        <input type="number" id="preferred_fruitiness" name="preferred_fruitiness" class="form-input" min="0" max="10">
                    </div>
                    <div class="form-group">
                        <label for="preferred_spiciness" class="form-label">Spiciness (0-10)</label>
                        <input type="number" id="preferred_spiciness" name="preferred_spiciness" class="form-input" min="0" max="10">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <button type="button" onclick="window.parent.postMessage('closeModal', '*')" class="btn-secondary">Cancel</button>
                    <button type="button" id="deleteBtn" class="btn-danger" style="margin-left: auto;">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">Confirm Delete</div>
            <div class="modal-body">Are you sure you want to delete this user preference record? This action cannot be undone.</div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-danger">Delete</button>
            </div>
        </div>
    </div>

    <script type="module">
        import { API_URL_USER_PREFERENCES } from "../../js/pages/config.js";

        // Helper since we can't import apiRequest easily inside iframe without path issues
        async function apiCall(url, method = 'GET', body = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
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
                const response = await apiCall(`${API_URL_USER_PREFERENCES}?id=${recordId}`);
                
                if (!response.success || !response.data) {
                    throw new Error(response.message || 'Failed to load data');
                }

                const data = response.data;

                // Quick View
                el('detail_user').textContent = data.user_name || '-';
                el('detail_email').textContent = data.user_email || '-';

                // Form
                el('recordId').value = data.id;
                el('user_id').value = data.user_id;

                el('preferred_sweetness').value = data.preferred_sweetness ?? '';
                el('preferred_bitterness').value = data.preferred_bitterness ?? '';
                el('preferred_strength').value = data.preferred_strength ?? '';
                el('preferred_smokiness').value = data.preferred_smokiness ?? '';
                el('preferred_fruitiness').value = data.preferred_fruitiness ?? '';
                el('preferred_spiciness').value = data.preferred_spiciness ?? '';

            } catch (err) {
                errorDiv.textContent = 'Error: ' + err.message;
                errorDiv.style.display = 'block';
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';

            // Gather integer values, or null if empty
            const getInt = (id) => {
                const val = el(id).value;
                return val === '' ? null : Number(val);
            };

            const payload = {
                id: recordId,
                user_id: Number(el('user_id').value), // Required usually
                preferred_sweetness: getInt('preferred_sweetness'),
                preferred_bitterness: getInt('preferred_bitterness'),
                preferred_strength: getInt('preferred_strength'),
                preferred_smokiness: getInt('preferred_smokiness'),
                preferred_fruitiness: getInt('preferred_fruitiness'),
                preferred_spiciness: getInt('preferred_spiciness')
            };

            try {
                // Determine repository update method.
                // The current API might map PUT to just updating fields.
                // We'll send payload.
                const response = await apiCall(`${API_URL_USER_PREFERENCES}?id=${recordId}`, 'PUT', payload);
                
                if (response.success) {
                    successDiv.textContent = '✅ Preferences updated successfully!';
                    successDiv.style.display = 'block';
                    loadData(); // Reload to refresh
                } else {
                    throw new Error(response.message || 'Update failed');
                }
            } catch (err) {
                errorDiv.textContent = '❌ ' + err.message;
                errorDiv.style.display = 'block';
            }
        });

        // Delete handling
        deleteBtn.addEventListener('click', () => confirmModal.style.display = 'flex');
        el('modalCancel').addEventListener('click', () => confirmModal.style.display = 'none');
        
        el('modalConfirm').addEventListener('click', async () => {
            confirmModal.style.display = 'none';
            try {
                const response = await apiCall(`${API_URL_USER_PREFERENCES}?id=${recordId}`, 'DELETE');
                if (response.success) {
                    successDiv.textContent = '✅ Record deleted. Closing...';
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