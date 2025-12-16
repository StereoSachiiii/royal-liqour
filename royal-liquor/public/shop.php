<?php
$pageName = 'shop';
$pageTitle = 'Shop All - Royal Liquor';
require_once __DIR__ . "/components/header.php";
?>

<main class="shop-page">
    <div class="shop-container container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?= BASE_URL ?>">Home</a>
            <span class="separator">›</span>
            <span class="current">Shop All</span>
        </nav>

        <div class="shop-header">
            <h1 class="page-title">Shop All Spirits</h1>
            <p class="page-subtitle">Discover our curated collection of premium spirits</p>
        </div>

        <div class="shop-layout">
            <!-- Filter Sidebar -->
            <aside class="filter-sidebar" id="filterSidebar">
                <div class="filter-header">
                    <h2>Filters</h2>
                    <button class="clear-filters" id="clearFilters">Clear All</button>
                </div>

                <!-- Active Filters -->
                <div class="active-filters" id="activeFilters" style="display: none;">
                    <div class="active-filters-list"></div>
                </div>

                <!-- Category Filter -->
                <div class="filter-group">
                    <h3 class="filter-title">Category</h3>
                    <div class="filter-options" id="categoryFilters">
                        <!-- Populated via JS -->
                    </div>
                </div>

                <!-- Price Range Filter -->
                <div class="filter-group">
                    <h3 class="filter-title">Price Range</h3>
                    <div class="price-range-inputs">
                        <input type="number" id="minPrice" placeholder="Min" min="0" step="1">
                        <span class="range-separator">–</span>
                        <input type="number" id="maxPrice" placeholder="Max" min="0" step="1">
                    </div>
                    <div class="price-slider-container">
                        <input type="range" id="priceSlider" min="0" max="500" value="500">
                        <div class="price-range-labels">
                            <span>$0</span>
                            <span id="maxPriceLabel">$500+</span>
                        </div>
                    </div>
                </div>

                <!-- Rating Filter -->
                <div class="filter-group">
                    <h3 class="filter-title">Rating</h3>
                    <div class="filter-options rating-options">
                        <label class="filter-option">
                            <input type="radio" name="rating" value="4">
                            <span class="rating-stars">★★★★☆</span>
                            <span class="rating-text">4+ Stars</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="rating" value="3">
                            <span class="rating-stars">★★★☆☆</span>
                            <span class="rating-text">3+ Stars</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="rating" value="2">
                            <span class="rating-stars">★★☆☆☆</span>
                            <span class="rating-text">2+ Stars</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="rating" value="0" checked>
                            <span class="rating-text">All Ratings</span>
                        </label>
                    </div>
                </div>

                <!-- Availability Filter -->
                <div class="filter-group">
                    <h3 class="filter-title">Availability</h3>
                    <div class="filter-options">
                        <label class="filter-option">
                            <input type="checkbox" id="inStockOnly">
                            <span class="checkmark"></span>
                            <span>In Stock Only</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" id="premiumOnly">
                            <span class="checkmark"></span>
                            <span>Premium ($100+)</span>
                        </label>
                    </div>
                </div>

                <!-- Flavor Profile Filter -->
                <div class="filter-group flavor-filter-group">
                    <h3 class="filter-title">
                        Flavor Profile
                        <button class="flavor-toggle-btn" id="flavorToggle">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                    </h3>
                    <div class="flavor-filter-content" id="flavorFilterContent">
                        <!-- Interactive Radar Chart -->
                        <div class="flavor-radar-wrapper" id="flavorRadarWrapper"></div>
                        
                        <!-- Flavor Sliders -->
                        <div class="flavor-sliders">
                            <div class="flavor-slider-item">
                                <label>🍯 Sweetness</label>
                                <input type="range" id="filterSweetness" min="0" max="10" value="0">
                                <span class="slider-value" id="sweetnessValue">Any</span>
                            </div>
                            <div class="flavor-slider-item">
                                <label>🍋 Bitterness</label>
                                <input type="range" id="filterBitterness" min="0" max="10" value="0">
                                <span class="slider-value" id="bitternessValue">Any</span>
                            </div>
                            <div class="flavor-slider-item">
                                <label>💪 Strength</label>
                                <input type="range" id="filterStrength" min="0" max="10" value="0">
                                <span class="slider-value" id="strengthValue">Any</span>
                            </div>
                            <div class="flavor-slider-item">
                                <label>🔥 Smokiness</label>
                                <input type="range" id="filterSmokiness" min="0" max="10" value="0">
                                <span class="slider-value" id="smokinessValue">Any</span>
                            </div>
                            <div class="flavor-slider-item">
                                <label>🍇 Fruitiness</label>
                                <input type="range" id="filterFruitiness" min="0" max="10" value="0">
                                <span class="slider-value" id="fruitinessValue">Any</span>
                            </div>
                            <div class="flavor-slider-item">
                                <label>🌶️ Spiciness</label>
                                <input type="range" id="filterSpiciness" min="0" max="10" value="0">
                                <span class="slider-value" id="spicinessValue">Any</span>
                            </div>
                        </div>
                        
                        <button class="btn-reset-flavor" id="resetFlavorBtn">Reset Flavor Filters</button>
                    </div>
                </div>
            </aside>

            <!-- Main Product Area -->
            <section class="shop-main">
                <!-- Top Bar -->
                <div class="shop-topbar">
                    <div class="results-count" id="resultsCount">Loading...</div>
                    <div class="topbar-actions">
                        <button class="mobile-filter-btn" id="mobileFilterBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="4" y1="6" x2="20" y2="6"></line>
                                <line x1="4" y1="12" x2="16" y2="12"></line>
                                <line x1="4" y1="18" x2="12" y2="18"></line>
                            </svg>
                            Filters
                        </button>
                        <select id="sortSelect" class="sort-select">
                            <option value="newest">Newest First</option>
                            <option value="match">Best Match</option>
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                            <option value="name_asc">Name: A to Z</option>
                            <option value="popularity">Best Selling</option>
                            <option value="rating">Highest Rated</option>
                        </select>
                        <div class="view-toggle">
                            <button class="view-btn active" data-cols="4" title="4 columns">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <rect x="0" y="0" width="3" height="3"/><rect x="4.5" y="0" width="3" height="3"/>
                                    <rect x="9" y="0" width="3" height="3"/><rect x="13" y="0" width="3" height="3"/>
                                    <rect x="0" y="4.5" width="3" height="3"/><rect x="4.5" y="4.5" width="3" height="3"/>
                                    <rect x="9" y="4.5" width="3" height="3"/><rect x="13" y="4.5" width="3" height="3"/>
                                </svg>
                            </button>
                            <button class="view-btn" data-cols="3" title="3 columns">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <rect x="0" y="0" width="4" height="4"/><rect x="6" y="0" width="4" height="4"/>
                                    <rect x="12" y="0" width="4" height="4"/>
                                    <rect x="0" y="6" width="4" height="4"/><rect x="6" y="6" width="4" height="4"/>
                                    <rect x="12" y="6" width="4" height="4"/>
                                </svg>
                            </button>
                            <button class="view-btn" data-cols="2" title="2 columns">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <rect x="0" y="0" width="7" height="7"/><rect x="9" y="0" width="7" height="7"/>
                                    <rect x="0" y="9" width="7" height="7"/><rect x="9" y="9" width="7" height="7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="products-grid cols-4" id="productsGrid">
                    <!-- Products loaded via JS -->
                </div>

                <!-- Empty State -->
                <div class="empty-state" id="emptyState" style="display: none;">
                    <div class="empty-icon">🔍</div>
                    <h2>No products found</h2>
                    <p>Try adjusting your filters or search criteria</p>
                    <button class="btn btn-gold" id="resetFiltersBtn">Reset Filters</button>
                </div>

                <!-- Load More -->
                <div class="load-more" id="loadMore" style="display: none;">
                    <button class="btn btn-outline" id="loadMoreBtn">Load More Products</button>
                </div>
            </section>
        </div>
    </div>

    <!-- Mobile Filter Overlay -->
    <div class="mobile-filter-overlay" id="mobileFilterOverlay"></div>
