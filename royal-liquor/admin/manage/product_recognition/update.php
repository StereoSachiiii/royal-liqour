<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product Recognition</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update Product Recognition</h1>
        
        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>📊 Product Recognition Overview</h2>
            <div id="quickViewContent">
                <div class="modal-content">
                    <h2>🔍 Product Recognition Detail</h2>
                    <div class="detail-section">
                        <h3>Basic Info</h3>
                        <div class="detail-field"><strong>Session ID:</strong> <span id="product_recognition_detail_session_id"></span></div>
                        <div class="detail-field"><strong>Image URL:</strong> <span id="product_recognition_detail_image_url"></span></div>
                        <div class="detail-field"><strong>Confidence Score:</strong> <span id="product_recognition_detail_confidence_score"></span></div>
                        <div class="detail-field"><strong>API Provider:</strong> <span id="product_recognition_detail_api_provider"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>Recognition</h3>
                        <div class="detail-field"><strong>Recognized Text:</strong> <span id="product_recognition_detail_recognized_text"></span></div>
                        <div class="detail-field"><strong>Recognized Labels:</strong> <span id="product_recognition_detail_recognized_labels"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>Matched Product</h3>
                        <div class="detail-field"><strong>Matched Product Name:</strong> <span id="product_recognition_detail_matched_product_name"></span></div>
                        <div class="detail-field"><strong>Matched Product Slug:</strong> <span id="product_recognition_detail_matched_product_slug"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>User Info</h3>
                        <div class="detail-field"><strong>User Name:</strong> <span id="product_recognition_detail_user_name"></span></div>
                        <div class="detail-field"><strong>User Email:</strong> <span id="product_recognition_detail_user_email"></span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>✏️ Edit Product Recognition</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">
                
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
        import { fetchHandler, API_USER, API_PRODUCT, API_PRODUCT_RECOGNITION } from '../utils.js';

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        const apiUrl = `${API_PRODUCT_RECOGNITION}/${recordId}?enriched=true`;
       
        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');

        const product_recognitionDetailSessionid = document.getElementById('product_recognition_detail_session_id');
        const product_recognitionDetailImageurl = document.getElementById('product_recognition_detail_image_url');
        const product_recognitionDetailConfidencescore = document.getElementById('product_recognition_detail_confidence_score');
        const product_recognitionDetailApiprovider = document.getElementById('product_recognition_detail_api_provider');
        const product_recognitionDetailRecognizedtext = document.getElementById('product_recognition_detail_recognized_text');
        const product_recognitionDetailRecognizedlabels = document.getElementById('product_recognition_detail_recognized_labels');
        const product_recognitionDetailMatchedproductname = document.getElementById('product_recognition_detail_matched_product_name');
        const product_recognitionDetailMatchedproductslug = document.getElementById('product_recognition_detail_matched_product_slug');
        const product_recognitionDetailUsername = document.getElementById('product_recognition_detail_user_name');
        const product_recognitionDetailUseremail = document.getElementById('product_recognition_detail_user_email');

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

        let usersMap = new Map();
        let productsMap = new Map();

        let currentAction = null;
        const confirmModal = document.getElementById('confirmModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalCancel = document.getElementById('modalCancel');
        const modalConfirm = document.getElementById('modalConfirm');
        const softDeleteBtn = document.getElementById('softDeleteBtn');
        const hardDeleteBtn = document.getElementById('hardDeleteBtn');

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
                    usersMap.set(user.id.toString(), displayText);
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
                    productsMap.set(product.id.toString(), displayText);
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
               
                const response = await fetchHandler(API_PRODUCT_RECOGNITION, 'POST', {id: recordId});
                successDiv.textContent = 'Product Recognition deleted successfully!';
                successDiv.style.display = 'block';
                setTimeout(() => { window.location.href = '../../index.php'; }, 2000);
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to delete product recognition';
                errorDiv.style.display = 'block';
            }
            currentAction = null;
        });

        const loadProductRecognitionData = async () => {
            const data = await fetchHandler(`${API_PRODUCT_RECOGNITION}/${recordId}?enriched=true`, 'GET');

            // Update detail view
            product_recognitionDetailSessionid.textContent = data.session_id || '-';
            product_recognitionDetailImageurl.innerHTML = data.image_url ? 
                `<a href="${data.image_url}" target="_blank">View Image</a>` : '-';
            product_recognitionDetailConfidencescore.textContent = data.confidence_score ? 
                `${data.confidence_score}%` : '-';
            product_recognitionDetailApiprovider.textContent = data.api_provider || '-';
            product_recognitionDetailRecognizedtext.textContent = data.recognized_text || '-';
            product_recognitionDetailRecognizedlabels.textContent = Array.isArray(data.recognized_labels) ? 
                data.recognized_labels.join(', ') : (data.recognized_labels || '-');
            product_recognitionDetailMatchedproductname.textContent = data.matched_product_name || '-';
            product_recognitionDetailMatchedproductslug.textContent = data.matched_product_slug || '-';
            product_recognitionDetailUsername.textContent = data.user_name || '-';
            product_recognitionDetailUseremail.textContent = data.user_email || '-';

            // Update form
            document.getElementById('recordId').value = data.id;
            
            form.session_id.value = data.session_id || '';
            form.image_url.value = data.image_url || '';
            form.recognized_text.value = data.recognized_text || '';
            form.recognized_labels.value = Array.isArray(data.recognized_labels) ? 
                data.recognized_labels.join(', ') : (data.recognized_labels || '');
            form.confidence_score.value = data.confidence_score || '';
            form.api_provider.value = data.api_provider || '';

            // Set user
            if (data.user_id) {
                user_idElement.value = data.user_id;
                const userDisplay = usersMap.get(data.user_id.toString());
                if (userDisplay) {
                    user_id_searchElement.value = userDisplay;
                }
            }

            // Set matched product
            if (data.matched_product_id) {
                matched_product_idElement.value = data.matched_product_id;
                const productDisplay = productsMap.get(data.matched_product_id.toString());
                if (productDisplay) {
                    matched_product_searchElement.value = productDisplay;
                }
            }
        };

        form.addEventListener('submit', async (e) => {
            
            e.preventDefault();
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
            successDiv.textContent = '';
            successDiv.style.display = 'none';

            const payload = {
                id: recordId,
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
                const result = await fetchHandler(API_PRODUCT_RECOGNITION, "PUT", payload);
                successDiv.textContent = 'Product Recognition updated successfully!';
                successDiv.style.display = 'block';
                await loadProductRecognitionData();
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to update product recognition';
                errorDiv.style.display = 'block';
            }
        });

        // Load data on page load
        Promise.all([loadUsers(), loadProducts()]).then(() => {
            loadProductRecognitionData().then(() => {
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</body>
</html>