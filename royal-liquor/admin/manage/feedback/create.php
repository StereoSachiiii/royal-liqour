<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Feedback</title>
    <link rel="stylesheet" href="../admin.css">
</head>

<body>

    <div class="admin-container">
        <h1>Create Feedback</h1>
        <div id="confirmation_modal" class="confirmation_overlay">
            <div class="confirmation_box">
                <h2>Feedback Created</h2>
                <p id="confirmation_message"></p>
                <div class="confirmation_actions">
                    <button id="confirmation_close" class="confirmation_btn">Close</button>
                    <a id="confirmation_view" class="confirmation_btn" href="#">View Feedback</a>
                </div>
            </div>
        </div>
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        <div class="edit-card">
            <h2>➕ New Feedback</h2>
            <form id="mainForm">
<div class="form-group">
    <label for="user_id" class="form-label required">User</label>
    <input list="usersList" id="user_id" name="user_id" type="number" class="form-input" placeholder="Select or search user..." required>
    <datalist id="usersList">
        <!-- Options populated via JS -->
    </datalist>
</div>
            
                <div class="form-group  ">
                    <label for="product_id" class="form-label required">Product</label>
                    <input list="productsList" type="number" id="product_id" name="product_id" class="form-input" required placeholder="Select or search product..."  >
                    <datalist id="productsList">
                        <!-- Options populated via JS -->
                    </datalist>
                </div>
            

            <div class="form-group">

                <label for="rating" class="form-label required">Rating</label>

                <input type="number" id="rating" name="rating" class="form-input" required placeholder="" min="1" max="5">

            </div>

            

            <div class="form-group">

                <label for="comment" class="form-label ">Comment</label>

                <textarea id="comment" name="comment" class="form-input" ></textarea>

            </div>

            

        <div class="form-group">

            <div class="checkbox-inline">

                

            <div class="checkbox-wrapper">

                <input type="checkbox" id="is_verified_purchase" name="is_verified_purchase" class="form-checkbox" >

                <label for="is_verified_purchase">Is Verified Purchase</label>

            </div>

            

            <div class="checkbox-wrapper">

                <input type="checkbox" id="is_active" name="is_active" class="form-checkbox" checked>

                <label for="is_active">Is Active</label>

            </div>

            

            </div>

        </div>

        

                

                <div class="form-actions">

                    <button type="submit" class="btn-primary">Create Feedback</button>

                    <a href="../../index.php" class="btn-secondary">Cancel</a>

                </div>

            </form>

        </div>

    </div>

    

<script type="module">
import { fetchHandler, API_FEEDBACK, API_USER, API_PRODUCT } from '../utils.js';
const user_idElement = document.getElementById('user_id');
const product_idElement = document.getElementById('product_id');
const ratingElement = document.getElementById('rating');
const commentElement = document.getElementById('comment');
const is_verified_purchaseElement = document.getElementById('is_verified_purchase');
const is_activeElement = document.getElementById('is_active');
const form = document.getElementById('mainForm');
const errorDiv = document.getElementById('errorDiv');
const successDiv = document.getElementById('successDiv');   
const modal = document.getElementById('confirmation_modal');
const modalMsg = document.getElementById('confirmation_message');
const modalClose = document.getElementById('confirmation_close');
const modalView = document.getElementById('confirmation_view');


const populateUsers = async () => { 
    const users = await fetchHandler(`${API_USER}`, 'GET');
    console.log(users.items);
    const userSelect = document.getElementById('usersList');
    userSelect.innerHTML = '<option value="">Select a user...</option>';
    users.items.forEach(user => {
        const option = document.createElement('option');
        option.value = user.id;
        option.textContent = `${user.id} - ${user.name}`;
        userSelect.appendChild(option);
    });
}


const populateProducts = async () => { 
    const products = await fetchHandler(`${API_PRODUCT}`, 'GET');
    console.log(products.items);
    const productSelect = document.getElementById('productsList');
    productSelect.innerHTML = '<option value="">Select a product...</option>';
    products.items.forEach(product => {
        const option = document.createElement('option');
        option.value = product.id;
        option.textContent = `${product.id} - ${product.name}`;
        productSelect.appendChild(option);
    });
}
document.addEventListener('DOMContentLoaded', async () => {
    await populateUsers();
    await populateProducts();
});


function showConfirmationModal(feedback) {
modalMsg.textContent = `Feedback "${ feedback.name || feedback.id }" was created successfully!`;
modalView.href = `../../index.php`;
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

            user_id: user_idElement.value,
            product_id: product_idElement.value,
            rating: ratingElement.value,
            comment: commentElement.value,
            is_verified_purchase: is_verified_purchaseElement.checked,
            is_active: is_activeElement.checked,
        };

        try {
                const response = await fetchHandler(API_FEEDBACK, 'POST', body);
                console.log(response, '<< Response');
                if (response.error === false) {
                    showConfirmationModal(response.data);
                    form.reset();
                } else {
                    throw new Error(response.message || 'Failed to create feedback');
                }
            } catch (error) {
                errorDiv.textContent = error.message || 'An error occurred while creating the feedback.';
                errorDiv.style.display = 'block';
            }
        }); 

    </script>

</body>

</html>