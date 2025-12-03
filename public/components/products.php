<?php
require_once __DIR__ . "../../header/header.php";
?>

<div class="products-page">
    <div class="section-title">Our Finest Selection</div>
    <div class="products-container" id="productsContainer">
        <!-- Products will be inserted here -->
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
            </div>
        </div>
    </div>
</div>

<script type="module">
    import { cartAddItem } from './utils/cart.js';
    import { updateCartCount } from './header/header.js';
    import { addToWishlist } from './utils/wishlist.js';

    // Store products in memory
    let productsData = [];

    // Fetch top products
    const fetchTopProducts = async () => {
        try {
            const response = await fetch('http://localhost/royal-liquor/admin/api/products.php?top=10', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin'
            });

            if (!response.ok) throw new Error('Failed to fetch products');
            
            const result = await response.json();
            if (!result.success) throw new Error('API returned error');
            
            return result.data || [];
        } catch (error) {
            console.error('Error fetching products:', error);
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
    const renderProductCard = (product) => {
        const isAvailable = product.is_available && product.available_stock > 0;
        const price = (product.price_cents / 100).toFixed(2);
        const stockClass = !isAvailable ? 'out-of-stock' : (product.available_stock < 50 ? 'low-stock' : 'in-stock');
        const stockText = !isAvailable ? 'Out of Stock' : `${product.available_stock} Available`;
        
        // Parse flavor profile for tags
        let tags = [];
        try {
            const flavor = JSON.parse(product.flavor_profile);
            tags = flavor.tags || [];
        } catch (e) {
            tags = [];
        }
        
        const tagsHtml = tags.slice(0, 2).map(tag => `<span class="product-tag">${tag}</span>`).join('');
        
        return `
            <div class="product-card ${!isAvailable ? 'unavailable' : ''}" data-id="${product.id}">
                <div class="product-image-wrapper">
                    <img src="${product.image_url}" alt="${product.name}" class="product-image">
                    <span class="stock-badge ${stockClass}">${stockText}</span>
                    ${product.units_sold > 100 ? '<span class="bestseller-badge">Bestseller</span>' : ''}
                </div>
                
                <div class="product-body">
                    <h3 class="product-title">${product.name}</h3>
                    <p class="product-desc">${product.description}</p>
                    
                    <div class="product-rating">
                        ${generateStars(parseFloat(product.avg_rating))}
                        ${product.feedback_count > 0 ? `<span class="review-count">(${product.feedback_count})</span>` : ''}
                    </div>
                    
                    ${tagsHtml ? `<div class="product-tags">${tagsHtml}</div>` : ''}
                    
                    <div class="product-footer">
                        <div class="product-price">$${price}</div>
                        <div class="product-actions">
                            <button class="btn-wishlist" data-id="${product.id}" title="Add to Wishlist">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </button>
                            <button class="btn-quick-add" data-id="${product.id}" ${!isAvailable ? 'disabled' : ''}>
                                Quick Add
                            </button>
                            <button class="btn-view-details" data-id="${product.id}">Details</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    // Render all products
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
        
        // Fetch products
        productsData = await fetchTopProducts();
        renderProducts(productsData);
        
        // Event delegation for product actions
        document.addEventListener('click', async (e) => {
            // View Details
            if (e.target.closest('.btn-view-details')) {
                const btn = e.target.closest('.btn-view-details');
                openProductModal(btn.dataset.id);
            }
            
            // Quick Add
            if (e.target.closest('.btn-quick-add')) {
                const btn = e.target.closest('.btn-quick-add');
                if (btn.disabled) return;
                await cartAddItem(parseInt(btn.dataset.id), 1);
                await updateCartCount();
            }
            
            // Wishlist
            if (e.target.closest('.btn-wishlist')) {
                const btn = e.target.closest('.btn-wishlist');
                await addToWishlist(parseInt(btn.dataset.id));
            }
            
            // Modal Add to Cart
            if (e.target.closest('#modalAddToCart')) {
                const btn = e.target.closest('#modalAddToCart');
                if (btn.disabled) return;
                const qty = parseInt(document.getElementById('modalQuantity').value) || 1;
                await cartAddItem(parseInt(btn.dataset.id), qty);
                await updateCartCount();
                closeProductModal();
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

