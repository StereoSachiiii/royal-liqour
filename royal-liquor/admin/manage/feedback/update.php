<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Feedback</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update Feedback</h1>
        
        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card" >
            <h2>📊 Feedback Overview</h2>
            <div id="quickViewContent">
                <div class="modal-content"><h2> ⭐ Feedback Detail:</h2><div class="detail-section"><h3>Basic Info</h3><div class="detail-field"><strong>Rating:</strong> <span id="feedback_detail_rating"></span></div><div class="detail-field"><strong>Is Active:</strong> <span id="feedback_detail_is_active"></span></div><div class="detail-field"><strong>Is Verified Purchase:</strong> <span id="feedback_detail_is_verified_purchase"></span></div></div><div class="detail-section"><h3>User Info</h3><div class="detail-field"><strong>User Name:</strong> <span id="feedback_detail_user_name"></span></div><div class="detail-field"><strong>User Email:</strong> <span id="feedback_detail_user_email"></span></div></div><div class="detail-section"><h3>Product Info</h3><div class="detail-field"><strong>Product Name:</strong> <span id="feedback_detail_product_name"></span></div><div class="detail-field"><strong>Product Slug:</strong> <span id="feedback_detail_product_slug"></span></div></div><div class="detail-section"><h3>Purchase Count</h3><div class="detail-field"><strong>Purchase Count:</strong> <span id="feedback_detail_purchase_count"></span></div></div></div>
            </div>
        </div>
        
        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>✏️ Edit Feedback</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">

<div class="form-group">
    <label for="user_search" class="form-label required">User</label>
    <input
        list="usersList"
        id="user_search"
        class="form-input"
        placeholder="Search user by name or email..."
        autocomplete="off"
        required
        readonly
    >
    <datalist id="usersList"></datalist>

    <!-- Hidden field with actual ID -->
    <input type="hidden" id="user_id" name="user_id">
</div>


            

<div class="form-group">
    <label for="product_search" class="form-label required">Product</label>
    <input
        list="productsList"
        id="product_search"
        class="form-input"
        placeholder="Search products..."
        autocomplete="off"
        required
        readonly
    >
    <datalist id="productsList"></datalist>

    <!-- Hidden actual ID -->
    <input type="hidden" id="product_id" name="product_id">
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

      import { fetchHandler, API_FEEDBACK, API_USER, API_PRODUCT } from '../utils.js';

const recordId = Number(new URLSearchParams(window.location.search).get('id'));

const apiUrl = `${API_FEEDBACK}/${recordId}?enriched=true`;


const form = document.getElementById('mainForm');
const errorDiv = document.getElementById('errorDiv');
const successDiv = document.getElementById('successDiv');

const feedbackDetailRating = document.getElementById('feedback_detail_rating');
const feedbackDetailIsactive = document.getElementById('feedback_detail_is_active');
const feedbackDetailIsverifiedpurchase = document.getElementById('feedback_detail_is_verified_purchase');
const feedbackDetailUsername = document.getElementById('feedback_detail_user_name');
const feedbackDetailUseremail = document.getElementById('feedback_detail_user_email');
const feedbackDetailProductname = document.getElementById('feedback_detail_product_name');
const feedbackDetailProductslug = document.getElementById('feedback_detail_product_slug');
const feedbackDetailPurchasecount = document.getElementById('feedback_detail_purchase_count');

const userSearch = document.getElementById('user_search');
const productSearch = document.getElementById('product_search');
const user_idElement = document.getElementById('user_id');
const product_idElement = document.getElementById('product_id');
const ratingElement = document.getElementById('rating');
const commentElement = document.getElementById('comment');
const is_verified_purchaseElement = document.getElementById('is_verified_purchase');
const is_activeElement = document.getElementById('is_active');

let currentAction = null;

const confirmModal = document.getElementById('confirmModal');
const modalTitle = document.getElementById('modalTitle');
const modalMessage = document.getElementById('modalMessage');
const modalCancel = document.getElementById('modalCancel');
const modalConfirm = document.getElementById('modalConfirm');
const softDeleteBtn = document.getElementById('softDeleteBtn');
const hardDeleteBtn = document.getElementById('hardDeleteBtn');

async function populateUsers() {
    const res = await fetchHandler(`${API_USER}?limit=500`, "GET");

    const usersList = document.getElementById("usersList");
    usersList.innerHTML = "";

    res.items.forEach(user => {
        const opt = document.createElement("option");
        const displayText = `${user.name} (${user.email})`;
        opt.value = displayText;
        opt.textContent = displayText;
        opt.dataset.id = user.id;
        usersList.appendChild(opt);
    });
}

