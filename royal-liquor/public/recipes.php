<?php
$pageName = 'recipes';
$pageTitle = 'Cocktail Recipes - Royal Liquor';
require_once __DIR__ . "/components/header.php";
?>

<main class="recipes-page">
    <div class="container">
        <!-- Page Header -->
        <div class="recipes-header">
            <h1 class="page-title">Cocktail Recipes</h1>
            <p class="page-subtitle">Discover delicious cocktails you can make at home with our premium spirits</p>
        </div>

        <!-- Filters -->
        <div class="recipes-filters">
            <div class="filter-group">
                <label>Difficulty</label>
                <select id="difficultyFilter">
                    <option value="">All</option>
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="expert">Expert</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Time</label>
                <select id="timeFilter">
                    <option value="">Any Time</option>
                    <option value="5">Under 5 min</option>
                    <option value="10">Under 10 min</option>
                    <option value="15">Under 15 min</option>
                </select>
            </div>
            <div class="filter-group filter-toggle">
                <label class="toggle-label">
                    <input type="checkbox" id="canMakeFilter">
                    <span class="toggle-switch"></span>
                    <span>I Can Make</span>
                </label>
                <span class="filter-hint">Based on your purchases</span>
            </div>
        </div>

        <!-- Results Count -->
        <div class="results-bar">
            <span id="resultsCount">Loading recipes...</span>
        </div>

        <!-- Recipe Grid -->
        <div class="recipes-grid" id="recipesGrid">
            <!-- Recipes will be loaded here -->
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-icon">🍸</div>
            <h2>No Recipes Found</h2>
            <p>Try adjusting your filters to find more cocktails</p>
            <button class="btn btn-gold" id="resetFiltersBtn">Reset Filters</button>
        </div>
    </div>
</main>

<!-- Recipe Quick View Modal -->
<div class="recipe-modal" id="recipeModal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <button class="modal-close" id="modalClose">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="modal-body">
            <div class="modal-image">
                <img id="modalImage" src="" alt="">
            </div>
            <div class="modal-info">
                <div class="modal-badges" id="modalBadges"></div>
                <h2 id="modalName" class="modal-name"></h2>
                <p id="modalDescription" class="modal-description"></p>
                
                <div class="modal-ingredients">
                    <h3>Ingredients</h3>
                    <ul id="modalIngredients" class="ingredients-list"></ul>
                </div>
                
                <div class="modal-instructions">
                    <h3>Instructions</h3>
                    <div id="modalInstructions" class="instructions-text"></div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-gold btn-lg" id="addAllToCart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Add All Ingredients to Cart
                    </button>
                    <a href="#" id="viewFullRecipe" class="btn btn-outline">View Full Recipe</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Recipes Page Styles */
.recipes-page {
    padding: var(--space-2xl) 0 var(--space-3xl);
    min-height: calc(100vh - 200px);
    background: var(--gray-50);
}

.recipes-header {
    text-align: center;
    margin-bottom: var(--space-2xl);
}

.page-title {
    font-family: var(--font-serif);
    font-size: 3rem;
    font-weight: 300;
    font-style: italic;
    color: var(--black);
    margin-bottom: var(--space-sm);
}

.page-title::after {
    content: '';
    display: block;
    width: 80px;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--gold), transparent);
    margin: var(--space-md) auto 0;
}

.page-subtitle {
    color: var(--gray-500);
    font-size: 1.1rem;
}

/* Filters */
.recipes-filters {
    display: flex;
    gap: var(--space-lg);
    align-items: center;
    flex-wrap: wrap;
    padding: var(--space-lg);
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    margin-bottom: var(--space-xl);
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
}

.filter-group label {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--gray-500);
}

.filter-group select {
    padding: var(--space-sm) var(--space-md);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-md);
    font-size: 0.95rem;
    min-width: 150px;
    background: var(--white);
}

.filter-toggle {
    margin-left: auto;
}

.toggle-label {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    cursor: pointer;
    font-weight: 600;
    color: var(--black);
}

.toggle-label input {
    display: none;
}

