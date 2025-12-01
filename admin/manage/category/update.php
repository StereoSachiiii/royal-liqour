<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Update Category</title>

    <link rel="stylesheet" href="../admin.css">

</head>

<body>

    <div class="admin-container">

        <h1>Update Category</h1>

        

        <!-- QUICK VIEW CARD -->

        <div id="quickViewCard" class="quick-view-card" >

            <h2>📊 Category Overview</h2>

            <div id="quickViewContent">

<div class="modal-content">
  <h2> 📁 Category Detail: <span id="category_detail_title"></span></h2>
  
  <div class="detail-section">
    <h3>Basic Info</h3>
    <div class="detail-field"><strong>Name:</strong> <span id="category_detail_name"></span></div>
    <div class="detail-field"><strong>Slug:</strong> <span id="category_detail_slug"></span></div>
    <div class="detail-field"><strong>Is Active:</strong> <span id="category_detail_is_active"></span></div>
  </div>

  <div class="detail-section">
    <h3>Product Summary</h3>
    <div class="detail-field"><strong>Total Products:</strong> <span id="category_detail_total_products"></span></div>
    <div class="detail-field"><strong>Active Products:</strong> <span id="category_detail_active_products"></span></div>
    <div class="detail-field"><strong>Avg Price (Cents):</strong> <span id="category_detail_avg_price_cents"></span></div>
  </div>

  <div class="detail-section">
    <h3>Top Products</h3>
    <div id="category_detail_top_products" class="json-data-block"></div>
  </div>
</div>

            </div>

        </div>

        

        <div id="errorDiv" class="error-message"></div>

        <div id="successDiv" class="success-message"></div>

        

        <div class="edit-card">

            <h2>✏️ Edit Category</h2>

            <form id="mainForm">

                <input type="hidden" id="recordId" name="id">


            <div class="form-group">

                <label for="name" class="form-label required">Name</label>

                <input type="text" id="name" name="name" class="form-input" required placeholder="">

            </div>

            

            <div class="form-group">

                <label for="slug" class="form-label required">Slug</label>

                <input type="text" id="slug" name="slug" class="form-input" required placeholder="">

            </div>

            

            <div class="form-group">

                <label for="description" class="form-label ">Description</label>

                <textarea id="description" name="description" class="form-input" ></textarea>

            </div>

            

            <div class="form-group">

                <label for="image_url" class="form-label ">Image</label>

                <div id="currentImage" class="file-current" style="display:none;"></div>
<input type="file" id="image_url" name="image_url" class="form-file" accept="image/*" >

                <div id="imagePreview" class="file-preview"></div>

            </div>

            

        <div class="form-group">

            <div class="checkbox-inline">

                

            <div class="checkbox-wrapper">

                <input type="checkbox" id="is_active" name="is_active" class="form-checkbox" checked>

                <label for="is_active">Is Active</label>

            </div>

            

            </div>

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

      import { fetchHandler, API_URL, API_CATEGORY } from '../utils.js';

const recordId = Number(new URLSearchParams(window.location.search).get('id'));

const apiUrl = `${API_URL}?entity=categories&id=${recordId}`;

const updateUrl = `${API_CATEGORY}`;

const form = document.getElementById('mainForm');

const errorDiv = document.getElementById('errorDiv');

const successDiv = document.getElementById('successDiv');

const categoryDetailName = document.getElementById('category_detail_name');
const categoryDetailSlug = document.getElementById('category_detail_slug');
const categoryDetailIsactive = document.getElementById('category_detail_is_active');
const categoryDetailTotalproducts = document.getElementById('category_detail_total_products');
const categoryDetailActiveproducts = document.getElementById('category_detail_active_products');
const categoryDetailAvgpricecents = document.getElementById('category_detail_avg_price_cents');
const categoryDetailTopproducts = document.getElementById('category_detail_top_products');


const nameElement = document.getElementById('name');
const slugElement = document.getElementById('slug');
const descriptionElement = document.getElementById('description');
const is_activeElement = document.getElementById('is_active');


let currentAction = null;

const confirmModal = document.getElementById('confirmModal');

const modalTitle = document.getElementById('modalTitle');

const modalMessage = document.getElementById('modalMessage');

const modalCancel = document.getElementById('modalCancel');

const modalConfirm = document.getElementById('modalConfirm');

const softDeleteBtn = document.getElementById('softDeleteBtn');

const hardDeleteBtn = document.getElementById('hardDeleteBtn');

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

        const deleteUrl = `${API_UPDATE}?entity=categories&action=${currentAction}`;

        const response = await fetchHandler(deleteUrl, 'POST', {id: recordId});

        successDiv.textContent = 'Category deleted successfully!';

        // Perhaps redirect to list

        setTimeout(() => { window.location.href = '../../index.php'; }, 2000);

    } catch (err) {

        errorDiv.textContent = err.message || 'Failed to delete category';

    }

    currentAction = null;

});

const loadCategoryData = async () => {

    const data = await fetchHandler(apiUrl, 'GET');

categoryDetailName.textContent = data.slug;
categoryDetailSlug.textContent = data.slug;
categoryDetailIsactive.textContent = data.is_active ? 'Yes' : 'No';
categoryDetailTotalproducts.textContent = data.total_products;
categoryDetailActiveproducts.textContent = data.active_products;
categoryDetailAvgpricecents.textContent = data.avg_price_cents;
categoryDetailTopproducts.textContent = JSON.stringify(data.top_products, null, 2);
categoryDetailName.textContent = data.name;

document.getElementById('recordId').value = data.id;

form.name.value = data.name;
form.slug.value = data.slug;
form.description.value = data.description || '';
form.is_active.checked = data.is_active;



        if (data.image_url) {

            currentImage.innerHTML = `<img src="${data.image_url}" alt="Current Image_url">`;

            currentImage.style.display = 'block';

        }

        

};

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    errorDiv.textContent = '';
    successDiv.textContent = '';

    // Get the file input
    const fileInput = document.getElementById('image_url');
    const file = fileInput.files[0];

    // TODO: Upload the image to your image API and get back the URL
    /*
    let image = null;
    if (file) {
        const formData = new FormData();
        formData.append('file', file);

        try {
            const uploadResponse = await fetch('https://your-image-api.com/upload', {
                method: 'POST',
                body: formData
            });
            const result = await uploadResponse.json();

            if (result.url) {
                image = result.url; // success: set image URL
            } else {
                // API responded but no URL returned
                return { error: 'Image upload failed: No URL returned from API' };
            }
        } catch (err) {
            console.error('Image upload failed', err);
            return { error: err.message || 'Image upload failed' };
        }
    }
    */

    // Current request payload (image variable not used yet)
    const payload = {
        id: recordId,
        name: nameElement.value,
        slug: slugElement.value,
        description: descriptionElement.value,
        is_active: is_activeElement.checked,
        image_url: null // replace with 'image' when image API is ready
    };

    try {
        const result = await fetchHandler(updateUrl, "PUT", payload);
        successDiv.textContent = 'Category updated successfully!';
        await loadCategoryData();
    } catch (err) {
        errorDiv.textContent = err.message || 'Failed to update category';
    }
});


loadCategoryData().then(() => {

    form.scrollIntoView({ behavior: 'smooth', block: 'start' });

});

    </script>

</body>

</html>