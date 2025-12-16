<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container users-edit">
        <h1>Create User</h1>
        
        <!-- CREATE CONFIRMATION MODAL -->
        <div id="confirmation_modal" class="confirmation_overlay">
            <div class="confirmation_box">
                <h2>User Created</h2>
                <p id="confirmation_message"></p>

                <div class="confirmation_actions">
                    <button id="confirmation_close" class="confirmation_btn">Close</button>
                    <a id="confirmation_view" class="confirmation_btn" href="#">View User</a>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>
        
        <div class="edit-card">
            <h2>➕ New User</h2>
            <form id="mainForm">
                
                <div class="form-group">
                    <label for="name" class="form-label required">Name</label>
                    <input type="text" id="name" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-input" placeholder="+94 77 123 4567">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label required">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required minlength="8">
                </div>
                
                <div class="form-group">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <input type="file" id="profile_image" name="profile_image" class="form-file" accept="image/*">
                    <input type="hidden" id="profileImageUrl" name="profileImageUrl">
                    <div id="imagePreview" class="file-preview"></div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-inline">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="is_active" name="is_active" class="form-checkbox" checked>
                            <label for="is_active">Is Active</label>
                        </div>
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="is_admin" name="is_admin" class="form-checkbox">
                            <label for="is_admin">Is Admin</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create User</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script type="module">
        import { fetchHandler, API_USER } from '../utils.js';

        const nameElement = document.getElementById('name');
        const emailElement = document.getElementById('email');  
        const phoneElement = document.getElementById('phone');
        const passwordElement = document.getElementById('password');
        const isActiveElement = document.getElementById('is_active');
        const isAdminElement = document.getElementById('is_admin');
        const profileInput = document.getElementById('profile_image');
        const imagePreview = document.getElementById('imagePreview');
        const profileImageUrlInput = document.getElementById('profileImageUrl');

        let uploadedProfileImageUrl = null;

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');   

        const modal = document.getElementById('confirmation_modal');
        const modalMsg = document.getElementById('confirmation_message');
        const modalClose = document.getElementById('confirmation_close');
        const modalView = document.getElementById('confirmation_view');

        async function uploadProfileImage(file) {
            const fd = new FormData();
            fd.append('entity', 'user');
            fd.append('image', file);

            const res = await fetch('/admin/api/images.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (!data || !data.success || !data.data || !data.data.url) {
                throw new Error(data?.message || 'Failed to upload profile image');
            }

            return data.data.url;
        }

        profileInput.addEventListener('change', async (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            errorDiv.style.display = 'none';
            errorDiv.textContent = '';

            try {
                uploadedProfileImageUrl = await uploadProfileImage(file);

                const reader = new FileReader();
                reader.onload = () => {
                    imagePreview.innerHTML = `<img src="${reader.result}" alt="Profile preview" style="max-width:120px; border-radius:8px;" />`;
                };
                reader.readAsDataURL(file);
                profileImageUrlInput.value = uploadedProfileImageUrl;
            } catch (err) {
                console.error('Image upload failed:', err);
                uploadedProfileImageUrl = null;
                imagePreview.innerHTML = '';
                errorDiv.textContent = err.message || 'Failed to upload profile image.';
                errorDiv.style.display = 'block';
            }
        });

        function showConfirmationModal(user) {
            modalMsg.textContent = `User "${user.name}" was created successfully!`;
            modalView.href = `/admin/users/view.html?id=${user.id}`;
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
                name: nameElement.value,
                email: emailElement.value,
                phone: phoneElement.value,
                password: passwordElement.value,
                is_active: isActiveElement.checked,
                is_admin: isAdminElement.checked,
                profileImageUrl: uploadedProfileImageUrl || undefined
            };

            try {
                const apiUrl = `${API_USER}?action=register`;
                console.log('Submitting to:', apiUrl);
                
                const response = await fetchHandler(apiUrl, 'POST', body);
                
                // Check if response indicates success
                if (response && response.success && response.data) {
                    showConfirmationModal(response.data);
                    form.reset();
                } else {
                    throw new Error(response.message || 'Failed to create user');
                }
                
            } catch (error) {
                console.error('Error creating user:', error);
                errorDiv.textContent = error.message || 'An error occurred while creating the user.';
                errorDiv.style.display = 'block';
            }
        }); 
    </script>
</body>
</html>