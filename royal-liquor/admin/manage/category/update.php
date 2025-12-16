<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Category</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/categories.css">
</head>

<body class="categories-edit">
    <div class="admin-container">
        <div class="admin-content">
            <div class="admin-header">
                <h1>Update Category</h1>
                <div class="admin-actions">
                    <a href="../../index.php" class="btn btn-secondary">← Back to List</a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div id="errorDiv" class="alert alert-error" style="display: none;"></div>
            <div id="successDiv" class="alert alert-success" style="display: none;"></div>

            <!-- Main Form -->
            <div class="categories_card">
                <form id="mainForm" class="categories_form">
                    <input type="hidden" id="recordId" name="id">
                    
                    <div class="categories_form-row">
                        <div class="categories_field">
                            <label for="name" class="categories_label">Name</label>
                            <input type="text" id="name" name="name" class="categories_input" required>
                        </div>

                        <div class="categories_field">
                            <label for="slug" class="categories_label">Slug</label>
                            <input type="text" id="slug" name="slug" class="categories_input" required>
                        </div>
                    </div>

                    <div class="categories_field">
                        <label for="description" class="categories_label">Description</label>
                        <textarea id="description" name="description" class="categories_textarea" rows="4"></textarea>
                    </div>

                    <div class="categories_field">
                        <label for="image_file" class="categories_label">Category Image</label>
                        <input type="file" id="image_file" name="image_file" class="categories_file" accept="image/*">
                        <input type="hidden" id="image_url" name="image_url">
                        <div id="imagePreview" class="categories_image-preview"></div>
                    </div>

                    <div class="categories_field categories_field-checkbox">
                        <label class="categories_checkbox">
                            <input type="checkbox" id="is_active" name="is_active">
                            <span>Active</span>
                        </label>
                    </div>

                    <div class="categories_form-actions">
                        <button type="submit" class="btn btn-primary">Update Category</button>
                        <button type="button" id="deleteBtn" class="btn btn-danger">Delete</button>
                        <a href="../../index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Quick View Card -->
            <div class="categories_card">
                <div class="categories_card-header">
                    <h3>📊 Category Overview</h3>
                </div>
                <div id="quickViewContent" class="categories_card-body">
                    <div class="categories_data-grid">
                        <div class="categories_field">
                            <span class="categories_label">Name</span>
                            <span id="category_detail_name" class="categories_value">-</span>
                        </div>
                        <div class="categories_field">
                            <span class="categories_label">Slug</span>
                            <span id="category_detail_slug" class="categories_value">-</span>
                        </div>
                        <div class="categories_field">
                            <span class="categories_label">Status</span>
                            <span id="category_detail_is_active" class="categories_value">-</span>
                        </div>
                    </div>

                    <h4 class="categories_section-title">Product Summary</h4>
                    <div class="categories_data-grid">
                        <div class="categories_field">
                            <span class="categories_label">Total Products</span>
                            <span id="category_detail_total_products" class="categories_value">0</span>
                        </div>
                        <div class="categories_field">
                            <span class="categories_label">Active Products</span>
                            <span id="category_detail_active_products" class="categories_value">0</span>
                        </div>
                        <div class="categories_field">
                            <span class="categories_label">Avg Price</span>
                            <span id="category_detail_avg_price_cents" class="categories_value">-</span>
                        </div>
                    </div>

                    <div id="category_detail_top_products" class="categories_products-grid">
                        <!-- Top products will be inserted here by JavaScript -->
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
                <button type="button" class="modal-close" id="cancelDelete">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this category? This action cannot be undone.</p>
                <p class="text-danger">Warning: This will not delete associated products but will remove the category assignment.</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirmDelete" class="btn btn-danger">Delete Category</button>
                <button type="button" id="cancelDeleteBtn" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <script type="module">
        import { fetchHandler, API_URL, API_CATEGORY } from '../utils.js';

        // Get category ID from URL
        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        const apiUrl = `${API_CATEGORY}/${recordId}`;
        const detailUrl = `${API_CATEGORY}/${recordId}?enriched=true`;

        // DOM Elements
        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const deleteBtn = document.getElementById('deleteBtn');
        const deleteModal = document.getElementById('deleteModal');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const cancelDelete = document.getElementById('cancelDelete');

        // Form elements
        const nameElement = document.getElementById('name');
        const slugElement = document.getElementById('slug');
        const descriptionElement = document.getElementById('description');
        const isActiveElement = document.getElementById('is_active');
        const imageFileInput = document.getElementById('image_file');
        const imageUrlInput = document.getElementById('image_url');
        const imagePreview = document.getElementById('imagePreview');

        // Quick view elements
        const categoryDetailName = document.getElementById('category_detail_name');
        const categoryDetailSlug = document.getElementById('category_detail_slug');
        const categoryDetailIsActive = document.getElementById('category_detail_is_active');
        const categoryDetailTotalProducts = document.getElementById('category_detail_total_products');
        const categoryDetailActiveProducts = document.getElementById('category_detail_active_products');
        const categoryDetailAvgPriceCents = document.getElementById('category_detail_avg_price_cents');
        const categoryDetailTopProducts = document.getElementById('category_detail_top_products');

        // Load category data on page load
        document.addEventListener('DOMContentLoaded', async () => {
            if (!recordId) {
                showError('No category ID provided');
                form.style.display = 'none';
                deleteBtn.style.display = 'none';
                return;
            }
            
            try {
                await loadCategory();
                await loadCategoryDetails();
            } catch (error) {
                console.error('Error initializing page:', error);
                showError('Failed to load category data');
            }
        });

        // Load category data for editing
        async function loadCategory() {
            try {
                showLoading(true, 'Loading category...');
                clearMessages();
                
                const response = await fetch(apiUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                const data = result.data || result;

                // Populate form fields
                document.getElementById('recordId').value = data.id;
                nameElement.value = data.name || '';
                slugElement.value = data.slug || '';
                descriptionElement.value = data.description || '';
                isActiveElement.checked = data.is_active == 1;
                
                // Handle image
                if (data.image_url) {
                    imageUrlInput.value = data.image_url;
                    imagePreview.innerHTML = `
                        <div class="categories_image-preview-container">
                            <img src="${data.image_url}" alt="${data.name || 'Category image'}" class="categories_thumb-large" />
                            <button type="button" class="categories_remove-image" onclick="removeImage()">
                                &times; Remove
                            </button>
                        </div>
                    `;
                }

            } catch (error) {
                console.error('Error loading category:', error);
                showError(error.message || 'Failed to load category');
                throw error;
            } finally {
                showLoading(false);
            }
        }

        // Load category details for quick view
        async function loadCategoryDetails() {
            try {
                const response = await fetch(detailUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                const data = result.data || result;

                // Update quick view
                categoryDetailName.textContent = data.name || 'N/A';
                categoryDetailSlug.textContent = data.slug || 'N/A';
                categoryDetailIsActive.textContent = data.is_active ? 'Active' : 'Inactive';
                categoryDetailTotalProducts.textContent = data.total_products || 0;
                categoryDetailActiveProducts.textContent = data.active_products || 0;
                categoryDetailAvgPriceCents.textContent = data.avg_price_cents ? `$${(data.avg_price_cents / 100).toFixed(2)}` : 'N/A';

                // Update top products
                if (data.top_products && data.top_products.length > 0) {
                    let html = '';
                    data.top_products.forEach(product => {
                        html += `
                            <div class="categories_product-card">
                                <div class="categories_product-image">
                                    ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}">` : '<div class="categories_no-image">No Image</div>'}
                                </div>
                                <div class="categories_product-info">
                                    <h5>${product.name || 'Unnamed Product'}</h5>
                                    <div class="categories_product-price">$${(product.price_cents / 100).toFixed(2)}</div>
                                    <div class="categories_product-stock ${product.stock_quantity > 0 ? 'in-stock' : 'out-of-stock'}">
                                        ${product.stock_quantity > 0 ? `${product.stock_quantity} in stock` : 'Out of stock'}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    categoryDetailTopProducts.innerHTML = html;
                } else {
                    categoryDetailTopProducts.innerHTML = '<p class="categories_no-data">No products found in this category.</p>';
                }

            } catch (error) {
                console.error('Error loading category details:', error);
                // Don't show error in UI for this as it's a secondary feature
            }
        }

        // Handle form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            clearMessages();
            showLoading(true, 'Updating category...');

            const body = {
                id: recordId,
                name: nameElement.value.trim(),
                slug: slugElement.value.trim(),
                description: descriptionElement.value.trim(),
                is_active: isActiveElement.checked ? 1 : 0,
                image_url: imageUrlInput.value || null
            };

            // Basic validation
            if (!body.name) {
                showError('Category name is required');
                showLoading(false);
                return;
            }

            try {
                const response = await fetch(apiUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(body),
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result?.message || `HTTP error! status: ${response.status}`);
                }

                showSuccess('Category updated successfully!');
                
                // Update the quick view with new data
                await loadCategoryDetails();

            } catch (error) {
                console.error('Error updating category:', error);
                showError(error.message || 'An error occurred while updating the category.');
            } finally {
                showLoading(false);
            }
        });

        // Handle image upload
        imageFileInput.addEventListener('change', async (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.match('image.*')) {
                showError('Please select a valid image file (JPEG, PNG, etc.)');
                return;
            }

            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showError('Image size should be less than 5MB');
                return;
            }

            showLoading(true, 'Uploading image...');
            clearMessages();

            try {
                const url = await uploadCategoryImage(file);
                imageUrlInput.value = url;

                // Show preview
                const reader = new FileReader();
                reader.onload = () => {
                    imagePreview.innerHTML = `
                        <div class="categories_image-preview-container">
                            <img src="${reader.result}" alt="Category preview" class="categories_thumb-large" />
                            <button type="button" class="categories_remove-image" onclick="removeImage()">
                                &times; Remove
                            </button>
                        </div>
                    `;
                    showLoading(false);
                };
                reader.readAsDataURL(file);
            } catch (err) {
                console.error('Category image upload failed', err);
                showError(err.message || 'Failed to upload category image');
                showLoading(false);
            }
        });

        // Upload image to server
        async function uploadCategoryImage(file) {
            const fd = new FormData();
            fd.append('entity', 'category');
            fd.append('image', file);

            try {
                const res = await fetch('/admin/api/images.php', { 
                    method: 'POST', 
                    body: fd,
                    credentials: 'same-origin'
                });
                
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                
                const data = await res.json();

                if (!data || !data.success || !data.data || !data.data.url) {
                    throw new Error(data?.message || 'Failed to upload category image');
                }

                return data.data.url;
            } catch (error) {
                console.error('Image upload failed:', error);
                throw new Error('Failed to upload image. Please try again.');
            }
        }

        // Handle delete button click
        deleteBtn.addEventListener('click', () => {
            deleteModal.style.display = 'flex';
        });

        // Handle delete confirmation
        confirmDeleteBtn.addEventListener('click', async () => {
            showLoading(true, 'Deleting category...');
            
            try {
                const response = await fetch(apiUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result?.message || `HTTP error! status: ${response.status}`);
                }

                showSuccess('Category deleted successfully!');
                setTimeout(() => {
                    window.location.href = '../../index.php';
                }, 1500);

            } catch (error) {
                console.error('Error deleting category:', error);
                showError(error.message || 'An error occurred while deleting the category.');
            } finally {
                deleteModal.style.display = 'none';
                showLoading(false);
            }
        });

        // Close modal when clicking the close button, cancel button, or outside the modal
        [cancelDelete, cancelDeleteBtn].forEach(btn => {
            btn.addEventListener('click', () => {
                deleteModal.style.display = 'none';
            });
        });

        window.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });

        // Auto-generate slug from name
        nameElement.addEventListener('input', () => {
            if (!slugElement.value || slugElement.dataset.autoGenerated) {
                const slug = nameElement.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                slugElement.value = slug;
                slugElement.dataset.autoGenerated = 'true';
            }
        });

        // Remove auto-generated flag when user manually edits the slug
        slugElement.addEventListener('input', () => {
            if (slugElement.dataset.autoGenerated) {
                delete slugElement.dataset.autoGenerated;
            }
        });

        // Helper function to show error messages
        function showError(message) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Helper function to show success messages
        function showSuccess(message) {
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Helper function to clear all messages
        function clearMessages() {
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
            successDiv.textContent = '';
            successDiv.style.display = 'none';
        }

        // Helper function to show loading state
        function showLoading(isLoading, message = '') {
            const submitBtn = form.querySelector('button[type="submit"]');
            const deleteBtn = document.getElementById('deleteBtn');
            
            if (isLoading) {
                submitBtn.disabled = true;
                deleteBtn.disabled = true;
                if (message) {
                    submitBtn.innerHTML = `<span class="spinner"></span> ${message}`;
                } else {
                    submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
                }
            } else {
                submitBtn.disabled = false;
                deleteBtn.disabled = false;
                submitBtn.textContent = 'Update Category';
            }
        }

        // Function to remove selected image
        window.removeImage = function() {
            imageFileInput.value = '';
            imageUrlInput.value = '';
            imagePreview.innerHTML = '';
        };

    </script>

</body>

</html>