.toggle-switch {
    width: 44px;
    height: 24px;
    background: var(--gray-300);
    border-radius: var(--radius-full);
    position: relative;
    transition: background var(--duration-fast);
}

.toggle-switch::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    background: var(--white);
    border-radius: 50%;
    top: 3px;
    left: 3px;
    transition: transform var(--duration-fast);
}

.toggle-label input:checked + .toggle-switch {
    background: var(--gold);
}

.toggle-label input:checked + .toggle-switch::after {
    transform: translateX(20px);
}

.filter-hint {
    font-size: 0.75rem;
    color: var(--gray-400);
}

/* Results Bar */
.results-bar {
    margin-bottom: var(--space-lg);
    color: var(--gray-500);
}

/* Recipe Grid */
.recipes-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-xl);
}

/* Recipe Card */
.recipe-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all var(--duration-normal);
    cursor: pointer;
}

.recipe-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg);
}

.recipe-card-image {
    aspect-ratio: 16/10;
    overflow: hidden;
    position: relative;
}

.recipe-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-slow);
}

.recipe-card:hover .recipe-card-image img {
    transform: scale(1.08);
}

.recipe-badges {
    position: absolute;
    top: var(--space-sm);
    left: var(--space-sm);
    display: flex;
    gap: var(--space-xs);
}

.recipe-badge {
    padding: var(--space-xs) var(--space-sm);
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: var(--radius-sm);
}

.recipe-badge.easy {
    background: #22c55e;
    color: #fff;
}

.recipe-badge.medium {
    background: var(--gold);
    color: var(--black);
}

.recipe-badge.expert {
    background: #ef4444;
    color: #fff;
}

.recipe-badge.time {
    background: rgba(0,0,0,0.7);
    color: #fff;
}

.recipe-badge.can-make {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
}

.recipe-card-content {
    padding: var(--space-lg);
}

.recipe-card-name {
    font-family: var(--font-serif);
    font-size: 1.25rem;
    font-weight: 500;
    font-style: italic;
    color: var(--black);
    margin-bottom: var(--space-sm);
}

.recipe-card-description {
    font-size: 0.9rem;
    color: var(--gray-500);
    line-height: 1.5;
    margin-bottom: var(--space-md);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.recipe-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: var(--space-md);
    border-top: 1px solid var(--gray-100);
}

.recipe-ingredients-count {
    font-size: 0.85rem;
    color: var(--gray-500);
}

.recipe-ingredients-count strong {
    color: var(--gold);
}

.recipe-serves {
    font-size: 0.85rem;
    color: var(--gray-500);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--space-3xl);
    background: var(--white);
    border-radius: var(--radius-lg);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: var(--space-lg);
}

.empty-state h2 {
    font-family: var(--font-serif);
    font-style: italic;
    margin-bottom: var(--space-sm);
}

.empty-state p {
    color: var(--gray-500);
    margin-bottom: var(--space-xl);
}

/* Recipe Modal */
.recipe-modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: none;
}

.recipe-modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}

.modal-content {
    position: relative;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    background: var(--white);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.modal-close {
    position: absolute;
    top: var(--space-md);
    right: var(--space-md);
    z-index: 10;
    width: 40px;
    height: 40px;
    background: var(--white);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-md);
}

.modal-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-image {
    aspect-ratio: 1/1;
}

.modal-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.modal-info {
    padding: var(--space-xl);
    overflow-y: auto;
}

.modal-badges {
    display: flex;
    gap: var(--space-sm);
    margin-bottom: var(--space-md);
}

.modal-name {
    font-family: var(--font-serif);
    font-size: 2rem;
    font-weight: 300;
    font-style: italic;
    margin-bottom: var(--space-md);
}

.modal-description {
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: var(--space-xl);
}

.modal-ingredients h3,
.modal-instructions h3 {
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--gold);
    margin-bottom: var(--space-md);
}

.ingredients-list {
    list-style: none;
    padding: 0;
    margin: 0 0 var(--space-xl);
}

