<div class="floating-icons-container">
    <a href="./myaccount/wishlist" class="floating-icon wishlist-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
        </svg>
    </a>
    
    <a href="./cart.php" class="floating-icon cart-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
    </a>
</div>

<style>
    .floating-icons-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        z-index: 1000;
    }
    
    .floating-icon {
        position: relative;
        width: 56px;
        height: 56px;
        background: #fff;
        border: 2px solid #000;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        color: #000;
        text-decoration: none;
    }
    
    .floating-icon:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        border-color: #d4af37;
    }
    
    .floating-icon svg {
        stroke: #000;
    }
    
    .floating-icon:hover svg {
        stroke: #d4af37;
    }
    
    .icon-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        min-width: 20px;
        height: 20px;
        background: #d4af37; 
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .cart-icon .icon-badge {
        background: #000; 
        color: #fff;
    }
</style>

<script type="module">
    import { getCartItemCount } from '../utils/cart-storage.js';
    import { getWishlist } from '../utils/wishlist-storage.js';

    async function updateCounts() {
        // Update cart count
        const cartCount = getCartItemCount();
        const cartBadge = document.querySelector('.cart-icon .icon-badge');
        if (cartCount && cartCount > 0) {
            const displayCount = cartCount > 99 ? '99+' : cartCount;
            if (cartBadge) {
                cartBadge.textContent = displayCount;
            } else {
                const newBadge = document.createElement('span');
                newBadge.className = 'icon-badge';
                newBadge.textContent = displayCount;
                document.querySelector('.cart-icon').appendChild(newBadge);
            }
        } else if (cartBadge) {
            cartBadge.remove();
        }

        // Update wishlist count
        try {
            const wishlistItems = getWishlist();
            const wishlistCount = wishlistItems.length;
            const wishlistBadge = document.querySelector('.wishlist-icon .icon-badge');
            if (wishlistCount > 0) {
                const displayCount = wishlistCount > 99 ? '99+' : wishlistCount;
                if (wishlistBadge) {
                    wishlistBadge.textContent = displayCount;
                } else {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'icon-badge';
                    newBadge.textContent = displayCount;
                    document.querySelector('.wishlist-icon').appendChild(newBadge);
                }
            } else if (wishlistBadge) {
                wishlistBadge.remove();
            }
        } catch (error) {
            console.error('Error updating wishlist count:', error);
        }
    }

    updateCounts();

    window.addEventListener('storage', (event) => {
        if (event.key === 'cart' || event.key === 'wishlist') {
            updateCounts();
        }
    });

    setInterval(updateCounts, 5000);
</script>