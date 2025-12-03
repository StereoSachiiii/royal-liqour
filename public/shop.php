<?php require_once __DIR__ . "/header/header.php"; ?>

<div class="shop-all-page">
    <div class="shop-header">
        <h1 class="page-title">Shop All</h1>
        <div class="filters-bar">
            <input type="text" id="searchInput" placeholder="Search spirits..." class="search-input">

            <select id="categorySelect" class="filter-select">
                <option value="">All Categories</option>
                <!-- Filled via JS -->
            </select>

            <select id="sortSelect" class="filter-select">
                <option value="newest">Newest First</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="name_asc">Name: A to Z</option>
                <option value="name_desc">Name: Z to A</option>
                <option value="popularity">Best Selling</option>
            </select>
        </div>
    </div>

    <div class="shop-grid" id="productsGrid"></div>

    <div class="load-more" id="loadMore" style="display:none;margin:100px auto;text-align:center">
        <button class="btn-load-more">Load More</button>
    </div>
</div>

<!-- LUXURY MODAL — PERFECTLY CENTERED -->
<div class="detail-modal" id="detailModal">
    <div class="detail-modal-overlay"></div>
    <div class="detail-modal-content">
        <button class="detail-close-btn" id="detailCloseBtn">×</button>

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
                    <div class="detail-row"><span class="detail-label">Category</span><span id="modalCategory"></span></div>
                    <div class="detail-row"><span class="detail-label">Supplier</span><span id="modalSupplier"></span></div>
                    <div class="detail-row"><span class="detail-label">Sold</span><span id="modalUnitsSold"></span></div>
                    <div class="detail-row"><span class="detail-label">Stock</span><span id="modalStock"></span></div>
                </div>

                <div class="flavor-profile">
                    <h3 class="flavor-title">Flavor Profile</h3>
                    <div id="flavorBars"></div>
                    <div id="flavorTags" class="flavor-tags"></div>
                </div>

                <div class="modal-actions">
                    <button class="btn-wishlist-modal" id="modalWishlistBtn" title="Add to Wishlist">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>

                    <div class="quantity-selector">
                        <label>Qty</label>
                        <input type="number" id="modalQuantity" value="1" min="1">
                    </div>

                    <button class="btn-add-to-cart" id="modalAddToCart">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.shop-all-page {max-width:1480px;margin:0 auto;padding:90px 40px;font-family:"Canela",serif;background:#fff;color:#111}
.page-title {font-size:5.6rem;font-weight:300;font-style:italic;letter-spacing:-0.06em;text-align:center;margin-bottom:80px}
.filters-bar {display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:28px;margin-bottom:70px}
.search-input {width:420px;padding:20px 30px;font-size:1.4rem;border:1px solid #ddd;background:#fff}
.filter-select {padding:20px 30px;font-size:1.3rem;border:1px solid #ddd;background:#fff;min-width:240px}
.shop-grid {display:grid;grid-template-columns:repeat(auto-fill,minmax(390px,1fr));gap:80px 50px}
.product-card {background:#fff;transition:transform .5s;cursor:pointer}
.product-card:hover {transform:translateY(-20px)}
.product-card img {width:100%;aspect-ratio:1;object-fit:cover;background:#f9f9f9f}
.info {padding:40px 0;text-align:center}
.info h3 {font-size:2rem;margin:0 0 12px;font-weight:300}
.category {font-size:1.1rem;opacity:.7;margin:10px 0;font-style:italic}
.price {font-size:2.4rem;font-weight:300;margin:24px 0}
.tags .tag {display:inline-block;background:#111;color:#fff;padding:8px 18px;margin:5px;font-size:.95rem}
.actions {display:flex;align-items:center;justify-content:center;gap:16px;margin-top:20px}
.actions button {background:#111;color:#fff;padding:16px 34px;border:none;cursor:pointer;font-size:1.1rem;transition:.3s}
.actions button:hover {background:#333}
.actions .wishlist-btn {background:none;padding:10px}
.actions .wishlist-btn svg {stroke:#111;transition:all .3s}
.actions .wishlist-btn.active svg {fill:#111;stroke:none}
.out {color:#999;font-style:italic}

/* MODAL — FIXED & CENTERED */
.detail-modal {position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,0.94)}
.detail-modal.active {display:flex}
.detail-modal-content {background:#fff;max-width:1340px;width:94%;max-height:94vh;overflow-y:auto;position:relative;box-shadow:0 30px 100px rgba(0,0,0,0.5)}
.detail-close-btn {position:absolute;top:30px;right:30px;background:none;border:none;font-size:52px;color:#111;cursor:pointer;z-index:10}
.detail-grid {display:grid;grid-template-columns:1fr 1fr;gap:100px;padding:100px}
.detail-image {width:100%;aspect-ratio:1;object-fit:cover}
.modal-badge {position:absolute;top:40px;left:40px;background:#111;color:#fff;padding:14px 28px;font-size:1.2rem}
.modal-name {font-size:4.8rem;font-weight:300;font-style:italic;margin:0 0 30px;letter-spacing:-0.04em}
.modal-price {font-size:3.8rem;font-weight:300}
.modal-description {margin:40px 0;font-size:1.4rem;line-height:1.9;opacity:.9}
.detail-row {display:flex;justify-content:space-between;padding:20px 0;border-top:1px solid #eee;font-size:1.3rem}
.detail-label {opacity:.7}
.flavor-title {font-size:2rem;margin:70px 0 40px;font-weight:300}
.flavor-bar-item {display:flex;align-items:center;gap:24px;margin-bottom:28px}
.flavor-name {min-width:140px;font-size:1.3rem}
.flavor-bar {flex:1;height:12px;background:#eee;position:relative;overflow:hidden}
.flavor-fill {height:100%;background:#111;transition:width .8s ease}
.flavor-percent {font-size:1.1rem;width:70px;text-align:right}
.flavor-tag {display:inline-block;background:#111;color:#fff;padding:10px 20px;margin:8px 10px 0 0;font-size:1.1rem}
.modal-actions {display:flex;align-items:center;gap:40px;margin-top:80px;flex-wrap:wrap}
.quantity-selector input {width:100px;padding:18px;font-size:1.4rem;text-align:center;border:1px solid #ddd}
.btn-add-to-cart {background:#111;color:#fff;padding:22px 60px;font-size:1.5rem;border:none;cursor:pointer}
.btn-add-to-cart:disabled {background:#999;cursor:not-allowed}
.btn-wishlist-modal svg {stroke:#111;transition:all .3s}
.btn-wishlist-modal.active svg {fill:#111;stroke:none}
</style>

<script type="module">
import { cartAddItem } from './utils/cart.js';
import { updateCartCount } from './header/header.js';
import { addToWishlist, getWishlist, isInWishlist } from './utils/wishlist.js';

let productsData = [];
let currentOffset = 0;
const limit = 24;
let loading = false;
let hasMore = true;

const fetchCategories = async () => {
    try {
        const res = await fetch('http://localhost/royal-liquor/admin/api/categories.php?enriched=true&limit=100');
        const json = await res.json();
        if (json.success && json.data) {
            const select = document.getElementById('categorySelect');
            json.data.forEach(cat => {
                const opt = new Option(cat.name, cat.id);
                select.appendChild(opt);
            });
        }
    } catch (e) { console.error('Failed to load categories', e); }
};

const fetchProducts = async (reset = false) => {
    if (loading || (!hasMore && !reset)) return;
    loading = true;
    if (reset) { currentOffset = 0; hasMore = true; productsData = []; }

    const params = new URLSearchParams({
        enriched: 'true',
        limit,
        offset: currentOffset,
        search: document.getElementById('searchInput').value.trim(),
        sort: document.getElementById('sortSelect').value
    });

    const catId = document.getElementById('categorySelect').value;
    if (catId) params.append('category_id', catId);

    try {
        const res = await fetch(`http://localhost/royal-liquor/admin/api/products.php?${params}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        const products = json.data || [];
        const container = document.getElementById('productsGrid');

        if (reset) container.innerHTML = '';
        if (products.length === 0) {
            container.innerHTML = reset ? '<div class="empty>No products found</div>' : '';
            hasMore = false;
        } else {
            container.innerHTML += products.map(renderProductCard).join('');
            productsData.push(...products);
            currentOffset += products.length;
            hasMore = products.length === limit;
            document.getElementById('loadMore').style.display = hasMore ? 'block' : 'none';
        }
    } catch (err) {
        console.error(err);
    } finally {
        loading = false;
    }
};

const renderProductCard = (p) => {
    const price = (p.price_cents / 100).toFixed(2);
    const available = p.available_stock > 0;
    const flavor = JSON.parse(p.flavor_profile);
    const tags = (flavor.tags || []).slice(0,3).map(t => `<span class="tag">${t}</span>`).join('');
    const inWish = isInWishlist(p.id);

    return `
        <div class="product-card" data-id="${p.id}">
            <img src="${p.image_url}" alt="${p.name}" loading="lazy">
            <div class="info">
                <h3>${p.name}</h3>
                <p class="category">${p.category_name || '—'}</p>
                <div class="price">$${price}</div>
                ${tags ? `<div class="tags">${tags}</div>` : ''}
                <div class="actions">
                    <button class="wishlist-btn ${inWish ? 'active' : ''}" data-id="${p.id}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="${inWish ? '#111' : 'none'}" stroke="#111" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>
                    <button class="view-btn" data-id="${p.id}">View Details</button>
                    ${available 
                        ? `<button class="add-btn" data-id="${p.id}">Add to Cart</button>`
                        : '<span class="out">Out of Stock</span>'
                    }
                </div>
            </div>
        </div>
    `;
};

const generateStars = (r) => {
    if (!r) return '<span class="no-rating">No ratings</span>';
    let s = '';
    for (let i = 0; i < 5; i++) {
        if (i < Math.floor(r)) s += '★';
        else if (i === Math.floor(r) && r % 1 >= 0.5) s += '★';
        else s += '★';
    }
    return `<div class="stars">${s}</div><span class="rating-value">${r}</span>`;
};

const openProductModal = async (id) => {
    const p = productsData.find(x => x.id === parseInt(id));
    if (!p) return;

    const available = p.available_stock > 0;
    const price = (p.price_cents / 100).toFixed(2);

    document.getElementById('modalImage').src = p.image_url;
    document.getElementById('modalName').textContent = p.name;
    document.getElementById('modalPrice').textContent = `$${price}`;
    document.getElementById('modalDescription').textContent = p.description || 'No description.';
    document.getElementById('modalCategory').textContent = p.category_name || '—';
    document.getElementById('modalSupplier').textContent = p.supplier_name || '—';
    document.getElementById('modalUnitsSold').textContent = p.units_sold || 0;
    document.getElementById('modalStock').textContent = available ? `${p.available_stock} units` : 'Out of Stock';

    const badge = document.getElementById('modalBadge');
    badge.className = 'modal-badge ' + (available ? (p.available_stock < 50 ? 'low-stock' : 'in-stock') : 'out-of-stock');
    badge.textContent = available ? (p.available_stock < 50 ? 'Low Stock' : 'In Stock') : 'Out of Stock';

    document.getElementById('modalRating').innerHTML = generateStars(parseFloat(p.avg_rating));

    // Flavor profile
    try {
        const f = JSON.parse(p.flavor_profile);
        const bars = document.getElementById('flavorBars');
        const tags = document.getElementById('flavorTags');
        if (f.sweetness != null) {
            const attrs = ['sweetness','bitterness','strength','smokiness','fruitiness','spiciness'];
            bars.innerHTML = attrs.map(a => {
                const v = f[a] ?? 5;
                return `<div class="flavor-bar-item">
                    <span class="flavor-name">${a.charAt(0).toUpperCase() + a.slice(1)}</span>
                    <div class="flavor-bar"><div class="flavor-fill" style="width:${(v/10)*100}%"></div></div>
                    <span>${v}/10</span>
                </div>`;
            }).join('');
            tags.innerHTML = (f.tags || []).map(t => `<span class="flavor-tag">${t}</span>`).join('') || '<span style="opacity:.6">No notes</span>';
        } else throw 1;
    } catch {
        document.getElementById('flavorBars').innerHTML = '<p>No flavor profile</p>';
        document.getElementById('flavorTags').innerHTML = '';
    }

    // Buttons
    const qtyInput = document.getElementById('modalQuantity');
    const addBtn = document.getElementById('modalAddToCart');
    const wishBtn = document.getElementById('modalWishlistBtn');
    qtyInput.disabled = !available;
    qtyInput.value = 1;
    addBtn.disabled = !available;
    addBtn.dataset.id = p.id;
    wishBtn.classList.toggle('active', isInWishlist(p.id));
    wishBtn.dataset.id = p.id;

    document.getElementById('detailModal').classList.add('active');
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    document.getElementById('detailModal').classList.remove('active');
    document.body.style.overflow = '';
};

// Events
document.getElementById('searchInput').addEventListener('input', () => fetchProducts(true));
document.getElementById('categorySelect').addEventListener('change', () => fetchProducts(true));
document.getElementById('sortSelect').addEventListener('change', () => fetchProducts(true));
document.getElementById('loadMore').addEventListener('click', () => fetchProducts());

document.addEventListener('click', async e => {
    const btn = e.target.closest('button');
    if (!btn) return;

    if (btn.classList.contains('view-btn')) openProductModal(btn.dataset.id);

    if (btn.classList.contains('add-btn')) {
        await cartAddItem(btn.dataset.id, 1);
        updateCartCount();
    }

    if (btn.classList.contains('wishlist-btn') || btn.id === 'modalWishlistBtn') {
        const id = btn.dataset.id;
        await addToWishlist(id);
        btn.classList.toggle('active', isInWishlist(id));
        btn.querySelector('svg')?.setAttribute('fill', isInWishlist(id) ? '#111' : 'none');
    }

    if (btn.id === 'modalAddToCart' && !btn.disabled) {
        const qty = parseInt(document.getElementById('modalQuantity').value) || 1;
        await cartAddItem(btn.dataset.id, qty);
        updateCartCount();
        closeModal();
    }

    if (btn.id === 'detailCloseBtn' || btn.classList.contains('detail-modal-overlay')) {
        closeModal();
    }
});

document.addEventListener('keydown', e => e.key === 'Escape' && closeModal());

// Init
await updateCartCount();
await fetchCategories();
fetchProducts(true);
</script>