<?php require_once __DIR__ . '/../../header/header.php'; ?>
<div class="wishlist-container">
    <div class="wishlist-header">
        <h1>My Wishlist</h1>
        <p class="wishlist-count"><span id="wishlist-count">0</span> items</p>
        <button id="add-all-to-cart-btn" class="wishlist-btn add-all-btn" style="display:none;">
            Add All To Cart
        </button>
    </div>

    <div id="wishlist-content">
        <div class="loading">Loading your wishlist...</div>
    </div>
</div>

<div id="toast-container"></div>
<div id="cart-modal-wishlist" class="cart-modal-wishlist">
    <div class="cart-modal-wishlist-content">
        <p><span id="modal-item-count">X</span> items added to cart!</p>
        <a href="/cart" class="visit-cart-btn">Visit Cart</a>
    </div>
</div>

<style>
    /* Paste the updated CSS from section 3 here */
</style>

<script type="module">
    import { getWishlist, saveWishlist } from '../../utils/wishlist.js';
    import { cartAddItem, updateCartCount } from '../../utils/cart.js';
    import { updateCartCount} from '../../header.header.js'

    function showToast(message, duration = 3000) {
        const toastContainer = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        
        toastContainer.appendChild(toast);
        
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, duration);
    }

    function showCartModal(count) {
        const modal = document.getElementById('cart-modal-wishlist');
        document.getElementById('modal-item-count').textContent = count;
        modal.classList.add('show-modal');
        
        setTimeout(() => {
            modal.classList.remove('show-modal');
        }, 5000);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    async function loadWishlist() {
        const wishlistContent = document.getElementById('wishlist-content');
        const wishlistCountElement = document.getElementById('wishlist-count');
        const addAllBtn = document.getElementById('add-all-to-cart-btn');
        
        try {
            const wishlist = await getWishlist();
            
            wishlistCountElement.textContent = wishlist.length;

            if (wishlist.length === 0) {
                addAllBtn.style.display = 'none';
                wishlistContent.innerHTML = `
                    <div class="empty-wishlist">
                        <h2>Your wishlist is empty</h2>
                        <p>Start adding items you love to your wishlist!</p>
                        <a href="<?= BASE_URL ?>" class="continue-shopping-btn">Continue Shopping</a>
                    </div>
                `;
                return;
            }

            addAllBtn.style.display = 'block';

            // Grouping and Sorting
            const groupedWishlist = wishlist.reduce((acc, item) => {
                // Assuming 'added_date' is available in the item object from getWishlist()
                const dateKey = item.added_date ? formatDate(item.added_date) : 'Unsorted Items';
                if (!acc[dateKey]) {
                    acc[dateKey] = [];
                }
                acc[dateKey].push(item);
                return acc;
            }, {});

            const sortedDateKeys = Object.keys(groupedWishlist).sort((a, b) => new Date(b) - new Date(a));

            const listHTML = sortedDateKeys.map(dateKey => {
                const items = groupedWishlist[dateKey];
                return `
                    <div class="wishlist-date-group">
                        <div class="date-header">Wishlist made on ${dateKey}</div>
                        <div class="wishlist-list">
                            ${items.map(item => `
                                <div class="wishlist-item" data-item-id="${item.id}">
                                    <div class="item-summary">
                                        <div class="wishlist-item-name">${item.name}</div>
                                        <div class="wishlist-item-price">Rs. ${parseFloat(item.price).toFixed(2)}</div>
                                    </div>
                                    <div class="item-details">
                                        <img src="<?= BASE_URL ?>${item.image}" alt="${item.name}" class="wishlist-item-image">
                                        <div class="detail-actions">
                                            <div class="wishlist-item-quantity">
                                                <label for="qty-${item.id}">Qty:</label>
                                                <select id="qty-${item.id}" class="item-qty-select" data-item-id="${item.id}">
                                                    ${Array.from({length: 10}, (_, i) => i + 1).map(qty => `
                                                        <option value="${qty}">${qty}</option>
                                                    `).join('')}
                                                </select>
                                            </div>
                                            <button class="wishlist-btn add-to-cart-btn" data-item-id="${item.id}">
                                                Add to Cart
                                            </button>
                                            <button class="wishlist-btn remove-btn" data-item-id="${item.id}">
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }).join('');

            wishlistContent.innerHTML = listHTML;

            // Add to Cart Logic
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const button = e.target;
                    const itemId = parseInt(button.dataset.itemId);
                    const itemElement = button.closest('.wishlist-item');
                    const qtySelect = itemElement.querySelector(`.item-qty-select[data-item-id="${itemId}"]`);
                    const quantity = parseInt(qtySelect.value);
                    const originalText = button.textContent;
                    
                    try {
                        button.disabled = true;
                        button.textContent = 'Adding...';
                        
                        await cartAddItem(itemId, quantity);
                        await updateCartCount()
                        
                        showToast(`"${itemElement.querySelector('.wishlist-item-name').textContent}" added to cart!`);
                        
                        await removeFromWishlist(itemId);
                        
                    } catch (error) {
                        console.error('Failed to add to cart:', error);
                        button.textContent = 'Failed';
                        button.style.backgroundColor = '#dc3545';
                        
                        setTimeout(() => {
                            button.textContent = originalText;
                            button.style.backgroundColor = '';
                            button.disabled = false;
                        }, 2000);
                    }
                });
            });

            // Remove Logic
            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const itemId = parseInt(e.target.dataset.itemId);
                        await removeFromWishlist(itemId);
                        showToast('Item removed from wishlist.', 2000);
                    
                });
            });
            
            // Add All to Cart Logic
            addAllBtn.addEventListener('click', async () => {
                addAllBtn.disabled = true;
                addAllBtn.textContent = 'Processing...';

                let itemsAdded = 0;
                let newWishlist = [];
                const currentWishlist = await getWishlist();
                
                for (const item of currentWishlist) {
                    try {
                        // Add 1 unit of each item
                        await cartAddItem(item.id, 1);
                        itemsAdded++;
                        updateCartCount()
                    } catch (error) {
                        console.error(`Failed to add item ${item.id} to cart:`, error);
                        // If it fails, keep it in the wishlist
                        newWishlist.push(item);
                    }
                }

                saveWishlist(newWishlist);
                await loadWishlist();
                
                addAllBtn.textContent = 'Add All To Cart';
                addAllBtn.disabled = false;

                if (itemsAdded > 0) {
                    showCartModal(itemsAdded);
                } else {
                    showToast('No items were added to the cart.', 3000);
                }
            });

        } catch (error) {
            console.error('Failed to load wishlist:', error);
            wishlistContent.innerHTML = `
                <div class="empty-wishlist">
                    <h2>Error loading wishlist</h2>
                    <p>Please try again later.</p>
                </div>
            `;
        }
    }

    async function removeFromWishlist(itemId) {
        try {
            const wishlist = await getWishlist();
            const newWishlist = wishlist.filter(item => item.id !== itemId);
            saveWishlist(newWishlist);
            await loadWishlist();
        } catch (error) {
            console.error('Failed to remove from wishlist:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', loadWishlist);
</script>

</body>
</html>