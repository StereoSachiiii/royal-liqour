import { fetchCocktailRecipes, fetchModalDetails } from "./CocktailRecipes.utils.js";
import { escapeHtml, formatDate, formatOrderDate } from "../../utils.js";

// ...existing code...
const DEFAULT_LIMIT = 5;
let currentOffset = 0;
let currentQuery = '';

async function loadMoreCocktailRecipes() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const recipes = await fetchCocktailRecipes(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (recipes.error) {
            return `<tr><td colspan="9" class="cocktail_recipes_error-cell">Error: ${escapeHtml(recipes.error)}</td></tr>`;
        }

        if (recipes.length === 0) {
            return `<tr><td colspan="9" class="cocktail_recipes_no-data-cell">No more cocktail recipes to load</td></tr>`;
        }

        return recipes.map(recipe => renderCocktailRecipeRow(recipe)).join('');
    } catch (error) {
        console.error('Error loading more cocktail recipes:', error);
        return `<tr><td colspan="9" class="cocktail_recipes_error-cell">Failed to load cocktail recipes</td></tr>`;
    }
}

function renderCocktailRecipeRow(recipe) {
    return `
        <tr class="cocktail_recipes_row cocktail-recipe-row" data-recipe-id="${recipe.id}">
            <td class="cocktail_recipes_cell">${recipe.id}</td>
            <td class="cocktail_recipes_cell">${escapeHtml(recipe.name)}</td>
            <td class="cocktail_recipes_cell">${escapeHtml(recipe.difficulty)}</td>
            <td class="cocktail_recipes_cell">${recipe.preparation_time || '-'}</td>
            <td class="cocktail_recipes_cell">${recipe.serves || 1}</td>
            <td class="cocktail_recipes_cell">
                <span class="cocktail_recipes_badge ${recipe.is_active ? 'cocktail_recipes_status_paid' : 'cocktail_recipes_status_cancelled'}">
                    ${recipe.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="cocktail_recipes_cell">${formatDate(recipe.created_at)}</td>
            <td class="cocktail_recipes_cell">${recipe.ingredient_count || 0}</td>
            <td class="cocktail_recipes_cell cocktail_recipes_actions">
                <button class="cocktail_recipes_btn-view" data-id="${recipe.id}" title="View Details">👁️ View</button>
                <a href="manage/cocktail_recipe/update.php?id=${recipe.id}" class="cocktail_recipes_btn-edit btn-edit" title="Edit Cocktail Recipe">✏️ Edit</a>
            </td>
        </tr>
    `;
}

export const CocktailRecipes = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const recipes = await fetchCocktailRecipes(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (recipes.error) {
            return `
                <div class="cocktail_recipes_table cocktail-recipes-table">
                    <div class="cocktail_recipes_error-box">
                        <strong>Error:</strong> ${escapeHtml(recipes.error)}
                    </div>
                </div>
            `;
        }

        if (recipes.length === 0) {
            return `
                <div class="cocktail_recipes_table cocktail-recipes-table">
                    <div class="cocktail_recipes_no-data-box">
                        <p>📭 No cocktail recipes found.</p>
                    </div>
                </div>
            `;
        }
        const tableRows = recipes.map(recipe => renderCocktailRecipeRow(recipe)).join('');

        return `
            <div class="cocktail_recipes_table cocktail-recipes-table">
                <div class="cocktail_recipes_header table-header">
                    <h2>Cocktail Recipes Management (${recipes.length}${recipes.length === DEFAULT_LIMIT ? '+' : ''})</h2>

                    <div class="cocktail_recipes_header-actions" style="display:flex; gap:8px; align-items:center;">
                        <input id="cocktail_recipes-search-input" class="cocktail_recipes_search-input" type="search" placeholder="Search name or difficulty" aria-label="Search cocktail recipes" />
                            <a href="manage/cocktail_recipe/create.php" class="cocktail_recipes_btn-primary btn-primary">
            Create
        </a>
                        <button id="cocktail_recipes_refresh-btn" class="cocktail_recipes_btn-refresh">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="cocktail_recipes_wrapper table-wrapper">
                    <table class="cocktail_recipes_data-table cocktail-recipes-data-table">
                        <thead>
                            <tr class="cocktail_recipes_header-row table-header-row">
                                <th>ID</th>
                                <th>Name</th>
                                <th>Difficulty</th>
                                <th>Prep Time</th>
                                <th>Serves</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Ingredients</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cocktail_recipes-table-body">
                            ${tableRows}
                        </tbody>
                    </table>
                </div>

                <div id="cocktail_recipes_load-more-wrapper" class="cocktail_recipes_load-more-wrapper" style="text-align:center;">
                    ${recipes.length === DEFAULT_LIMIT ? `
                        <button id="cocktail_recipes_load-more-btn" class="cocktail_recipes_btn-load-more btn-load-more">
                            Load More Cocktail Recipes
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering cocktail recipes table:', error);
        return `
            <div class="cocktail_recipes_table cocktail-recipes-table">
                <div class="cocktail_recipes_error-box">
                    <strong>Error:</strong> Failed to load cocktail recipes table
                </div>
            </div>
        `;
    }
};

