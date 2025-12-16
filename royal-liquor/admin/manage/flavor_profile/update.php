<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Flavor Profile</title>
    <link rel="stylesheet" href="../../admin.css">
    <link rel="stylesheet" href="../../assets/css/products.css">
</head>
<body>
    <div class="admin-container products-edit">
        <h1>Update Flavor Profile</h1>
        
        <div id="quickViewCard" class="quick-view-card">
            <h2>👅 Profile Overview</h2>
            <div id="quickViewContent">
                <div class="modal-content">
                    <h2><span id="detail_product_name">Loading...</span></h2>
                    <div class="detail-section">
                        <h3>Scores</h3>
                        <div class="detail-field"><strong>Intensity:</strong> <span id="detail_intensity">-</span></div>
                        <div class="detail-field"><strong>Sweetness:</strong> <span id="detail_sweetness">-</span></div>
                        <div class="detail-field"><strong>Bitterness:</strong> <span id="detail_bitterness">-</span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>✏️ Edit Profile</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">
                
                <div class="form-group">
                    <label class="form-label">Product (Read-only)</label>
                    <input type="text" id="product_name_display" class="form-input" readonly disabled>
                </div>
                
                <div class="form-group">
                    <label for="name" class="form-label">Profile Name (Override)</label>
                    <input type="text" id="name" name="name" class="form-input" placeholder="e.g. Bold Selection">
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-input" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="intensity" class="form-label">Intensity (0-10)</label>
                        <input type="number" id="intensity" name="intensity" class="form-input" min="0" max="10">
                    </div>
                    <div class="form-group">
                         <label for="sweetness" class="form-label">Sweetness (0-10)</label>
                         <input type="number" id="sweetness" name="sweetness" class="form-input" min="0" max="10">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bitterness" class="form-label">Bitterness (0-10)</label>
                        <input type="number" id="bitterness" name="bitterness" class="form-input" min="0" max="10">
                    </div>
                    <div class="form-group">
                        <label for="acidity" class="form-label">Acidity (0-10)</label>
                        <input type="number" id="acidity" name="acidity" class="form-input" min="0" max="10">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="aftertaste" class="form-label">Aftertaste (0-10)</label>
                        <input type="number" id="aftertaste" name="aftertaste" class="form-input" min="0" max="10">
                    </div>
                     <div class="form-group">
                         <!-- Placeholder for alignment -->
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tags" class="form-label">Tags</label>
                    <input type="text" id="tags" name="tags" class="form-input" placeholder="e.g. Oaky, Vanilla, Smoky">
                    <small>Separate with commas</small>
                </div>

                <div class="form-group">
                    <label for="pocket_notes" class="form-label">Pocket Notes</label>
                    <input type="text" id="pocket_notes" name="pocket_notes" class="form-input" placeholder="e.g. Great with steak, Summer drink">
                    <small>Separate with commas</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                    <button type="button" id="deleteBtn" class="btn-danger">Delete Profile</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Confirm Modal -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">Confirm Delete</div>
            <div class="modal-body">Are you sure you want to delete this flavor profile? This cannot be undone.</div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-danger">Delete</button>
            </div>
        </div>
    </div>
    
    <script type="module">
        import { API_URL_FLAVOUR_PROFILES } from "../../js/pages/config.js";

        // Simple API wrapper for this page
        async function apiCall(url, method = 'GET', body = null) {
            const options = {
                method,
                headers: { 'Content-Type': 'application/json' },
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

        if (!recordId) {
            errorDiv.textContent = 'No Profile ID provided';
            errorDiv.style.display = 'block';
        }

        function formatArray(arr) {
            if (!arr) return '';
            if (Array.isArray(arr)) return arr.join(', ');
            return String(arr).replace(/[{}]/g, '').replace(/"/g, ''); // basic cleanup
        }

        async function loadData() {
            try {
                // Get profile by ID
                const response = await apiCall(`${API_URL_FLAVOUR_PROFILES}/${recordId}`);
                if (response.success === false) throw new Error(response.message);
                
                const data = response.data || response;
                
                // Quick View
                el('detail_product_name').textContent = data.product_name || `Profile #${data.id}`;
                el('detail_intensity').textContent = data.intensity ?? '-';
                el('detail_sweetness').textContent = data.sweetness ?? '-';
                el('detail_bitterness').textContent = data.bitterness ?? '-';

                // Form
                el('recordId').value = data.id;
                el('product_name_display').value = data.product_name || 'N/A';
                el('name').value = data.name || '';
                el('description').value = data.description || '';
                el('intensity').value = data.intensity ?? 5;
                el('sweetness').value = data.sweetness ?? 5;
                el('bitterness').value = data.bitterness ?? 5;
                el('acidity').value = data.acidity ?? 5;
                el('aftertaste').value = data.aftertaste ?? 5;
                el('tags').value = formatArray(data.tags);
                el('pocket_notes').value = formatArray(data.pocket_notes);

            } catch (err) {
                console.error(err);
                errorDiv.textContent = 'Error loading data: ' + err.message;
                errorDiv.style.display = 'block';
            }
        }

        el('mainForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';

            const tagsVal = el('tags').value;
            const notesVal = el('pocket_notes').value;

            const payload = {
                id: recordId,
                name: el('name').value,
                description: el('description').value,
                intensity: Number(el('intensity').value),
                sweetness: Number(el('sweetness').value),
                bitterness: Number(el('bitterness').value),
                acidity: Number(el('acidity').value),
                aftertaste: Number(el('aftertaste').value),
                tags: tagsVal ? tagsVal.split(',').map(s => s.trim()).filter(s => s) : [],
                pocket_notes: notesVal ? notesVal.split(',').map(s => s.trim()).filter(s => s) : []
            };

            try {
                const response = await apiCall(`${API_URL_FLAVOUR_PROFILES}/${recordId}`, 'PUT', payload);
                if (response.success) {
                    successDiv.textContent = '✅ Updated successfully!';
                    successDiv.style.display = 'block';
                    loadData(); // refresh
                } else {
                    throw new Error(response.message || 'Update failed');
                }
            } catch (err) {
                errorDiv.textContent = '❌ ' + err.message;
                errorDiv.style.display = 'block';
            }
        });

        // Delete
        el('deleteBtn').addEventListener('click', () => {
            confirmModal.style.display = 'flex';
        });

        el('modalCancel').addEventListener('click', () => {
            confirmModal.style.display = 'none';
        });

        el('modalConfirm').addEventListener('click', async () => {
            confirmModal.style.display = 'none';
            try {
                const response = await apiCall(`${API_URL_FLAVOUR_PROFILES}/${recordId}`, 'DELETE');
                if (response.success) {
                    successDiv.textContent = 'Profile deleted.';
                    successDiv.style.display = 'block';
                    setTimeout(() => window.location.href = '../../index.php', 1500);
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
