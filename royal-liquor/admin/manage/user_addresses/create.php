<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User Address</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create User Address</h1>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>New User Address</h2>
            <form id="mainForm">
                <div class="form-group">
                    <label for="user_search" class="form-label required">User</label>
                    <input list="usersList" id="user_search" class="form-input" placeholder="Search user by name/email..." required autocomplete="off">
                    <datalist id="usersList"></datalist>
                    <input type="hidden" id="user_id" name="user_id">
                </div>

                <div class="form-group">
                    <label for="address_type" class="form-label">Address Type</label>
                    <select id="address_type" name="address_type" class="form-input">
                        <option value="both">Both (Shipping + Billing)</option>
                        <option value="shipping">Shipping Only</option>
                        <option value="billing">Billing Only</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="recipient_name" class="form-label required">Recipient Name</label>
                    <input type="text" id="recipient_name" name="recipient_name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label required">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-input" required placeholder="+94 77 123 4567">
                </div>

                <div class="form-group">
                    <label for="address_line1" class="form-label required">Address Line 1</label>
                    <input type="text" id="address_line1" name="address_line1" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="address_line2" class="form-label">Address Line 2</label>
                    <input type="text" id="address_line2" name="address_line2" class="form-input">
                </div>

                <div class="form-group">
                    <label for="city" class="form-label required">City</label>
                    <input type="text" id="city" name="city" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="state" class="form-label">State / Province</label>
                    <input type="text" id="state" name="state" class="form-input">
                </div>

                <div class="form-group">
                    <label for="postal_code" class="form-label required">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="country" class="form-label required">Country</label>
                    <input type="text" id="country" name="country" class="form-input" value="Sri Lanka" required>
                </div>

                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="is_default" name="is_default" class="form-checkbox">
                        <label for="is_default">Make this the default address</label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Address</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <h2>Address Created Successfully!</h2>
            <p>New address has been added.</p>
            <div class="modal-actions">
                <button id="modalClose" class="btn-secondary">Close</button>
                <a id="viewLink" class="btn-primary">View Address</a>
            </div>
        </div>
    </div>

    <script type="module">
        import { fetchHandler, API_USER, API_USER_ADDRESS } from '../utils.js';

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const modal = document.getElementById('successModal');
        const viewLink = document.getElementById('viewLink');
        const modalClose = document.getElementById('modalClose');

        async function populateUsers() {
            const res = await fetchHandler(`${API_USER}?limit=500`, 'GET');
            const datalist = document.getElementById('usersList');
            datalist.innerHTML = '';
            res.items.forEach(u => {
                const opt = document.createElement('option');
                opt.value = `${u.name} (${u.email})`;
                opt.textContent = opt.value;
                opt.dataset.id = u.id;
                datalist.appendChild(opt);
            });
        }

        document.getElementById('user_search').addEventListener('input', e => {
            const val = e.target.value;
            const found = [...document.querySelectorAll('#usersList option')].find(o => o.value === val);
            document.getElementById('user_id').value = found ? found.dataset.id : '';
        });

        modalClose.onclick = () => modal.style.display = 'none';
        window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorDiv.textContent = '';
            successDiv.textContent = '';

            const payload = {
                user_id: Number(document.getElementById('user_id').value),
                address_type: document.getElementById('address_type').value || 'both',
                recipient_name: document.getElementById('recipient_name').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                address_line1: document.getElementById('address_line1').value.trim(),
                address_line2: document.getElementById('address_line2').value.trim(),
                city: document.getElementById('city').value.trim(),
                state: document.getElementById('state').value.trim(),
                postal_code: document.getElementById('postal_code').value.trim(),
                country: document.getElementById('country').value.trim(),
                is_default: document.getElementById('is_default').checked
            };

            if (!payload.user_id) {
                errorDiv.textContent = 'Please select a valid user';
                return;
            }

           try {
    const result = await fetchHandler(API_USER_ADDRESS, 'POST', payload);

    successDiv.textContent = 'Address created successfully!';
    viewLink.href = `../../index.php?id=${result.id}`;  // ← fixed
    modal.style.display = 'flex';

    form.reset();
    document.getElementById('user_search').value = '';
    document.getElementById('user_id').value = '';

} catch (err) {
    errorDiv.textContent = err.message || 'Failed to create address';
}
        });

        populateUsers();
    </script>
</body>
</html>