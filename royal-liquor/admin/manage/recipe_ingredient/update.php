<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Recipe Ingredient</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update Recipe Ingredient</h1>

        <!-- SUCCESS MODAL – same as Create page -->
        <div id="confirmation_modal" class="confirmation_overlay" style="display:none;">
            <div class="confirmation_box">
                <h2>Success</h2>
                <p id="confirmation_message"></p>
                <div class="confirmation_actions">
                    <button id="confirmation_close" class="confirmation_btn">Close</button>
                    <a id="confirmation_view" class="confirmation_btn" href="../../index.php">Back to List</a>
                </div>
            </div>
        </div>

        <!-- DELETE CONFIRM MODAL -->
        <div id="confirmModal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <div class="modal-header" id="modalTitle"></div>
                <div class="modal-body" id="modalMessage"></div>
                <div class="modal-actions">
                    <button id="modalCancel" class="btn-secondary">Cancel</button>
                    <button id="modalConfirm" class="btn-danger">Confirm</button>
                </div>
            </div>
        </div>

        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>Recipe Ingredient Overview</h2>
            <div id="quickViewContent">
                <div class="modal-content">
                    <h2>Recipe Ingredient Detail:</h2>
                    <div class="detail-section"><h3>Basic Info</h3>
                        <div class="detail-field"><strong>Quantity:</strong> <span id="recipe_ingredient_detail_quantity"></span></div>
                        <div class="detail-field"><strong>Unit:</strong> <span id="recipe_ingredient_detail_unit"></span></div>
                        <div class="detail-field"><strong>Is Optional:</strong> <span id="recipe_ingredient_detail_is_optional"></span></div>
                    </div>
                    <div class="detail-section"><h3>Recipe Info</h3>
                        <div class="detail-field"><strong>Recipe Name:</strong> <span id="recipe_ingredient_detail_recipe_name"></span></div>
                        <div class="detail-field"><strong>Recipe Difficulty:</strong> <span id="recipe_ingredient_detail_recipe_difficulty"></span></div>
                    </div>
                    <div class="detail-section"><h3>Product Info</h3>
                        <div class="detail-field"><strong>Product Name:</strong> <span id="recipe_ingredient_detail_product_name"></span></div>
                        <div class="detail-field"><strong>Product Price (Cents):</strong> <span id="recipe_ingredient_detail_product_price_cents"></span></div>
                    </div>
                    <div class="detail-section"><h3>Cost</h3>
                        <div class="detail-field"><strong>Ingredient Cost (Cents):</strong> <span id="recipe_ingredient_detail_ingredient_cost_cents"></span></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>

        <div class="edit-card">
            <h2>Edit Recipe Ingredient</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">

                <div class="form-group">
                    <label for="recipe_id" class="form-label required">Recipe ID</label>
                    <input list="recipesList" type="text" id="recipe_id" name="recipe_id" class="form-input" required min="1">
                    <datalist id="recipesList"></datalist>
                </div>

                <div class="form-group">
                    <label for="product_id" class="form-label required">Product ID</label>
                    <input list="productsList" type="text" id="product_id" name="product_id" class="form-input" required min="1">
                    <datalist id="productsList"></datalist>
                </div>

                <div class="form-group">
                    <label for="quantity" class="form-label required">Quantity</label>
                    <input type="number" step="0.01" id="quantity" name="quantity" class="form-input" required min="0">
                </div>

                <div class="form-group">
                    <label for="unit" class="form-label required">Unit</label>
                    <input type="text" id="unit" name="unit" class="form-input" required>
                </div>

                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="is_optional" name="is_optional" class="form-checkbox">
                        <label for="is_optional">Is Optional</label>
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

    <script type="module">
        import { API_PRODUCT, API_COCKTAIL_RECIPES, API_RECIPE_INGREDIENT } from '../utils.js';

        const urlParams = new URLSearchParams(window.location.search);
        const recordId = Number(urlParams.get('id'));
        if (!recordId) { alert('No ID'); location.href = '../../index.php'; }

        const form         = document.getElementById('mainForm');
        const errorDiv     = document.getElementById('errorDiv');

        // Success modal (same as Create page)
        const successModal = document.getElementById('confirmation_modal');
        const successMsg   = document.getElementById('confirmation_message');
        const successClose = document.getElementById('confirmation_close');
        const successView  = document.getElementById('confirmation_view');

        // Delete confirm modal
        const confirmModal = document.getElementById('confirmModal');
        const modalTitle   = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalCancel  = document.getElementById('modalCancel');
        const modalConfirm = document.getElementById('modalConfirm');

        // Close handlers
        successClose.onclick = () => successModal.style.display = 'none';
        modalCancel.onclick  = () => confirmModal.style.display = 'none';
        window.onclick = e => {
            if (e.target === successModal) successModal.style.display = 'none';
            if (e.target === confirmModal) confirmModal.style.display = 'none';
        };

        const doFetch = async (url, options = {}) => {
            try {
                const res = await fetch(url, options);
                return await res.json();
            } catch (err) {
                console.error(err);
                return null;
            }
        };

        const populateDatalists = async () => {
            const recipes = await doFetch(`${API_COCKTAIL_RECIPES}`);
            const recipesList = document.getElementById('recipesList');
            recipes?.data?.items?.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = `ID: ${r.id} - ${r.recipe_name || 'No name'}`;
                recipesList.appendChild(opt);
            });

            const products = await doFetch(`${API_PRODUCT}`);
            const productsList = document.getElementById('productsList');
            products?.data?.items.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `ID: ${p.id} - ${p.name || 'No name'}`;
                productsList.appendChild(opt);
            });
        };

        const loadData = async () => {
            const res = await doFetch(`${API_RECIPE_INGREDIENT}/${recordId}?enriched=true`);
            if (!res?.data) {
                errorDiv.textContent = 'Ingredient not found';
                errorDiv.style.display = 'block';
                return;
            }
            const i = res.data.data;
            console.log(i);

            document.getElementById('recordId').value = i.id;
            document.getElementById('recipe_id').value = i.recipe_id;
            document.getElementById('product_id').value = i.product_id;
            document.getElementById('quantity').value = i.quantity;
            document.getElementById('unit').value = i.unit;
            document.getElementById('is_optional').checked = !!i.is_optional;

            // Quick view
            document.getElementById('recipe_ingredient_detail_quantity').textContent = i.quantity;
            document.getElementById('recipe_ingredient_detail_unit').textContent = i.unit;
            document.getElementById('recipe_ingredient_detail_is_optional').textContent = i.is_optional ? 'Yes' : 'No';
            document.getElementById('recipe_ingredient_detail_recipe_name').textContent = i.recipe_name || '-';
            document.getElementById('recipe_ingredient_detail_recipe_difficulty').textContent = i.recipe_difficulty || '-';
            document.getElementById('recipe_ingredient_detail_product_name').textContent = i.product_name || '-';
            document.getElementById('recipe_ingredient_detail_product_price_cents').textContent = i.product_price_cents || '-';
            document.getElementById('recipe_ingredient_detail_ingredient_cost_cents').textContent = i.ingredient_cost_cents || '-';
        };

        // UPDATE
        form.addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.style.display = 'none';

            const payload = {
                id: recordId,
                recipe_id: document.getElementById('recipe_id').value.trim(),
                product_id: document.getElementById('product_id').value.trim(),
                quantity: parseFloat(document.getElementById('quantity').value),
                unit: document.getElementById('unit').value.trim(),
                is_optional: Boolean(document.getElementById('is_optional').checked)
            };

            const res = await doFetch(`${API_RECIPE_INGREDIENT}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res?.success) {
                successMsg.textContent = 'Recipe ingredient updated successfully!';
                successModal.style.display = 'flex';
                await loadData(); // refresh quick view
            } else {
                errorDiv.textContent = res?.message || 'Update failed';
                errorDiv.style.display = 'block';
            }
        });

        // DELETE
        let deleteAction = null;
        document.getElementById('softDeleteBtn').onclick = () => {
            deleteAction = 'soft_delete';
            modalTitle.textContent = 'Soft Delete';
            modalMessage.textContent = 'This will deactivate the ingredient. Continue?';
            confirmModal.style.display = 'flex';
        };
        document.getElementById('hardDeleteBtn').onclick = () => {
            deleteAction = 'hard_delete';
            modalTitle.textContent = 'Hard Delete';
            modalMessage.textContent = 'This will permanently delete the ingredient. Continue?';
            confirmModal.style.display = 'flex';
        };

        modalConfirm.onclick = async () => {
            confirmModal.style.display = 'none';
            const res = await doFetch(`${API_RECIPE_INGREDIENT}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: recordId })
            });

            if (res?.success) {
                successMsg.textContent = 'Ingredient deleted!';
                successModal.style.display = 'flex';
                setTimeout(() => location.href = '../../index.php', 1500);
            } else {
                errorDiv.textContent = res?.message || 'Delete failed';
                errorDiv.style.display = 'block';
            }
        };

        // Init
        document.addEventListener('DOMContentLoaded', async () => {
            await populateDatalists();
            await loadData();
        });
    </script>
</body>
</html>