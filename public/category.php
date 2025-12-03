<?php require_once __DIR__ . '/header/header.php'; ?>

<?php
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($categoryId <= 0) {
    echo "<h2 style='text-align:center;padding:100px;color:#e74c3c;font-family:system-ui;'>Invalid Category ID</h2>";
    exit;
}
?>

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:"Canela",Georgia,"Playfair Display",serif;
    background:#ffffff;
    color:#111111;
    line-height:1.7;
    min-height:100vh;
    font-size:18px;
    font-weight:300;
}
.category-page{max-width:1380px;margin:0 auto;padding:40px 20px}

/* HERO */
.hero{
    background:#ffffff;
    border:1px solid #e8e8e8;
    border-radius:0;
    padding:100px 80px 90px;
    margin-bottom:100px;
    position:relative;
    text-align:center;
}
.hero .name{
    font-size:5.5rem;
    font-weight:400;
    letter-spacing:-0.04em;
    margin:0 0 24px;
    font-style:italic;
    line-height:1;
}
.hero .description{
    font-size:1.55rem;
    max-width:960px;
    margin:0 auto 40px;
    opacity:0.88;
    font-style:italic;
}
.meta{
    font-size:1.25rem;
    opacity:0.78;
    margin-bottom:32px;
    letter-spacing:0.5px;
    text-transform:uppercase;
    font-weight:500;
}
.pricing{
    font-size:2.8rem;
    font-weight:300;
    letter-spacing:0.5px;
    margin:40px 0 0;
    font-style:italic;
}
.flavor-profile{
    margin-top:70px;
    padding:40px 60px;
    border-top:1px solid #e8e8e8;
    font-size:1.35rem;
    line-height:1.9;
    font-style:italic;
    opacity:0.9;
}

/* PRODUCTS SECTION */
.products-section h2{
    font-size:3.8rem;
    text-align:center;
    margin:0 0 90px;
    font-weight:300;
    font-style:italic;
    letter-spacing:-0.02em;
}
.products-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(360px,1fr));
    gap:60px 40px;
    padding:0 20px;
}

/* PRODUCT CARD */
.product-card{
    background:#ffffff;
    border:1px solid #e8e8e8;
    transition:transform .5s ease,box-shadow .5s ease;
    position:relative;
    display:flex;
    flex-direction:column;
    height:100%;
}
.product-card:hover{
    transform:translateY(-12px);
    box-shadow:0 30px 60px rgba(0,0,0,0.08);
}
.product-card.out{opacity:0.45}
.product-card img{
    width:100%;
    height:420px;
    object-fit:cover;
    background:#f8f8f8;
}
.product-card h3{
    font-size:2rem;
    margin:36px 36px 12px;
    font-weight:400;
    letter-spacing:-0.02em;
    font-style:italic;
}
.product-card>p{
    margin:0 36px 32px;
    font-size:1.15rem;
    opacity:0.8;
    flex-grow:1;
    line-height:1.8;
}
.tags{
    margin:0 36px 28px;
    display:flex;
    flex-wrap:wrap;
    gap:12px;
}
.tag{
    background:#f5f5f5;
    color:#333;
    padding:7px 16px;
    border-radius:0;
    font-size:0.95rem;
    font-weight:500;
    border:1px solid #e8e8e8;
}

