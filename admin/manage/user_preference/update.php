<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User Preference</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update User Preference</h1>
        
        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>📊 User Preference Overview</h2>
            <div id="quickViewContent">
                <div class="modal-content">
                    <h2>⚙️ User Preference Detail</h2>
                    <div class="detail-section">
                        <h3>Preferences</h3>
                        <div class="detail-field"><strong>Preferred Sweetness:</strong> <span id="user_preference_detail_preferred_sweetness"></span></div>
                        <div class="detail-field"><strong>Preferred Bitterness:</strong> <span id="user_preference_detail_preferred_bitterness"></span></div>
                        <div class="detail-field"><strong>Preferred Strength:</strong> <span id="user_preference_detail_preferred_strength"></span></div>
                        <div class="detail-field"><strong>Preferred Smokiness:</strong> <span id="user_preference_detail_preferred_smokiness"></span></div>
                        <div class="detail-field"><strong>Preferred Fruitiness:</strong> <span id="user_preference_detail_preferred_fruitiness"></span></div>
                        <div class="detail-field"><strong>Preferred Spiciness:</strong> <span id="user_preference_detail_preferred_spiciness"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>Favorites</h3>
                        <div class="detail-field"><strong>Favorite Categories:</strong> <span id="user_preference_detail_favorite_categories"></span></div>
                        <div class="detail-field"><strong>Favorite Category Names:</strong> <span id="user_preference_detail_favorite_category_names"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>User Info</h3>
                        <div class="detail-field"><strong>User Name:</strong> <span id="user_preference_detail_user_name"></span></div>
                        <div class="detail-field"><strong>User Email:</strong> <span id="user_preference_detail_user_email"></span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>✏️ Edit User Preference</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">
                
                <div class="form-group">
                    <label for="user_id" class="form-label required">User</label>
                    <input type="text" id="user_id_display" class="form-input" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    <input type="hidden" id="user_id" name="user_id">
                </div>
                
                <div class="form-group">
                    <label for="preferred_sweetness" class="form-label">Preferred Sweetness</label>
                    <input type="number" id="preferred_sweetness" name="preferred_sweetness" class="form-input" placeholder="0-10" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="preferred_bitterness" class="form-label">Preferred Bitterness</label>
                    <input type="number" id="preferred_bitterness" name="preferred_bitterness" class="form-input" placeholder="0-10" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="preferred_strength" class="form-label">Preferred Strength</label>
                    <input type="number" id="preferred_strength" name="preferred_strength" class="form-input" placeholder="0-10" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="preferred_smokiness" class="form-label">Preferred Smokiness</label>
                    <input type="number" id="preferred_smokiness" name="preferred_smokiness" class="form-input" placeholder="0-10" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="preferred_fruitiness" class="form-label">Preferred Fruitiness</label>
                    <input type="number" id="preferred_fruitiness" name="preferred_fruitiness" class="form-input" placeholder="0-10" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="preferred_spiciness" class="form-label">Preferred Spiciness</label>
                    <input type="number" id="preferred_spiciness" name="preferred_spiciness" class="form-input" placeholder="0-10" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label for="favorite_categories" class="form-label">Favorite Categories</label>
                    <select id="favorite_categories" name="favorite_categories" class="form-input" multiple size="5">
                        <option value="">Loading categories...</option>
                    </select>
                    <small>Hold Ctrl/Cmd to select multiple</small>
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
        import { fetchHandler, API_URL, API_USER_PREFERENCE } from '../utils.js';

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        const apiUrl = `${API_URL}?entity=user_preferences&id=${recordId}`;
        const updateUrl = `${API_USER_PREFERENCE}`;

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');

        const user_preferenceDetailPreferredsweetness = document.getElementById('user_preference_detail_preferred_sweetness');
        const user_preferenceDetailPreferredbitterness = document.getElementById('user_preference_detail_preferred_bitterness');
        const user_preferenceDetailPreferredstrength = document.getElementById('user_preference_detail_preferred_strength');
        const user_preferenceDetailPreferredsmokiness = document.getElementById('user_preference_detail_preferred_smokiness');
        const user_preferenceDetailPreferredfruitiness = document.getElementById('user_preference_detail_preferred_fruitiness');
        const user_preferenceDetailPreferredspiciness = document.getElementById('user_preference_detail_preferred_spiciness');
        const user_preferenceDetailFavoritecategories = document.getElementById('user_preference_detail_favorite_categories');
        const user_preferenceDetailFavoritecategorynames = document.getElementById('user_preference_detail_favorite_category_names');
        const user_preferenceDetailUsername = document.getElementById('user_preference_detail_user_name');
        const user_preferenceDetailUseremail = document.getElementById('user_preference_detail_user_email');

        const user_idElement = document.getElementById('user_id');
        const user_idDisplayElement = document.getElementById('user_id_display');
        const preferred_sweetnessElement = document.getElementById('preferred_sweetness');
        const preferred_bitternessElement = document.getElementById('preferred_bitterness');
        const preferred_strengthElement = document.getElementById('preferred_strength');
        const preferred_smokinessElement = document.getElementById('preferred_smokiness');
        const preferred_fruitinessElement = document.getElementById('preferred_fruitiness');
        const preferred_spicinessElement = document.getElementById('preferred_spiciness');
        const favorite_categoriesElement = document.getElementById('favorite_categories');

        let currentAction = null;
        const confirmModal = document.getElementById('confirmModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalCancel = document.getElementById('modalCancel');
        const modalConfirm = document.getElementById('modalConfirm');
        const softDeleteBtn = document.getElementById('softDeleteBtn');
        const hardDeleteBtn = document.getElementById('hardDeleteBtn');

        // Helper function to format arrays
        function formatArrayDisplay(arr) {
            if (!arr) return 'None';
            
            // Handle PostgreSQL array string format {1,3,5,7}
            if (typeof arr === 'string') {
                const cleaned = arr.trim().replace(/[{}]/g, '');
                if (!cleaned) return 'None';
                arr = cleaned.split(',').map(item => item.trim());
            }
            
            if (arr.length === 0) return 'None';
            return arr.join(', ');
        }

        // Load categories dropdown
        async function loadCategories() {
            try {
                const categories = await fetchHandler(`${API_URL}?entity=categories`, 'GET');
                favorite_categoriesElement.innerHTML = '';
                categories?.items?.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    favorite_categoriesElement.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading categories:', error);
                favorite_categoriesElement.innerHTML = '<option value="">Error loading categories</option>';
            }
        }

        if (softDeleteBtn) {
            softDeleteBtn.addEventListener('click', () => {
                currentAction = 'soft_delete';
                modalTitle.textContent = 'Confirm Soft Delete';
                modalMessage.textContent = 'Are you sure? This will deactivate the record.';
                confirmModal.style.display = 'flex';
            });
        }

        if (hardDeleteBtn) {
            hardDeleteBtn.addEventListener('click', () => {
                currentAction = 'hard_delete';
                modalTitle.textContent = 'Confirm Hard Delete';
                modalMessage.textContent = 'Are you sure? This will permanently delete the record.';
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
                const deleteUrl = `${API_USER_PREFERENCE}`;
                const response = await fetchHandler(deleteUrl, 'DELETE', {id: recordId});
                successDiv.textContent = 'User Preference deleted successfully!';
                successDiv.style.display = 'block';
                setTimeout(() => { window.location.href = '../../index.php'; }, 2000);
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to delete user preference';
                errorDiv.style.display = 'block';
            }
            currentAction = null;
        });

        const loadUserPreferenceData = async () => {
            const response = await fetchHandler(apiUrl, 'GET');
            const data = response.data || response;

            user_preferenceDetailPreferredsweetness.textContent = data.preferred_sweetness ?? 'Not set';
            user_preferenceDetailPreferredbitterness.textContent = data.preferred_bitterness ?? 'Not set';
            user_preferenceDetailPreferredstrength.textContent = data.preferred_strength ?? 'Not set';
            user_preferenceDetailPreferredsmokiness.textContent = data.preferred_smokiness ?? 'Not set';
            user_preferenceDetailPreferredfruitiness.textContent = data.preferred_fruitiness ?? 'Not set';
            user_preferenceDetailPreferredspiciness.textContent = data.preferred_spiciness ?? 'Not set';
            user_preferenceDetailFavoritecategories.textContent = formatArrayDisplay(data.favorite_categories);
            user_preferenceDetailFavoritecategorynames.textContent = formatArrayDisplay(data.favorite_category_names);
            user_preferenceDetailUsername.textContent = data.user_name;
            user_preferenceDetailUseremail.textContent = data.user_email;

            document.getElementById('recordId').value = data.id;
            
            // Set user_id as immutable (hidden field + readonly display)
            user_idElement.value = data.user_id;
            user_idDisplayElement.value = `${data.user_name} (${data.user_email})`;
            
            form.preferred_sweetness.value = data.preferred_sweetness ?? '';
            form.preferred_bitterness.value = data.preferred_bitterness ?? '';
            form.preferred_strength.value = data.preferred_strength ?? '';
            form.preferred_smokiness.value = data.preferred_smokiness ?? '';
            form.preferred_fruitiness.value = data.preferred_fruitiness ?? '';
            form.preferred_spiciness.value = data.preferred_spiciness ?? '';

            // Select the favorite categories
            if (data.favorite_categories?.length > 0) {
                Array.from(favorite_categoriesElement.options).forEach(option => {
                    option.selected = data.favorite_categories?.includes(parseInt(option.value));
                });
            }
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
            successDiv.textContent = '';
            successDiv.style.display = 'none';

            const selectedCategories = Array.from(favorite_categoriesElement.selectedOptions).map(opt => parseInt(opt.value));

            const payload = {
                id: recordId,
                user_id: parseInt(user_idElement.value),
                preferred_sweetness: preferred_sweetnessElement.value ? parseInt(preferred_sweetnessElement.value) : null,
                preferred_bitterness: preferred_bitternessElement.value ? parseInt(preferred_bitternessElement.value) : null,
                preferred_strength: preferred_strengthElement.value ? parseInt(preferred_strengthElement.value) : null,
                preferred_smokiness: preferred_smokinessElement.value ? parseInt(preferred_smokinessElement.value) : null,
                preferred_fruitiness: preferred_fruitinessElement.value ? parseInt(preferred_fruitinessElement.value) : null,
                preferred_spiciness: preferred_spicinessElement.value ? parseInt(preferred_spicinessElement.value) : null,
                favorite_categories: selectedCategories?.length > 0 ? selectedCategories : null
            };

            try {
                const result = await fetchHandler(updateUrl, "PUT", payload);
                if (!result.error) {
                    successDiv.textContent = 'User Preference updated successfully!';
                    successDiv.style.display = 'block';
                    await loadUserPreferenceData();
                } else {
                    throw new Error(result.msg || 'Failed to update user preference');
                }
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to update user preference';
                errorDiv.style.display = 'block';
            }
        });

      
        loadCategories().then(() => {
            loadUserPreferenceData().then(() => {
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</body>
</html>