// ...existing code...
const detailsHtml = (recipe) => {
    const estimatedCost = parseFloat(recipe.estimated_cost_cents) || 0;

    return `
<div class="cocktail_recipes_card cocktail-recipe-card">
    <div class="cocktail_recipes_card-header cocktail-recipe-card-header">
        <span>Cocktail Recipe Details</span>
        <span class="cocktail_recipes_badge ${recipe.is_active ? 'cocktail_recipes_status_paid' : 'cocktail_recipes_status_cancelled'}">
            ${recipe.is_active ? 'Active' : 'Inactive'}
        </span>
        <button class="cocktail_recipes_close-btn modal-close-btn">&times;</button>
    </div>

    <div class="cocktail_recipes_section-title cocktail-recipe-section-title">Basic Info</div>
    <div class="cocktail_recipes_data-grid cocktail-recipe-data-grid">
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">ID</strong>
            <span class="cocktail_recipes_value data-value">${recipe.id || 'N/A'}</span>
        </div>
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Name</strong>
            <span class="cocktail_recipes_value data-value">${recipe.name ? escapeHtml(recipe.name) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Description</strong>
            <span class="cocktail_recipes_value data-value">${recipe.description ? escapeHtml(recipe.description) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Instructions</strong>
            <span class="cocktail_recipes_value data-value">${recipe.instructions ? escapeHtml(recipe.instructions) : '<span class="data-empty">-</span>'}</span>
        </div>
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Image</strong>
            <span class="cocktail_recipes_value data-value">
                ${recipe.image_url ? `<a href="${escapeHtml(recipe.image_url)}" target="_blank" class="link-primary">View Image</a>` : '<span class="data-empty">No Image</span>'}
            </span>
        </div>
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Difficulty</strong>
            <span class="cocktail_recipes_value data-value">${recipe.difficulty || 'easy'}</span>
        </div>
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Preparation Time</strong>
            <span class="cocktail_recipes_value data-value">${recipe.preparation_time || '-'}</span>
        </div>
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Serves</strong>
            <span class="cocktail_recipes_value data-value">${recipe.serves || 1}</span>
        </div>
    </div>

    <div class="cocktail_recipes_section-title cocktail-recipe-section-title">Timeline</div>
    <div class="cocktail_recipes_data-grid cocktail-recipe-data-grid">
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Created At</strong>
            <span class="cocktail_recipes_value data-value">${recipe.created_at ? formatDate(recipe.created_at) : 'N/A'}</span>
        </div>
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Updated At</strong>
            <span class="cocktail_recipes_value data-value">${recipe.updated_at ? formatDate(recipe.updated_at) : 'N/A'}</span>
        </div>
    </div>

    <div class="cocktail_recipes_section-title cocktail-recipe-section-title">Stats</div>
    <div class="cocktail_recipes_data-grid cocktail-recipe-data-grid">
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Ingredient Count</strong>
            <span class="cocktail_recipes_value data-value">${recipe.ingredient_count || 0}</span>
        </div>
        <div class="cocktail_recipes_field data-field">
            <strong class="cocktail_recipes_label data-label">Estimated Cost</strong>
            <span class="cocktail_recipes_value data-value">$${(estimatedCost / 100).toFixed(2)}</span>
        </div>
    </div>

    <div class="cocktail_recipes_section-title cocktail-recipe-section-title">Ingredients</div>
    <div class="cocktail_recipes_items ingredients-container">
        ${recipe.ingredients && Array.isArray(recipe.ingredients) && recipe.ingredients.length > 0 ?
            recipe.ingredients.map(ingredient => `
                <div class="cocktail_recipes_item-row ingredient-row">
                    <div class="cocktail_recipes_item-name">${ingredient.product_name || 'N/A'}</div>
                    <div class="cocktail_recipes_item-qty">${ingredient.quantity} ${ingredient.unit}</div>
                    <div class="cocktail_recipes_item-price">
                        ${ingredient.is_optional ? '<span class="cocktail_recipes_badge cocktail_recipes_status_pending">Optional</span>' : ''}
                    </div>
                </div>
            `).join('')
            : '<div class="empty-state">No ingredients found.</div>'
        }
    </div>

    <div class="cocktail_recipes_footer card-footer">
        <a href="manage/cocktail_recipe/update.php?id=${recipe.id}" class="cocktail_recipes_btn-primary btn-primary">
            Edit Cocktail Recipe
        </a>
    </div>
</div>
`;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(id);

        if (!result || !result.success) {
            throw new Error(result?.error || result?.message || 'Failed to fetch cocktail recipe details');
        }

        const recipe = result.cocktail_recipe;

        if (!recipe || typeof recipe !== 'object' || !recipe.id) {
            throw new Error('Invalid cocktail recipe data format');
        }

        return detailsHtml(recipe);

    } catch (error) {
        throw new Error(error.message || 'Failed to load cocktail recipe details');
    }
};

