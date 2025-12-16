<?php 
$pageName = 'product';
$pageTitle = 'Product Details - Royal Liquor';
require_once __DIR__ . "/components/header.php"; 

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productSlug = isset($_GET['slug']) ? htmlspecialchars($_GET['slug']) : '';
?>

<main class="product-detail-page">
    <!-- Breadcrumb -->
    <nav class="breadcrumb container">
        <a href="<?= BASE_URL ?>">Home</a>
        <span class="separator">›</span>
        <a href="<?= getPageUrl('shop') ?>">Shop</a>
        <span class="separator">›</span>
        <span class="current" id="breadcrumbProduct">Loading...</span>
    </nav>

    <!-- Product Content -->
    <section class="product-content container">
        <div class="product-grid">
            <!-- Image Gallery -->
            <div class="product-gallery">
                <div class="gallery-main">
                    <img id="mainImage" src="" alt="" class="main-image skeleton">
                    <button class="gallery-zoom-btn" id="zoomBtn" title="Zoom">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            <line x1="11" y1="8" x2="11" y2="14"></line>
                            <line x1="8" y1="11" x2="14" y2="11"></line>
                        </svg>
                    </button>
                    <div class="product-badges" id="productBadges"></div>
                </div>
                <div class="gallery-thumbnails" id="thumbnails">
                    <!-- Thumbnails will be generated -->
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <div class="product-category-tag" id="categoryTag">Loading...</div>
                <h1 class="product-title" id="productTitle">Loading...</h1>
                
                <!-- Rating Summary -->
                <div class="product-rating-summary" id="ratingSummary">
                    <div class="rating-stars" id="ratingStars"></div>
                    <span class="rating-count" id="ratingCount"></span>
                    <a href="#reviews" class="rating-link">Read Reviews</a>
                </div>

                <!-- Price -->
                <div class="product-price-section">
                    <span class="product-price" id="productPrice">$0.00</span>
                    <span class="product-price-original" id="originalPrice"></span>
                    <span class="product-discount-badge" id="discountBadge"></span>
                </div>

                <!-- Stock Status -->
                <div class="stock-indicator" id="stockStatus">
                    <span class="stock-dot"></span>
                    <span class="stock-text">Checking availability...</span>
                </div>

                <!-- Description -->
                <div class="product-description" id="productDescription">
                    <p class="skeleton skeleton-text"></p>
                    <p class="skeleton skeleton-text"></p>
                </div>

                <!-- Flavor Profile -->
                <div class="flavor-profile-section" id="flavorSection">
                    <h3 class="section-label">Tasting Notes</h3>
                    <div class="flavor-visual">
                        <div class="flavor-radar-container" id="productFlavorRadar"></div>
                        <div class="flavor-details">
                            <div class="flavor-bars" id="flavorBars"></div>
                            <div class="flavor-tags" id="flavorTags"></div>
                        </div>
                    </div>
                    <a href="#" class="btn-find-similar" id="findSimilarBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        Find Similar Flavors
                    </a>
                </div>

                <!-- Add to Cart -->
                <div class="product-actions">
                    <div class="quantity-control">
                        <label class="qty-label">Quantity</label>
                        <div class="qty-selector">
                            <button class="qty-btn minus" id="qtyMinus">−</button>
                            <input type="number" id="quantity" value="1" min="1" class="qty-input">
                            <button class="qty-btn plus" id="qtyPlus">+</button>
                        </div>
                    </div>
                    <button class="btn btn-gold btn-lg btn-add-to-cart" id="addToCartBtn">
                        Add to Cart
                    </button>
                    <button class="btn btn-outline btn-icon wishlist-action" id="wishlistBtn" title="Add to Wishlist">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </button>
                </div>

                <!-- Product Details Accordion -->
                <div class="product-details-accordion">
                    <details class="accordion-item" open>
                        <summary class="accordion-header">Product Details</summary>
                        <div class="accordion-content" id="detailsContent">
                            <div class="detail-row">
                                <span class="label">Category</span>
                                <span class="value" id="detailCategory">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Supplier</span>
                                <span class="value" id="detailSupplier">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Units Sold</span>
                                <span class="value" id="detailSold">-</span>
                            </div>
                        </div>
                    </details>
                    <details class="accordion-item">
                        <summary class="accordion-header">Shipping Information</summary>
                        <div class="accordion-content">
                            <p>Free shipping on orders over $100. Standard delivery 3-5 business days.</p>
                            <p>Express delivery available at checkout.</p>
                        </div>
                    </details>
                    <details class="accordion-item">
                        <summary class="accordion-header">Return Policy</summary>
                        <div class="accordion-content">
                            <p>Unopened products may be returned within 30 days of purchase.</p>
                            <p>Please contact customer service for return authorization.</p>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews-section container" id="reviews">
        <div class="section-header">
            <h2 class="section-title">Customer Reviews</h2>
            <div class="review-summary" id="reviewSummaryBox">
                <div class="summary-rating">
                    <span class="big-rating" id="avgRating">-</span>
                    <div class="summary-stars" id="summaryStars"></div>
                    <span class="review-total" id="totalReviews">0 reviews</span>
                </div>
            </div>
        </div>

        <!-- Reviews Carousel -->
        <div class="reviews-carousel-wrapper">
            <button class="carousel-btn prev" id="reviewPrev" disabled>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            
            <div class="reviews-carousel" id="reviewsCarousel">
                <!-- Reviews will be populated dynamically -->
                <div class="reviews-track" id="reviewsTrack"></div>
            </div>
            
            <button class="carousel-btn next" id="reviewNext">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>

        <!-- Empty State for No Reviews -->
        <div class="reviews-empty-state" id="reviewsEmpty" style="display: none;">
            <div class="empty-icon">💬</div>
            <h3>No Reviews Yet</h3>
            <p>Be the first to share your thoughts on this product.</p>
            <a href="<?= BASE_URL ?>feedback.php" class="btn btn-outline">Write a Review</a>
        </div>
    </section>

    <!-- Similar Products Section -->
    <section class="related-products container">
        <h2 class="section-title">If You Like This, You'll Love These</h2>
        <p class="section-subtitle">Products with similar flavor profiles</p>
        <div class="related-grid" id="relatedProducts">
            <!-- Related products will be loaded -->
        </div>
    </section>