.ingredients-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-sm) 0;
    border-bottom: 1px dashed var(--gray-200);
}

.ingredient-name {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.ingredient-name.has-product {
    color: var(--gold);
    font-weight: 600;
}

.ingredient-check {
    color: #22c55e;
}

.ingredient-missing {
    color: var(--gray-400);
}

.ingredient-qty {
    font-size: 0.9rem;
    color: var(--gray-500);
}

.instructions-text {
    color: var(--gray-600);
    line-height: 1.8;
    white-space: pre-line;
}

.modal-actions {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    margin-top: var(--space-xl);
    padding-top: var(--space-xl);
    border-top: 1px solid var(--gray-200);
}

.modal-actions .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
}

/* Responsive */
@media (max-width: 1024px) {
    .recipes-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .page-title {
        font-size: 2rem;
    }
    
    .recipes-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-toggle {
        margin-left: 0;
    }
    
    .recipes-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-body {
        grid-template-columns: 1fr;
    }
    
    .modal-image {
        aspect-ratio: 16/9;
    }
}
</style>

<script type="module">
import { API } from '<?= BASE_URL ?>utils/api-helper.js';
import { addItemToCart } from '<?= BASE_URL ?>utils/cart-storage.js';
import { updateCartCount } from '<?= BASE_URL ?>utils/header.js';

let allRecipes = [];
let filteredRecipes = [];
let currentRecipe = null;

// DOM Elements
const recipesGrid = document.getElementById('recipesGrid');
const resultsCount = document.getElementById('resultsCount');
const emptyState = document.getElementById('emptyState');
const difficultyFilter = document.getElementById('difficultyFilter');
const timeFilter = document.getElementById('timeFilter');
const canMakeFilter = document.getElementById('canMakeFilter');
const recipeModal = document.getElementById('recipeModal');

// Initialize
const init = async () => {
    await updateCartCount();
    await loadRecipes();
    applyFilters();
    setupEventListeners();
};

// Load recipes from API
const loadRecipes = async () => {
    try {
        const response = await API.recipes.list({ limit: 50 });
        if (response.success && response.data) {
            allRecipes = response.data.items || [];
        }
    } catch (error) {
        console.error('[Recipes] Failed to load:', error);
    }
};

// Apply filters
const applyFilters = () => {
    filteredRecipes = allRecipes.filter(recipe => {
        // Difficulty filter
        if (difficultyFilter.value && recipe.difficulty !== difficultyFilter.value) {
            return false;
        }
        
        // Time filter
        if (timeFilter.value && recipe.preparation_time > parseInt(timeFilter.value)) {
            return false;
        }
        
        // "Can Make" filter - simplified for now (would check purchase history in real app)
        // For demo, this just shows all recipes
        
        return true;
    });
    
    renderRecipes();
};

// Render recipe cards
const renderRecipes = () => {
    if (filteredRecipes.length === 0) {
        recipesGrid.style.display = 'none';
        emptyState.style.display = 'block';
        resultsCount.textContent = '0 recipes found';
        return;
    }
    
    recipesGrid.style.display = 'grid';
    emptyState.style.display = 'none';
    resultsCount.textContent = `${filteredRecipes.length} recipe${filteredRecipes.length !== 1 ? 's' : ''} found`;
    
    recipesGrid.innerHTML = filteredRecipes.map(recipe => {
        const ingredients = recipe.ingredients || [];
        const ingredientCount = ingredients.filter(i => !i.is_optional).length;
        const optionalCount = ingredients.filter(i => i.is_optional).length;
        
        return `
            <article class="recipe-card" data-id="${recipe.id}">
                <div class="recipe-card-image">
                    <img src="${recipe.image_url}" alt="${recipe.name}" loading="lazy">
                    <div class="recipe-badges">
                        <span class="recipe-badge ${recipe.difficulty}">${recipe.difficulty}</span>
                        <span class="recipe-badge time">${recipe.preparation_time} min</span>
                    </div>
                </div>
                <div class="recipe-card-content">
                    <h3 class="recipe-card-name">${recipe.name}</h3>
                    <p class="recipe-card-description">${recipe.description}</p>
                    <div class="recipe-card-meta">
                        <span class="recipe-ingredients-count">
                            <strong>${ingredientCount}</strong> ingredients
                            ${optionalCount > 0 ? `+ ${optionalCount} optional` : ''}
                        </span>
                        <span class="recipe-serves">Serves ${recipe.serves}</span>
                    </div>
                </div>
            </article>
        `;
    }).join('');
};

