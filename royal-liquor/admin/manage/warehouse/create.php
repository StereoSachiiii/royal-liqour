<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Warehouse</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Warehouse</h1>
        
        <!-- CREATE CONFIRMATION MODAL -->
        <div id="confirmation_modal" class="confirmation_overlay">
            <div class="confirmation_box">
                <h2>Warehouse Created</h2>
                <p id="confirmation_message"></p>
                <div class="confirmation_actions">
                    <button id="confirmation_close" class="confirmation_btn">Close</button>
                    <a id="confirmation_view" class="confirmation_btn" href="#">View Warehouse</a>
                </div>
            </div>
        </div>
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>➕ New Warehouse</h2>
            <form id="mainForm">
                
            <div class="form-group">
                <label for="name" class="form-label required">Name</label>
                <input type="text" id="name" name="name" class="form-input" required placeholder="">
            </div>
            
            <div class="form-group">
                <label for="address" class="form-label ">Address</label>
                <textarea id="address" name="address" class="form-input" ></textarea>
            </div>
            
            <div class="form-group">
                <label for="phone" class="form-label ">Phone</label>
                <input type="tel" id="phone" name="phone" class="form-input"  placeholder="+94 77 123 4567">
            </div>
            
            <div class="form-group">
                <label for="image_url" class="form-label ">Image</label>
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
                    <button type="submit" class="btn-primary">Create Warehouse</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
<script type="module">
import { fetchHandler, API_WAREHOUSE } from '../utils.js';

const nameElement = document.getElementById('name');
const addressElement = document.getElementById('address');
const phoneElement = document.getElementById('phone');
const is_activeElement = document.getElementById('is_active');

const form = document.getElementById('mainForm');
const errorDiv = document.getElementById('errorDiv');
const successDiv = document.getElementById('successDiv');   
const modal = document.getElementById('confirmation_modal');
const modalMsg = document.getElementById('confirmation_message');
const modalClose = document.getElementById('confirmation_close');
const modalView = document.getElementById('confirmation_view');

function showConfirmationModal(warehouse) {
    modalMsg.textContent = `Warehouse "${ warehouse.name || warehouse.id }" was created successfully!`;
    modalView.href = `../../index.php?id`;
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
            image = result.url; // assuming the API returns { url: 'https://...' }
        } catch (err) {
            console.error('Image upload failed', err);
        }
    }
    */

    // Current request body (image variable not set yet)
    const body = {
        name: nameElement.value,
        address: addressElement.value,
        phone: phoneElement.value,
        is_active: is_activeElement.checked,
        image_url: null // replace with 'image' when image API is ready
    };

    try {
        const apiUrl = `${API_WAREHOUSE}`;
        const response = await fetchHandler(apiUrl, 'POST', body);

        if (!response.error) {
            showConfirmationModal(response);
            form.reset();
        } else {
            throw new Error(response.message || 'Failed to create warehouse');
        }

    } catch (error) {
        console.error('Error creating warehouse:', error);
        errorDiv.textContent = error.message || 'An error occurred while creating the warehouse.';
        errorDiv.style.display = 'block';
    }
}); 
</script>

</body>
</html>