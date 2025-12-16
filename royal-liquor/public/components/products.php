<?php
require_once __DIR__ . "/header.php";
?>

<div class="products-page">
    <div class="section-title">Our Finest Selection</div>
    <div class="products-container" id="productsContainer">
        <!-- Products will be inserted here -->
    </div>
    
    <!-- Pagination Controls -->
    <div class="pagination" id="pagination">
        <button class="pagination-btn pagination-prev" id="prevPage" disabled>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Previous
        </button>
        <div class="pagination-info">
            <span id="pageInfo">Page 1 of 1</span>
            <span id="productCount">(0 products)</span>
        </div>
        <button class="pagination-btn pagination-next" id="nextPage">
            Next
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
    </div>
</div>

<!-- Product Detail Modal -->
<div class="detail-modal" id="detailModal">
    <div class="detail-modal-overlay"></div>
    <div class="detail-modal-content">
        <button class="detail-close-btn" id="detailCloseBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div class="detail-grid">
            <div class="detail-image-section">
                <img id="modalImage" src="" alt="" class="detail-image">
                <div id="modalBadge" class="modal-badge"></div>
            </div>
            
            <div class="detail-info-section">
                <h1 id="modalName" class="modal-name"></h1>
                <div class="modal-meta">
                    <div class="modal-price" id="modalPrice"></div>
                    <div class="modal-rating" id="modalRating"></div>
                </div>
                
                <div class="modal-description" id="modalDescription"></div>
                
                <div class="modal-details">
                    <div class="detail-row">
                        <span class="detail-label">Category</span>
                        <span class="detail-value" id="modalCategory"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Supplier</span>
                        <span class="detail-value" id="modalSupplier"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Units Sold</span>
                        <span class="detail-value" id="modalUnitsSold"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Stock Available</span>
                        <span class="detail-value" id="modalStock"></span>
                    </div>
                </div>
                
                <div class="flavor-profile" id="flavorProfile">
                    <h3 class="flavor-title">Flavor Profile</h3>
                    <div class="flavor-bars" id="flavorBars"></div>
                    <div class="flavor-tags" id="flavorTags"></div>
                </div>
                
                <div class="modal-actions">
                    <div class="quantity-selector">
                        <label for="modalQuantity">Quantity</label>
                        <input type="number" id="modalQuantity" value="1" min="1" class="quantity-input">
                    </div>
                    <button class="btn-add-to-cart" id="modalAddToCart">Add to Cart</button>
                </div>
                <a href="#" class="view-full-details-link" id="viewFullDetailsLink">
                    View Full Details →
                </a>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import { API } from '<?= BASE_URL ?>utils/api-helper.js';
    import { addItemToCart } from '<?= BASE_URL ?>utils/cart-storage.js';
    import { updateCartCount } from '<?= BASE_URL ?>utils/header.js';
    import { toggleWishlistItem } from '<?= BASE_URL ?>utils/wishlist-storage.js';

    // Store products in memory
    let productsData = [];
    
    // Pagination state
    const PRODUCTS_PER_PAGE = 10;
    let currentPage = 1;
    let totalPages = 1;

    // Fetch ALL products using real API
    const fetchAllProducts = async () => {
        console.log('[Products] Fetching all products from API...');
        try {
            const response = await API.products.list({ limit: 100 });
            console.log('[Products] API Response:', response);
            if (response.success && response.data) {
                const products = response.data.items || response.data || [];
                console.log('[Products] Found', products.length, 'products');
                return products;
            }
            console.warn('[Products] No products in response');
            return [];
        } catch (error) {
            console.error('[Products] Error fetching products:', error);
            return [];
        }
    };

    // Generate star rating HTML
    const generateStars = (rating) => {
        if (!rating) return '<span class="no-rating">No ratings yet</span>';
        
        const stars = [];
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        
        for (let i = 0; i < 5; i++) {
            if (i < fullStars) {
                stars.push('<span class="star filled">★</span>');
            } else if (i === fullStars && hasHalfStar) {
                stars.push('<span class="star half">★</span>');
            } else {
                stars.push('<span class="star">★</span>');
            }
        }
        
        return `<div class="stars">${stars.join('')}</div><span class="rating-value">${rating}</span>`;
    };

    // Render product card
    // Render product card
    const renderProductCard = (product) => {
        const isAvailable = product.is_available && product.available_stock > 0;
        const price = (product.price_cents / 100).toFixed(2);
        const stockClass = !isAvailable ? 'out-of-stock' : (product.available_stock < 20 ? 'low-stock' : 'in-stock');
        const stockText = !isAvailable ? 'Out of Stock' : (product.available_stock < 20 ? `Only ${product.available_stock} Left` : 'In Stock');
        const isPremium = product.price_cents > 10000; // Premium if over $100
        
        // Generate star rating
        const rating = parseFloat(product.avg_rating) || 0;
        const fullStars = Math.floor(rating);
        const starsHtml = Array.from({length: 5}, (_, i) => 
            `<svg class="star ${i < fullStars ? '' : 'empty'}" width="14" height="14" viewBox="0 0 24 24" fill="${i < fullStars ? '#d4af37' : '#e0e0e0'}">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>`
        ).join('');
        
        return `
            <div class="product-card ${!isAvailable ? 'unavailable' : ''}" data-id="${product.id}">
                <div class="product-image-wrapper">
                    <img src="${product.image_url}" alt="${product.name}" class="product-image" loading="lazy">
                    <span class="stock-badge ${stockClass}">${stockText}</span>
                    ${isPremium ? '<span class="premium-badge">Premium</span>' : ''}
                    <button class="wishlist-btn" data-id="${product.id}" title="Add to Wishlist">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="product-info">
                    <p class="product-category">${product.category_name || 'Spirits'}</p>
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    
                    <div class="product-meta">
                        <div class="product-price">$${price}</div>
                        <div class="product-rating">
                            <div class="rating-stars">${starsHtml}</div>
                            ${product.feedback_count > 0 ? `<span class="rating-count">(${product.feedback_count})</span>` : ''}
                        </div>
                    </div>
                </div>
                
                <div class="product-actions">
                    <button class="btn-add-cart" data-id="${product.id}" ${!isAvailable ? 'disabled' : ''}>
                        Add to Cart
                    </button>
                    <button class="btn-quick-view" data-id="${product.id}">
                        Quick View
                    </button>
                </div>
            </div>
        `;
    };

    // Get products for current page
    const getPageProducts = () => {
        const startIndex = (currentPage - 1) * PRODUCTS_PER_PAGE;
        const endIndex = startIndex + PRODUCTS_PER_PAGE;
        return productsData.slice(startIndex, endIndex);
    };

    // Update pagination UI
    const updatePagination = () => {
        totalPages = Math.ceil(productsData.length / PRODUCTS_PER_PAGE);
        
        const pageInfo = document.getElementById('pageInfo');
        const productCount = document.getElementById('productCount');
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const pagination = document.getElementById('pagination');
        
        // Hide pagination if only 1 page
        pagination.style.display = totalPages <= 1 ? 'none' : 'flex';
        
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        productCount.textContent = `(${productsData.length} products)`;
        
        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = currentPage >= totalPages;
    };

    // Go to specific page
    const goToPage = (page) => {
        if (page < 1 || page > totalPages) return;
        
        currentPage = page;
        const pageProducts = getPageProducts();
        renderProducts(pageProducts);
        updatePagination();
        
        // Scroll to top of products section
        document.querySelector('.products-page').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // Render products (just renders what's passed)
    const renderProducts = (products) => {
        const container = document.getElementById('productsContainer');
        
        if (!products || products.length === 0) {
            container.innerHTML = '<div class="empty-message">No products available at the moment.</div>';
            return;
        }
        
        container.innerHTML = products.map(renderProductCard).join('');
    };

    // Open product detail modal
    const openProductModal = (productId) => {
        const product = productsData.find(p => p.id === parseInt(productId));
        if (!product) return;
        
        const modal = document.getElementById('detailModal');
        const isAvailable = product.is_available && product.available_stock > 0;
        const price = (product.price_cents / 100).toFixed(2);
        
        // Set basic info
        document.getElementById('modalImage').src = product.image_url;
        document.getElementById('modalImage').alt = product.name;
        document.getElementById('modalName').textContent = product.name;
        document.getElementById('modalPrice').textContent = `$${price}`;
        document.getElementById('modalDescription').textContent = product.description;
        document.getElementById('modalCategory').textContent = product.category_name || 'N/A';
        document.getElementById('modalSupplier').textContent = product.supplier_name || 'N/A';
        document.getElementById('modalUnitsSold').textContent = product.units_sold || 0;
        document.getElementById('modalStock').textContent = isAvailable ? `${product.available_stock} units` : 'Out of Stock';
        
        // Set badge
        const badge = document.getElementById('modalBadge');
        badge.className = 'modal-badge ' + (!isAvailable ? 'out-of-stock' : (product.available_stock < 50 ? 'low-stock' : 'in-stock'));
        badge.textContent = !isAvailable ? 'Out of Stock' : (product.available_stock < 50 ? 'Low Stock' : 'In Stock');
        
        // Set rating
        document.getElementById('modalRating').innerHTML = generateStars(parseFloat(product.avg_rating));
        
        // Parse and display flavor profile
        try {
            const flavor = JSON.parse(product.flavor_profile);
            const flavorBars = document.getElementById('flavorBars');
            const flavorTags = document.getElementById('flavorTags');
            
            if (flavor.sweetness !== null) {
                const attributes = ['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'];
                const barsHtml = attributes.map(attr => {
                    const value = flavor[attr] || 0;
                    const percentage = (value / 10) * 100;
                    return `
                        <div class="flavor-bar-item">
                            <span class="flavor-name">${attr.charAt(0).toUpperCase() + attr.slice(1)}</span>
                            <div class="flavor-bar">
                                <div class="flavor-fill" style="width: ${percentage}%"></div>
                            </div>
                            <span class="flavor-percent">${percentage}%</span>
                        </div>
                    `;
                }).join('');
                flavorBars.innerHTML = barsHtml;
                
                if (flavor.tags && flavor.tags.length > 0) {
                    flavorTags.innerHTML = flavor.tags.map(tag => `<span class="flavor-tag">${tag}</span>`).join('');
                } else {
                    flavorTags.innerHTML = '';
                }
            } else {
                flavorBars.innerHTML = '<p class="no-flavor">No flavor profile available</p>';
                flavorTags.innerHTML = '';
            }
        } catch (e) {
            document.getElementById('flavorBars').innerHTML = '<p class="no-flavor">No flavor profile available</p>';
            document.getElementById('flavorTags').innerHTML = '';
        }
        
        // Set quantity input state
        const qtyInput = document.getElementById('modalQuantity');
        const addToCartBtn = document.getElementById('modalAddToCart');
        qtyInput.disabled = !isAvailable;
        addToCartBtn.disabled = !isAvailable;
        addToCartBtn.dataset.id = product.id;
        
        // Set View Full Details link
        document.getElementById('viewFullDetailsLink').href = `product.php?id=${product.id}`;
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    // Close modal
    const closeProductModal = () => {
        const modal = document.getElementById('detailModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    };

    // Initialize
    document.addEventListener('DOMContentLoaded', async () => {
        await updateCartCount();
        
        // Fetch ALL products
        productsData = await fetchAllProducts();
        
        // Render first page
        const firstPageProducts = getPageProducts();
        renderProducts(firstPageProducts);
        updatePagination();
        
        // Pagination button handlers
        document.getElementById('prevPage').addEventListener('click', () => {
            goToPage(currentPage - 1);
        });
        
        document.getElementById('nextPage').addEventListener('click', () => {
            goToPage(currentPage + 1);
        });
        
        // Event delegation for product actions
        document.addEventListener('click', async (e) => {
            // Quick View / View Details
            if (e.target.closest('.btn-quick-view') || e.target.closest('.btn-view-details')) {
                const btn = e.target.closest('.btn-quick-view') || e.target.closest('.btn-view-details');
                openProductModal(btn.dataset.id);
            }
            
            // Add to Cart (from card)
            if (e.target.closest('.btn-add-cart')) {
                const btn = e.target.closest('.btn-add-cart');
                if (btn.disabled) return;
                const productId = parseInt(btn.dataset.id);
                addItemToCart(productId, 1);
                await updateCartCount();
                // Show toast notification
                console.log('Added to cart:', productId);
            }
            
            // Wishlist Toggle
            if (e.target.closest('.wishlist-btn')) {
                const btn = e.target.closest('.wishlist-btn');
                const productId = parseInt(btn.dataset.id);
                toggleWishlistItem(productId);
                btn.classList.toggle('active');
            }
            
            // Modal Add to Cart
            if (e.target.closest('#modalAddToCart')) {
                const btn = e.target.closest('#modalAddToCart');
                if (btn.disabled) return;
                const qty = parseInt(document.getElementById('modalQuantity').value) || 1;
                const productId = parseInt(btn.dataset.id);
                addItemToCart(productId, qty);
                await updateCartCount();
                closeProductModal();
                console.log('Added to cart from modal:', productId, 'qty:', qty);
            }
            
            // Close modal
            if (e.target.closest('#detailCloseBtn') || e.target.classList.contains('detail-modal-overlay')) {
                closeProductModal();
            }
        });
        
        // ESC key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeProductModal();
            }
        });
    });
</script>

