<?php 

$id = intval($_GET['id']);


?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage User</title>
<link rel="stylesheet" href="../index.css">
</head>
<body>

<div class="container">
    <h2>Manage User</h2>
    <h6>
        update the users details
    </h6>
    <form id="update-user-form">
        <div class="field">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" placeholder="Current: John Doe" >
        </div>

        <div class="field">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Current: john@example.com" >
        </div>

        <div class="field">
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" placeholder="Current: 1234567890">
        </div>
        
        <label for="privilages"> Privilages </label>
        <fieldset name="privilages" class="privilages">
            <div class="field">
                <label><input type="checkbox" id="is_active" name="is_active"> Active</label>
            </div>
            <div class="field">
                <label><input type="checkbox" id="is_admin" name="is_admin"> Admin</label>
            </div>
        </fieldset>

        <div class="actions">
            <button type="submit" class="update-btn">Update User</button>
            <button type="button" class="delete-btn" id="delete-user-btn">Delete User</button>
        </div>      
    </form>
</div>

<!-- Confirmation Modal -->
<div class="modal" id="confirm-modal">
    <div class="modal-content">
        <span class="modal-close" id="confirm-close">&times;</span>
        <h3>Confirm Delete</h3>
        <p id="confirm-message">Are you sure you want to delete this user?</p>
        <div class="modal-actions">
            <button class="delete-btn" id="confirm-yes">Yes</button>
            <button class="cancel-btn" id="confirm-no">No</button>
        </div>
    </div>
</div>

<script>
const confirmModal = document.getElementById('confirm-modal');
const confirmClose = document.getElementById('confirm-close');
const confirmYes = document.getElementById('confirm-yes');
const confirmNo = document.getElementById('confirm-no');
const deleteBtn = document.getElementById('delete-user-btn');
const updateBtn = document.querySelector('.update-btn')
const form = document.querySelector('#update-user-form')

function showConfirm(message, callback) {
    document.getElementById('confirm-message').textContent = message;
    confirmModal.classList.add('active');

    const yesHandler = () => { callback(true); closeConfirm(); };
    const noHandler = () => { callback(false); closeConfirm(); };

    confirmYes.addEventListener('click', yesHandler, { once: true });
    confirmNo.addEventListener('click', noHandler, { once: true });
}

function closeConfirm() { confirmModal.classList.remove('active'); }

confirmClose.addEventListener('click', closeConfirm);
confirmModal.addEventListener('click', e => { if(e.target === confirmModal) closeConfirm(); });

deleteBtn.addEventListener('click', () => {
    showConfirm('Are you sure you want to delete this user?', result => {
        if(result) console.log('User deleted via API call');
    });
});

//handle submit
form.addEventListener('submit', function (e) {
    e.preventDefault();

    let formdata = new FormData(this)
    let name = formdata.get('name')?? '';
    let email = formdata.get('email')?? '';
    let phoneNumber = formdata.get('phone')?? '';


    let message =  name||email||phoneNumber ? `You will  add the following information to the existing record ${name} ${email} ${phoneNumber}` : false;

   
    


});


document.addEventListener('DOMContentLoaded', async () => {
    const userId = <?= $id ?>;
    const API_URL = `http://localhost/royal-liquor/admin/api/users.php?action=getUserById&id=${userId}`;

    try {
        const res = await fetch(API_URL, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include'
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);

        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed to fetch user');

        const user = data.user;

        // Populate form placeholders and checkboxes
        document.getElementById('name').placeholder = `Current: ${user.name}`;
        document.getElementById('email').placeholder = `Current: ${user.email}`;
        document.getElementById('phone').placeholder = `Current: ${user.phone || '-'}`;
        document.getElementById('is_active').checked = !!user.is_active;
        document.getElementById('is_admin').checked = !!user.is_admin;

    } catch (err) {
        console.error('Error fetching user details:', err);
    }
});


</script>

</body>
</html>
