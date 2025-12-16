import { fetchRecipeIngredients, fetchModalDetails } from "./RecipeIngredients.utils.js";
import { renderRecipeIngredientEdit, initRecipeIngredientEditHandlers } from "./RecipeIngredientEdit.js";
import { renderRecipeIngredientCreate, initRecipeIngredientCreateHandlers } from "./RecipeIngredientCreate.js";
import { escapeHtml, formatDate, openStandardModal, debounce } from "../../utils.js";

const DEFAULT_LIMIT = 20;
let currentOffset = 0;
let currentQuery = '';
let lastResults = [];

function renderRecipeIngredientRow(item) {
    return `
        <tr data-id="${item.id}">
            <td>${item.id}</td>
            <td>${escapeHtml(item.recipe_name || '-')}</td>
            <td>${escapeHtml(item.product_name || '-')}</td>
            <td>${item.quantity} ${escapeHtml(item.unit || '')}</td>
            <td>
                <span class="badge ${item.is_optional ? 'badge-warning' : 'badge-active'}">
                    ${item.is_optional ? 'Optional' : 'Required'}
                </span>
            </td>
            <td>
                <button class="btn btn-outline btn-sm recipe-ingredient-view" data-id="${item.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm recipe-ingredient-edit" data-id="${item.id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

async function loadMoreRecipeIngredients() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const ingredients = await fetchRecipeIngredients(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (ingredients.error) {
            return `<tr><td colspan="6" class="admin-entity__empty">Error: ${escapeHtml(ingredients.error)}</td></tr>`;
        }

        if (ingredients.length === 0) {
            return `<tr><td colspan="6" class="admin-entity__empty">No more recipe ingredients to load</td></tr>`;
        }

        lastResults = [...lastResults, ...ingredients];
        return ingredients.map(renderRecipeIngredientRow).join('');
    } catch (error) {
        console.error('Error loading more recipe ingredients:', error);
        return `<tr><td colspan="6" class="admin-entity__empty">Failed to load recipe ingredients</td></tr>`;
    }
}

export const RecipeIngredients = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const ingredients = await fetchRecipeIngredients(DEFAULT_LIMIT, currentOffset, currentQuery);

        lastResults = ingredients.error ? [] : (Array.isArray(ingredients) ? ingredients : []);
        const hasData = lastResults.length > 0;
        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        const tableRows = hasData
            ? lastResults.map(renderRecipeIngredientRow).join('')
            : `<tr><td colspan="6" class="admin-entity__empty">${ingredients.error ? escapeHtml(ingredients.error) : 'No recipe ingredients found'}</td></tr>`;

        return `
            <div class="admin-entity">
                <div class="admin-entity__header">
                    <h2 class="admin-entity__title">Recipe Ingredients (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="recipe_ingredients-search-input" class="admin-entity__search" type="search" 
                               placeholder="Search by product name..." value="${escapeHtml(currentQuery)}" />
                        <button id="recipe_ingredients-create-btn" class="btn btn-primary">Add Ingredient</button>
                        <button id="recipe_ingredients_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Recipe</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recipe_ingredients-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="recipe_ingredients_load-more-wrapper" style="text-align:center; margin-top: var(--space-4);">
                    ${hasData && lastResults.length === DEFAULT_LIMIT ? `<button id="recipe_ingredients_load-more-btn" class="btn btn-outline btn-sm">Load More Ingredients</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering recipe ingredients table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> Failed to load recipe ingredients table</div></div>`;
    }
};

// View modal content (matching Feedback styling)
const ingredientDetailsHtml = (item) => {
    if (!item) return '<div class="admin-entity__empty">No ingredient data</div>';

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Recipe Ingredient Details</span>
                <span class="badge ${item.is_optional ? 'badge-warning' : 'badge-active'}">${item.is_optional ? 'Optional' : 'Required'}</span>
            </div>
            <div class="products_section-title">Product Information</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>ID</strong><span>${item.id ?? 'N/A'}</span></div>
                <div class="products_field"><strong>Product Name</strong><span>${escapeHtml(item.product_name || '-')}</span></div>
                <div class="products_field"><strong>Product ID</strong><span>${item.product_id ?? '-'}</span></div>
                ${item.product_price_cents ? `<div class="products_field"><strong>Product Price</strong><span>₹${(item.product_price_cents / 100).toFixed(2)}</span></div>` : ''}
            </div>
            <div class="products_section-title">Recipe & Quantity</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Recipe Name</strong><span>${escapeHtml(item.recipe_name || '-')}</span></div>
                <div class="products_field"><strong>Recipe ID</strong><span>${item.recipe_id ?? '-'}</span></div>
                <div class="products_field"><strong>Quantity</strong><span>${item.quantity} ${escapeHtml(item.unit || '')}</span></div>
                <div class="products_field"><strong>Optional</strong><span>${item.is_optional ? 'Yes' : 'No'}</span></div>
            </div>
            <div class="products_section-title">Timeline</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Created At</strong><span>${formatDate(item.created_at)}</span></div>
            </div>
            <div class="products_footer">
                <button class="btn btn-primary recipe-ingredient-edit" data-id="${item.id}">Edit Ingredient</button>
            </div>
        </div>
    `;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(Number(id));
        if (result.error) throw new Error(result.error);
        if (!result.recipe_ingredient) throw new Error('No ingredient data returned from API');
        return ingredientDetailsHtml(result.recipe_ingredient);
    } catch (error) {
        console.error('[RecipeIngredients] Render modal error:', error);
        throw new Error(error.message || 'Failed to load ingredient details');
    }
};

// Attach delegated handlers
(() => {
    async function performSearch(query) {
        try {
            currentQuery = query || '';
            currentOffset = 0;
            const results = await fetchRecipeIngredients(DEFAULT_LIMIT, 0, currentQuery);

            lastResults = results.error ? [] : (Array.isArray(results) ? results : []);
            const hasData = lastResults.length > 0;
            const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

            const titleEl = document.querySelector('.admin-entity__title');
            if (titleEl) titleEl.textContent = `Recipe Ingredients (${countLabel})`;

            const tbody = document.getElementById('recipe_ingredients-table-body');
            if (tbody) {
                tbody.innerHTML = hasData
                    ? lastResults.map(renderRecipeIngredientRow).join('')
                    : `<tr><td colspan="6" class="admin-entity__empty">${results.error ? escapeHtml(results.error) : 'No recipe ingredients found'}</td></tr>`;
            }

            const loadMoreWrapper = document.getElementById('recipe_ingredients_load-more-wrapper');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = hasData && lastResults.length === DEFAULT_LIMIT
                    ? '<button id="recipe_ingredients_load-more-btn" class="btn btn-outline btn-sm">Load More Ingredients</button>'
                    : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // View modal
        if (e.target.matches('.recipe-ingredient-view') || e.target.closest('.recipe-ingredient-view')) {
            const btn = e.target.closest('.recipe-ingredient-view');
            const id = btn?.dataset.id;
            if (!id) return;

            try {
                const html = await renderModal(id);
                openStandardModal({
                    title: 'Recipe Ingredient Details',
                    bodyHtml: html,
                    size: 'lg'
                });
            } catch (error) {
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Edit modal (direct injection like Products)
        if (e.target.matches('.recipe-ingredient-edit') || e.target.closest('.recipe-ingredient-edit')) {
            const btn = e.target.closest('.recipe-ingredient-edit');
            const id = btn?.dataset.id;
            if (!id) return;

            console.log('[RecipeIngredients] Edit button clicked:', { id });

            try {
                console.log('[RecipeIngredients] Rendering edit form...');
                const formHtml = await renderRecipeIngredientEdit(parseInt(id));
                console.log('[RecipeIngredients] Form HTML rendered, length:', formHtml.length);

                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                if (!modal || !modalBody) {
                    console.error('[RecipeIngredients] Modal elements not found!', { modal: !!modal, modalBody: !!modalBody });
                    return;
                }

                console.log('[RecipeIngredients] Setting modal content...');
                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';
                console.log('[RecipeIngredients] Modal should be visible now');

                initRecipeIngredientEditHandlers(modalBody, parseInt(id), (data, action) => {
                    if (action === 'updated' || action === 'deleted') {
                        // Refresh the table
                        RecipeIngredients().then(html => {
                            const adminEntity = document.querySelector('.admin-entity');
                            if (adminEntity) adminEntity.outerHTML = html;
                        });
                    }
                });
            } catch (error) {
                console.error('[RecipeIngredients] Error opening edit form:', error);
                alert(`Error: ${error.message}`);
            }
        }

        // Create modal (direct injection like Products)
        if (e.target.id === 'recipe_ingredients-create-btn') {
            console.log('[RecipeIngredients] Create button clicked');

            try {
                console.log('[RecipeIngredients] Rendering create form...');
                const formHtml = await renderRecipeIngredientCreate();
                console.log('[RecipeIngredients] Form HTML rendered, length:', formHtml.length);

                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                if (!modal || !modalBody) {
                    console.error('[RecipeIngredients] Modal elements not found!', { modal: !!modal, modalBody: !!modalBody });
                    return;
                }

                console.log('[RecipeIngredients] Setting modal content...');
                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';
                console.log('[RecipeIngredients] Modal should be visible now');

                initRecipeIngredientCreateHandlers(modalBody, (data, action) => {
                    if (action === 'created') {
                        // Refresh the table
                        RecipeIngredients().then(html => {
                            const adminEntity = document.querySelector('.admin-entity');
                            if (adminEntity) adminEntity.outerHTML = html;
                        });
                    }
                });
            } catch (error) {
                console.error('[RecipeIngredients] Error opening create form:', error);
                alert(`Error: ${error.message}`);
            }
        }

        // Load More
        if (e.target.id === 'recipe_ingredients_load-more-btn') {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Loading...';
            const html = await loadMoreRecipeIngredients();
            document.getElementById('recipe_ingredients-table-body').insertAdjacentHTML('beforeend', html);
            if (html.includes('No more') || html.includes('Error') || html.includes('Failed')) {
                btn.disabled = true;
                btn.textContent = 'No more items';
            } else {
                btn.disabled = false;
                btn.textContent = 'Load More Ingredients';
            }
        }

        // Refresh
        if (e.target.id === 'recipe_ingredients_refresh-btn') {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Refreshing...';
            const html = await RecipeIngredients();
            const container = document.querySelector('.admin-entity');
            if (container) container.outerHTML = html;
        }
    });

    document.addEventListener('input', (e) => {
        if (e.target.id === 'recipe_ingredients-search-input') {
            debouncedSearch(e);
        }
    });
})();

window.loadMoreRecipeIngredients = loadMoreRecipeIngredients;
window.fetchRecipeIngredients = fetchRecipeIngredients;