</main>

<!-- Styles moved to css/shop.css -->


<script type="module">
import { API } from '<?= BASE_URL ?>utils/api-helper.js';
import { addItemToCart } from '<?= BASE_URL ?>utils/cart-storage.js';
import { updateCartCount } from '<?= BASE_URL ?>utils/header.js';
import { toggleWishlistItem, isInWishlist } from '<?= BASE_URL ?>utils/wishlist-storage.js';
import { FlavorRadarChart } from '<?= BASE_URL ?>components/flavor-radar-chart.js';
import { getTastePreferences, hasPreferences } from '<?= BASE_URL ?>utils/preferences-storage.js';
import { quickViewModal } from '<?= BASE_URL ?>utils/quick-view-modal.js';
import { toast } from '<?= BASE_URL ?>utils/toast.js';

// Calculate match score for a product based on user preferences
const calcMatchScore = (product) => {
    if (!hasPreferences()) return 0;
    const prefs = getTastePreferences();
    try {
        const flavor = typeof product.flavor_profile === 'string'
            ? JSON.parse(product.flavor_profile)
            : product.flavor_profile;
        if (!flavor) return 0;
        const attrs = ['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'];
        const maxDist = Math.sqrt(600);
        const dist = Math.sqrt(attrs.reduce((s, a) => s + Math.pow((prefs[a]||5)-(flavor[a]||5), 2), 0));
        return Math.round(((maxDist - dist) / maxDist) * 100);
    } catch { return 0; }
};