export const cocktailRecipesListeners = async () => {
    
    const modal = document.getElementById('modal');
    const modalBody = document.getElementById('modal-body');
    const modalClose = document.getElementById('modal-close');

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
            const results = await fetchCocktailRecipes(DEFAULT_LIMIT, 0, currentQuery);

            const tbody = document.getElementById('cocktail_recipes-table-body');
            const loadMoreWrapper = document.getElementById('cocktail_recipes_load-more-wrapper');

            if (!tbody) return;

            if (results.error) {
                tbody.innerHTML = `<tr><td colspan="9" class="cocktail_recipes_error-cell">${escapeHtml(results.error)}</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            if (!results.length) {
                tbody.innerHTML = `<tr><td colspan="9" class="cocktail_recipes_no-data-cell">No cocktail recipes found</td></tr>`;
                if (loadMoreWrapper) loadMoreWrapper.innerHTML = '';
                return;
            }

            tbody.innerHTML = results.map(renderCocktailRecipeRow).join('');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = results.length === DEFAULT_LIMIT ? `<button id="cocktail_recipes_load-more-btn" class="cocktail_recipes_btn-load-more btn-load-more">Load More Cocktail Recipes</button>` : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // view modal
        
        if (e.target.matches('.cocktail_recipes_btn-view') || e.target.closest('.cocktail_recipes_btn-view')) {
            e.preventDefault();
            const button = e.target.matches('.cocktail_recipes_btn-view') ? e.target : e.target.closest('.cocktail_recipes_btn-view');
            const recipeId = button.dataset.id;
            console.log('clicked');
            if (!recipeId) return;

            modalBody.innerHTML = '<div class="modal-loading">⏳ Loading cocktail recipe details...</div>';
            modal.classList.add('active');

            try {
                const html = await renderModal(parseInt(recipeId));
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
                        <h3 class="modal-error-title">Error Loading Cocktail Recipe</h3>
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
        if (e.target.id === 'cocktail_recipes_load-more-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const html = await loadMoreCocktailRecipes();
                document.getElementById('cocktail_recipes-table-body').insertAdjacentHTML('beforeend', html);

                if (html.includes('No more cocktail recipes to load') || html.includes('Failed to load')) {
                    button.textContent = 'No more cocktail recipes to load';
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.textContent = 'Load More Cocktail Recipes';
                }
            } catch (error) {
                console.error('Error loading more cocktail recipes:', error);
                button.disabled = false;
                button.textContent = 'Load More Cocktail Recipes';
            }
        }

        // refresh
        if (e.target.id === 'cocktail_recipes_refresh-btn') {
            const button = e.target;
            button.disabled = true;
            button.textContent = '🔄 Refreshing...';

            try {
                currentOffset = 0;
                currentQuery = '';
                const content = await CocktailRecipes();
                document.querySelector('.cocktail-recipes-table').outerHTML = content;
            } catch (error) {
                console.error('Error refreshing cocktail recipes:', error);
                button.disabled = false;
                button.textContent = '🔄 Refresh';
            }
        }
    });

    // wire search input
    document.addEventListener('input', (e) => {
        if (e.target && e.target.id === 'cocktail_recipes-search-input') {
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

window.loadMoreCocktailRecipes = loadMoreCocktailRecipes;
window.fetchCocktailRecipes = fetchCocktailRecipes;