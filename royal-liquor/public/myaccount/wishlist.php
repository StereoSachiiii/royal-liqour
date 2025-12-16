<?php
/**
 * MyAccount - Wishlist
 * Saved products for later
 */
$pageName = 'wishlist';
$pageTitle = 'My Wishlist - Royal Liquor';
require_once __DIR__ . "/_layout.php";
?>

<h1 class="account-page-title">My Wishlist</h1>

<!-- Wishlist Grid -->
<div class="wishlist-grid" id="wishlistGrid">
    <!-- Sample Wishlist Item -->
    <div class="wishlist-item card" data-product-id="1">
        <button class="wishlist-remove" title="Remove from wishlist">×</button>
        <div class="wishlist-image skeleton skeleton-card" style="height:180px;"></div>
        <div class="wishlist-details">
            <h3 class="wishlist-name">Macallan 18 Year Sherry Oak</h3>
            <p class="wishlist-category">Single Malt Scotch</p>
            <div class="wishlist-price">Rs. 85,000</div>
            <div class="wishlist-stock in-stock">In Stock</div>
        </div>
        <div class="wishlist-actions">
            <button class="btn btn-gold btn-full btn-add-cart">Add to Cart</button>
        </div>
    </div>

    <div class="wishlist-item card" data-product-id="2">
        <button class="wishlist-remove" title="Remove from wishlist">×</button>
        <div class="wishlist-image skeleton skeleton-card" style="height:180px;"></div>
        <div class="wishlist-details">
            <h3 class="wishlist-name">Hennessy XO</h3>
            <p class="wishlist-category">Cognac</p>
            <div class="wishlist-price">Rs. 48,500</div>
            <div class="wishlist-stock in-stock">In Stock</div>
        </div>
        <div class="wishlist-actions">
            <button class="btn btn-gold btn-full btn-add-cart">Add to Cart</button>
        </div>
    </div>

    <div class="wishlist-item card" data-product-id="3">
        <button class="wishlist-remove" title="Remove from wishlist">×</button>
        <div class="wishlist-image skeleton skeleton-card" style="height:180px;"></div>
        <div class="wishlist-details">
            <h3 class="wishlist-name">Dom Pérignon Vintage 2012</h3>
            <p class="wishlist-category">Champagne</p>
            <div class="wishlist-price">Rs. 65,000</div>
            <div class="wishlist-stock out-of-stock">Out of Stock</div>
        </div>
        <div class="wishlist-actions">
            <button class="btn btn-outline btn-full" disabled>Out of Stock</button>
        </div>
    </div>
</div>

<!-- Empty State -->
<div class="empty-state hidden" id="emptyWishlist">
    <div class="empty-state-icon">❤️</div>
    <h3 class="empty-state-title">Your Wishlist is Empty</h3>
    <p class="empty-state-text">Save your favorite products here for easy access later.</p>
    <a href="<?= getPageUrl('shop') ?>" class="btn btn-gold">Browse Products</a>
</div>

<style>
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--space-xl);
}

.wishlist-item {
    position: relative;
    padding: 0;
    overflow: hidden;
}

.wishlist-remove {
    position: absolute;
    top: var(--space-sm);
    right: var(--space-sm);
    width: 32px;
    height: 32px;
    background: var(--white);
    border: none;
    border-radius: var(--radius-full);
    font-size: 1.25rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-sm);
    z-index: 10;
    transition: all var(--duration-fast) var(--ease-out);
}

.wishlist-remove:hover {
    background: var(--error);
    color: var(--white);
}

.wishlist-image {
    width: 100%;
}

.wishlist-details {
    padding: var(--space-lg);
}

.wishlist-name {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: var(--space-xs);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.wishlist-category {
    font-size: 0.875rem;
    color: var(--gray-500);
    margin-bottom: var(--space-sm);
}

.wishlist-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--black);
    margin-bottom: var(--space-sm);
}

.wishlist-stock {
    font-size: 0.875rem;
    font-weight: 500;
}

.wishlist-stock.in-stock {
    color: var(--success);
}

.wishlist-stock.out-of-stock {
    color: var(--error);
}

.wishlist-actions {
    padding: 0 var(--space-lg) var(--space-lg);
}