/* BADGES */
.badge{
    position:absolute;
    top:24px;
    left:24px;
    padding:9px 18px;
    background:#111;
    color:#fff;
    font-size:0.9rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:1px;
}
.badge.out{background:#999}
.bestseller{
    position:absolute;
    top:24px;
    right:24px;
    background:#ffffff;
    color:#111;
    padding:9px 18px;
    font-size:0.85rem;
    font-weight:700;
    border:1px solid #111;
}

/* FOOTER */
.footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:32px 36px;
    border-top:1px solid #e8e8e8;
    margin-top:auto;
}
.price{
    font-size:2.6rem;
    font-weight:300;
    letter-spacing:0.5px;
    font-style:italic;
}
.add-btn{
    background:#111;
    color:#fff;
    border:none;
    padding:18px 40px;
    font-size:1.05rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:2px;
    cursor:pointer;
    transition:all .3s ease;
}
.add-btn:hover:not(:disabled){background:#333}
.add-btn:disabled{
    background:#ccc;
    color:#888;
    cursor:not-allowed;
}

/* EMPTY / LOADING */
.products-grid>div[style*="text-align"]{
    grid-column:1/-1;
    text-align:center;
    padding:140px 20px;
    font-size:1.6rem;
    opacity:0.6;
    font-style:italic;
}

/* RESPONSIVE */
@media(max-width:1024px){
    .hero{padding:80px 50px}
    .hero .name{font-size:4.5rem}
    .products-grid{grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:50px}
}
@media(max-width:768px){
    .hero{padding:70px 30px}
    .hero .name{font-size:3.8rem}
    .hero .description{font-size:1.4rem}
    .products-section h2{font-size:3.2rem}
    .product-card img{height:360px}
    .footer{flex-direction:column;gap:24px;text-align:center}
    .add-btn{width:100%}
}
@media(max-width:480px){
    .hero .name{font-size:3.2rem}
    .pricing{font-size:2.2rem}
    .product-card img{height:300px}
}

</style>

<section class="category-page">
    <div class="hero">
        <h1 class="name">Loading category...</h1>
        <p class="description"></p>
        <div class="meta">
            <span class="products-count">0 products</span> • 
            <span class="stock-info">Checking stock...</span>
        </div>
        <div class="pricing"></div>
        <div class="flavor-profile"></div>
    </div>

    <div class="products-section">
        <h2>Products in this Category</h2>
        <div class="products-grid" id="products-grid">
            <div>Loading products...</div>
        </div>
    </div>
</section>

<script type="module">
    import { updateCartCount } from './header/header.js';

    const categoryId = <?= $categoryId ?>;
    const API_BASE = "http://localhost/royal-liquor/admin/api/categories.php";

    const fetchCategory = async () => {
        const res = await fetch(`${API_BASE}?id=${categoryId}&enriched=true`, { credentials: "same-origin" });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || "Failed to load category");
        return Array.isArray(json.data) ? json.data[0] : json.data;
    };

    const fetchProducts = async () => {
        const res = await fetch(`${API_BASE}?category_id=${categoryId}&enriched=true&limit=100`, { credentials: "same-origin" });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || "Failed to load products");
        return json.data?.items || json.data || [];
    };

    const renderHero = (cat) => {
        document.querySelector('.hero .name').textContent = cat.name || 'Unknown Category';
        document.querySelector('.hero .description').textContent = cat.description || 'No description available.';
        document.querySelector('.meta .products-count').textContent = `${cat.product_count ?? 0} products`;
        document.querySelector('.meta .stock-info').textContent = cat.has_stock ? 'In Stock' : 'Out of Stock';

        const min = ((cat.min_price_cents ?? 0) / 100).toFixed(2);
        const max = ((cat.max_price_cents ?? 0) / 100).toFixed(2);
        const avg = ((cat.avg_price_cents ?? 0) / 100).toFixed(2);
        document.querySelector('.pricing').textContent = `Price: $${min} – $${max} (avg $${avg})`;

        const f = cat.flavor_summary;
        if (f) {
            const tags = f.common_tags?.length ? `<br><strong>Popular Tags:</strong> ${f.common_tags.join(', ')}` : '';
            document.querySelector('.flavor-profile').innerHTML = `
                <strong>Flavor Profile:</strong><br>
                Sweet ${ (f.avg_sweetness ?? 0).toFixed(1) }/10 • 
                Bitter ${ (f.avg_bitterness ?? 0).toFixed(1) }/10 • 
                Strong ${ (f.avg_strength ?? 0).toFixed(1) }/10${tags}
            `;
        }
    };

    const renderProduct = (p) => {
        const price = ((p.price_cents ?? 0) / 100).toFixed(2);
        const inStock = (p.available_stock ?? 0) > 0;
        const tags = (p.flavor_profile?.tags || []).slice(0,3).map(t=>`<span class="tag">${t}</span>`).join('');

        const imgHtml = p.image_url && p.image_url.trim() && !p.image_url.includes('null')
            ? `<img src="${p.image_url}" alt="${p.name}" loading="lazy">`
            : '';

        return `
            <div class="product-card ${!inStock ? 'out' : ''}">
                ${imgHtml}
                <div class="badge ${inStock ? 'in' : 'out'}">
                    ${inStock ? (p.available_stock + ' left') : 'Sold Out'}
                </div>
                ${ (p.units_sold ?? 0) > 150 ? '<div class="bestseller">Bestseller</div>' : '' }
                <h3>${p.name}</h3>
                <p>${ (p.description || '').substring(0,130) }...</p>
                ${ tags ? `<div class="tags">${tags}</div>` : '' }
                <div class="footer">
                    <strong class="price">$${price}</strong>
                    <button class="add-btn" data-id="${p.id}" ${!inStock ? 'disabled' : ''}>
                        ${inStock ? 'Add to Cart' : 'Unavailable'}
                    </button>
                </div>
            </div>
        `;
    };

    const renderProducts = (products) => {
        const grid = document.getElementById('products-grid');
        if (!products?.length) {
            grid.innerHTML = '<div>No products found in this category.</div>';
            return;
        }
        grid.innerHTML = products.map(renderProduct).join('');
    };

    document.addEventListener('DOMContentLoaded', async () => {
        await updateCartCount();
        try {
            const [category, products] = await Promise.all([fetchCategory(), fetchProducts()]);
            renderHero(category);
            renderProducts(products);
        } catch (err) {
            console.error(err);
            document.querySelector('.hero').innerHTML = '<h1 style="color:#e74c3c;">Failed to load category</h1>';
            document.getElementById('products-grid').innerHTML = '<div style="color:#e74c3c;">Error loading data</div>';
        }
    });
</script>