</main>

<!-- Styles moved to css/product.css -->

<script type="module">
import { API } from '<?= BASE_URL ?>utils/api-helper.js';
import { addItemToCart } from '<?= BASE_URL ?>utils/cart-storage.js';
import { updateCartCount } from '<?= BASE_URL ?>utils/header.js';
import { isInWishlist, toggleWishlistItem } from '<?= BASE_URL ?>utils/wishlist-storage.js';
import { toast } from '<?= BASE_URL ?>utils/toast.js';

// Get product ID from URL
const urlParams = new URLSearchParams(window.location.search);
const productId = parseInt(urlParams.get('id')) || 1;

let currentProduct = null;
let allProducts = [];
let reviewIndex = 0;

// Generate star SVGs
const generateStars = (rating, size = 16) => {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    let stars = '';
    
    for (let i = 0; i < 5; i++) {
        const fill = i < fullStars ? '#d4af37' : (i === fullStars && hasHalfStar ? '#d4af37' : '#e0e0e0');
        stars += `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="${fill}">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>`;
    }
    return stars;
};

// Format date
const formatDate = (dateStr) => {
    if (!dateStr) return 'Recently';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

// Load product data
const loadProduct = async () => {
    try {
        const response = await API.products.list({ limit: 100 });
        if (response.success && response.data) {
            allProducts = response.data;
            currentProduct = allProducts.find(p => p.id === productId) || allProducts[0];
            
            if (currentProduct) {
                renderProduct(currentProduct);
                loadFeedback(currentProduct.id);
                loadRelatedProducts(currentProduct);
            }
        }
    } catch (error) {
        console.error('[Product] Failed to load product:', error);
    }
};

// Render product info
const renderProduct = (product) => {
    const price = (product.price_cents / 100).toFixed(2);
    const isAvailable = product.is_available && product.available_stock > 0;
    const isPremium = product.price_cents > 10000;
    const isLowStock = product.available_stock < 20 && product.available_stock > 0;
    
    // Update page title
    document.title = `${product.name} - Royal Liquor`;
    document.getElementById('breadcrumbProduct').textContent = product.name;
    
    // Main image
    const mainImage = document.getElementById('mainImage');
    mainImage.src = product.image_url;
    mainImage.alt = product.name;
    mainImage.classList.remove('skeleton');
    
    // Product badges
    const badgesContainer = document.getElementById('productBadges');
    badgesContainer.innerHTML = '';
    if (isPremium) {
        badgesContainer.innerHTML += '<span class="product-badge premium">Premium</span>';
    }
    if (!isAvailable) {
        badgesContainer.innerHTML += '<span class="product-badge out-of-stock">Out of Stock</span>';
    } else if (isLowStock) {
        badgesContainer.innerHTML += '<span class="product-badge low-stock">Low Stock</span>';
    }
    
    // Basic info
    document.getElementById('categoryTag').textContent = product.category_name || 'Spirits';
    document.getElementById('productTitle').textContent = product.name;
    document.getElementById('productPrice').textContent = `$${price}`;
    document.getElementById('productDescription').innerHTML = `<p>${product.description}</p>`;
    
    // Rating
    const rating = parseFloat(product.avg_rating) || 0;
    document.getElementById('ratingStars').innerHTML = generateStars(rating, 18);
    document.getElementById('ratingCount').textContent = rating > 0 ? `${rating.toFixed(1)}` : 'No ratings';
    
    // Stock status
    const stockEl = document.getElementById('stockStatus');
    if (!isAvailable) {
        stockEl.className = 'stock-indicator out-of-stock';
        stockEl.querySelector('.stock-text').textContent = 'Out of Stock';
    } else if (isLowStock) {
        stockEl.className = 'stock-indicator low-stock';
        stockEl.querySelector('.stock-text').textContent = `Only ${product.available_stock} left in stock`;
    } else {
        stockEl.className = 'stock-indicator';
        stockEl.querySelector('.stock-text').textContent = 'In Stock';
    }
    
    // Flavor profile
    renderFlavorProfile(product);
    
    // Details
    document.getElementById('detailCategory').textContent = product.category_name || '-';
    document.getElementById('detailSupplier').textContent = product.supplier_name || '-';
    document.getElementById('detailSold').textContent = product.units_sold || '0';
    
    // Update buttons
    const addToCartBtn = document.getElementById('addToCartBtn');
    addToCartBtn.disabled = !isAvailable;
    addToCartBtn.textContent = isAvailable ? 'Add to Cart' : 'Out of Stock';
    
    // Wishlist state
    const wishlistBtn = document.getElementById('wishlistBtn');
    if (isInWishlist(product.id)) {
        wishlistBtn.classList.add('active');
    }
};

// Render flavor profile
const renderFlavorProfile = (product) => {
    const flavorSection = document.getElementById('flavorSection');
    
    try {
        const flavor = typeof product.flavor_profile === 'string' 
            ? JSON.parse(product.flavor_profile) 
            : product.flavor_profile;
        
        if (!flavor || flavor.sweetness === null) {
            flavorSection.style.display = 'none';
            return;
        }
        
        const attributes = ['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'];
        const barsHtml = attributes.map(attr => {
            const value = flavor[attr] || 0;
            const percentage = (value / 10) * 100;
            return `
                <div class="flavor-bar-row">
                    <span class="flavor-name">${attr}</span>
                    <div class="flavor-track">
                        <div class="flavor-fill" style="width: ${percentage}%"></div>
                    </div>
                    <span class="flavor-value">${value}/10</span>
                </div>
            `;
        }).join('');
        
        document.getElementById('flavorBars').innerHTML = barsHtml;
        
        // Tags - make them clickable links to shop search
        if (flavor.tags && flavor.tags.length > 0) {
            document.getElementById('flavorTags').innerHTML = 
                flavor.tags.map(tag => `<a href="search.php?q=${encodeURIComponent(tag)}" class="flavor-tag">#${tag}</a>`).join('');
        }
        
        flavorSection.style.display = 'block';
    } catch (e) {
        flavorSection.style.display = 'none';
    }
};

// Load feedback/reviews
const loadFeedback = async (productId) => {
    try {
        const response = await API.feedback.getByProduct(productId);
        const feedbackData = response.success ? response.data : [];
        
        renderReviews(feedbackData);
    } catch (error) {
        console.error('[Product] Failed to load feedback:', error);
        renderReviews([]);
    }
};

// Render reviews - handles null/empty and dynamic amounts
const renderReviews = (reviews) => {
    const track = document.getElementById('reviewsTrack');
    const emptyState = document.getElementById('reviewsEmpty');
    const carouselWrapper = document.querySelector('.reviews-carousel-wrapper');
    const summaryBox = document.getElementById('reviewSummaryBox');
    
    // Handle null/empty reviews
    if (!reviews || reviews.length === 0) {
        carouselWrapper.style.display = 'none';
        emptyState.style.display = 'block';
        summaryBox.style.display = 'none';
        return;
    }
    
    carouselWrapper.style.display = 'flex';
    emptyState.style.display = 'none';
    summaryBox.style.display = 'block';
    
    // Calculate average
    const avgRating = reviews.reduce((sum, r) => sum + (r.rating || 0), 0) / reviews.length;
    document.getElementById('avgRating').textContent = avgRating.toFixed(1);
    document.getElementById('summaryStars').innerHTML = generateStars(avgRating, 20);
    document.getElementById('totalReviews').textContent = `${reviews.length} review${reviews.length !== 1 ? 's' : ''}`;
    
    // Render review cards
    track.innerHTML = reviews.map(review => {
        const initial = (review.user_name || 'A')[0].toUpperCase();
        return `
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-info">
                        <div class="reviewer-avatar">${initial}</div>
                        <div>
                            <div class="reviewer-name">${review.user_name || 'Anonymous'}</div>
                            <div class="review-date">${formatDate(review.created_at)}</div>
                        </div>
                    </div>
                    <div class="review-rating">${generateStars(review.rating, 16)}</div>
                </div>
                <div class="review-content">${review.comment || 'Great product!'}</div>
                ${review.is_verified_purchase ? '<div class="verified-badge">✓ Verified Purchase</div>' : ''}
            </div>
        `;
    }).join('');
    
    // Setup carousel navigation
    setupCarouselNav(reviews.length);
};

// Carousel navigation
const setupCarouselNav = (totalItems) => {
    const track = document.getElementById('reviewsTrack');
    const prevBtn = document.getElementById('reviewPrev');
    const nextBtn = document.getElementById('reviewNext');
    
    const itemsPerView = window.innerWidth > 1024 ? 3 : (window.innerWidth > 768 ? 2 : 1);
    const maxIndex = Math.max(0, totalItems - itemsPerView);
    
    const updateCarousel = () => {
        const cardWidth = track.querySelector('.review-card')?.offsetWidth || 300;
        const gap = 24; // var(--space-lg)
        track.style.transform = `translateX(-${reviewIndex * (cardWidth + gap)}px)`;
        
        prevBtn.disabled = reviewIndex === 0;
        nextBtn.disabled = reviewIndex >= maxIndex;
    };
    
    prevBtn.onclick = () => {
        if (reviewIndex > 0) {
            reviewIndex--;
            updateCarousel();
        }
    };
    
    nextBtn.onclick = () => {
        if (reviewIndex < maxIndex) {
            reviewIndex++;
            updateCarousel();
        }
    };
    
    updateCarousel();
};

// Calculate flavor profile similarity score (returns match percentage 0-100)
const getFlavorMatchScore = (product1, product2) => {
    try {
        const f1 = typeof product1.flavor_profile === 'string' 
            ? JSON.parse(product1.flavor_profile) 
            : product1.flavor_profile;
        const f2 = typeof product2.flavor_profile === 'string' 
            ? JSON.parse(product2.flavor_profile) 
            : product2.flavor_profile;
        
        if (!f1 || !f2 || f1.sweetness === null || f2.sweetness === null) {
            return 0; // No flavor data, 0% match
        }
        
        const attributes = ['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'];
        
        // Calculate Euclidean distance between flavor profiles
        // Max possible distance = sqrt(6 * 10^2) = sqrt(600) ≈ 24.5
        const maxDistance = Math.sqrt(600);
        const distance = Math.sqrt(
            attributes.reduce((sum, attr) => {
                const diff = (f1[attr] || 0) - (f2[attr] || 0);
                return sum + (diff * diff);
            }, 0)
        );
        
        // Convert distance to percentage (0 distance = 100% match)
        const matchPercentage = Math.round(((maxDistance - distance) / maxDistance) * 100);
        return Math.max(0, Math.min(100, matchPercentage));
    } catch (e) {
        return 0;
    }
};

// Load related products based on flavor profile similarity
const loadRelatedProducts = (product) => {
    // Calculate match scores for all other products
    const productsWithScore = allProducts
        .filter(p => p.id !== product.id)
        .map(p => ({
            ...p,
            matchScore: getFlavorMatchScore(product, p)
        }))
        .sort((a, b) => b.matchScore - a.matchScore) // Higher match first
        .slice(0, 4);
    
    const container = document.getElementById('relatedProducts');
    
    if (productsWithScore.length === 0) {
        container.innerHTML = '<p class="empty-related">No similar products found.</p>';
        return;
    }
    
    container.innerHTML = productsWithScore.map(p => {
        const price = (p.price_cents / 100).toFixed(2);
        const matchClass = p.matchScore >= 80 ? 'high' : (p.matchScore >= 60 ? 'medium' : 'low');
        return `
            <a href="product.php?id=${p.id}" class="related-card">
                <div class="related-card-image">
                    <img src="${p.image_url}" alt="${p.name}" loading="lazy">
                    ${p.matchScore > 0 ? `<span class="match-badge ${matchClass}">${p.matchScore}% Match</span>` : ''}
                </div>
                <div class="related-card-info">
                    <div class="related-card-name">${p.name}</div>
                    <div class="related-card-price">$${price}</div>
                </div>
            </a>
        `;
    }).join('');
};

// Event handlers
document.addEventListener('DOMContentLoaded', async () => {
    await loadProduct();
    await updateCartCount();
    
    // Quantity controls
    const qtyInput = document.getElementById('quantity');
    document.getElementById('qtyMinus').onclick = () => {
        const val = parseInt(qtyInput.value) || 1;
        if (val > 1) qtyInput.value = val - 1;
    };
    document.getElementById('qtyPlus').onclick = () => {
        const val = parseInt(qtyInput.value) || 1;
        qtyInput.value = val + 1;
    };
    
    // Add to cart
    document.getElementById('addToCartBtn').onclick = async () => {
        if (!currentProduct) return;
        const qty = parseInt(qtyInput.value) || 1;
        addItemToCart(currentProduct.id, qty);
        await updateCartCount();
        toast.success(`${currentProduct.name} added to cart!`);
    };
    
    // Wishlist toggle
    document.getElementById('wishlistBtn').onclick = (e) => {
        if (!currentProduct) return;
        toggleWishlistItem(currentProduct.id);
        e.currentTarget.classList.toggle('active');
        const isNowInWishlist = e.currentTarget.classList.contains('active');
        toast.gold(isNowInWishlist ? 'Added to wishlist!' : 'Removed from wishlist');
    };
    
    // Find Similar Flavors button
    document.getElementById('findSimilarBtn').onclick = (e) => {
        e.preventDefault();
        if (!currentProduct || !currentProduct.flavor_profile) return;
        
        try {
            const flavor = typeof currentProduct.flavor_profile === 'string' 
                ? JSON.parse(currentProduct.flavor_profile) 
                : currentProduct.flavor_profile;
            
            // Build URL with flavor parameters for shop page filtering
            const params = new URLSearchParams();
            if (flavor.sweetness) params.set('sweetness', flavor.sweetness);
            if (flavor.bitterness) params.set('bitterness', flavor.bitterness);
            if (flavor.strength) params.set('strength', flavor.strength);
            if (flavor.smokiness) params.set('smokiness', flavor.smokiness);
            if (flavor.fruitiness) params.set('fruitiness', flavor.fruitiness);
            if (flavor.spiciness) params.set('spiciness', flavor.spiciness);
            params.set('similar_to', currentProduct.id);
            
            window.location.href = `shop.php?${params.toString()}`;
        } catch (e) {
            console.error('[Product] Error parsing flavor profile:', e);
        }
    };
});
</script>

<?php require_once __DIR__ . "/footer/footer.php"; ?>