let productsData = [];
let categoriesData = [];
let filteredProducts = [];
let filterRadarChart = null;

// Filter state
const filters = {
    category: null,
    minPrice: null,
    maxPrice: null,
    rating: 0,
    inStockOnly: false,
    premiumOnly: false,
    sortBy: 'newest',
    // Flavor filters (0 = any)
    flavor: {
        sweetness: 0,
        bitterness: 0,
        strength: 0,
        smokiness: 0,
        fruitiness: 0,
        spiciness: 0
    }
};

// DOM Elements
const productsGrid = document.getElementById('productsGrid');
const resultsCount = document.getElementById('resultsCount');
const emptyState = document.getElementById('emptyState');
const categoryFilters = document.getElementById('categoryFilters');
const sortSelect = document.getElementById('sortSelect');

// Initialize
const init = async () => {
    await updateCartCount();
    await loadData();
    populateCategoryFilters();
    
    // Check for flavor params from URL (from "Find Similar" feature)
    applyUrlFlavorParams();
    
    applyFilters();
    setupEventListeners();
    initFlavorRadar();
};

// Apply flavor filters from URL parameters (used by "Find Similar" feature)
const applyUrlFlavorParams = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const flavorAttrs = ['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'];
    let hasFlavorParams = false;
    
    flavorAttrs.forEach(attr => {
        const value = parseInt(urlParams.get(attr));
        if (value && value > 0) {
            // Set filter to value minus 2 (tolerance) but minimum 1
            filters.flavor[attr] = Math.max(1, value - 2);
            hasFlavorParams = true;
            
            // Update slider UI
            const slider = document.getElementById(`filter${attr.charAt(0).toUpperCase() + attr.slice(1)}`);
            const valueSpan = document.getElementById(`${attr}Value`);
            if (slider) {
                slider.value = filters.flavor[attr];
                valueSpan.textContent = `${filters.flavor[attr]}+`;
            }
        }
    });
    
    // If we have flavor params, expand the flavor filter section
    if (hasFlavorParams) {
        const flavorContent = document.getElementById('flavorFilterContent');
        const toggleBtn = document.getElementById('flavorToggle');
        if (flavorContent) flavorContent.classList.remove('collapsed');
        if (toggleBtn) toggleBtn.classList.remove('collapsed');
    }
};

// Load data
const loadData = async () => {
    try {
        const [productsRes, categoriesRes] = await Promise.all([
            API.products.list({ limit: 100 }),
            API.categories.list()
        ]);

        productsData = productsRes.success ? productsRes.data : [];
        categoriesData = categoriesRes.success ? categoriesRes.data : [];
    } catch (error) {
        console.error('[Shop] Failed to load data:', error);
    }
};

// Populate category filters
const populateCategoryFilters = () => {
    categoryFilters.innerHTML = `
        <label class="filter-option">
            <input type="radio" name="category" value="" checked>
            <span>All Categories</span>
        </label>
        ${categoriesData.map(c => `
            <label class="filter-option">
                <input type="radio" name="category" value="${c.id}">
                <span>${c.name}</span>
                <span class="count" style="margin-left:auto;color:var(--gray-400);font-size:0.8rem;">
                    (${productsData.filter(p => p.category_id === c.id).length})
                </span>
            </label>
        `).join('')}
    `;
};

