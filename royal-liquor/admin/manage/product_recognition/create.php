<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product Recognition</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Product Recognition</h1>
        
        <!-- CREATE CONFIRMATION MODAL -->
        <div id="confirmation_modal" class="confirmation_overlay">
            <div class="confirmation_box">
                <h2>Product Recognition Created</h2>
                <p id="confirmation_message"></p>
                <div class="confirmation_actions">
                    <button id="confirmation_close" class="confirmation_btn">Close</button>
                    <a id="confirmation_view" class="confirmation_btn" href="#">View Product Recognition</a>
                </div>
            </div>
        </div>
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>➕ New Product Recognition</h2>
            <form id="mainForm">
                
                <div class="form-group">
                    <label for="user_id" class="form-label">User</label>
                    <input 
                        type="text" 
                        id="user_id_search" 
                        name="user_id_search" 
                        class="form-input" 
                        list="users_list"
                        placeholder="Search and select a user...">
                    <datalist id="users_list"></datalist>
                    <input type="hidden" id="user_id" name="user_id">
                </div>
                
                <div class="form-group">
                    <label for="session_id" class="form-label required">Session ID</label>
                    <input type="text" id="session_id" name="session_id" class="form-input" required placeholder="Enter session ID">
                </div>
                
                <div class="form-group">
                    <label for="image_url" class="form-label required">Image URL</label>
                    <input type="url" id="image_url" name="image_url" class="form-input" required placeholder="https://example.com/image.jpg">
                </div>
                
                <div class="form-group">
                    <label for="recognized_text" class="form-label">Recognized Text</label>
                    <textarea id="recognized_text" name="recognized_text" class="form-input" rows="4" placeholder="Text extracted from the image"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="recognized_labels" class="form-label">Recognized Labels</label>
                    <input type="text" id="recognized_labels" name="recognized_labels" class="form-input" placeholder="bottle, whiskey, johnnie walker">
                    <small>Comma separated labels</small>
                </div>
                
                <div class="form-group">
                    <label for="matched_product_id" class="form-label">Matched Product</label>
                    <input 
                        type="text" 
                        id="matched_product_search" 
                        name="matched_product_search" 
                        class="form-input" 
                        list="products_list"
                        placeholder="Search and select a product...">
                    <datalist id="products_list"></datalist>
                    <input type="hidden" id="matched_product_id" name="matched_product_id">
                </div>
                
                <div class="form-group">
                    <label for="confidence_score" class="form-label">Confidence Score</label>
                    <input type="number" id="confidence_score" name="confidence_score" class="form-input" placeholder="0-100" step="0.01" min="0" max="100">
                    <small>Percentage (0-100)</small>
                </div>
                
                <div class="form-group">
                    <label for="api_provider" class="form-label">API Provider</label>
                    <input 
                        type="text" 
                        id="api_provider" 
                        name="api_provider" 
                        class="form-input" 
                        list="providers_list"
                        placeholder="Select or enter provider...">
                    <datalist id="providers_list">
                        <option value="Google Vision">
                        <option value="AWS Rekognition">
                        <option value="Azure Computer Vision">
                        <option value="OpenAI Vision">
                        <option value="Custom">
                    </datalist>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Product Recognition</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script type="module">
        import { fetchHandler, API_PRODUCT_RECOGNITION, API_USER, API_PRODUCT } from '../utils.js';
        
        const user_id_searchElement = document.getElementById('user_id_search');
        const user_idElement = document.getElementById('user_id');
        const users_listElement = document.getElementById('users_list');
        
        const matched_product_searchElement = document.getElementById('matched_product_search');
        const matched_product_idElement = document.getElementById('matched_product_id');
        const products_listElement = document.getElementById('products_list');
        
        const session_idElement = document.getElementById('session_id');
        const image_urlElement = document.getElementById('image_url');
        const recognized_textElement = document.getElementById('recognized_text');
        const recognized_labelsElement = document.getElementById('recognized_labels');
        const confidence_scoreElement = document.getElementById('confidence_score');
        const api_providerElement = document.getElementById('api_provider');

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');   
        const modal = document.getElementById('confirmation_modal');
        const modalMsg = document.getElementById('confirmation_message');
        const modalClose = document.getElementById('confirmation_close');
        const modalView = document.getElementById('confirmation_view');

        let usersMap = new Map();
        let productsMap = new Map();

        // Load users datalist
        async function loadUsers() {
            try {
                const response = await fetchHandler(`${API_USER}`, 'GET');
                const users = response?.items || [];
                
                users_listElement.innerHTML = '';
                users.forEach(user => {
                    const option = document.createElement('option');
                    const displayText = `${user.name} (${user.email})`;
                    option.value = displayText;
                    option.dataset.id = user.id;
                    users_listElement.appendChild(option);
                    usersMap.set(displayText, user.id);
                });
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        // Load products datalist
        async function loadProducts() {
            try {
                const response = await fetchHandler(`${API_PRODUCT}`, 'GET');
                const products = response?.items || [];
                
                products_listElement.innerHTML = '';
                products.forEach(product => {
                    const option = document.createElement('option');
                    const displayText = `${product.name} (ID: ${product.id})`;
                    option.value = displayText;
                    option.dataset.id = product.id;
                    products_listElement.appendChild(option);
                    productsMap.set(displayText, product.id);
                });
            } catch (error) {
                console.error('Error loading products:', error);
            }
        }

        // Handle user selection
        user_id_searchElement.addEventListener('input', (e) => {
            const selectedValue = e.target.value;
            if (usersMap.has(selectedValue)) {
                user_idElement.value = usersMap.get(selectedValue);
            } else {
                user_idElement.value = '';
            }
        });

        // Handle product selection
        matched_product_searchElement.addEventListener('input', (e) => {
            const selectedValue = e.target.value;
            if (productsMap.has(selectedValue)) {
                matched_product_idElement.value = productsMap.get(selectedValue);
            } else {
                matched_product_idElement.value = '';
            }
        });

        function showConfirmationModal(product_recognition) {
            modalMsg.textContent = `Product Recognition #${product_recognition.id} was created successfully!`;
            modalView.href = `update.php?id=${product_recognition.id}`;
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

            const body = {
                user_id: user_idElement.value || null,
                session_id: session_idElement.value,
                image_url: image_urlElement.value,
                recognized_text: recognized_textElement.value || null,
                recognized_labels: recognized_labelsElement.value ? 
                    recognized_labelsElement.value.split(',').map(t => t.trim()).filter(Boolean) : [],
                matched_product_id: matched_product_idElement.value || null,
                confidence_score: confidence_scoreElement.value || null,
                api_provider: api_providerElement.value || null,
            };

            try {
                
                
                const response = await fetchHandler(API_PRODUCT_RECOGNITION, 'POST', body);
                
                if (response && response.success && response.data) {
                    showConfirmationModal(response.data);
                    form.reset();
                    user_idElement.value = '';
                    matched_product_idElement.value = '';
                } else {
                    throw new Error(response.message || 'Failed to create product recognition');
                }
                
            } catch (error) {
                console.error('Error creating product recognition:', error);
                errorDiv.textContent = error.message || 'An error occurred while creating the product recognition.';
                errorDiv.style.display = 'block';
            }
        });

        // Load dropdowns on page load
        Promise.all([loadUsers(), loadProducts()]);
    </script>
</body>
</html>