// Open recipe modal
const openModal = (recipeId) => {
    currentRecipe = allRecipes.find(r => r.id === recipeId);
    if (!currentRecipe) return;
    
    // Populate modal
    document.getElementById('modalImage').src = currentRecipe.image_url;
    document.getElementById('modalName').textContent = currentRecipe.name;
    document.getElementById('modalDescription').textContent = currentRecipe.description;
    document.getElementById('modalInstructions').textContent = currentRecipe.instructions;
    document.getElementById('viewFullRecipe').href = `recipe.php?id=${currentRecipe.id}`;
    
    // Badges
    document.getElementById('modalBadges').innerHTML = `
        <span class="recipe-badge ${currentRecipe.difficulty}">${currentRecipe.difficulty}</span>
        <span class="recipe-badge time">${currentRecipe.preparation_time} min</span>
        <span class="recipe-badge">Serves ${currentRecipe.serves}</span>
    `;
    
    // Ingredients
    const ingredientsList = document.getElementById('modalIngredients');
    const ingredients = currentRecipe.ingredients || [];
    ingredientsList.innerHTML = ingredients.map(ing => {
        const hasProduct = ing.product_id !== null;
        return `
            <li>
                <span class="ingredient-name ${hasProduct ? 'has-product' : ''}">
                    ${hasProduct ? '<span class="ingredient-check">✓</span>' : '<span class="ingredient-missing">○</span>'}
                    ${ing.product_name}
                    ${ing.is_optional ? '<small>(optional)</small>' : ''}
                </span>
                <span class="ingredient-qty">${ing.quantity} ${ing.unit}</span>
            </li>
        `;
    }).join('');
    
    recipeModal.classList.add('active');
    document.body.style.overflow = 'hidden';
};

// Close modal
const closeModal = () => {
    recipeModal.classList.remove('active');
    document.body.style.overflow = '';
    currentRecipe = null;
};

// Add all ingredients to cart
const addAllIngredients = async () => {
    if (!currentRecipe) return;
    
    const ingredients = currentRecipe.ingredients || [];
    const productsToAdd = ingredients
        .filter(ing => ing.product_id !== null)
        .map(ing => ing.product_id);
    
    if (productsToAdd.length === 0) {
        alert('No purchasable ingredients in this recipe!');
        return;
    }
    
    for (const productId of productsToAdd) {
        addItemToCart(productId, 1);
    }
    
    await updateCartCount();
    
    // Show feedback
    const btn = document.getElementById('addAllToCart');
    const originalText = btn.innerHTML;
    btn.innerHTML = '✓ Added to Cart!';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
};

// Setup event listeners
const setupEventListeners = () => {
    // Filters
    difficultyFilter.addEventListener('change', applyFilters);
    timeFilter.addEventListener('change', applyFilters);
    canMakeFilter.addEventListener('change', applyFilters);
    
    // Reset filters
    document.getElementById('resetFiltersBtn').addEventListener('click', () => {
        difficultyFilter.value = '';
        timeFilter.value = '';
        canMakeFilter.checked = false;
        applyFilters();
    });
    
    // Recipe card click
    recipesGrid.addEventListener('click', (e) => {
        const card = e.target.closest('.recipe-card');
        if (card) {
            openModal(parseInt(card.dataset.id));
        }
    });
    
    // Modal close
    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.querySelector('.modal-overlay').addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
    
    // Add all to cart
    document.getElementById('addAllToCart').addEventListener('click', addAllIngredients);
};

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', init);
</script>

<?php require_once __DIR__ . "/footer/footer.php"; ?>