// Apply filters
const applyFilters = () => {
    filteredProducts = productsData.filter(p => {
        // Category filter
        if (filters.category && p.category_id !== parseInt(filters.category)) return false;

        // Price filter
        const price = p.price_cents / 100;
        if (filters.minPrice && price < filters.minPrice) return false;
        if (filters.maxPrice && price > filters.maxPrice) return false;

        // Rating filter
        if (filters.rating > 0) {
            const rating = parseFloat(p.avg_rating) || 0;
            if (rating < filters.rating) return false;
        }

        // In stock filter
        if (filters.inStockOnly && p.available_stock <= 0) return false;

        // Premium filter
        if (filters.premiumOnly && p.price_cents < 10000) return false;

        // Flavor profile filter
        const flavorActive = Object.values(filters.flavor).some(v => v > 0);
        if (flavorActive) {
            try {
                const fp = typeof p.flavor_profile === 'string' 
                    ? JSON.parse(p.flavor_profile) 
                    : p.flavor_profile || {};
                
                // Check each flavor attribute (tolerance of ±2)
                for (const [key, minVal] of Object.entries(filters.flavor)) {
                    if (minVal > 0) {
                        const productVal = fp[key] || 0;
                        if (productVal < minVal - 2) return false;
                    }
                }
            } catch (e) {
                // No flavor profile, exclude if flavor filter active
                return false;
            }
        }

        return true;
    });

    // Sort
    sortProducts();

    // Render
    renderProducts();
};

// Sort products
const sortProducts = () => {
    // Pre-calculate match scores if sorting by match
    if (filters.sortBy === 'match') {
        filteredProducts.forEach(p => { p._matchScore = calcMatchScore(p); });
    }
    
    filteredProducts.sort((a, b) => {
        switch (filters.sortBy) {
            case 'match': return (b._matchScore || 0) - (a._matchScore || 0);
            case 'price_asc': return a.price_cents - b.price_cents;
            case 'price_desc': return b.price_cents - a.price_cents;
            case 'name_asc': return a.name.localeCompare(b.name);
            case 'popularity': return (b.units_sold || 0) - (a.units_sold || 0);
            case 'rating': return (parseFloat(b.avg_rating) || 0) - (parseFloat(a.avg_rating) || 0);
            default: return new Date(b.created_at || 0) - new Date(a.created_at || 0);
        }
    });
};

// Render products
const renderProducts = () => {
    if (filteredProducts.length === 0) {
        productsGrid.style.display = 'none';
        emptyState.style.display = 'block';
        resultsCount.textContent = '0 products found';
        return;
    }

    productsGrid.style.display = 'grid';
    emptyState.style.display = 'none';
    resultsCount.textContent = `${filteredProducts.length} product${filteredProducts.length !== 1 ? 's' : ''} found`;

    productsGrid.innerHTML = filteredProducts.map(renderProductCard).join('');
};

// Render product card
const renderProductCard = (p) => {
    const price = (p.price_cents / 100).toFixed(2);
    const rating = parseFloat(p.avg_rating) || 0;
    const stars = '★'.repeat(Math.floor(rating)) + '☆'.repeat(5 - Math.floor(rating));
    const inStock = p.available_stock > 0;
    const isPremium = p.price_cents >= 10000;
    const isLowStock = p.available_stock < 20 && p.available_stock > 0;
    
    // Match score badge (only show if user has preferences)
    const matchScore = hasPreferences() ? calcMatchScore(p) : 0;
    const matchClass = matchScore >= 80 ? 'high' : (matchScore >= 60 ? 'medium' : 'low');
    const matchBadge = matchScore > 0 ? `<span class="product-badge match ${matchClass}">${matchScore}% Match</span>` : '';

    let badge = '';
    if (!inStock) badge = '<span class="product-badge out-of-stock">Out of Stock</span>';
    else if (isPremium) badge = '<span class="product-badge premium">Premium</span>';
    else if (isLowStock) badge = '<span class="product-badge low-stock">Low Stock</span>';

    return `
        <article class="product-card" data-id="${p.id}">
            <a href="product.php?id=${p.id}" class="product-card-image">
                <img src="${p.image_url}" alt="${p.name}" loading="lazy">
                <div class="product-badges">
                    ${badge}
                    ${matchBadge}
                </div>
            </a>
            <div class="product-card-info">
                <div class="product-card-category">${p.category_name || 'Spirits'}</div>
                <h3 class="product-card-name">${p.name}</h3>
                <div class="product-card-rating">
                    <span class="stars">${stars}</span>
                    <span class="count">(${rating.toFixed(1)})</span>
                </div>
                <div class="product-card-footer">
                    <span class="product-card-price">$${price}</span>
                    <div class="product-card-actions">
                        <button class="btn-quick-view" data-id="${p.id}">Quick View</button>
                        <button class="btn-add-cart" data-id="${p.id}" ${!inStock ? 'disabled' : ''}>
                            ${inStock ? '+' : '✕'}
                        </button>
                    </div>
                </div>
            </div>
        </article>
    `;
};

