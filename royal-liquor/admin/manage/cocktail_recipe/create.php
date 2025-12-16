<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Cocktail Recipe</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Cocktail Recipe</h1>

        <div id="errorDiv" class="error-message"></div>

        <div class="edit-card">
            <h2>➕ New Cocktail Recipe</h2>
            <form id="mainForm">
                
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
                    <label for="image_file" class="form-label">Upload Image</label>
                    <input type="file" id="image_file" name="image_file" class="form-input" accept="image/*">
                    <p id="fileNameDisplay" style="margin-top: 5px; font-size: 0.9em; color: #555;"></p>
                </div>
                <input type="hidden" id="image_url" name="image_url" value="">

                <div class="form-group">
                    <label for="difficulty" class="form-label">Difficulty</label>
                    <select id="difficulty" name="difficulty" class="form-input">
                        <option value="easy" selected>Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="preparation_time" class="form-label">Preparation Time (minutes)</label>
                    <input type="number" id="preparation_time" name="preparation_time" class="form-input" min="1">
                </div>

                <div class="form-group">
                    <label for="serves" class="form-label">Serves</label>
                    <input type="number" id="serves" name="serves" class="form-input" min="1" value="1">
                </div>

                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="is_active" name="is_active" class="form-checkbox" checked>
                        <label for="is_active">Is Active</label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Recipe</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div id="successModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header">Cocktail Recipe Created Successfully</div>
            <div class="modal-body" id="successModalMessage"></div>
            <div class="modal-actions">
                <button id="successModalClose" class="btn-secondary">Create Another</button>
                <button id="successModalView" class="btn-primary">View Recipe</button>
            </div>
        </div>
    </div>

    <script type="module">
        const API_COCKTAIL_RECIPES = '/royal-liquor/api/v1/cocktail-recipes';
       // const API_IMAGE_UPLOAD = 'http://localhost/royal-liquor/admin/api/image-upload.php'; 

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successModal = document.getElementById('successModal');
        const el = id => document.getElementById(id);

        let createdRecipeId = null;

     
        async function uploadImageAndGetURL(imageFile) {
            // Check if a file was selected
            if (!imageFile) return null;

            // --- COMMENTED OUT IMAGE FETCH SETUP ---
            /*
            const formData = new FormData();
            formData.append('cocktail_image', imageFile);

            try {
                // Replace this with your actual image upload API call
                const res = await fetch(API_IMAGE_UPLOAD, {
                    method: 'POST',
                    body: formData // Note: Do not manually set Content-Type header when using FormData
                });

                if (!res.ok) throw new Error(await res.text() || 'Image upload failed');

                const json = await res.json();
                // Assuming the API returns a JSON object with the URL in a 'url' field
                return json.url; 
            } catch (error) {
                console.error('Image Upload Error:', error);
                // Important: Throw the error so the main form submission stops
                throw new Error('Image upload failed: ' + error.message);
            }
            */

            console.log("Simulating image upload for:", imageFile.name);
            return 'http://example.com/uploaded/' + imageFile.name.replace(/\s/g, '_'); 
        }


        form.addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
            const imageFile = el('image_file').files[0];

            try {
          
               // const imageUrl = await uploadImageAndGetURL(imageFile); 
                
                const data = {
                    name: el('name').value.trim(),
                    description: el('description').value.trim() || null,
                    instructions: el('instructions').value.trim(),
                    image_url:null,// imageUrl, 
                    difficulty: el('difficulty').value,
                    preparation_time: el('preparation_time').value ? Number(el('preparation_time').value) : null,
                    serves: el('serves').value ? Number(el('serves').value) : 1,
                    is_active: el('is_active').checked
                };
                
                const res = await fetch(API_COCKTAIL_RECIPES, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });

                if (!res.ok) throw new Error(await res.text() || 'Failed to create recipe');

                const json = await res.json();
                const recipe = json.data?.data ?? json.data ?? json;
                createdRecipeId = recipe.id;

                el('successModalMessage').innerHTML = `
                    <p><strong>${recipe.name}</strong> created!</p>
                    <p>Difficulty: <strong>${recipe.difficulty}</strong> • Serves: ${recipe.serves}</p>
                `;
                successModal.style.display = 'flex';

            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to create recipe';
                errorDiv.style.display = 'block';
            }
        });

        el('image_file').addEventListener('change', (e) => {
            const fileNameDisplay = el('fileNameDisplay');
            if (e.target.files.length > 0) {
                fileNameDisplay.textContent = `Selected file: ${e.target.files[0].name}`;
            } else {
                fileNameDisplay.textContent = '';
            }
        });

        el('successModalClose').onclick = () => {
            successModal.style.display = 'none';
            form.reset();
            el('fileNameDisplay').textContent = ''; 
        };

        el('successModalView').onclick = () => {
            location.href = `update.php?id=${createdRecipeId}`;
        };
    </script>
</body>
</html>