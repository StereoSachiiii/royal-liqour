<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Cocktail Recipe</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update Cocktail Recipe</h1>

        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>🍹 Cocktail Recipe Overview</h2>
            <div class="modal-content">
                <h2>📋 Recipe Detail</h2>
                <div class="detail-section">
                    <h3>Basic Info</h3>
                    <div class="detail-field"><strong>Name:</strong> <span id="detail_name">--</span></div>
                    <div class="detail-field"><strong>Description:</strong> <span id="detail_description">--</span></div>
                    <div class="detail-field"><strong>Difficulty:</strong> <span id="detail_difficulty">--</span></div>
                    <div class="detail-field"><strong>Prep Time:</strong> <span id="detail_preparation_time">--</span> min</div>
                    <div class="detail-field"><strong>Serves:</strong> <span id="detail_serves">--</span></div>
                    <div class="detail-field"><strong>Active:</strong> <span id="detail_is_active">--</span></div>
                </div>
                <div class="detail-section">
                    <h3>Stats</h3>
                    <div class="detail-field"><strong>Ingredients:</strong> <span id="detail_ingredient_count">--</span></div>
                    <div class="detail-field"><strong>Est. Cost:</strong> <span id="detail_estimated_cost">--</span> LKR</div>
                </div>
                <div class="detail-section">
                    <h3>Instructions</h3>
                    <div class="detail-field" id="detail_instructions" style="white-space: pre-wrap;">--</div>
                </div>
                <div class="detail-section" id="imageSection" style="display:none;">
                    <h3>Current Image</h3>
                    <img id="detail_image" src="" style="max-width:100%; border-radius:8px;">
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>✏️ Edit Cocktail Recipe</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">

                <div class="form-group">
                    <label for="name" class="form-label required">Name</label>
                    <input type="text" id="name" name="name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-input" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="instructions" class="form-label required">Instructions</label>
                    <textarea id="instructions" name="instructions" class="form-input" rows="7" required></textarea>
                </div>

                <div class="form-group">
                    <label for="image_url" class="form-label">Image URL</label>
                    <input type="text" id="image_url" name="image_url" class="form-input" placeholder="https://example.com/image.jpg">
                </div>

                <div class="form-group">
                    <label for="difficulty" class="form-label">Difficulty</label>
                    <select id="difficulty" name="difficulty" class="form-input">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="preparation_time" class="form-label">Preparation Time (min)</label>
                    <input type="number" id="preparation_time" name="preparation_time" class="form-input">
                </div>

                <div class="form-group">
                    <label for="serves" class="form-label">Serves</label>
                    <input type="number" id="serves" name="serves" class="form-input" min="1">
                </div>

                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="is_active" name="is_active" class="form-checkbox">
                        <label for="is_active">Is Active</label>
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

    <!-- MODALS -->
    <div id="confirmModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle">Confirm Delete</div>
            <div class="modal-body" id="modalMessage"></div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-danger">Confirm</button>
            </div>
        </div>
    </div>

    <div id="successModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header">Recipe Updated Successfully</div>
            <div class="modal-body" id="successModalMessage"></div>
            <div class="modal-actions">
                <button id="successModalStay" class="btn-secondary">Stay on Page</button>
                <button id="successModalReturn" class="btn-primary">Return to List</button>
            </div>
        </div>
    </div>

    <script type="module">
        const API_COCKTAIL_RECIPES = '/royal-liquor/api/v1/cocktail-recipes';

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        if (!recordId) {
            alert('No recipe ID provided');
            window.location.href = '../../index.php';
        }

        const loadUrl = `${API_COCKTAIL_RECIPES}/${recordId}?enriched=true`;
        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const modal = document.getElementById('confirmModal');
        const successModal = document.getElementById('successModal');
        const el = id => document.getElementById(id);

        let currentAction = null;

        const q = {
            name: el('detail_name'),
            description: el('detail_description'),
            difficulty: el('detail_difficulty'),
            preparationTime: el('detail_preparation_time'),
            serves: el('detail_serves'),
            isActive: el('detail_is_active'),
            ingredientCount: el('detail_ingredient_count'),
            estimatedCost: el('detail_estimated_cost'),
            instructions: el('detail_instructions'),
            image: el('detail_image'),
            imageSection: el('imageSection')
        };

        async function loadRecipe() {
            try {
                const response = await fetch(loadUrl, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('Failed to load recipe');
                
                const json = await response.json();
                const data = json.data?.data ?? json.data ?? json;

                // Quick view
                q.name.textContent = data.name;
                q.description.textContent = data.description || 'No description';
                q.difficulty.textContent = data.difficulty;
                q.preparationTime.textContent = data.preparation_time || '--';
                q.serves.textContent = data.serves;
                q.isActive.textContent = data.is_active ? 'Yes' : 'No';
                q.ingredientCount.textContent = data.ingredient_count || 0;
                q.estimatedCost.textContent = ((data.estimated_cost_cents || 0)/100).toFixed(2);
                q.instructions.textContent = data.instructions || 'No instructions';

                if (data.image_url) {
                    q.image.src = data.image_url;
                    q.imageSection.style.display = 'block';
                }

                // Form
                el('recordId').value = data.id;
                el('name').value = data.name;
                el('description').value = data.description || '';
                el('instructions').value = data.instructions || '';
                el('image_url').value = data.image_url || '';
                el('difficulty').value = data.difficulty;
                el('preparation_time').value = data.preparation_time || '';
                el('serves').value = data.serves || 1;
                el('is_active').checked = data.is_active;

            } catch (err) {
                errorDiv.textContent = 'Failed to load recipe: ' + (err.message || 'Unknown error');
                errorDiv.style.display = 'block';
            }
        }

        form.addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
            successDiv.textContent = '';
            successDiv.style.display = 'none';

            const payload = {
                id: recordId,
                name: el('name').value.trim(),
                description: el('description').value.trim() || null,
                instructions: el('instructions').value.trim(),
                image_url: el('image_url').value.trim() || null,
                difficulty: el('difficulty').value,
                preparation_time: el('preparation_time').value ? Number(el('preparation_time').value) : null,
                serves: el('serves').value ? Number(el('serves').value) : 1,
                is_active: el('is_active').checked
            };

            try {
                const response = await fetch(`${API_COCKTAIL_RECIPES}/${recordId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(text || 'Failed to update recipe');
                }

                const json = await response.json();
                const data = json.data ?? json;
                
                el('successModalMessage').innerHTML = `
                    <div style="text-align: left;">
                        <p><strong>Updated Recipe:</strong> ${data.name}</p>
                        <p><strong>Difficulty:</strong> ${data.difficulty}</p>
                        <p><strong>Serves:</strong> ${data.serves}</p>
                    </div>
                `;
                successModal.style.display = 'flex';
                await loadRecipe();

            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to update recipe';
                errorDiv.style.display = 'block';
            }
        });

        el('softDeleteBtn').onclick = () => {
            currentAction = 'soft';
            el('modalTitle').textContent = 'Soft Delete Recipe';
            el('modalMessage').textContent = 'This will hide the recipe from the site. Reversible.';
            modal.style.display = 'flex';
        };

        el('hardDeleteBtn').onclick = () => {
            currentAction = 'hard';
            el('modalTitle').textContent = 'Permanently Delete Recipe';
            el('modalMessage').textContent = 'This action cannot be undone.';
            modal.style.display = 'flex';
        };

        el('modalCancel').onclick = () => modal.style.display = 'none';

        el('modalConfirm').onclick = async () => {
            modal.style.display = 'none';
            try {
                const response = await fetch(`${API_COCKTAIL_RECIPES}/${recordId}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ id: recordId, hard: currentAction === 'hard' })
                });
                if (!response.ok) throw new Error('Delete failed');
                
                successDiv.textContent = currentAction === 'hard' ? 'Permanently deleted!' : 'Recipe hidden!';
                successDiv.style.display = 'block';
                setTimeout(() => location.href = '../../index.php', 1500);
            } catch (err) {
                errorDiv.textContent = err.message || 'Delete failed';
                errorDiv.style.display = 'block';
            }
        };

        el('successModalStay').onclick = () => { successModal.style.display = 'none'; };
        el('successModalReturn').onclick = () => { location.href = '../../index.php'; };

        loadRecipe().then(() => { 
            form.scrollIntoView({ behavior: 'smooth', block: 'start' }); 
        });
    </script>
</body>
</html>