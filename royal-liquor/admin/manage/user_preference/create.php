<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User Preference</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create User Preference</h1>
        
        <!-- CREATE CONFIRMATION MODAL -->
        <div id="confirmation_modal" class="confirmation_overlay">
            <div class="confirmation_box">
                <h2>User Preference Created</h2>
                <p id="confirmation_message"></p>
                <div class="confirmation_actions">
                    <button id="confirmation_close" class="confirmation_btn">Close</button>
                    <a id="confirmation_view" class="confirmation_btn" href="#">View User Preference</a>
                </div>
            </div>
        </div>
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>➕ New User Preference</h2>
            <form id="mainForm">
                
                <div class="form-group">
                    <label for="user_id" class="form-label required">User</label>
                    <select id="user_id" name="user_id" class="form-input" required>
                        <option value="">Loading users...</option>
                    </select>
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
                    <button type="submit" class="btn-primary">Create User Preference</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script type="module">
        import { fetchHandler, API_USER, API_CATEGORY, API_USER_PREFERENCE } from '../utils.js';
        
        const user_idElement = document.getElementById('user_id');
        const preferred_sweetnessElement = document.getElementById('preferred_sweetness');
        const preferred_bitternessElement = document.getElementById('preferred_bitterness');
        const preferred_strengthElement = document.getElementById('preferred_strength');
        const preferred_smokinessElement = document.getElementById('preferred_smokiness');
        const preferred_fruitinessElement = document.getElementById('preferred_fruitiness');
        const preferred_spicinessElement = document.getElementById('preferred_spiciness');
        const favorite_categoriesElement = document.getElementById('favorite_categories');

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');   
        const modal = document.getElementById('confirmation_modal');
        const modalMsg = document.getElementById('confirmation_message');
        const modalClose = document.getElementById('confirmation_close');
        const modalView = document.getElementById('confirmation_view');

        // Load users dropdown
        async function loadUsers() {
            try {
                const users = await fetchHandler(`${API_USER}`, 'GET');
                user_idElement.innerHTML = '<option value="">Select a user...</option>';
                users?.items.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = `${user.name} (${user.email})`;
                    user_idElement.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading users:', error);
                user_idElement.innerHTML = '<option value="">Error loading users</option>';
            }
        }

        // Load categories dropdown
        async function loadCategories() {
            try {
                const categories = await fetchHandler(`${API_CATEGORY}`, 'GET');
                favorite_categoriesElement.innerHTML = '';
                categories?.items.forEach(category => {
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

        function showConfirmationModal(user_preference) {
            modalMsg.textContent = `User Preference "${user_preference.name || user_preference.id}" was created successfully!`;
            modalView.href = `update.html?id=${user_preference.id}`;
            modal.style.display = 'flex';
        }

        modalClose.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
            successDiv.style.display = 'none';
            successDiv.textContent = '';
            const selectedCategories = Array.from(favorite_categoriesElement.selectedOptions).map(opt => parseInt(opt.value));
            const body = {
                user_id: parseInt(user_idElement.value),
                preferred_sweetness: preferred_sweetnessElement.value ? parseInt(preferred_sweetnessElement.value) : null,
                preferred_bitterness: preferred_bitternessElement.value ? parseInt(preferred_bitternessElement.value) : null,
                preferred_strength: preferred_strengthElement.value ? parseInt(preferred_strengthElement.value) : null,
                preferred_smokiness: preferred_smokinessElement.value ? parseInt(preferred_smokinessElement.value) : null,
                preferred_fruitiness: preferred_fruitinessElement.value ? parseInt(preferred_fruitinessElement.value) : null,
                preferred_spiciness: preferred_spicinessElement.value ? parseInt(preferred_spicinessElement.value) : null,
                favorite_categories: selectedCategories.length > 0 ? selectedCategories : null
            };
            try {
                const apiUrl = `${API_USER_PREFERENCE}`;
                const response = await fetchHandler(apiUrl, 'POST', body);
                console.log(response);
                if (!response.error) {
                    showConfirmationModal(response);
                    form.reset();
                } else {
                    throw new Error(response.msg || 'Failed to create user preference');
                }
                
            } catch (error) {
                console.error('Error creating user preference:', error);
                errorDiv.textContent = error.message || 'An error occurred while creating the user preference.';
                errorDiv.style.display = 'block';
            }
        });

        
        loadUsers();
        loadCategories();
    </script>
</body>
</html>