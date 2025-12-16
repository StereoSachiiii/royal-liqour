import { fetchCocktailRecipes, fetchModalDetails } from "./CocktailRecipes.utils.js";
import { renderCocktailRecipeEdit, initCocktailRecipeEditHandlers } from "./CocktailRecipeEdit.js";
import { renderCocktailRecipeCreate, initCocktailRecipeCreateHandlers } from "./CocktailRecipeCreate.js";
import { escapeHtml, formatDate, openStandardModal, debounce } from "../../utils.js";

const DEFAULT_LIMIT = 20;
let currentOffset = 0;
let currentQuery = '';
let lastResults = [];

function renderCocktailRecipeRow(recipe) {
    return `
        <tr data-id="${recipe.id}">
            <td>${recipe.id}</td>
            <td>${escapeHtml(recipe.name || '-')}</td>
            <td>${escapeHtml(recipe.difficulty || 'easy')}</td>
            <td>${recipe.preparation_time || '-'}</td>
            <td>${recipe.serves || 1}</td>
            <td>
                <span class="badge ${recipe.is_active ? 'badge-active' : 'badge-inactive'}">
                    ${recipe.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>${formatDate(recipe.created_at)}</td>
            <td>${recipe.ingredient_count || 0}</td>
            <td>
                <button class="btn btn-outline btn-sm cocktail-recipe-view" data-id="${recipe.id}" title="View Details">👁️ View</button>
                <button class="btn btn-primary btn-sm cocktail-recipe-edit" data-id="${recipe.id}" title="Edit">✏️ Edit</button>
            </td>
        </tr>
    `;
}

async function loadMoreCocktailRecipes() {
    try {
        currentOffset += DEFAULT_LIMIT;
        const recipes = await fetchCocktailRecipes(DEFAULT_LIMIT, currentOffset, currentQuery);

        if (recipes.error) {
            return `<tr><td colspan="9" class="admin-entity__empty">Error: ${escapeHtml(recipes.error)}</td></tr>`;
        }

        if (recipes.length === 0) {
            return `<tr><td colspan="9" class="admin-entity__empty">No more cocktail recipes to load</td></tr>`;
        }

        lastResults = [...lastResults, ...recipes];
        return recipes.map(renderCocktailRecipeRow).join('');
    } catch (error) {
        console.error('Error loading more cocktail recipes:', error);
        return `<tr><td colspan="9" class="admin-entity__empty">Failed to load cocktail recipes</td></tr>`;
    }
}

export const CocktailRecipes = async () => {
    try {
        currentOffset = 0;
        currentQuery = '';
        const recipes = await fetchCocktailRecipes(DEFAULT_LIMIT, currentOffset, currentQuery);

        lastResults = recipes.error ? [] : (Array.isArray(recipes) ? recipes : []);
        const hasData = lastResults.length > 0;
        const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

        const tableRows = hasData
            ? lastResults.map(renderCocktailRecipeRow).join('')
            : `<tr><td colspan="9" class="admin-entity__empty">${recipes.error ? escapeHtml(recipes.error) : 'No cocktail recipes found'}</td></tr>`;

        return `
            <div class="admin-entity">
                <div class="admin-entity__header">
                    <h2 class="admin-entity__title">Cocktail Recipes (${countLabel})</h2>
                    <div class="admin-entity__actions">
                        <input id="cocktail_recipes-search-input" class="admin-entity__search" type="search" 
                               placeholder="Search by name..." value="${escapeHtml(currentQuery)}" />
                        <button id="cocktail_recipes-create-btn" class="btn btn-primary">Add Recipe</button>
                        <button id="cocktail_recipes_refresh-btn" class="btn btn-outline btn-sm">🔄 Refresh</button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="admin-entity__table">
                        <thead>
                            <tr>
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

                <div id="cocktail_recipes_load-more-wrapper" style="text-align:center; margin-top: var(--space-4);">
                    ${hasData && lastResults.length === DEFAULT_LIMIT ? `<button id="cocktail_recipes_load-more-btn" class="btn btn-outline btn-sm">Load More Recipes</button>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering cocktail recipes table:', error);
        return `<div class="admin-entity"><div class="admin-entity__empty"><strong>Error:</strong> Failed to load cocktail recipes table</div></div>`;
    }
};

