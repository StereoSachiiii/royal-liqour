<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Supplier</title>

    <link rel="stylesheet" href="../admin.css">

</head>

<body>

    <div class="admin-container">

        <h1>Create Supplier</h1>

        

        <!-- CREATE CONFIRMATION MODAL -->

        <div id="confirmation_modal" class="confirmation_overlay">

            <div class="confirmation_box">

                <h2>Supplier Created</h2>

                <p id="confirmation_message"></p>

                <div class="confirmation_actions">

                    <button id="confirmation_close" class="confirmation_btn">Close</button>

                    <a id="confirmation_view" class="confirmation_btn" href="#">View Supplier</a>

                </div>

            </div>

        </div>

        <div id="errorDiv" class="error-message"></div>

        <div id="successDiv" class="success-message"></div>

        

        <div class="edit-card">

            <h2>➕ New Supplier</h2>

            <form id="mainForm">

                

            <div class="form-group">

                <label for="name" class="form-label required">Name</label>

                <input type="text" id="name" name="name" class="form-input" required placeholder="">

            </div>

            

            <div class="form-group">

                <label for="email" class="form-label ">Email</label>

                <input type="email" id="email" name="email" class="form-input"  placeholder="">

            </div>

            

            <div class="form-group">

                <label for="phone" class="form-label ">Phone</label>

                <input type="tel" id="phone" name="phone" class="form-input"  placeholder="+94 77 123 4567">

            </div>

            

            <div class="form-group">

                <label for="address" class="form-label ">Address</label>

                <textarea id="address" name="address" class="form-input" ></textarea>

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

                    <button type="submit" class="btn-primary">Create Supplier</button>

                    <a href="../../index.php" class="btn-secondary">Cancel</a>

                </div>

            </form>

        </div>

    </div>

    

    <script type="module">

        import { fetchHandler, API_URL , API_SUPPLIER } from '../utils.js';

        const nameElement = document.getElementById('name');
const emailElement = document.getElementById('email');
const phoneElement = document.getElementById('phone');
const addressElement = document.getElementById('address');
const is_activeElement = document.getElementById('is_active');


        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');   
        const modal = document.getElementById('confirmation_modal');
        const modalMsg = document.getElementById('confirmation_message');
        const modalClose = document.getElementById('confirmation_close');
        const modalView = document.getElementById('confirmation_view');
function showConfirmationModal(supplier) {
    // If supplier is null or undefined, fallback to a generic message
    const supplierName = supplier?.name || supplier?.id || 'Unknown Supplier';
    
    modalMsg.textContent = `Supplier "${supplierName}" was created successfully!`;
    
    // Optionally link to the supplier view if you have an id
    modalView.href = supplier?.id ? `../../index.php` : '#';
    
    modal.style.display = 'flex';
}

form.addEventListener('submit', async (e) => {
    e.preventDefault(); 

    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
    successDiv.style.display = 'none';
    successDiv.textContent = '';

    const body = {
        name: nameElement.value,
        email: emailElement.value,
        phone: phoneElement.value,
        address: addressElement.value,
        is_active: is_activeElement.checked,
    };

    const response = await fetchHandler(API_SUPPLIER, 'POST', body);

    if (response.error) {
        errorDiv.textContent = response.msg; // show API error
        errorDiv.style.display = 'block';
    } else {
        showConfirmationModal(response.data); 
        form.reset();
    }
});


    </script>

</body>

</html>