async function populateProducts() {
    const res = await fetchHandler(`${API_PRODUCT}?limit=500`, "GET");

    const productsList = document.getElementById("productsList");
    productsList.innerHTML = "";

    res.items.forEach(product => {
        const opt = document.createElement("option");
        const displayText = `${product.name} (${product.slug})`;
        opt.value = displayText;
        opt.textContent = displayText;
        opt.dataset.id = product.id;
        productsList.appendChild(opt);
    });
}

userSearch.addEventListener("input", e => {
    const val = e.target.value;
    const options = [...document.querySelectorAll("#usersList option")];

    const found = options.find(opt => opt.value === val);
    user_idElement.value = found ? found.dataset.id : "";
});

// PRODUCT: convert text -> ID
productSearch.addEventListener("input", e => {
    const val = e.target.value;
    const options = [...document.querySelectorAll("#productsList option")];

    const found = options.find(opt => opt.value === val);
    product_idElement.value = found ? found.dataset.id : "";
});

softDeleteBtn.addEventListener('click', () => {
    currentAction = 'soft';
    modalTitle.textContent = 'Confirm Soft Delete';
    modalMessage.textContent = 'Are you sure? This will deactivate the feedback.';
    confirmModal.style.display = 'flex';
});

hardDeleteBtn.addEventListener('click', () => {
    currentAction = 'hard';
    modalTitle.textContent = 'Confirm Hard Delete';
    modalMessage.textContent = 'Are you sure? This will permanently delete this feedback.';
    confirmModal.style.display = 'flex';
});

modalCancel.addEventListener('click', () => {
    currentAction = null;
    confirmModal.style.display = 'none';
});

modalConfirm.addEventListener('click', async () => {
    confirmModal.style.display = 'none';

    const payload = {
        id: recordId,
        hard: currentAction === 'hard'
    };

    try {
        const res = await fetchHandler(API_FEEDBACK, "DELETE", payload);
        successDiv.textContent = "Feedback deleted successfully!";
        setTimeout(() => {
            window.location.href = "../../index.php";
        }, 1500);
    } catch (err) {
        errorDiv.textContent = err.message || "Failed to delete feedback.";
    }
});

async function loadFeedback() {
    try {
        const data = await fetchHandler(apiUrl, "GET");
        

        document.getElementById("recordId").value = data.id;

        // Fill QUICK VIEW
        feedbackDetailRating.textContent = data.rating;
        feedbackDetailIsactive.textContent = data.is_active ? "Yes" : "No";
        feedbackDetailIsverifiedpurchase.textContent = data.is_verified_purchase ? "Yes" : "No";
        feedbackDetailUsername.textContent = data.user_name || "--";
        feedbackDetailUseremail.textContent = data.user_email || "--";
        feedbackDetailProductname.textContent = data.product_name || "--";
        feedbackDetailProductslug.textContent = data.product_slug || "--";
        feedbackDetailPurchasecount.textContent = data.purchase_count || 0;

        // Fill form
        user_idElement.value = data.user_id;
        product_idElement.value = data.product_id;
        userSearch.value = data.user_name ? `${data.user_name} (${data.user_email})` : "";
        productSearch.value = data.product_name ? `${data.product_name} (${data.product_slug})` : "";
        ratingElement.value = data.rating;
        commentElement.value = data.comment || "";
        is_verified_purchaseElement.checked = data.is_verified_purchase;
        is_activeElement.checked = data.is_active;

        form.scrollIntoView({ behavior: "smooth", block: "start" });

    } catch (err) {
        errorDiv.textContent = err.message || "Failed to load feedback.";
    }
}

form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorDiv.textContent = "";
    successDiv.textContent = "";

    const payload = {
        id: recordId,
        rating: Number(ratingElement.value),
        comment: commentElement.value,
        is_verified_purchase: is_verified_purchaseElement.checked,
        is_active: is_activeElement.checked
    };

    try {
        await fetchHandler(API_FEEDBACK, "PUT", payload);
        successDiv.textContent = "Feedback updated successfully!";
        await loadFeedback();
    } catch (err) {
        errorDiv.textContent = err.message || "Failed to update feedback.";
    }
});

document.addEventListener("DOMContentLoaded", async () => {
    await populateUsers();
    await populateProducts();
    await loadFeedback();
});

    </script>

</body>

</html>