// View modal content (matching Feedback styling)
const recipeDetailsHtml = (recipe) => {
    if (!recipe) return '<div class="admin-entity__empty">No recipe data</div>';

    const estimatedCost = parseFloat(recipe.estimated_cost_cents) || 0;

    return `
        <div class="products_card">
            <div class="products_card-header">
                <span>Cocktail Recipe Details</span>
                <span class="badge ${recipe.is_active ? 'badge-active' : 'badge-inactive'}">${recipe.is_active ? 'Active' : 'Inactive'}</span>
            </div>
            <div class="products_section-title">Basic Info</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>ID</strong><span>${recipe.id ?? 'N/A'}</span></div>
                <div class="products_field"><strong>Name</strong><span>${escapeHtml(recipe.name || '-')}</span></div>
                <div class="products_field"><strong>Difficulty</strong><span>${escapeHtml(recipe.difficulty || 'easy')}</span></div>
                <div class="products_field"><strong>Prep Time</strong><span>${recipe.preparation_time || '-'} min</span></div>
                <div class="products_field"><strong>Serves</strong><span>${recipe.serves || 1}</span></div>
            </div>
            <div class="products_section-title">Description</div>
            <div class="products_data-grid">
                <div class="products_field" style="grid-column: span 2;">
                    <strong>Description</strong>
                    <span>${escapeHtml(recipe.description || '-')}</span>
                </div>
                <div class="products_field" style="grid-column: span 2;">
                    <strong>Instructions</strong>
                    <span style="white-space: pre-wrap;">${escapeHtml(recipe.instructions || '-')}</span>
                </div>
            </div>
            ${recipe.image_url ? `
            <div class="products_section-title">Image</div>
            <div style="padding: 1rem;">
                <img src="${escapeHtml(recipe.image_url)}" alt="${escapeHtml(recipe.name)}" style="max-width: 200px; border-radius: 8px;">
            </div>
            ` : ''}
            <div class="products_section-title">Stats</div>
            <div class="products_data-grid">
                <div class="products_field"><strong>Ingredients</strong><span>${recipe.ingredient_count || 0}</span></div>
                <div class="products_field"><strong>Est. Cost</strong><span>$${(estimatedCost / 100).toFixed(2)}</span></div>
                <div class="products_field"><strong>Created</strong><span>${formatDate(recipe.created_at)}</span></div>
                <div class="products_field"><strong>Updated</strong><span>${formatDate(recipe.updated_at)}</span></div>
            </div>
            ${recipe.ingredients && Array.isArray(recipe.ingredients) && recipe.ingredients.length > 0 ? `
            <div class="products_section-title">Ingredients (${recipe.ingredients.length})</div>
            <div style="padding: 0 1rem 1rem;">
                <table class="admin-entity__table" style="font-size: 0.875rem;">
                    <thead><tr><th>Product</th><th>Quantity</th><th>Optional</th></tr></thead>
                    <tbody>
                        ${recipe.ingredients.map(ing => `
                            <tr>
                                <td>${escapeHtml(ing.product_name || 'N/A')}</td>
                                <td>${ing.quantity} ${escapeHtml(ing.unit || '')}</td>
                                <td>${ing.is_optional ? '<span class="badge badge-warning">Optional</span>' : ''}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            ` : ''}
            <div class="products_footer">
                <button class="btn btn-primary cocktail-recipe-edit" data-id="${recipe.id}">Edit Recipe</button>
            </div>
        </div>
    `;
};

const renderModal = async (id) => {
    try {
        const result = await fetchModalDetails(Number(id));
        if (result.error) throw new Error(result.error);
        if (!result.cocktail_recipe) throw new Error('No recipe data returned from API');
        return recipeDetailsHtml(result.cocktail_recipe);
    } catch (error) {
        console.error('[CocktailRecipes] Render modal error:', error);
        throw new Error(error.message || 'Failed to load recipe details');
    }
};

// Attach delegated handlers
(() => {
    async function performSearch(query) {
        try {
            currentQuery = query || '';
            currentOffset = 0;
            const results = await fetchCocktailRecipes(DEFAULT_LIMIT, 0, currentQuery);

            lastResults = results.error ? [] : (Array.isArray(results) ? results : []);
            const hasData = lastResults.length > 0;
            const countLabel = hasData ? `${lastResults.length}${lastResults.length === DEFAULT_LIMIT ? '+' : ''}` : '0';

            const titleEl = document.querySelector('.admin-entity__title');
            if (titleEl) titleEl.textContent = `Cocktail Recipes (${countLabel})`;

            const tbody = document.getElementById('cocktail_recipes-table-body');
            if (tbody) {
                tbody.innerHTML = hasData
                    ? lastResults.map(renderCocktailRecipeRow).join('')
                    : `<tr><td colspan="9" class="admin-entity__empty">${results.error ? escapeHtml(results.error) : 'No cocktail recipes found'}</td></tr>`;
            }

            const loadMoreWrapper = document.getElementById('cocktail_recipes_load-more-wrapper');
            if (loadMoreWrapper) {
                loadMoreWrapper.innerHTML = hasData && lastResults.length === DEFAULT_LIMIT
                    ? '<button id="cocktail_recipes_load-more-btn" class="btn btn-outline btn-sm">Load More Recipes</button>'
                    : '';
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    const debouncedSearch = debounce((e) => performSearch(e.target.value.trim()), 300);

    document.addEventListener('click', async (e) => {
        // View modal
        if (e.target.matches('.cocktail-recipe-view') || e.target.closest('.cocktail-recipe-view')) {
            const btn = e.target.closest('.cocktail-recipe-view');
            const id = btn?.dataset.id;
            if (!id) return;

            try {
                const html = await renderModal(id);
                openStandardModal({
                    title: 'Cocktail Recipe Details',
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
        if (e.target.matches('.cocktail-recipe-edit') || e.target.closest('.cocktail-recipe-edit')) {
            const btn = e.target.closest('.cocktail-recipe-edit');
            const id = btn?.dataset.id;
            if (!id) return;

            console.log('[CocktailRecipes] Edit button clicked:', { id });

            try {
                const formHtml = await renderCocktailRecipeEdit(parseInt(id));

                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                if (!modal || !modalBody) {
                    console.error('[CocktailRecipes] Modal elements not found!');
                    return;
                }

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initCocktailRecipeEditHandlers(modalBody, parseInt(id), (data, action) => {
                    if (action === 'updated' || action === 'deleted') {
                        CocktailRecipes().then(html => {
                            const adminEntity = document.querySelector('.admin-entity');
                            if (adminEntity) adminEntity.outerHTML = html;
                        });
                    }
                });
            } catch (error) {
                console.error('[CocktailRecipes] Error opening edit form:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Create modal (direct injection like Products)
        if (e.target.id === 'cocktail_recipes-create-btn') {
            console.log('[CocktailRecipes] Create button clicked');

            try {
                const formHtml = await renderCocktailRecipeCreate();

                const modal = document.getElementById('modal');
                const modalBody = document.getElementById('modal-body');

                if (!modal || !modalBody) {
                    console.error('[CocktailRecipes] Modal elements not found!');
                    return;
                }

                modalBody.innerHTML = formHtml;
                modal.classList.remove('hidden');
                modal.classList.add('active');
                modal.style.display = 'flex';

                initCocktailRecipeCreateHandlers(modalBody, (data, action) => {
                    if (action === 'created') {
                        CocktailRecipes().then(html => {
                            const adminEntity = document.querySelector('.admin-entity');
                            if (adminEntity) adminEntity.outerHTML = html;
                        });
                    }
                });
            } catch (error) {
                console.error('[CocktailRecipes] Error opening create form:', error);
                openStandardModal({
                    title: 'Error',
                    bodyHtml: `<div class="admin-entity__empty">⚠️ ${escapeHtml(error.message)}</div>`
                });
            }
        }

        // Load More
        if (e.target.id === 'cocktail_recipes_load-more-btn') {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Loading...';
            const html = await loadMoreCocktailRecipes();
            document.getElementById('cocktail_recipes-table-body').insertAdjacentHTML('beforeend', html);
            if (html.includes('No more') || html.includes('Error') || html.includes('Failed')) {
                btn.disabled = true;
                btn.textContent = 'No more items';
            } else {
                btn.disabled = false;
                btn.textContent = 'Load More Recipes';
            }
        }

        // Refresh
        if (e.target.id === 'cocktail_recipes_refresh-btn') {
            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Refreshing...';
            const html = await CocktailRecipes();
            const container = document.querySelector('.admin-entity');
            if (container) container.outerHTML = html;
        }
    });

    document.addEventListener('input', (e) => {
        if (e.target.id === 'cocktail_recipes-search-input') {
            debouncedSearch(e);
        }
    });
})();

window.loadMoreCocktailRecipes = loadMoreCocktailRecipes;
window.fetchCocktailRecipes = fetchCocktailRecipes;

// Legacy export for backward compatibility
export const cocktailRecipesListeners = () => {
    console.log('[CocktailRecipes] Event listeners already attached via IIFE');
};