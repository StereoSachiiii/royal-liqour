import { fetchRecipeIngredients, fetchModalDetails } from "./RecipeIngredients.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreRecipeIngredients() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const ingredients = await fetchRecipeIngredients(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (ingredients.error) {
            return `<tr><td colspan="8" class="recipe_ingredients_error-cell">Error: ${escapeHtml(ingredients.error)}</td></tr>`;
        }

        if (ingredients.length === 0) {
            return `<tr><td colspan="8" class="recipe_ingredients_no-data-cell">No more recipe ingredients to load</td></tr>`;
        }

        return ingredients.map(ingredient => renderRecipeIngredientRow(ingredient)).join('');
    } catch (error) {
        console.error('Error loading more recipe ingredients:', error);
        return `<tr><td colspan="8" class="recipe_ingredients_error-cell">Failed to load recipe ingredients</td></tr>`;
    }
}

function renderRecipeIngredientRow(ingredient) {
    return `
        <tr class="recipe_ingredients_row recipe-ingredient-row" data-ingredient-id="${ingredient.id}">
            <td class="recipe_ingredients_cell">${ingredient.id}</td>
            <td class="recipe_ingredients_cell">${ingredient.recipe_id}</td>
            <td class="recipe_ingredients_cell">${escapeHtml(ingredient.recipe_name)}</td>
            <td class="recipe_ingredients_cell">${ingredient.product_id}</td>
            <td class="recipe_ingredients_cell">${escapeHtml(ingredient.product_name)}</td>
            <td class="recipe_ingredients_cell">${ingredient.quantity}</td>
            <td class="recipe_ingredients_cell">${escapeHtml(ingredient.unit)}</td>
            <td class="recipe_ingredients_cell">${ingredient.is_optional ? 'Yes' : 'No'}</td>
            <td class="recipe_ingredients_cell recipe_ingredients_actions">
                <button class="recipe_ingredients_btn-view" data-id="${ingredient.id}" title="View Details">👁️ View</button>
                <a href="manage/recipe_ingredient/update.php?id=${ingredient.id}" class="recipe_ingredients_btn-edit btn-edit" title="Edit Recipe Ingredient">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const RecipeIngredients = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const ingredients = await fetchRecipeIngredients(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (ingredients.error) {
            return `
                <div class="recipe_ingredients_table recipe-ingredients-table">
                    <div class="recipe_ingredients_error-box">
                        <strong>Error:</strong> ${escapeHtml(ingredients.error)}
                    </div>
                </div>
            `;
        }

        if (ingredients.length === 0) {
            return `
                <div class="recipe_ingredients_table recipe-ingredients-table">
                    <div class="recipe_ingredients_no-data-box">
                        <p>📭 No recipe ingredients found.</p>
                    </div>
                </div>
            `;
        }

        const tableRows = ingredients.map(ingredient => renderRecipeIngredientRow(ingredient)).join('');

        return `
            <div class="recipe_ingredients_table recipe-ingredients-table">
                <div class="recipe_ingredients_header table-header">
                    <h2>Recipe Ingredients Management (${ingredients.length}${ingredients.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="recipe_ingredients_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="recipe_ingredients-search-input" class="recipe_ingredients_search-input" type="search" placeholder="Search recipe or product name" aria-label="Search recipe ingredients" />
                       
        <a href="manage/recipe_ingredient/create.php" class="recipe_ingredients_btn-primary btn-primary">
           Create
        </a>                        <button id="recipe_ingredients_refresh-btn" class="recipe_ingredients_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="recipe_ingredients_wrapper table-wrapper">
                    <table class="recipe_ingredients_data-table recipe-ingredients-data-table">
                        <thead>
                            <tr class="recipe_ingredients_header-row table-header-row">
                                <th>ID</th>
                                <th>Recipe ID</th>
                                <th>Recipe Name</th>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Optional</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recipe_ingredients-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="recipe_ingredients_load-more-wrapper" class="recipe_ingredients_load-more-wrapper" style="text-align:center;">
                    ${ingredients.length === DEFAULT_LIMIT ? `
                        <button id="recipe_ingredients_load-more-btn" class="recipe_ingredients_btn-load-more btn-load-more">
                            Load More Recipe Ingredients
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering recipe ingredients table:', error);
        return `
            <div class="recipe_ingredients_table recipe-ingredients-table">
                <div class="recipe_ingredients_error-box">
                    <strong>Error:</strong> Failed to load recipe ingredients table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (ingredient) => {
    const cost = parseFloat(ingredient.ingredient_cost_cents) || 0;

    return `
<div class="recipe_ingredients_card recipe-ingredient-card">
    <div class="recipe_ingredients_card-header recipe-ingredient-card-header">
        <span>Recipe Ingredient Details</span>
        <button class="recipe_ingredients_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="recipe_ingredients_section-title recipe-ingredient-section-title">Basic Info</div>
    <div class="recipe_ingredients_data-grid recipe-ingredient-data-grid">
        <div class="recipe_ingredients_field data-field">
            <strong class="recipe_ingredients_label data-label">ID</strong>
            <span class="recipe_ingredients_value data-value">${ingredient.id || 'N/A'}</span>
        </div>
        <div class="recipe_ingredients_field data-field">
            <strong class="recipe_ingredients_label data-label">Recipe Name</strong>
            <span class="recipe_ingredients_value data-value">${ingredient.recipe_name ? escapeHtml(ingredient.recipe_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="recipe_ingredients_field data-field">
            <strong class="recipe_ingredients_label data-label">Difficulty</strong>
            <span class="recipe_ingredients_value data-value">${ingredient.difficulty || '-'}</span>
        </div>
        <div class="recipe_ingredients_field data-field">
            <strong class="recipe_ingredients_label data-label">Product Name</strong>
            <span class="recipe_ingredients_value data-value">${ingredient.product_name ? escapeHtml(ingredient.product_name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="recipe_ingredients_field data-field">
            <strong class="recipe_ingredients_label data-label">Product Price</strong>
            <span class="recipe_ingredients_value data-value">$${(ingredient.product_price_cents / 100).toFixed(2)}</span>
        </div>
        <div class="recipe_ingredients_field data-field">
            <strong class="recipe_ingredients_label data-label">Quantity</strong>
            <span class="recipe_ingredients_value data-value">${ingredient.quantity}</span>
        </div>
        <div class="recipe_ingredients_field data-field">
            <strong class="recipe_ingredients_label data-label">Unit</strong>
            <span class="recipe_ingredients_value data-value">${escapeHtml(ingredient.unit)}</span>
        </div>
        <div class="recipe_ingredients_field data-field">
            <strong class="recipe_ingredients_label data-label">Optional</strong>
            <span class="recipe_ingredients_value data-value">${ingredient.is_optional ? 'Yes' : 'No'}</span>
        </div>
        <div class="recipe_ingredients_field data-field">
            <strong class="recipe_ingredients_label data-label">Created At</strong>
            <span class="recipe_ingredients_value data-value">${ingredient.created_at ? formatDate(ingredient.created_at) : 'N/A'}</span>
        </div>
    </div>

    <div class="recipe_ingredients_section-title recipe-ingredient-section-title">Cost</div>
    <div class="recipe_ingredients_data-grid recipe-ingredient-data-grid">
        <div class="recipe_ingredients_field data-field">
            <strong class="recipe_ingredients_label data-label">Ingredient Cost</strong>
            <span class="recipe_ingredients_value data-value">$${(cost / 100).toFixed(2)}</span>
        </div>
    </div>

    <div class="recipe_ingredients_footer card-footer">
        <a href="manage/recipe_ingredient/update.php?id=${ingredient.id}" class="recipe_ingredients_btn-primary btn-primary">
            Edit Recipe Ingredient
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch recipe ingredient details');
        }

        const ingredient = result.recipe_ingredient;

        if (!ingredient || typeof ingredient !== 'object' || !ingredient.id) {
            throw new Error('Invalid recipe ingredient data format');
        }

        return detailsHtml(ingredient);

    } catch (error) {
        throw new Error(error.message || 'Failed to load recipe ingredient details');
    }
};

export const recipeIngredientsListeners = async ( ) => {

    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

    // search debounce helper
    function debounce(fn, wait = 300) {
        let t = null;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), wait);
        };
    }

    async function performSearch(query) {
        try {
            currentQuery = query || '';
            currentOffset = 0;
            const results = await fetchRecipeIngredients(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('recipe_ingredients-table-body');
            const loadMoreWrapper = document.getElementById('recipe_ingredients_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="8" class="recipe_ingredients_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="recipe_ingredients_no-data-cell">No recipe ingredients found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderRecipeIngredientRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="recipe_ingredients_load-more-btn" class="recipe_ingredients_btn-load-more btn-load-more">Load More Recipe Ingredients</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        if (e.target.matches('.recipe_ingredients_btn-view') || e.target.closest('.recipe_ingredients_btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.recipe_ingredients_btn-view') ? e.target : e.target.closest('.recipe_ingredients_btn-view');
            const ingredientId = button.dataset.id;

            if (!ingredientId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading recipe ingredient details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(ingredientId));
                modalBody.innerHTML = html;

                const closeBtn = modalBody.querySelector('.modal-close-btn');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        modal.classList.remove('active');
                    });
                }

            } catch (error) {
                modalBody.innerHTML = `
                    <div class="modal-error">
                        <div class="modal-error-icon">⚠️</div>
                        <h3 class="modal-error-title">Error Loading Recipe Ingredient</h3>
                        <p class="modal-error-message">${escapeHtml(error.message)}</p>
                        <button class="modal-close-btn modal-error-btn">
                            Close
                        </button>
                    </div>
                `;

                const errorCloseBtn = modalBody.querySelector('.modal-close-btn');
                if (errorCloseBtn) {
                    errorCloseBtn.addEventListener('click', () => {
                        modal.classList.remove('active');
                    });
                }
            }
        }

        // load more
        if (e.target.id === 'recipe_ingredients_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreRecipeIngredients();
                document.getElementById('recipe_ingredients-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more recipe ingredients to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more recipe ingredients to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Recipe Ingredients';
                }
            } catch (error) {
                console.error('Error loading more recipe ingredients:', error);
                button.disabled = false;
                button.textContent = 'Load More Recipe Ingredients';
            }
        }

        // refresh
        if (e.target.id === 'recipe_ingredients_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await RecipeIngredients();
                document.querySelector('.recipe-ingredients-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing recipe ingredients:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'recipe_ingredients-search-input') {
            debouncedSearch(e);
        }
    });

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    if (modalClose) {
        modalClose.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }

}

window.loadMoreRecipeIngredients = loadMoreRecipeIngredients;
window.fetchRecipeIngredients = fetchRecipeIngredients;