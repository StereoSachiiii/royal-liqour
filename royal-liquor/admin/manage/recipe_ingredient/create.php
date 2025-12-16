<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Recipe Ingredient</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Recipe Ingredient</h1>

        <!-- SUCCESS MODAL -->
        <div id="confirmation_modal" class="confirmation_overlay">
            <div class="confirmation_box">
                <h2>Recipe Ingredient Created</h2>
                <p id="confirmation_message"></p>
                <div class="confirmation_actions">
                    <button id="confirmation_close" class="confirmation_btn">Close</button>
                    <a id="confirmation_view" class="confirmation_btn" href="#">View Ingredient</a>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>New Recipe Ingredient</h2>
            <form id="mainForm">

                <div class="form-group">
                    <label for="recipe_id" class="form-label required">Recipe ID</label>
                    <input list="recipesList" type="text" id="recipe_id" name="recipe_id" class="form-input" required min="1"
                        placeholder="e.g. 12">
                    <datalist id="recipesList"></datalist>
                </div>

                <div class="form-group">
                    <label for="product_id" class="form-label required">Product ID</label>
                    <input list="productsList" type="text" id="product_id" name="product_id" class="form-input" required min="1"
                        placeholder="e.g. 45">
                    <datalist id="productsList"></datalist>
                </div>

                <div class="form-group">
                    <label for="quantity" class="form-label required">Quantity</label>
                    <input type="number" step="0.01" id="quantity" name="quantity" class="form-input" required
                        placeholder="e.g. 250 or 2.5" min="0">
                </div>

                <div class="form-group">
                    <label for="unit" class="form-label required">Unit</label>
                    <input type="text" id="unit" name="unit" class="form-input" required
                        placeholder="e.g. ml, g, tsp, tbsp, piece, clove">
                </div>

                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="is_optional" name="is_optional" class="form-checkbox">
                        <label for="is_optional">This ingredient is optional</label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Ingredient</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script type="module">
        import { API_RECIPE_INGREDIENT, API_PRODUCT, API_COCKTAIL_RECIPES } from '../utils.js';

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');

        const modal = document.getElementById('confirmation_modal');
        const modalMessage = document.getElementById('confirmation_message');
        const modalClose = document.getElementById('confirmation_close');
        const modalView = document.getElementById('confirmation_view');

        modalClose.addEventListener('click', () => modal.style.display = 'none');
        window.addEventListener('click', (e) => {
            if (e.target === modal) modal.style.display = 'none';
        });

        const doFetch = async (url, options = {}) => {
            try {
                const res = await fetch(url, options);
                return await res.json();
            } catch (err) {
                console.error('Fetch error:', err);
                return null;
            }
        };

        const populateSelects = async () => {
            const recipes = await doFetch(`${API_COCKTAIL_RECIPES}`);
            const recipesList = document.getElementById('recipesList');

            if (recipes && recipes.data && recipes.data.items) {
                recipes.data.items.forEach(recipe => {
                    const option = document.createElement('option');
                    option.value = recipe.id;
                    option.textContent = `ID: ${recipe.id} - ${recipe.recipe_name || 'No Name'}`;
                    recipesList.appendChild(option);
                });
            }

            const products = await doFetch(`${API_PRODUCT}`);
            const productsList = document.getElementById('productsList');
            
            if (products && products.data && products.data.items) {
                products.data.items.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = `ID: ${product.id} - ${product.name || 'No Name'}`;
                    productsList.appendChild(option);
                });
            }
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
            successDiv.textContent = '';
            successDiv.style.display = 'none';

            const body = {
                recipe_id: document.getElementById('recipe_id').value.trim(),
                product_id: document.getElementById('product_id').value.trim(),
                quantity: parseFloat(document.getElementById('quantity').value),
                unit: document.getElementById('unit').value.trim(),
                is_optional: document.getElementById('is_optional').checked
            };

            try {
                const response = await doFetch(API_RECIPE_INGREDIENT, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                    console.log(response);
                if (response && response.success) {
                    modalMessage.textContent = `Ingredient successfully added to recipe!`;
                    modalView.href = `../../index.php`;
                    modal.style.display = 'flex';
                    form.reset();
                } else {
                    throw new Error(response?.message || 'Failed to create ingredient');
                }
            } catch (err) {
                errorDiv.textContent = err.message || 'An error occurred while creating the ingredient.';
                errorDiv.style.display = 'block';
            }
        });

        document.addEventListener('DOMContentLoaded', async () => {
            await populateSelects();
        });
    </script>
</body>
</html>
