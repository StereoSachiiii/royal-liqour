<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../assets/css/products.css">
</head>
<body>
    <div class="admin-container products-edit">
        <h1>Update Product</h1>
        
        <!-- Quick View Card -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>📦 Product Overview</h2>
            <div id="quickViewContent">
                <div class="modal-content">
                    <h2>📦 Product Detail: <span id="product_detail_name"></span></h2>
                    <div class="detail-section">
                        <h3>Basic Info</h3>
                        <div class="detail-field"><strong>Slug:</strong> <span id="product_detail_slug"></span></div>
                        <div class="detail-field"><strong>Price:</strong> <span id="product_detail_price"></span></div>
                        <div class="detail-field"><strong>Category:</strong> <span id="product_detail_category"></span></div>
                        <div class="detail-field"><strong>Supplier:</strong> <span id="product_detail_supplier"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>Stock & Sales</h3>
                        <div class="detail-field"><strong>Total Stock:</strong> <span id="product_detail_stock"></span></div>
                        <div class="detail-field"><strong>Available:</strong> <span id="product_detail_available"></span></div>
                        <div class="detail-field"><strong>Times Ordered:</strong> <span id="product_detail_orders"></span></div>
                        <div class="detail-field"><strong>Total Sold:</strong> <span id="product_detail_sold"></span></div>
                        <div class="detail-field"><strong>Revenue:</strong> <span id="product_detail_revenue"></span></div>
                    </div>
                    <div class="detail-section">
                        <h3>Feedback</h3>
                        <div class="detail-field"><strong>Avg Rating:</strong> <span id="product_detail_rating"></span></div>
                        <div class="detail-field"><strong>Reviews:</strong> <span id="product_detail_reviews"></span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>✏️ Edit Product</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">
                
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
                    <label for="image_file" class="form-label">Product Image</label>
                    <input type="file" id="image_file" name="image_file" class="form-file" accept="image/*">
                    <input type="hidden" id="image_url" name="image_url" class="form-input">
                    <div id="imagePreview" class="file-preview"></div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="is_active" name="is_active" class="form-checkbox">
                        <label for="is_active">Is Active</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                    <button type="button" id="softDeleteBtn" class="btn-danger">Soft Delete</button>
                </div>
            </form>
        </div>
    </div>
    
    <script type="module">
        import { fetchHandler, API_IMAGES, API_CATEGORY, API_PRODUCT, API_SUPPLIER } from '../utils.js';

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const imageFileInput = document.getElementById('image_file');
        const imageUrlInput = document.getElementById('image_url');
        const imagePreview = document.getElementById('imagePreview');

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
                console.error('Failed to load dropdowns:', error);
            }
        };

        const loadProductData = async () => {
            const apiUrl = `${API_PRODUCT}/${recordId}?enriched=true`;
            const data = await fetchHandler(apiUrl, 'GET');

            document.getElementById('product_detail_name').textContent = data.name;
            document.getElementById('product_detail_slug').textContent = data.slug;
            document.getElementById('product_detail_price').textContent = `${data.price_cents} cents`;
            document.getElementById('product_detail_category').textContent = data.category_name || 'N/A';
            document.getElementById('product_detail_supplier').textContent = data.supplier_name || 'N/A';
            document.getElementById('product_detail_stock').textContent = data.total_quantity || 0;
            document.getElementById('product_detail_available').textContent = data.total_available || 0;
            document.getElementById('product_detail_orders').textContent = data.times_ordered || 0;
            document.getElementById('product_detail_sold').textContent = data.total_sold || 0;
            document.getElementById('product_detail_revenue').textContent = `${data.total_revenue_cents || 0} cents`;
            document.getElementById('product_detail_rating').textContent = data.avg_rating ? Number(data.avg_rating).toFixed(1) : 'N/A';
            document.getElementById('product_detail_reviews').textContent = data.feedback_count || 0;
            console.log(data);
            document.getElementById('recordId').value = data.id;
            form.name.value = data.name;
            form.slug.value = data.slug;
            form.description.value = data.description || '';
            form.price_cents.value = data.price_cents;
            form.category_id.value = data.category_id;
            form.supplier_id.value = data.supplier_id || '';
            imageUrlInput.value = data.image_url || '';
            form.is_active.checked = data.is_active;

            if (data.image_url) {
                imagePreview.innerHTML = `<img src="${data.image_url}" alt="Current product image" class="products_thumb-large" />`;
            } else {
                imagePreview.innerHTML = '';
            }
        };

        async function uploadProductImage(file) {
            const fd = new FormData();
            fd.append('entity', 'product');
            fd.append('image', file);

            const res = await fetch(API_IMAGES, { method: 'POST', body: fd });
            const data = await res.json();

            if (!data || !data.success || !data.data || !data.data.url) {
                throw new Error(data?.message || 'Failed to upload product image');
            }

            return data.data.url;
        }

        imageFileInput.addEventListener('change', async (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            errorDiv.textContent = '';
            errorDiv.style.display = 'none';

            try {
                const url = await uploadProductImage(file);
                imageUrlInput.value = url;

                const reader = new FileReader();
                reader.onload = () => {
                    imagePreview.innerHTML = `<img src="${reader.result}" alt="Product preview" class="products_thumb-large" />`;
                };
                reader.readAsDataURL(file);
            } catch (err) {
                console.error('Image upload failed:', err);
                imagePreview.innerHTML = '';
                errorDiv.textContent = err.message || 'Failed to upload product image';
                errorDiv.style.display = 'block';
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorDiv.textContent = '';
            successDiv.textContent = '';

            const payload = {
                id: recordId,
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
                const result = await fetchHandler(API_PRODUCT, "PUT", payload);

                successDiv.textContent = 'Product updated successfully!';
                successDiv.style.display = 'block';
                await loadProductData();
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to update product';
                errorDiv.style.display = 'block';
            }
        });

(async () => {
    await loadDropdowns();
    await loadProductData();
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
})();

    </script>
</body>
</html>