// Setup event listeners
const setupEventListeners = () => {
    // Category filter
    categoryFilters.addEventListener('change', (e) => {
        if (e.target.name === 'category') {
            filters.category = e.target.value || null;
            applyFilters();
        }
    });

    // Price inputs
    document.getElementById('minPrice').addEventListener('change', (e) => {
        filters.minPrice = e.target.value ? parseFloat(e.target.value) : null;
        applyFilters();
    });

    document.getElementById('maxPrice').addEventListener('change', (e) => {
        filters.maxPrice = e.target.value ? parseFloat(e.target.value) : null;
        applyFilters();
    });

    // Price slider
    document.getElementById('priceSlider').addEventListener('input', (e) => {
        filters.maxPrice = parseInt(e.target.value);
        document.getElementById('maxPrice').value = e.target.value;
        document.getElementById('maxPriceLabel').textContent = `$${e.target.value}+`;
        applyFilters();
    });

    // Rating filter
    document.querySelectorAll('input[name="rating"]').forEach(input => {
        input.addEventListener('change', (e) => {
            filters.rating = parseInt(e.target.value);
            applyFilters();
        });
    });

    // Availability filters
    document.getElementById('inStockOnly').addEventListener('change', (e) => {
        filters.inStockOnly = e.target.checked;
        applyFilters();
    });

    document.getElementById('premiumOnly').addEventListener('change', (e) => {
        filters.premiumOnly = e.target.checked;
        applyFilters();
    });

    // Sort
    sortSelect.addEventListener('change', (e) => {
        filters.sortBy = e.target.value;
        applyFilters();
    });

    // Clear filters
    document.getElementById('clearFilters').addEventListener('click', clearFilters);
    document.getElementById('resetFiltersBtn').addEventListener('click', clearFilters);

    // View toggle
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            productsGrid.className = `products-grid cols-${btn.dataset.cols}`;
        });
    });

    // Mobile filter toggle
    document.getElementById('mobileFilterBtn').addEventListener('click', () => {
        document.getElementById('filterSidebar').classList.add('active');
        document.getElementById('mobileFilterOverlay').classList.add('active');
    });

    document.getElementById('mobileFilterOverlay').addEventListener('click', () => {
        document.getElementById('filterSidebar').classList.remove('active');
        document.getElementById('mobileFilterOverlay').classList.remove('active');
    });

    // Flavor filter toggle
    document.getElementById('flavorToggle')?.addEventListener('click', () => {
        const btn = document.getElementById('flavorToggle');
        const content = document.getElementById('flavorFilterContent');
        btn.classList.toggle('collapsed');
        content.classList.toggle('collapsed');
    });

    // Flavor sliders
    const flavorSliders = ['Sweetness', 'Bitterness', 'Strength', 'Smokiness', 'Fruitiness', 'Spiciness'];
    flavorSliders.forEach(attr => {
        const slider = document.getElementById(`filter${attr}`);
        const valueSpan = document.getElementById(`${attr.toLowerCase()}Value`);
        
        if (slider) {
            slider.addEventListener('input', (e) => {
                const val = parseInt(e.target.value);
                filters.flavor[attr.toLowerCase()] = val;
                valueSpan.textContent = val === 0 ? 'Any' : `${val}+`;
                applyFilters();
                
                // Update radar chart
                if (filterRadarChart) {
                    filterRadarChart.setData(filters.flavor);
                }
            });
        }
    });

    // Reset flavor filters
    document.getElementById('resetFlavorBtn')?.addEventListener('click', () => {
        flavorSliders.forEach(attr => {
            const slider = document.getElementById(`filter${attr}`);
            const valueSpan = document.getElementById(`${attr.toLowerCase()}Value`);
            if (slider) {
                slider.value = 0;
                valueSpan.textContent = 'Any';
            }
            filters.flavor[attr.toLowerCase()] = 0;
        });
        
        if (filterRadarChart) {
            filterRadarChart.setData(filters.flavor);
        }
        applyFilters();
    });

    // Quick view and add to cart
    productsGrid.addEventListener('click', async (e) => {
        const quickViewBtn = e.target.closest('.btn-quick-view');
        const addCartBtn = e.target.closest('.btn-add-cart');

        if (quickViewBtn) {
            const productId = parseInt(quickViewBtn.dataset.id);
            const product = productsData.find(p => p.id === productId);
            if (product) {
                // Transform product data for modal
                const productForModal = {
                    id: product.id,
                    name: product.name,
                    price: product.price_cents / 100,
                    image_url: product.image_url,
                    category_name: product.category_name,
                    description: product.description,
                    rating: parseFloat(product.avg_rating) || 0,
                    rating_count: product.total_reviews || 0,
                    stock: product.available_stock || 0,
                    is_premium: product.price_cents >= 10000
                };
                
                quickViewModal.open(productForModal, {
                    onAddToCart: async (prod, qty) => {
                        await addItemToCart(prod.id, qty);
                        await updateCartCount();
                        toast.success(`${prod.name} added to cart!`);
                        quickViewModal.close();
                    },
                    isInWishlist: (id) => isInWishlist(id),
                    onToggleWishlist: (prod) => {
                        toggleWishlistItem(prod);
                        const isNowInWishlist = isInWishlist(prod.id);
                        toast.gold(isNowInWishlist ? 'Added to wishlist!' : 'Removed from wishlist');
                    }
                });
            }
        }

        if (addCartBtn && !addCartBtn.disabled) {
            const productId = addCartBtn.dataset.id;
            await addItemToCart(productId, 1);
            await updateCartCount();
            addCartBtn.textContent = '✓';
            const product = productsData.find(p => p.id === parseInt(productId));
            toast.success(product ? `${product.name} added to cart!` : 'Added to cart!');
            setTimeout(() => { addCartBtn.textContent = '+'; }, 1500);
        }
    });
};