@media (max-width: 640px) {
    .wishlist-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script type="module">
import { getWishlist, removeFromWishlist } from '<?= BASE_URL ?>utils/wishlist-storage.js';
import { addItemToCart } from '<?= BASE_URL ?>utils/cart-storage.js';
import { updateCartCount } from '<?= BASE_URL ?>utils/header.js';
import { API } from '<?= BASE_URL ?>utils/api-helper.js';
import { toast } from '<?= BASE_URL ?>utils/toast.js';

// Load wishlist data
async function loadWishlist() {
    const wishlistGrid = document.getElementById('wishlistGrid');
    const emptyState = document.getElementById('emptyWishlist');
    
    const wishlist = getWishlist();
    
    // Clear sample items
    wishlistGrid.innerHTML = '';
    
    if (wishlist.length === 0) {
        wishlistGrid.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }
    
    wishlistGrid.classList.remove('hidden');
    emptyState.classList.add('hidden');
    
    // Fetch all products to get details
    try {
        const response = await API.products.list({ limit: 100 });
        const products = response.data || [];
        
        wishlist.forEach(wishlistItem => {
            // Find product details
            const product = products.find(p => p.id === wishlistItem.id) || wishlistItem;
            const price = product.price_cents ? product.price_cents / 100 : (product.price || 0);
            const inStock = (product.available_stock || product.stock || 10) > 0;
            
            const card = document.createElement('div');
            card.className = 'wishlist-item card';
            card.dataset.productId = product.id;
            card.innerHTML = `
                <button class="wishlist-remove" title="Remove from wishlist">×</button>
                <a href="<?= BASE_URL ?>product.php?id=${product.id}" class="wishlist-image" style="display:block;height:180px;background:var(--gray-100);">
                    ${product.image_url ? `<img src="${product.image_url}" alt="${product.name}" style="width:100%;height:100%;object-fit:cover;">` : ''}
                </a>
                <div class="wishlist-details">
                    <h3 class="wishlist-name">${product.name}</h3>
                    <p class="wishlist-category">${product.category_name || 'Premium Spirits'}</p>
                    <div class="wishlist-price">$${price.toFixed(2)}</div>
                    <div class="wishlist-stock ${inStock ? 'in-stock' : 'out-of-stock'}">${inStock ? 'In Stock' : 'Out of Stock'}</div>
                </div>
                <div class="wishlist-actions">
                    <button class="btn ${inStock ? 'btn-gold' : 'btn-outline'} btn-full btn-add-cart" ${!inStock ? 'disabled' : ''}>
                        ${inStock ? 'Add to Cart' : 'Out of Stock'}
                    </button>
                </div>
            `;
            wishlistGrid.appendChild(card);
        });
    } catch (error) {
        console.error('[Wishlist] Failed to load products:', error);
        toast.error('Failed to load wishlist items');
    }
}

// Handle remove and add to cart clicks
document.addEventListener('click', async (e) => {
    // Remove from wishlist
    if (e.target.classList.contains('wishlist-remove')) {
        const item = e.target.closest('.wishlist-item');
        const productId = parseInt(item.dataset.productId);
        
        removeFromWishlist(productId);
        item.style.opacity = '0';
        item.style.transform = 'scale(0.9)';
        
        setTimeout(() => {
            item.remove();
            
            // Check if wishlist is now empty
            if (document.querySelectorAll('.wishlist-item').length === 0) {
                document.getElementById('wishlistGrid').classList.add('hidden');
                document.getElementById('emptyWishlist').classList.remove('hidden');
            }
        }, 200);
        
        toast.gold('Removed from wishlist');
    }
    
    // Add to cart
    if (e.target.classList.contains('btn-add-cart') && !e.target.disabled) {
        const item = e.target.closest('.wishlist-item');
        const productId = parseInt(item.dataset.productId);
        const btn = e.target;
        
        btn.disabled = true;
        btn.textContent = 'Adding...';
        
        try {
            await addItemToCart(productId, 1);
            await updateCartCount();
            
            btn.textContent = '✓ Added';
            toast.success('Added to cart!');
            
            setTimeout(() => {
                btn.disabled = false;
                btn.textContent = 'Add to Cart';
            }, 2000);
        } catch (error) {
            toast.error('Failed to add to cart');
            btn.disabled = false;
            btn.textContent = 'Add to Cart';
        }
    }
});

// Add transition style
const style = document.createElement('style');
style.textContent = '.wishlist-item { transition: opacity 0.2s, transform 0.2s; }';
document.head.appendChild(style);

loadWishlist();
console.log('[Wishlist] Wishlist page ready');
</script>

<?php require_once __DIR__ . "/_layout_end.php"; ?>
