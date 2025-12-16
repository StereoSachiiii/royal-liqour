<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Flavor Profile</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Flavor Profile</h1>
        
        <!-- CREATE CONFIRMATION MODAL -->
        <div id="confirmation_modal" class="confirmation_overlay">
            <div class="confirmation_box">
                <h2>Flavor Profile Created</h2>
                <p id="confirmation_message"></p>
                <div class="confirmation_actions">
                    <button id="confirmation_close" class="confirmation_btn">Close</button>
                    <a id="confirmation_view" class="confirmation_btn" href="#">View Profile</a>
                </div>
            </div>
        </div>
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>➕ New Flavor Profile</h2>
            <form id="mainForm">
                
                <div class="form-group">
                    <label for="product_id" class="form-label required">Product</label>
                    <select id="product_id" name="product_id" class="form-input" required>
                        <option value="">Loading products...</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sweetness" class="form-label">Sweetness</label>
                    <input type="number" id="sweetness" name="sweetness" class="form-input" placeholder="0-10" min="0" max="10" value="5">
                </div>
                
                <div class="form-group">
                    <label for="bitterness" class="form-label">Bitterness</label>
                    <input type="number" id="bitterness" name="bitterness" class="form-input" placeholder="0-10" min="0" max="10" value="5">
                </div>
                
                <div class="form-group">
                    <label for="strength" class="form-label">Strength</label>
                    <input type="number" id="strength" name="strength" class="form-input" placeholder="0-10" min="0" max="10" value="5">
                </div>
                
                <div class="form-group">
                    <label for="smokiness" class="form-label">Smokiness</label>
                    <input type="number" id="smokiness" name="smokiness" class="form-input" placeholder="0-10" min="0" max="10" value="5">
                </div>
                
                <div class="form-group">
                    <label for="fruitiness" class="form-label">Fruitiness</label>
                    <input type="number" id="fruitiness" name="fruitiness" class="form-input" placeholder="0-10" min="0" max="10" value="5">
                </div>
                
                <div class="form-group">
                    <label for="spiciness" class="form-label">Spiciness</label>
                    <input type="number" id="spiciness" name="spiciness" class="form-input" placeholder="0-10" min="0" max="10" value="5">
                </div>
                
                <div class="form-group">
                    <label for="tags" class="form-label">Tags</label>
                    <input type="text" id="tags" name="tags" class="form-input" placeholder="e.g. Oaky, Vanilla, Smooth">
                    <small>Separate tags with commas</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Profile</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script type="module">
        
        
        const API_PRODUCTS = '/royal-liquor/api/v1/products?limit=1000'; 
        const API_FLAVOR_PROFILE = '/royal-liquor/api/v1/flavour-profiles';
        
        const product_idElement = document.getElementById('product_id');
        const sweetnessElement = document.getElementById('sweetness');
        const bitternessElement = document.getElementById('bitterness');
        const strengthElement = document.getElementById('strength');
        const smokinessElement = document.getElementById('smokiness');
        const fruitinessElement = document.getElementById('fruitiness');
        const spicinessElement = document.getElementById('spiciness');
        const tagsElement = document.getElementById('tags');

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');   
        const modal = document.getElementById('confirmation_modal');
        const modalMsg = document.getElementById('confirmation_message');
        const modalClose = document.getElementById('confirmation_close');
        const modalView = document.getElementById('confirmation_view');

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

        // Load products dropdown
        async function loadProducts() {
            try {
                const response = await directFetch(API_PRODUCTS);
                const products = response.data?.items || response.data || response.items || [];
                
                product_idElement.innerHTML = '<option value="">Select a product...</option>';
                
                if (Array.isArray(products) && products.length) {
                    products.forEach(p => {
                        const option = document.createElement('option');
                        option.value = p.id;
                        option.textContent = `${p.name} (ID: ${p.id})`;
                        product_idElement.appendChild(option);
                    });
                } else {
                    product_idElement.innerHTML = '<option value="">No active products found</option>';
                }
            } catch (error) {
                console.error('Error loading products:', error);
                product_idElement.innerHTML = '<option value="">Error loading products</option>';
            }
        }

        function showConfirmationModal(profile) {
            modalMsg.textContent = `Flavor Profile for Product ID "${profile.product_id}" was created successfully!`;
            modalView.href = `update.php?id=${profile.product_id}`;
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

            const tagsValue = tagsElement.value.trim();
            const tagsArray = tagsValue ? tagsValue.split(',').map(t => t.trim()).filter(t => t) : [];

            const body = {
                product_id: parseInt(product_idElement.value),
                sweetness: sweetnessElement.value ? parseInt(sweetnessElement.value) : 5,
                bitterness: bitternessElement.value ? parseInt(bitternessElement.value) : 5,
                strength: strengthElement.value ? parseInt(strengthElement.value) : 5,
                smokiness: smokinessElement.value ? parseInt(smokinessElement.value) : 5,
                fruitiness: fruitinessElement.value ? parseInt(fruitinessElement.value) : 5,
                spiciness: spicinessElement.value ? parseInt(spicinessElement.value) : 5,
                tags: tagsArray
            };

            try {
                const response = await directFetch(API_FLAVOR_PROFILE, 'POST', body);
                const data = response.data || response;
                
                if (response.success === false) { 
                    throw new Error(data.message || 'Failed to create profile');
                }
                
                showConfirmationModal(data);
                form.reset();
                
            } catch (error) {
                console.error('Error creating profile:', error);
                errorDiv.textContent = error.message || 'An unexpected error occurred.';
                errorDiv.style.display = 'block';
            }
        });
        
        loadProducts();
    </script>
</body>
</html>