// Initialize flavor radar chart
const initFlavorRadar = () => {
    const wrapper = document.getElementById('flavorRadarWrapper');
    if (wrapper) {
        filterRadarChart = new FlavorRadarChart(wrapper, {
            size: 140,
            interactive: true,
            showLabels: false,
            showValues: false,
            colors: {
                area: 'rgba(212, 175, 55, 0.25)',
                stroke: '#d4af37',
                points: '#d4af37'
            },
            onChange: (data) => {
                // Sync sliders with radar chart
                Object.entries(data).forEach(([key, val]) => {
                    const slider = document.getElementById(`filter${key.charAt(0).toUpperCase() + key.slice(1)}`);
                    const valueSpan = document.getElementById(`${key}Value`);
                    if (slider) {
                        slider.value = val;
                        valueSpan.textContent = val === 0 ? 'Any' : `${val}+`;
                    }
                    filters.flavor[key] = val;
                });
                applyFilters();
            }
        });
        filterRadarChart.setData(filters.flavor);
    }
};

// Clear all filters
const clearFilters = () => {
    filters.category = null;
    filters.minPrice = null;
    filters.maxPrice = null;
    filters.rating = 0;
    filters.inStockOnly = false;
    filters.premiumOnly = false;
    
    // Reset flavor filters
    Object.keys(filters.flavor).forEach(key => filters.flavor[key] = 0);

    // Reset UI
    document.querySelectorAll('input[name="category"]')[0].checked = true;
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    document.getElementById('priceSlider').value = 500;
    document.getElementById('maxPriceLabel').textContent = '$500+';
    document.querySelectorAll('input[name="rating"]').forEach(r => r.checked = r.value === '0');
    document.getElementById('inStockOnly').checked = false;
    document.getElementById('premiumOnly').checked = false;
    
    // Reset flavor sliders
    ['Sweetness', 'Bitterness', 'Strength', 'Smokiness', 'Fruitiness', 'Spiciness'].forEach(attr => {
        const slider = document.getElementById(`filter${attr}`);
        const valueSpan = document.getElementById(`${attr.toLowerCase()}Value`);
        if (slider) slider.value = 0;
        if (valueSpan) valueSpan.textContent = 'Any';
    });
    
    if (filterRadarChart) filterRadarChart.setData(filters.flavor);

    applyFilters();
};

// Initialize
document.addEventListener('DOMContentLoaded', init);
</script>

<?php require_once __DIR__ . "/footer/footer.php"; ?>