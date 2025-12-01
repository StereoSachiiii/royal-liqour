<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Flavor Profile</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update Flavor Profile</h1>
        
        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>👅 Flavor Profile Overview</h2>
            <div id="quickViewContent">
                <div class="modal-content">
                    <h2>⚙️ Profile Detail</h2>
                    <div class="detail-section">
                        <h3>Metrics</h3>
                        <div class="detail-field"><strong>Sweetness:</strong> <span id="detail_sweetness"></span></div>
                        <div class="detail-field"><strong>Bitterness:</strong> <span id="detail_bitterness"></span></div>
                        <div class="detail-field"><strong>Strength:</strong> <span id="detail_strength"></span></div>
                        <div class="detail-field"><strong>Smokiness:</strong> <span id="detail_smokiness"></span></div>
                        <div class="detail-field"><strong>Fruitiness:</strong> <span id="detail_fruitiness"></span></div>
                        <div class="detail-field"><strong>Spiciness:</strong> <span id="detail_spiciness"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>Tags</h3>
                        <div class="detail-field"><span id="detail_tags"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>Product Info</h3>
                        <div class="detail-field"><strong>Product Name:</strong> <span id="detail_product_name"></span></div>
                        <div class="detail-field"><strong>Product Slug:</strong> <span id="detail_product_slug"></span></div>
                        <div class="detail-field"><strong>Avg Rating:</strong> <span id="detail_avg_rating"></span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>✏️ Edit Flavor Profile</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="product_id">
                
                <div class="form-group">
                    <label for="product_display" class="form-label required">Product</label>
                    <input type="text" id="product_display" class="form-input" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                </div>
                
                <div class="form-group">
                    <label for="sweetness" class="form-label">Sweetness (0-10)</label>
                    <input type="number" id="sweetness" name="sweetness" class="form-input" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="bitterness" class="form-label">Bitterness (0-10)</label>
                    <input type="number" id="bitterness" name="bitterness" class="form-input" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="strength" class="form-label">Strength (0-10)</label>
                    <input type="number" id="strength" name="strength" class="form-input" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="smokiness" class="form-label">Smokiness (0-10)</label>
                    <input type="number" id="smokiness" name="smokiness" class="form-input" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="fruitiness" class="form-label">Fruitiness (0-10)</label>
                    <input type="number" id="fruitiness" name="fruitiness" class="form-input" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="spiciness" class="form-label">Spiciness (0-10)</label>
                    <input type="number" id="spiciness" name="spiciness" class="form-input" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="tags" class="form-label">Tags</label>
                    <input type="text" id="tags" name="tags" class="form-input" placeholder="e.g. Oaky, Vanilla">
                    <small>Separate tags with commas</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                    <button type="button" id="hardDeleteBtn" class="btn-danger">Delete Profile</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- MODAL -->
    <div id="confirmModal" class="modal-overlay">
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
        
        const API_URL = 'http://localhost/royal-liquor/admin/api/admin-views.php';
        const API_FLAVOR_PROFILE = 'http://localhost/royal-liquor/admin/api/flavor-profile.php';

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        const viewUrl = `${API_URL}?entity=flavor_profiles&product_id=${recordId}`;
        const updateUrl = `${API_FLAVOR_PROFILE}`;

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');

        // Detail elements
        const detailSweetness = document.getElementById('detail_sweetness');
        const detailBitterness = document.getElementById('detail_bitterness');
        const detailStrength = document.getElementById('detail_strength');
        const detailSmokiness = document.getElementById('detail_smokiness');
        const detailFruitiness = document.getElementById('detail_fruitiness');
        const detailSpiciness = document.getElementById('detail_spiciness');
        const detailTags = document.getElementById('detail_tags');
        const detailProductName = document.getElementById('detail_product_name');
        const detailProductSlug = document.getElementById('detail_product_slug');
        const detailAvgRating = document.getElementById('detail_avg_rating');

        // Form elements
        const productDisplayElement = document.getElementById('product_display');
        const sweetnessElement = document.getElementById('sweetness');
        const bitternessElement = document.getElementById('bitterness');
        const strengthElement = document.getElementById('strength');
        const smokinessElement = document.getElementById('smokiness');
        const fruitinessElement = document.getElementById('fruitiness');
        const spicinessElement = document.getElementById('spiciness');
        const tagsElement = document.getElementById('tags');

        let currentAction = null;
        const confirmModal = document.getElementById('confirmModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalCancel = document.getElementById('modalCancel');
        const modalConfirm = document.getElementById('modalConfirm');
        const hardDeleteBtn = document.getElementById('hardDeleteBtn');

        async function directFetch(url, method = 'GET', body = null) {
            const options = {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
            };
            if (body && method !== 'GET') {
                options.body = JSON.stringify(body);
            }

            const response = await fetch(url, options);

            if (!response.ok) {
                const errorText = await response.text();
                try {
                    const errorJson = JSON.parse(errorText);
                    throw new Error(errorJson.message || `API error (${response.status})`);
                } catch {
                    throw new Error(`Request failed with status ${response.status}: ${errorText}`);
                }
            }
            return response.json();
        }

        function formatArrayDisplay(arr) {
            if (!arr) return 'None';
            if (typeof arr === 'string') {
                const cleaned = arr.trim().replace(/[{}]/g, '');
                if (!cleaned) return 'None';
                return cleaned.split(',').map(item => item.replace(/"/g, '').trim()).join(', ');
            }
            if (Array.isArray(arr)) {
                return arr.join(', ');
            }
            return 'None';
        }

        function formatArrayInput(arr) {
            if (!arr) return '';
            if (typeof arr === 'string') {
                const cleaned = arr.trim().replace(/[{}]/g, '');
                return cleaned.split(',').map(item => item.replace(/"/g, '').trim()).join(', ');
            }
            if (Array.isArray(arr)) {
                return arr.join(', ');
            }
            return '';
        }

        if (hardDeleteBtn) {
            hardDeleteBtn.addEventListener('click', () => {
                currentAction = 'hard_delete';
                modalTitle.textContent = 'Confirm Delete';
                modalMessage.textContent = 'Are you sure? This will permanently delete the flavor profile for this product.';
                confirmModal.style.display = 'flex';
            });
        }

        modalCancel.addEventListener('click', () => {
            confirmModal.style.display = 'none';
            currentAction = null;
        });

        modalConfirm.addEventListener('click', async () => {
            confirmModal.style.display = 'none';
            try {
                const deleteUrl = `${API_FLAVOR_PROFILE}?product_id=${recordId}`;
                const response = await directFetch(deleteUrl, 'DELETE');
                
                successDiv.textContent = 'Flavor Profile deleted successfully!';
                successDiv.style.display = 'block';
                setTimeout(() => { window.location.href = '../../index.php'; }, 2000);
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to delete profile';
                errorDiv.style.display = 'block';
            }
            currentAction = null;
        });

        const loadProfileData = async () => {
            try {
                const response = await directFetch(viewUrl, 'GET');
                
        
                let data = response.data?.data || response.data?.items?.[0] || response;
                
                if (Array.isArray(data)) {
                    data = data[0];
                }
                
                if (!data || !data.product_id) throw new Error('Profile data structure invalid or not found.');
             
                detailSweetness.textContent = data.sweetness ?? '-';
                detailBitterness.textContent = data.bitterness ?? '-';
                detailStrength.textContent = data.strength ?? '-';
                detailSmokiness.textContent = data.smokiness ?? '-';
                detailFruitiness.textContent = data.fruitiness ?? '-';
                detailSpiciness.textContent = data.spiciness ?? '-';
                detailTags.textContent = formatArrayDisplay(data.tags);
                detailProductName.textContent = data.product_name || `ID: ${data.product_id}`;
                detailProductSlug.textContent = data.product_slug || '-';
                detailAvgRating.textContent = data.avg_rating ? Number(data.avg_rating).toFixed(1) : 'N/A';

                // Update Form
                document.getElementById('recordId').value = data.product_id;
                productDisplayElement.value = `${data.product_name} (ID: ${data.product_id})`;
                
                sweetnessElement.value = data.sweetness ?? 5;
                bitternessElement.value = data.bitterness ?? 5;
                strengthElement.value = data.strength ?? 5;
                smokinessElement.value = data.smokiness ?? 5;
                fruitinessElement.value = data.fruitiness ?? 5;
                spicinessElement.value = data.spiciness ?? 5;
                tagsElement.value = formatArrayInput(data.tags);

            } catch (err) {
                console.error(err);
                errorDiv.textContent = 'Failed to load flavor profile data: ' + err.message;
                errorDiv.style.display = 'block';
            }
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
            successDiv.textContent = '';
            successDiv.style.display = 'none';

            const tagsValue = tagsElement.value.trim();
            const tagsArray = tagsValue ? tagsValue.split(',').map(t => t.trim()).filter(t => t) : [];

            const payload = {
                product_id: recordId, 
                sweetness: sweetnessElement.value ? parseInt(sweetnessElement.value) : null,
                bitterness: bitternessElement.value ? parseInt(bitternessElement.value) : null,
                strength: strengthElement.value ? parseInt(strengthElement.value) : null,
                smokiness: smokinessElement.value ? parseInt(smokinessElement.value) : null,
                fruitiness: fruitinessElement.value ? parseInt(fruitinessElement.value) : null,
                spiciness: spicinessElement.value ? parseInt(spicinessElement.value) : null,
                tags: tagsArray
            };

            try {
                const putUrl = `${updateUrl}?product_id=${recordId}`;
                const result = await directFetch(putUrl, "PUT", payload);
                
                if (result.success === true) {
                    successDiv.textContent = 'Flavor Profile updated successfully!';
                    successDiv.style.display = 'block';
                    await loadProfileData();
                } else {
                    throw new Error(result.message || 'Failed to update profile');
                }
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to update profile';
                errorDiv.style.display = 'block';
            }
        });

        loadProfileData().then(() => {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    </script>
</body>
</html>