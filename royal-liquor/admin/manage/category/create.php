<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Category</title>
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../assets/css/categories.css">
</head>
<body class="categories-edit">
    <div class="admin-container">
        <div class="admin-content">
            <div class="admin-header">
                <h1>Create New Category</h1>
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
                    <div class="edit-card">
                        <h2>➕ New Category</h2>
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
                                <input type="checkbox" id="is_active" name="is_active" checked>
                                <span>Active</span>
                            </label>
                        </div>

                        <div class="categories_form-actions">
                            <button type="submit" class="btn btn-primary">Create Category</button>
                            <a href="../../index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmation_modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Category Created</h3>
                <button type="button" class="modal-close" id="confirmation_close">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmation_message"></p>
            </div>
            <div class="modal-footer">
                <a href="../../index.php" class="btn btn-secondary">Back to List</a>
                <a href="#" id="confirmation_view" class="btn btn-primary">View Category</a>
            </div>
        </div>
    </div>

    <script type="module">
        import { fetchHandler, API_CATEGORY } from '../utils.js';

        const nameElement = document.getElementById('name');
        const slugElement = document.getElementById('slug');
        const descriptionElement = document.getElementById('description');
        const is_activeElement = document.getElementById('is_active');
        const imageFileInput = document.getElementById('image_file');
        const imageUrlInput = document.getElementById('image_url');
        const imagePreview = document.getElementById('imagePreview');

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');   
        const modal = document.getElementById('confirmation_modal');
        const modalMsg = document.getElementById('confirmation_message');
        const modalClose = document.getElementById('confirmation_close');
        const modalView = document.getElementById('confirmation_view');

        function showConfirmationModal(category) {
            modalMsg.textContent = `Category "${ category.name || category.id }" was created successfully!`;
            modalView.href = `../../index.php?id`;
            modal.style.display = 'flex';
        }

        modalClose.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        async function uploadCategoryImage(file) {
            const fd = new FormData();
            fd.append('entity', 'category');
            fd.append('image', file);

            const res = await fetch('/admin/api/images.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (!data || !data.success || !data.data || !data.data.url) {
                throw new Error(data?.message || 'Failed to upload category image');
            }

            return data.data.url;
        }

        imageFileInput.addEventListener('change', async (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            errorDiv.style.display = 'none';
            errorDiv.textContent = '';

            try {
                const url = await uploadCategoryImage(file);
                imageUrlInput.value = url;

                const reader = new FileReader();
                reader.onload = () => {
                    imagePreview.innerHTML = `<img src="${reader.result}" alt="Category preview" class="categories_thumb-large" />`;
                };
                reader.readAsDataURL(file);
            } catch (err) {
                console.error('Category image upload failed', err);
                imagePreview.innerHTML = '';
                errorDiv.textContent = err.message || 'Failed to upload category image';
                errorDiv.style.display = 'block';
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault(); 

            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
            successDiv.style.display = 'none';
            successDiv.textContent = '';

            const body = {
                name: nameElement.value,
                slug: slugElement.value,
                description: descriptionElement.value,
                is_active: is_activeElement.checked,
                image_url: imageUrlInput.value || null
            };

            try {
                const apiUrl = `${API_CATEGORY}`;
                const response = await fetchHandler(apiUrl, 'POST', body);

                if (!response.error) {
                    showConfirmationModal(response);
                    form.reset();
                    imagePreview.innerHTML = '';
                    imageUrlInput.value = '';
                } else {
                    throw new Error(response.message || 'Failed to create category');
                }

            } catch (error) {
                console.error('Error creating category:', error);
                errorDiv.textContent = error.message || 'An error occurred while creating the category.';
                errorDiv.style.display = 'block';
            }
        }); 
    </script>
</body>
</html>