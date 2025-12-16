
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Product</h1>
        
        <!-- Confirmation Modal -->
        <div id="confirmation_modal" class="confirmation_overlay">
            <div class="confirmation_box">
                <h2>Product Created</h2>
                <p id="confirmation_message"></p>
                <div class="confirmation_actions">
                    <button id="confirmation_close" class="confirmation_btn">Close</button>
                    <a id="confirmation_view" class="confirmation_btn" href="#">View Product</a>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>➕ New Product</h2>
            <form id="mainForm">
                
                <div class="form-group">
                    <label for="name" class="form-label required">Product Name</label>
                    <input type="text" id="name" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="slug" class="form-label required">Slug</label>
                    <input type="text" id="slug" name="slug" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-input" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price_cents" class="form-label required">Price (Cents)</label>
                    <input type="number" id="price_cents" name="price_cents" class="form-input" required min="1">
                </div>
                
                <div class="form-group">
                    <label for="category_id" class="form-label required">Category</label>
                    <select id="category_id" name="category_id" class="form-input" required>
                        <option value="">Select Category</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select id="supplier_id" name="supplier_id" class="form-input">
                        <option value="">Select Supplier (Optional)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image_url" class="form-label">Image URL</label>
                    <input type="url" id="image_url" name="image_url" class="form-input">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="is_active" name="is_active" class="form-checkbox" checked>
                        <label for="is_active">Is Active</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Product</button>
                    <a href="/admin/products/list.html" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script type="module">
        import { fetchHandler, API_CATEGORY, API_SUPPLIER, API_PRODUCT } from '../utils.js';

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        
        const modal = document.getElementById('confirmation_modal');
        const modalMsg = document.getElementById('confirmation_message');
        const modalClose = document.getElementById('confirmation_close');
        const modalView = document.getElementById('confirmation_view');

        const loadDropdowns = async () => {
            try {
                const [categories, suppliers] = await Promise.all([
                    fetchHandler(`${API_CATEGORY}`, 'GET'),
                    fetchHandler(`${API_SUPPLIER}`, 'GET')
                ]);

                const categorySelect = document.getElementById('category_id');
                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    categorySelect.appendChild(option);
                });

                const supplierSelect = document.getElementById('supplier_id');
                suppliers.forEach(sup => {
                    const option = document.createElement('option');
                    option.value = sup.id;
                    option.textContent = sup.name;
                    supplierSelect.appendChild(option);
                });
            } catch (error) {
                errorDiv.textContent = 'Failed to load categories/suppliers: ' + error.message;
                errorDiv.style.display = 'block';
            }
        };

        function showConfirmationModal(product) {
            modalMsg.textContent = `Product "${product.name}" was created successfully!`;
            modalView.href = `../index.php`;
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
                name: form.name.value,
                slug: form.slug.value,
                description: form.description.value,
                price_cents: parseInt(form.price_cents.value),
                category_id: parseInt(form.category_id.value),
                supplier_id: form.supplier_id.value ? parseInt(form.supplier_id.value) : null,
                image_url: form.image_url.value || null,
                is_active: form.is_active.checked
            };

            try {
                const apiUrl = `${API_PRODUCT}`;
                const response = await fetchHandler(apiUrl, 'POST', body);
                
                if (response && response.success && response.data) {
                    showConfirmationModal(response.data);
                    form.reset();
                } else {
                    throw new Error(response.message || 'Failed to create product');
                }
            } catch (error) {
                console.error('Error creating product:', error);
                errorDiv.textContent = error.message || 'An error occurred while creating the product.';
                errorDiv.style.display = 'block';
            }
        });

        loadDropdowns();
    </script>
</body>
</html>


